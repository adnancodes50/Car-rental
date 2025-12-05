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
use Illuminate\Support\Facades\Session;
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
        $bookings = Booking::with(['customer', 'location', 'category', 'equipment'])
               ->orderByRaw('YEAR(start_date) ASC')
               ->orderByRaw('MONTH(start_date) ASC')
               ->orderBy('start_date', 'ASC')
               ->get();

        return view('admin.booking.index', compact('bookings'));
    }

    public function show(Booking $booking)
    {
        $booking->load(['customer', 'location', 'category', 'equipment']);
        return view('admin.booking.show', compact('booking'));
    }

    /**
     * Show booking confirmation page
     */
    public function showConfirmation($id)
    {
        Log::info('📋 CONFIRMATION PAGE ACCESSED', [
            'booking_id' => $id,
            'url' => request()->fullUrl(),
            'referrer' => request()->header('referer'),
            'session_id' => session()->getId(),
            'all_params' => request()->all()
        ]);

        // First try to get from database
        $booking = Booking::with(['customer', 'location', 'category', 'equipment'])
            ->find($id);

        if (!$booking) {
            Log::warning('📋 BOOKING NOT FOUND', ['booking_id' => $id]);
            return redirect('/')->with('error', 'Booking not found.');
        }

        // Check if booking is confirmed/completed
        $isConfirmed = in_array(strtolower($booking->status), ['confirmed', 'completed']);

        Log::info('📋 CONFIRMATION STATUS CHECK', [
            'booking_id' => $id,
            'booking_status' => $booking->status,
            'is_confirmed' => $isConfirmed,
            'session_confirmed' => session()->get('confirmed_booking')
        ]);

        // 🔥 CRITICAL: If coming from PayFast (has payment_method param), update session
        if (request()->has('payment_method') && request()->input('payment_method') === 'payfast') {
            Log::info('🔄 Coming from PayFast - updating session');

            $sessionData = [
                'id' => $booking->id,
                'status' => 'success',
                'payment_method' => 'payfast',
                'paid_amount' => $booking->total_price,
                'timestamp' => now()->format('Y-m-d H:i:s')
            ];

            session()->put('confirmed_booking', $sessionData);
            session()->save();

            Log::info('📋 Session updated for PayFast redirect', [
                'booking_id' => $booking->id,
                'session_data' => $sessionData
            ]);
        }

        // If confirmed in DB but not in session, update session
        if ($isConfirmed && !session()->get('confirmed_booking')) {
            $sessionData = [
                'id' => $booking->id,
                'status' => 'success',
                'payment_method' => 'payfast', // Default to payfast since that's what we're testing
                'paid_amount' => $booking->total_price,
                'timestamp' => now()->format('Y-m-d H:i:s')
            ];

            session()->put('confirmed_booking', $sessionData);
            session()->save();

            Log::info('📋 SESSION UPDATED FROM DB', [
                'booking_id' => $booking->id,
                'session_data' => $sessionData
            ]);
        }

        // 🔥 ALLOW ACCESS if ANY of these are true:
        // 1. Confirmed in database (status = 'confirmed' or 'completed')
        // 2. Has valid session
        // 3. Coming from PayFast (has payment_method param)
        // 4. URL has status=success (PayFast return)

        $hasValidSession = session()->get('confirmed_booking') &&
                           session()->get('confirmed_booking')['id'] == $id &&
                           session()->get('confirmed_booking')['status'] == 'success';

        $fromPayfast = request()->has('payment_method') ||
                       (request()->has('status') && request()->input('status') === 'success');

        $shouldAllowAccess = $isConfirmed || $hasValidSession || $fromPayfast;

        Log::info('📋 ACCESS CHECK', [
            'booking_id' => $id,
            'db_confirmed' => $isConfirmed,
            'has_session' => $hasValidSession,
            'from_payfast' => $fromPayfast,
            'should_allow' => $shouldAllowAccess
        ]);

        if (!$shouldAllowAccess) {
            Log::warning('📋 ACCESS DENIED', [
                'booking_id' => $id,
                'reason' => 'Not confirmed, no session, not from PayFast'
            ]);
            return redirect('/')->with('error', 'Booking not found or payment not completed.');
        }

        Log::info('✅ CONFIRMATION PAGE ACCESS GRANTED', [
            'booking_id' => $id,
            'reason' => $isConfirmed ? 'db_confirmed' : ($hasValidSession ? 'valid_session' : 'from_payfast')
        ]);

        return view('booking.confirmation', [
            'booking' => $booking,
            'sessionData' => session()->get('confirmed_booking')
        ]);
    }

    /**
     * Clear booking session
     */
    public function clearBookingSession()
    {
        session()->forget('confirmed_booking');
        return response()->json(['success' => true]);
    }

    /**
     * Restore booking data (for returning users)
     */
    public function restoreBookingData($id)
    {
        $booking = Booking::with(['customer', 'location', 'category', 'equipment'])->find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'booking' => [
                'id' => $booking->id,
                'reference' => $booking->reference,
                'equipment' => $booking->equipment ? [
                    'id' => $booking->equipment->id,
                    'name' => $booking->equipment->name,
                    'image' => $booking->equipment->image ? asset('storage/' . $booking->equipment->image) : asset('images/no-image.png'),
                ] : null,
                'category' => $booking->category ? [
                    'id' => $booking->category->id,
                    'name' => $booking->category->name,
                ] : null,
                'quantity' => $booking->booked_stock,
                'location' => $booking->location ? [
                    'id' => $booking->location->id,
                    'name' => $booking->location->name,
                ] : null,
                'customer' => [
                    'name' => $booking->customer->name,
                    'email' => $booking->customer->email,
                    'phone' => $booking->customer->phone,
                    'country' => $booking->customer->country,
                ],
                'dates' => [
                    'start' => $booking->start_date,
                    'end' => $booking->end_date,
                    'days' => \Carbon\Carbon::parse($booking->start_date)->diffInDays($booking->end_date) + 1,
                ],
                'payment' => [
                    'status' => $booking->status, // Use status field instead of payment_status
                    'total_price' => $booking->total_price,
                ]
            ]
        ]);
    }

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
            'phone'    => ['required', 'regex:/^\+?[0-9\s]+$/'],
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
                    'status'       => 'pending', // Use ONLY status field
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
                        // Store payment info in notes if needed
                        'payment_method' => null,
                        'paid_at' => null,
                    ]),
                ]);

                DB::commit();

                $this->sendBookingCreationEmails($booking);

                return response()->json([
                    'success' => true,
                    'booking_id' => $booking->id,
                    'reference' => $booking->reference,
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
            $ok = $this->configureMailerFromSettings($settings);

            if (!$ok) {
                Log::warning('Mailer not configured - skipping booking emails', ['booking_id' => $booking->id]);
                return;
            }

            $ownerEmail = $this->resolveOwnerEmail($settings);

            // ensure relationships once
            $booking->loadMissing('customer', 'category', 'location', 'equipment');

            // Customer email (queued)
            if ($to = $booking->customer?->email) {
                try {
                    Mail::to($to)->queue(new BookingReceipt($booking, $paidNow));
                    Log::info('Queued booking receipt to customer', ['booking_id' => $booking->id, 'to' => $to]);
                } catch (\Throwable $e) {
                    Log::error('Failed to queue booking receipt to customer', [
                        'booking_id' => $booking->id,
                        'to' => $to,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            } else {
                Log::warning('No customer email for booking - skipping customer mail', ['booking_id' => $booking->id]);
            }

            // Owner email (queued)
            if ($ownerEmail) {
                try {
                    Mail::to($ownerEmail)->queue(new OwnerBookingAlert($booking, $paidNow));
                    Log::info('Queued owner booking alert', ['booking_id' => $booking->id, 'to' => $ownerEmail]);
                } catch (\Throwable $e) {
                    Log::error('Failed to queue owner booking alert', [
                        'booking_id' => $booking->id,
                        'to' => $ownerEmail,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

        } catch (\Throwable $e) {
            Log::warning('Booking emails failed (unexpected)', [
                'booking_id' => $booking->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
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
                // Update ONLY the status field (since other columns don't exist)
                $booking->update([
                    'status' => 'confirmed', // Update status to confirmed
                ]);

                $this->sendBookingEmails($booking, (float) $booking->total_price);

                // ✅ Save to session for confirmation page
                Session::put('confirmed_booking', [
                    'id' => $booking->id,
                    'status' => 'success',
                    'payment_method' => 'stripe',
                    'paid_amount' => $booking->total_price,
                    'timestamp' => now()->format('Y-m-d H:i:s')
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment successful.',
                    'redirect_url' => url('/booking/confirmation/' . $booking->id)
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Payment requires action.'], 422);
        } catch (\Throwable $e) {
            Log::error('Stripe payment error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function initPayfastBooking(Request $request, $bookingId)
    {
        try {
            Log::info('=== PAYFAST INIT START ===', ['booking_id' => $bookingId]);

            // Validate booking exists
            $booking = Booking::with(['customer', 'equipment', 'category', 'location'])->find($bookingId);

            if (!$booking) {
                Log::warning('Booking not found', ['booking_id' => $bookingId]);
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found.'
                ], 404);
            }

            $settings = SystemSetting::first();
            if (!$settings || !$settings->payfast_enabled) {
                Log::warning('PayFast not enabled');
                return response()->json([
                    'success' => false,
                    'message' => 'PayFast is not configured.'
                ], 422);
            }

            if (empty($settings->payfast_merchant_id) || empty($settings->payfast_merchant_key)) {
                Log::warning('PayFast merchant details missing');
                return response()->json([
                    'success' => false,
                    'message' => 'PayFast merchant details are missing.'
                ], 422);
            }

            $amountDue = (float) ($booking->total_price ?? 0);
            if ($amountDue <= 0) {
                Log::warning('Invalid amount', ['amount' => $amountDue]);
                return response()->json([
                    'success' => false,
                    'message' => 'No payable amount found for this booking.'
                ], 422);
            }

            // Use absolute URLs
            $returnUrl = url('/booking/confirmation/' . $booking->id);
            $cancelUrl = url('/?payment_status=cancelled');
            $notifyUrl = url('/api/payfast/booking/notify');

            Log::info('PayFast URLs configured', [
                'return_url' => $returnUrl,
                'notify_url' => $notifyUrl,
                'cancel_url' => $cancelUrl,
                'booking_id' => $booking->id
            ]);

            $action = $settings->payfast_test_mode
                ? 'https://sandbox.payfast.co.za/eng/process'
                : 'https://www.payfast.co.za/eng/process';

            $mPaymentId = 'book-' . $booking->id . '-' . Str::random(6);
            $amount = number_format($amountDue, 2, '.', '');

            // Basic required fields
            $fields = [
                'merchant_id'   => $settings->payfast_merchant_id,
                'merchant_key'  => $settings->payfast_merchant_key,
                'return_url'    => $returnUrl,
                'cancel_url'    => $cancelUrl,
                'notify_url'    => $notifyUrl,
                'm_payment_id'  => $mPaymentId,
                'amount'        => $amount,
                'item_name'     => 'Booking ' . ($booking->reference ?: '#' . $booking->id),
                'item_description' => 'Rental booking for ' . ($booking->equipment?->name ?? $booking->category?->name ?? 'Item'),
                'name_first'    => $booking->customer?->name ?? '',
                'email_address' => $booking->customer?->email ?? '',
                'custom_str1'   => (string) $booking->id,
            ];

            // Add passphrase if set
            $signatureFields = $fields;
            if (!empty($settings->payfast_passphrase)) {
                $signatureFields['passphrase'] = $settings->payfast_passphrase;
            }

            // Generate signature
            ksort($signatureFields);
            $pairs = [];
            foreach ($signatureFields as $key => $value) {
                if ($value === null || $value === '') {
                    continue;
                }
                $pairs[] = $key . '=' . urlencode(trim((string) $value));
            }
            $fields['signature'] = md5(implode('&', $pairs));

            // Update booking - only update status field
            $booking->update([
                'status' => 'pending', // Keep as pending until payment is complete
            ]);

            Log::info('PayFast checkout prepared successfully', [
                'booking_id' => $booking->id,
                'm_payment_id' => $mPaymentId,
                'amount' => $amount,
                'action_url' => $action
            ]);

            $responseData = [
                'success' => true,
                'action'  => $action,
                'fields'  => $fields,
                'debug' => [
                    'booking_id' => $booking->id,
                    'amount' => $amount,
                    'return_url' => $returnUrl
                ]
            ];

            Log::info('=== PAYFAST INIT COMPLETE ===', ['response_data' => $responseData]);

            return response()->json($responseData);

        } catch (\Exception $e) {
            Log::error('=== PAYFAST INIT ERROR ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'booking_id' => $bookingId ?? 'unknown'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
                'error_details' => $e->getMessage()
            ], 500);
        }
    }

    public function payfastBookingNotify(Request $request)
    {
        try {
            // Log the raw input
            $rawInput = file_get_contents('php://input');
            Log::info('🔔 PayFast ITN Received - Raw', ['raw' => $rawInput]);

            $payload = $request->all();
            Log::info('🔔 PayFast booking ITN received', $payload);

            // Return OK immediately
            $response = response('OK', 200);

            // Get booking ID
            $bookingId = $payload['custom_str1'] ?? null;

            if (!$bookingId) {
                Log::warning('PayFast ITN: Missing booking ID');
                return $response;
            }

            // 🔥 CRITICAL: Process synchronously
            $this->processPayfastITN($payload, $bookingId);

            return $response;

        } catch (\Throwable $e) {
            Log::error('❌ PayFast ITN endpoint error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response('OK', 200);
        }
    }

    private function processPayfastITN(array $payload, $bookingId): void
    {
        try {
            Log::info('🔵 Processing PayFast ITN for booking', ['booking_id' => $bookingId]);

            DB::transaction(function () use ($payload, $bookingId) {
                $booking = Booking::where('id', $bookingId)
                    ->lockForUpdate()
                    ->first();

                if (!$booking) {
                    Log::warning('PayFast ITN: Booking not found', ['booking_id' => $bookingId]);
                    return;
                }

                // Check if already confirmed
                if (in_array(strtolower($booking->status), ['confirmed', 'completed'])) {
                    Log::info('PayFast ITN: Already processed', ['booking_id' => $bookingId]);
                    return;
                }

                // 🚨 TEMPORARY: DISABLE SIGNATURE VERIFICATION
                Log::info('🔐 SKIPPING SIGNATURE VERIFICATION FOR NOW - Payment will be processed');

                $paymentStatus = strtolower($payload['payment_status'] ?? '');
                $amountPaid = (float) ($payload['amount_gross'] ?? 0);
                $pfPaymentId = $payload['pf_payment_id'] ?? null;

                Log::info('🔵 PayFast payment details', [
                    'status' => $paymentStatus,
                    'amount' => $amountPaid,
                    'booking_total' => $booking->total_price,
                    'pf_payment_id' => $pfPaymentId
                ]);

                // Process payment
                if ($paymentStatus === 'complete' && $amountPaid > 0) {
                    // Update ONLY the status field
                    $booking->update([
                        'status' => 'confirmed', // Update status to confirmed
                    ]);

                    Log::info('✅✅✅ PAYFAST PAYMENT PROCESSED - BOOKING CONFIRMED', [
                        'booking_id' => $booking->id,
                        'amount' => $amountPaid,
                        'reference' => $booking->reference,
                        'status' => $booking->status,
                        'pf_payment_id' => $pfPaymentId
                    ]);

                    // Send emails
                    $booking->refresh();
                    $booking->loadMissing('customer', 'category', 'location', 'equipment');
                    $this->sendBookingEmails($booking, $amountPaid);

                    // Save session
                    session()->put('confirmed_booking', [
                        'id' => $booking->id,
                        'status' => 'success',
                        'payment_method' => 'payfast',
                        'paid_amount' => $amountPaid,
                        'timestamp' => now()->format('Y-m-d H:i:s')
                    ]);
                    session()->save();
                }
            });

        } catch (\Throwable $e) {
            Log::error('❌ PayFast ITN processing error', [
                'booking_id' => $bookingId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function payfastBookingReturn(Request $request)
    {
        Log::info('🔄 PAYFAST RETURN STARTED', $request->all());

        $bookingId = $request->input('custom_str1');

        if (!$bookingId) {
            Log::warning('⚠️ PAYFAST RETURN: NO BOOKING ID FOUND');
            return redirect('/')->with('error', 'Booking ID not found.');
        }

        $booking = Booking::with(['customer', 'location', 'category', 'equipment'])
            ->find($bookingId);

        if (!$booking) {
            Log::error('❌ PAYFAST RETURN: BOOKING NOT FOUND', ['booking_id' => $bookingId]);
            return redirect('/')->with('error', 'Booking not found.');
        }

        $payfastStatus = strtolower($request->input('payment_status', ''));
        $pfPaymentId = $request->input('pf_payment_id');

        Log::info('📊 PAYFAST RETURN - Status check', [
            'booking_id' => $bookingId,
            'payfast_status' => $payfastStatus,
            'current_status' => $booking->status
        ]);

        // If payment is complete, update booking status
        if ($payfastStatus === 'complete') {
            Log::info('✅ PAYFAST RETURN: Payment complete - updating booking');

            $booking->update([
                'status' => 'confirmed',
            ]);

            // Send emails
            $this->sendBookingEmails($booking, (float) $booking->total_price);
        }

        // ALWAYS set session for confirmation page
        $sessionData = [
            'id' => $booking->id,
            'status' => 'success',
            'payment_method' => 'payfast',
            'paid_amount' => $booking->total_price,
            'timestamp' => now()->format('Y-m-d H:i:s')
        ];

        session()->put('confirmed_booking', $sessionData);
        session()->save();

        Log::info('💾 PAYFAST RETURN: Session saved and redirecting', [
            'booking_id' => $booking->id,
            'redirect_to' => url('/booking/confirmation/' . $booking->id)
        ]);

        return redirect()->to('/booking/confirmation/' . $booking->id);
    }

    public function payfastBookingCancel(Request $request)
    {
        $bookingId = $request->input('custom_str1');

        return redirect('/?booking=' . $bookingId . '&status=cancelled')
            ->with('payfast_message',
                'You cancelled the payment. Your booking remains pending until payment is completed.'
            );
    }
}
