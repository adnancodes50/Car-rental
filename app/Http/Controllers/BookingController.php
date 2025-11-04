<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Category;
use App\Models\Location;
use App\Models\Equipment;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Log;
use App\Mail\BookingReceipt;
use App\Mail\BookingStatusUpdate;
use App\Mail\OwnerBookingAlert;
use Stripe\StripeClient;

class BookingController extends Controller
{
    /* -------------------------------------------------
     |  List all bookings
     |--------------------------------------------------*/
  public function index()
{


    $bookings = Booking::with(['customer', 'location', 'category', 'equipment' ])
           ->orderByRaw('YEAR(start_date) ASC')
           ->orderByRaw('MONTH(start_date) ASC')
           ->orderBy('start_date', 'ASC')
           ->Get();
    // Return the view with the bookings data
    return view('admin.booking.index', compact('bookings'));
}


    /* -------------------------------------------------
     |  Show booking details
     |--------------------------------------------------*/
    public function show(Booking $booking)
    {
        $booking->load(['customer', 'location', 'category', 'equipment']);
        return view('admin.booking.show', compact('booking'));
    }


    /* -------------------------------------------------
     |  Store a new booking
     |--------------------------------------------------*/
    public function store(Request $request)
    {
        $validated = $request->validate([
            'location_id'    => ['required', 'exists:locations,id'],
            'category_id'    => ['required', 'exists:categories,id'],
            'equipment_id'   => ['nullable', 'exists:equipment,id'],
            'rental_unit'    => ['required', 'in:day,week,month'],
            'rental_quantity'=> ['required', 'integer', 'min:1'],
            'rental_start_date' => ['required', 'date'],
            'extra_days'     => ['nullable', 'integer', 'min:0'],
            'stock_quantity' => ['nullable', 'integer', 'min:1'],

            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email:rfc,filter', 'max:255'],
            'phone'    => ['required', 'regex:/^\\+?[0-9]{1,4}(?:[\\s-]?[0-9]{2,4}){2,4}$/'],
            'country'  => ['nullable', 'string', 'max:100'],
        ]);

        $lockKey = sprintf(
            'lock:booking:%s:%s:%s',
            $request->input('equipment_id') ?? 'cat' . $request->input('category_id'),
            strtolower(trim($request->input('email'))),
            Carbon::parse($request->input('rental_start_date'))->toDateString()
        );

        return Cache::lock($lockKey, 10)->block(5, function () use ($validated, $request) {
            DB::beginTransaction();

            try {
                /* ---------- 1) Customer ---------- */
                $customer = Customer::firstOrCreate(
                    ['email' => $validated['email']],
                    [
                        'name' => $validated['name'],
                        'phone' => $validated['phone'],
                        'country' => $validated['country'] ?? null,
                    ]
                );

                /* ---------- 2) Category & Pricing ---------- */
                $category = Category::findOrFail($validated['category_id']);
                $location = Location::findOrFail($validated['location_id']);
                $equipment = !empty($validated['equipment_id'])
                    ? Equipment::findOrFail($validated['equipment_id'])
                    : null;

                $unit = $validated['rental_unit']; // day, week, month
                $qty  = (int) $validated['rental_quantity'];
                $extraDays = (int) ($validated['extra_days'] ?? 0);
                $reservedUnits = max(1, (int) ($validated['stock_quantity'] ?? $qty));
                $start = Carbon::parse($validated['rental_start_date'])->startOfDay();

                switch ($unit) {
                    case 'day':
                        $end = $start->copy()->addDays($qty - 1 + $extraDays);
                        $pricePerUnit = $category->daily_price;
                        break;
                    case 'week':
                        $end = $start->copy()->addDays(($qty * 7) - 1 + $extraDays);
                        $pricePerUnit = $category->weekly_price;
                        break;
                    case 'month':
                        $end = $start->copy()->addMonths($qty)->subDay()->addDays($extraDays);
                        $pricePerUnit = $category->monthly_price;
                        break;
                    default:
                        throw new \InvalidArgumentException("Unsupported rental unit [$unit]");
                }

                if ($equipment) {
                    $baseStock = $equipment->stocks()
                        ->where('location_id', $location->id)
                        ->value('stock');

                    if ($baseStock === null) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Selected location does not have stock for this item.',
                        ], 422);
                    }

                    $overlapBookings = Booking::where('equipment_id', $equipment->id)
                        ->where('location_id', $location->id)
                        ->whereNotIn('status', ['cancelled'])
                        ->where(function ($query) use ($start, $end) {
                            $startDate = $start->toDateString();
                            $endDate = $end->toDateString();

                            $query->whereBetween('start_date', [$startDate, $endDate])
                                ->orWhereBetween('end_date', [$startDate, $endDate])
                                ->orWhere(function ($inner) use ($startDate, $endDate) {
                                    $inner->where('start_date', '<=', $startDate)
                                        ->where('end_date', '>=', $endDate);
                                });
                        })
                        ->lockForUpdate()
                        ->get();

                    $alreadyReserved = $overlapBookings->sum(function ($booking) {
                        return (int) ($booking->booked_stock ?? 1);
                    });

                    $availableUnits = max(0, $baseStock - $alreadyReserved);

                    if ($availableUnits < $reservedUnits) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "Only {$availableUnits} unit(s) are available for the selected dates at this location.",
                            'available_units' => $availableUnits,
                        ], 422);
                    }
                }

                $perUnitBase = $pricePerUnit * $qty;
                $extraDaysDivisor = $unit === 'week' ? 7 : ($unit === 'month' ? 30 : 1);
                $extraDaysPrice = $extraDays > 0 ? ($pricePerUnit / $extraDaysDivisor) * $extraDays : 0;
                $perUnitTotal = $perUnitBase + $extraDaysPrice;
                $totalPrice = round($perUnitTotal * $reservedUnits, 2);

                /* ---------- 3) Create booking ---------- */
                $booking = Booking::create([
                    'location_id'  => $location->id,
                    'category_id'  => $category->id,
                    'equipment_id' => $equipment?->id,
                    'customer_id'  => $customer->id,
                    'start_date'   => $start->toDateString(),
                    'end_date'     => $end->toDateString(),
                    'type'         => 'rental',
                    'status'       => 'pending',
                    'reference'    => 'BK-' . strtoupper(Str::random(8)),
                    'total_price'  => $totalPrice,
                    'extra_days'   => $extraDays,
                    'booked_stock' => $reservedUnits,
                    'notes'        => json_encode([
                        'rental_unit' => $unit,
                        'rental_quantity' => $qty,
                        'category_price' => $pricePerUnit,
                        'location' => $location->name,
                        'equipment' => $equipment?->name,
                        'units_reserved' => $reservedUnits,
                    ]),
                ]);

                DB::commit();

                $this->sendBookingCreationEmails($booking);

                return response()->json([
                    'success' => true,
                    'booking_id' => $booking->id,
                    'total_price' => $totalPrice,
                ]);
            } catch (\Throwable $e) {
                DB::rollBack();
                report($e);
                return response()->json(['success' => false, 'message' => 'Booking failed: ' . $e->getMessage()], 500);
            }
        });
    }

    /* -------------------------------------------------
     |  Email Helpers
     |--------------------------------------------------*/
    private function configureMailerFromSettings(?SystemSetting $settings = null): bool
    {
        $settings = $settings ?: SystemSetting::first();
        if (!$settings || !$settings->mail_enabled) return false;

        Config::set('mail.default', 'smtp');
        Config::set('mail.from.address', $settings->mail_from_address ?: config('mail.from.address'));
        Config::set('mail.from.name', $settings->mail_from_name ?: config('mail.from.name'));
        Config::set('mail.mailers.smtp', [
            'transport'  => 'smtp',
            'host'       => $settings->mail_host,
            'port'       => (int) $settings->mail_port,
            'encryption' => $settings->mail_encryption ?: null,
            'username'   => $settings->mail_username,
            'password'   => $settings->mail_password,
        ]);

        return true;
    }

    private function resolveOwnerEmail(?SystemSetting $settings = null): ?string
    {
        $settings = $settings ?: SystemSetting::first();
        return $settings?->mail_owner_address
            ?? $settings?->mail_from_address
            ?? config('mail.from.address')
            ?? null;
    }

    private function sendBookingCreationEmails(Booking $booking): void
    {
        try {
            $settings = SystemSetting::first();
            $this->configureMailerFromSettings($settings);
            $ownerEmail = $this->resolveOwnerEmail($settings);
            $booking->loadMissing('customer', 'location', 'category', 'equipment');

            if ($booking->customer?->email) {
                Mail::to($booking->customer->email)
                    ->send(new BookingStatusUpdate($booking, $booking->status ?? 'pending'));
            }

            if ($ownerEmail) {
                Mail::to($ownerEmail)
                    ->send(new OwnerBookingAlert($booking, 0.0));
            }
        } catch (\Throwable $e) {
            Log::warning('Booking email failed', ['booking_id' => $booking->id, 'error' => $e->getMessage()]);
        }
    }

    private function sendBookingEmails(Booking $booking, float $paidNow): void
    {
        try {
            $settings = SystemSetting::first();
            $this->configureMailerFromSettings($settings);
            $ownerEmail = $this->resolveOwnerEmail($settings);

            if ($booking->customer?->email) {
                Mail::to($booking->customer->email)
                    ->send(new BookingReceipt($booking->loadMissing('customer', 'category', 'location'), $paidNow));
            }

            if ($ownerEmail) {
                Mail::to($ownerEmail)
                    ->send(new OwnerBookingAlert($booking->loadMissing('customer', 'category', 'location'), $paidNow));
            }
        } catch (\Throwable $e) {
            Log::warning('Booking emails failed', ['booking_id' => $booking->id, 'error' => $e->getMessage()]);
        }
    }

    /* -------------------------------------------------
     |  Stripe payment (updated to new model)
     |--------------------------------------------------*/
    public function payWithStripe(Request $request, Booking $booking)
    {
        $request->validate(['payment_method_id' => 'required|string']);
        $booking->loadMissing('customer');

        $settings = SystemSetting::first();
        if (!$settings || !$settings->stripe_enabled) {
            return response()->json(['success' => false, 'message' => 'Stripe not configured.'], 500);
        }

        $stripe = new StripeClient($settings->stripe_secret);
        $currency = strtolower($booking->currency ?? 'zar');
        $amount = (int) round($booking->total_price * 100);

        try {
            $pi = $stripe->paymentIntents->create([
                'amount' => $amount,
                'currency' => $currency,
                'payment_method' => $request->input('payment_method_id'),
                'confirmation_method' => 'manual',
                'confirm' => true,
                'payment_method_types' => ['card'],
                'description' => "Booking #{$booking->id}",
                'metadata' => [
                    'booking_id' => $booking->id,
                    'customer_email' => $booking->customer?->email,
                ],
            ]);

            if ($pi->status === 'succeeded') {
                $booking->update([
                    'status' => 'confirmed',
                    'payment_status' => 'succeeded',
                    'paid_at' => now(),
                    'payment_method' => 'stripe',
                ]);
                $this->sendBookingEmails($booking, (float) $booking->total_price);
                return response()->json(['success' => true, 'message' => 'Payment successful.']);
            }

            return response()->json(['success' => false, 'message' => 'Payment requires action.'], 422);
        } catch (\Throwable $e) {
            Log::error('Stripe payment error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
