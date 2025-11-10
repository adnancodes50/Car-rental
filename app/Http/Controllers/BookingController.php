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
use Illuminate\Support\Facades\Schema;
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
            'extra_days'     => ['nullable', 'integer', 'min:0', 'max:29'],
            'stock_quantity' => ['nullable', 'integer', 'min:1'],

            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email:rfc,filter', 'max:255'],
'phone' => ['required', 'regex:/^\+?[0-9\s]+$/'],
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
                if ($unit === 'week') {
                    $extraDays = max(0, min($extraDays, 6));
                } elseif ($unit === 'month') {
                    $extraDays = max(0, min($extraDays, 29));
                } else {
                    $extraDays = 0;
                }
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

    public function initPayfastBooking(Request $request, Booking $booking)
    {
        $booking->loadMissing('customer', 'equipment', 'category', 'location');

        $settings = SystemSetting::first();
        if (!$settings || !$settings->payfast_enabled) {
            return response()->json(['success' => false, 'message' => 'PayFast is not configured.'], 422);
        }

        if (empty($settings->payfast_merchant_id) || empty($settings->payfast_merchant_key)) {
            return response()->json(['success' => false, 'message' => 'PayFast merchant details are missing.'], 422);
        }

        $amountDue = (float) ($booking->total_price ?? 0);
        if ($amountDue <= 0) {
            return response()->json(['success' => false, 'message' => 'No payable amount found for this booking.'], 422);
        }

        $action = $settings->payfast_test_mode
            ? 'https://sandbox.payfast.co.za/eng/process'
            : ($settings->payfast_live_url ?: 'https://www.payfast.co.za/eng/process');

        $mPaymentId = 'book-' . $booking->id . '-' . Str::random(6);
        $amount     = number_format($amountDue, 2, '.', '');

        $fields = [
            'merchant_id'   => $settings->payfast_merchant_id,
            'merchant_key'  => $settings->payfast_merchant_key,
            'return_url'    => route('payfast.booking.return', [], true),
            'cancel_url'    => route('payfast.booking.cancel', [], true),
            'notify_url'    => route('payfast.booking.notify', [], true),
            'm_payment_id'  => $mPaymentId,
            'amount'        => $amount,
            'item_name'     => 'Booking ' . ($booking->reference ?: '#' . $booking->id),
            'item_description' => trim('Rental booking ' . ($booking->equipment?->name ?? $booking->category?->name ?? '')),
            'name_first'    => $booking->customer?->name ?? '',
            'email_address' => $booking->customer?->email ?? '',
            'custom_str1'   => (string) $booking->id,
        ];

        if ($booking->customer?->phone) {
            $sanitizedCell = preg_replace('/\D+/', '', $booking->customer->phone);
            if ($sanitizedCell && str_starts_with($sanitizedCell, '27') && strlen($sanitizedCell) === 11) {
                $sanitizedCell = '0' . substr($sanitizedCell, 2);
            }
            if ($sanitizedCell && preg_match('/^0\d{9}$/', $sanitizedCell)) {
                $fields['cell_number'] = $sanitizedCell;
            }
        }

        $signatureFields = $fields;
        if (!empty($settings->payfast_passphrase)) {
            $signatureFields['passphrase'] = $settings->payfast_passphrase;
        }

        ksort($signatureFields);
        $pairs = [];
        foreach ($signatureFields as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $pairs[] = $key . '=' . urlencode(trim((string) $value));
        }
        $fields['signature'] = md5(implode('&', $pairs));

        $updates = [];
        if (Schema::hasColumn('bookings', 'payment_method')) {
            $updates['payment_method'] = 'payfast';
        }
        if (Schema::hasColumn('bookings', 'payment_status') && empty($booking->payment_status)) {
            $updates['payment_status'] = 'pending';
        }
        if (Schema::hasColumn('bookings', 'payfast_payment_id')) {
            $updates['payfast_payment_id'] = $mPaymentId;
        }

        if (!empty($updates)) {
            $booking->forceFill($updates)->save();
        }

        return response()->json([
            'success' => true,
            'action'  => $action,
            'fields'  => $fields,
        ]);
    }

    public function payfastBookingNotify(Request $request)
    {
        Log::info('PayFast booking ITN received', $request->all());

        $bookingId = $request->input('custom_str1');
        if (!$bookingId) {
            Log::warning('PayFast booking ITN missing booking ID.');
            return response('Missing booking reference', 400);
        }

        $booking = Booking::with(['customer', 'category', 'location', 'equipment'])->find($bookingId);
        if (!$booking) {
            Log::warning('PayFast booking ITN: booking not found', ['booking_id' => $bookingId]);
            return response('Booking not found', 404);
        }

        $paymentStatus = strtolower($request->input('payment_status', ''));
        $amountPaid    = (float) $request->input('amount_gross', 0);
        $pfPaymentId   = $request->input('pf_payment_id');
        $mPaymentId    = $request->input('m_payment_id');

        if ($paymentStatus === 'complete' && $amountPaid > 0) {
            $updates = ['status' => 'confirmed'];
            if (Schema::hasColumn('bookings', 'payment_status')) {
                $updates['payment_status'] = 'paid';
            }
            if (Schema::hasColumn('bookings', 'payment_method')) {
                $updates['payment_method'] = 'payfast';
            }
            if (Schema::hasColumn('bookings', 'paid_at')) {
                $updates['paid_at'] = now();
            }
            if (Schema::hasColumn('bookings', 'payfast_payment_id')) {
                $updates['payfast_payment_id'] = $mPaymentId ?: $pfPaymentId;
            }

            $booking->forceFill($updates)->save();
            $booking->loadMissing('customer', 'category', 'location', 'equipment');

            $this->sendBookingEmails($booking, $amountPaid);
        } elseif ($paymentStatus === 'failed') {
            if (Schema::hasColumn('bookings', 'payment_status')) {
                $booking->forceFill(['payment_status' => 'failed'])->save();
            }
        } elseif ($paymentStatus !== '') {
            if (Schema::hasColumn('bookings', 'payment_status')) {
                $booking->forceFill(['payment_status' => $paymentStatus])->save();
            }
        }

        return response('OK');
    }

    public function payfastBookingReturn(Request $request)
    {
        $bookingId = $request->input('custom_str1');
        $message = 'We have received your payment response. You will receive confirmation shortly.';

        if ($bookingId) {
            $booking = Booking::find($bookingId);
            if ($booking) {
                $status = strtolower($booking->payment_status ?? $booking->status ?? '');
                if (in_array($status, ['paid', 'succeeded', 'complete', 'confirmed'], true)) {
                    $message = 'Thank you! Your booking payment was successful.';
                }
            }
        }

        return redirect('/')->with('payfast_success', $message);
    }

    public function payfastBookingCancel(Request $request)
    {
        return redirect('/')->with(
            'payfast_cancelled',
            'You cancelled the payment. Your booking remains pending until payment is completed.'
        );
    }
}
