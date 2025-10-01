<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Vehicles;
use App\Models\AddOn;
use App\Models\AddOnReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Log;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\StripeClient;
use App\Models\SystemSetting;
use Validator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingReceipt;
use App\Mail\OwnerBookingAlert;


class BookingController extends Controller
{
  public function index()
    {
        $bookings = Booking::with(['customer', 'vehicle'])->get();
        return view('admin.booking.index', compact('bookings'));
    }

    public function show(Booking $booking)
    {
        $booking->load(['customer', 'vehicle', 'addOns']); // eager load relations
        return view('admin.booking.show', compact('booking'));
    }

    /**
     * Store a new booking (customer + booking + add_on_reservations).
     */
public function store(Request $request)
{
    // dd($request);
    $validated = $request->validate([
        'vehicle_id'         => ['required','exists:vehicles,id'],
        'rental_unit'        => ['required','in:day,week,month'],
        'rental_quantity'    => ['required','integer','min:1'],
        'rental_start_date'  => ['required','date'],
        'extra_days'         => ['nullable','integer','min:0'],

        'name'               => ['required','string','max:255'],
        'email'              => ['nullable','email','max:255'],
        'phone'              => ['nullable','string','max:50'],
        'country'            => ['nullable','string','max:100'],

        // NEW: accept nested add-on structure
        'add_ons'               => ['nullable','array'],
        'add_ons.*.type'        => ['nullable','in:day,week,month'],
        'add_ons.*.quantity'    => ['nullable','integer','min:1'],
        'add_ons.*.start_date'  => ['nullable','date'],
        'add_ons.*.end_date'    => ['nullable','date'],
        'add_ons.*.extra_days'    => ['nullable','integer','min:0'],
        'add_ons.*.total'       => ['nullable','numeric','min:0'], // ignored for security; we compute server-side
    ]);

    DB::beginTransaction();

    try {
        /* ---------- 1) Customer ---------- */
        if (!empty($validated['email'])) {
            $customer = Customer::firstOrCreate(
                ['email' => $validated['email']],
                ['name' => $validated['name'], 'phone' => $validated['phone'] ?? null, 'country' => $validated['country'] ?? null]
            );
        } elseif (!empty($validated['phone'])) {
            $customer = Customer::firstOrCreate(
                ['phone' => $validated['phone']],
                ['name' => $validated['name'], 'email' => $validated['email'] ?? null, 'country' => $validated['country'] ?? null]
            );
        } else {
            $customer = Customer::create([
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'country' => $validated['country'] ?? null,
            ]);
        }

        /* ---------- 2) Vehicle, dates, pricing ---------- */
        $vehicle    = Vehicles::findOrFail($validated['vehicle_id']);
        $unit       = $validated['rental_unit'];                   // day|week|month
        $qty        = (int) $validated['rental_quantity'];
        $extraDays  = (int) ($validated['extra_days'] ?? 0);
        $start      = \Carbon\Carbon::parse($validated['rental_start_date'])->startOfDay();

        // Inclusive end date (end is the last day charged)
        switch ($unit) {
            case 'day':
                $end = $start->copy()->addDays($qty - 1 + $extraDays);
                break;
            case 'week':
                $end = $start->copy()->addDays(($qty * 7) - 1 + $extraDays);
                break;
            case 'month':
                $end = $start->copy()->addMonths($qty)->subDay()->addDays($extraDays);
                break;
        }

        $priceField        = 'rental_price_' . $unit;
        $vehicleUnitPrice  = (float) ($vehicle->{$priceField} ?? 0.0);

        $basePrice = $vehicleUnitPrice * $qty;
        $extraDaysPrice = 0.0;
        if ($extraDays > 0) {
            if     ($unit === 'day')   $extraDaysPrice = $vehicleUnitPrice * $extraDays;
            elseif ($unit === 'week')  $extraDaysPrice = ($vehicleUnitPrice / 7)  * $extraDays;
            elseif ($unit === 'month') $extraDaysPrice = ($vehicleUnitPrice / 30) * $extraDays;
        }

        /* ---------- helper to compute add-on units from its own dates ---------- */
        $unitsBetween = function(\Carbon\Carbon $s, \Carbon\Carbon $e, string $u): int {
            $days = $s->diffInDays($e) + 1; // inclusive
            if ($u === 'day')   return $days;
            if ($u === 'week')  return (int) ceil($days / 7);
            if ($u === 'month') return (int) ceil($days / 30);
            return 0;
        };

        /* ---------- 3) Add-ons ---------- */
        $addonsTotal        = 0.0;
        $addOnReservations  = [];

        // Support BOTH shapes:
        // (A) nested: add_ons[ID][type|quantity|start_date|end_date]
        // (B) legacy: add_ons[ID] = quantity
        $rawAddOns = $request->input('add_ons', []);
        // $rawAddOns = $validated['add_ons'] ?? [];

        foreach ($rawAddOns as $addOnId => $payload) {
            // Normalize to a structure
            if (is_array($payload)) {
                $aType   = $payload['type']        ?? null;                   // day|week|month
                $aQty    = (int) ($payload['quantity'] ?? 0);
                $aStart  = !empty($payload['start_date']) ? \Carbon\Carbon::parse($payload['start_date'])->startOfDay() : null;
                $aEnd    = !empty($payload['end_date'])   ? \Carbon\Carbon::parse($payload['end_date'])->startOfDay()   : null;
            } else {
                // legacy integer -> treat as quantity, reuse main booking unit & dates
                $aType  = $unit;
                $aQty   = (int) $payload;
                $aStart = $start->copy();
                $aEnd   = $end->copy();
            }

            if ($aQty <= 0) {
                continue;
            }

            /** @var AddOn|null $addOn */
            $addOn = AddOn::lockForUpdate()->find($addOnId);
            if (!$addOn) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => "Add-on not found: {$addOnId}"], 422);
            }
            if ($addOn->qty_total < $aQty) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => "Not enough stock for add-on ID {$addOnId}."], 422);
            }

            // If user didn't give their own dates, fall back to main booking date window
            if (!$aStart || !$aEnd) { $aStart = $start->copy(); $aEnd = $end->copy(); }
            if (!$aType)            { $aType = $unit; }

            // Unit price by the add-on’s own type
            $aPriceField = 'price_' . $aType;         // price_day|price_week|price_month
            $unitPrice   = (float) ($addOn->{$aPriceField} ?? 0.0);

            // Units (per its own type) across its own dates
            $units = $unitsBetween($aStart, $aEnd, $aType);
            $priceTotal = $units * $unitPrice * $aQty;

            $addonsTotal += $priceTotal;

        $addOnReservations[] = [
    'add_on_id'   => (int) $addOn->id,
    'qty'         => (int) $aQty,
    'price_total' => (int) round($priceTotal),
    'start_date'  => $aStart->toDateString(),
    'end_date'    => $aEnd->toDateString(),
    'extra_days'  => isset($payload['extra_days']) ? (int)$payload['extra_days'] : 0, // ← now preserved
];


        }

        /* ---------- 4) Totals & Booking ---------- */
        $totalPrice = (int) round($basePrice + $extraDaysPrice + $addonsTotal);

        $booking = Booking::create([
            'vehicle_id'   => $vehicle->id,
            'customer_id'  => $customer->id,
            'start_date'   => $start->toDateString(),
            'end_date'     => $end->toDateString(),
            'type'         => 'rental',
            'status'       => 'pending',
            'reference'    => 'BK-' . strtoupper(Str::random(8)),
            'notes'        => json_encode([
                'rental_unit'        => $unit,
                'rental_quantity'    => $qty,
                'extra_days'         => $extraDays,
                'vehicle_unit_price' => $vehicleUnitPrice,
                'addons_summary'     => $addOnReservations,
            ]),
            'total_price'  => $totalPrice,
            'extra_days'   => $extraDays,
        ]);



        foreach ($addOnReservations as $r) {
            AddOnReservation::create([
                'add_on_id'   => $r['add_on_id'],
                'booking_id'  => $booking->id,
                'qty'         => $r['qty'],
                'price_total' => $r['price_total'],
                'start_date'  => $r['start_date'],
                'end_date'    => $r['end_date'],
                   'extra_days'  => $r['extra_days'] ?? 0,
            ]);
        }

        DB::commit();

        return response()->json([
            'success'     => true,
            'booking_id'  => $booking->id,
            'total_price' => $totalPrice,
        ]);
    } catch (\Throwable $e) {
        DB::rollBack();
        report($e);
        return response()->json(['success' => false, 'message' => 'Failed to create booking.'], 500);
    }
}



/**
 * Program the mailer from DB settings at runtime.
 */
private function configureMailerFromSettings(?SystemSetting $settings = null): bool
{
    $settings = $settings ?: SystemSetting::first();
    if (!$settings || !$settings->mail_enabled) return false;

    if (!$settings->mail_host || !$settings->mail_port || !$settings->mail_username || !$settings->mail_password) {
        return false;
    }

    Config::set('mail.default', 'smtp');
    Config::set('mail.from.address', $settings->mail_from_address ?: config('mail.from.address'));
    Config::set('mail.from.name', $settings->mail_from_name ?: config('mail.from.name'));
    Config::set('mail.mailers.smtp', [
        'transport'  => 'smtp',
        'host'       => $settings->mail_host,
        'port'       => (int) $settings->mail_port,
        'encryption' => $settings->mail_encryption ?: null, // 'tls'|'ssl'|null
        'username'   => $settings->mail_username,
        'password'   => $settings->mail_password,
        'timeout'    => null,
        'auth_mode'  => null,
    ]);

    return true;
}

/**
 * Decide where the owner copy goes.
 */
private function resolveOwnerEmail(?SystemSetting $settings = null): ?string
{
    $settings = $settings ?: SystemSetting::first();

    if (!empty($settings?->mail_owner_address)) {
        return $settings->mail_owner_address;
    }

    return $settings?->mail_from_address
        ?: (config('mail.from.address') ?: env('OWNER_EMAIL'));
}

/**
 * Send booking emails (customer + owner).
 */
private function sendBookingEmails(\App\Models\Booking $booking, float $paidNow): void
{
    try {
        $settings = SystemSetting::first();
        $this->configureMailerFromSettings($settings);
        $ownerEmail = $this->resolveOwnerEmail($settings);

        // customer
        if ($booking->customer?->email) {
            Mail::to($booking->customer->email)
                ->send(new BookingReceipt($booking->loadMissing('vehicle','customer'), $paidNow));
        }

        // owner
        if ($ownerEmail) {
            Mail::to($ownerEmail)
                ->send(new OwnerBookingAlert($booking->loadMissing('vehicle','customer'), $paidNow));
        }
    } catch (\Throwable $e) {
        Log::warning('Booking emails failed', [
            'booking_id' => $booking->id ?? null,
            'error'      => $e->getMessage(),
        ]);
    }
}










public function payWithStripe(Request $request, Booking $booking)
{
    $request->validate(['payment_method_id' => 'required|string']);
    $booking->loadMissing('customer');

    $settings = \App\Models\SystemSetting::first();
    if (!$settings || !$settings->stripe_enabled) {
        return response()->json(['success' => false, 'message' => 'Stripe is not configured or disabled.'], 500);
    }

    $secret   = $settings->stripe_secret;
    $currency = strtolower($booking->currency ?: 'zar');
    $amount   = (int) round($booking->total_price * 100);
    $receipt  = optional($booking->customer)->email;

    if ($amount < 50) {
        return response()->json(['success' => false, 'message' => 'Amount too low to charge (minimum R0.50).'], 422);
    }

    $stripe = new \Stripe\StripeClient($secret);

    try {
        if (!$booking->stripe_payment_intent_id) {
            // Create PI
            $pi = $stripe->paymentIntents->create([
                'amount'               => $amount,
                'currency'             => $currency,
                'payment_method'       => $request->input('payment_method_id'),
                'confirmation_method'  => 'manual',
                'confirm'              => true,
                'payment_method_types' => ['card'],
                'receipt_email'        => $receipt,
                'description'          => "Payment for Booking #{$booking->id}",
                'metadata'             => [
                    'booking_id' => (string) $booking->id,
                    'vehicle_id' => (string) $booking->vehicle_id,
                    'type'       => 'booking',
                ],
                'expand'               => ['payment_method', 'charges.data.balance_transaction'],
            ]);
            $booking->update(['stripe_payment_intent_id' => $pi->id]);

            // ❌ Do NOT send emails here — payment may require 3DS or may fail.
        } else {
            // Confirm existing PI if needed
            $pi = $stripe->paymentIntents->retrieve(
                $booking->stripe_payment_intent_id,
                ['expand' => ['payment_method', 'charges.data.balance_transaction']]
            );

            if ($pi->status !== 'succeeded') {
                $pi = $stripe->paymentIntents->confirm($pi->id, [
                    'payment_method' => $request->input('payment_method_id'),
                ]);
            }
        }

        if ($pi->status === 'requires_action' && ($pi->next_action->type ?? null) === 'use_stripe_sdk') {
            $booking->update(['status' => 'processing']);
            return response()->json([
                'success'                      => false,
                'requires_action'              => true,
                'payment_intent_client_secret' => $pi->client_secret,
            ]);
        }

        if ($pi->status === 'succeeded') {
            $pm     = $pi->payment_method ?? null;
            $card   = $pm->card ?? null;
            $charge = $pi->charges->data[0] ?? null;

            $booking->update([
                'status'                   => 'completed',
                'payment_method'           => 'stripe',     // ✅ ensure saved
                'payment_status'           => 'succeeded',
                'currency'                 => $pi->currency,
                'paid_at'                  => now(),
                'stripe_payment_intent_id' => $pi->id,
                'stripe_payment_method_id' => $pm?->id,
                'stripe_charge_id'         => $charge?->id,
                'card_brand'               => $card?->brand,
                'card_last4'               => $card?->last4,
                'card_exp_month'           => $card?->exp_month,
                'card_exp_year'            => $card?->exp_year,
                'receipt_url'              => $charge?->receipt_url,
            ]);

            // ✅ Send emails ONLY after success
            $this->sendBookingEmails($booking, (float) $booking->total_price);

            return response()->json([
                'success'    => true,
                'message'    => 'Payment successful.',
                'booking_id' => $booking->id,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Payment could not be completed. Status: ' . $pi->status,
        ], 422);

    } catch (\Stripe\Exception\ApiErrorException $e) {
        \Log::error('Stripe error on booking payment', [
            'booking_id' => $booking->id,
            'code'       => $e->getError()?->code,
            'param'      => $e->getError()?->param,
            'message'    => $e->getMessage(),
        ]);
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    } catch (\Throwable $e) {
        \Log::error('Booking payWithStripe crashed', ['booking_id' => $booking->id, 'message' => $e->getMessage()]);
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}



public function initPayfastBooking(Request $request, $bookingId)
{
    if (! $request->expectsJson()) {
        $request->headers->set('Accept', 'application/json');
    }

    // load booking
    $booking = Booking::with(['vehicle', 'customer'])->find($bookingId);
    if (! $booking) {
        return response()->json([
            'success' => false,
            'message' => 'Booking not found.',
        ], 404);
    }

    // ---- Load PayFast settings from DB ----
    $pf = SystemSetting::where('payfast_enabled', true)->first();
    if (! $pf) {
        return response()->json([
            'success' => false,
            'message' => 'PayFast is not configured.',
        ], 500);
    }

    // ---- Determine action URL (sandbox vs live) ----
    $isTest = (bool) ($pf->payfast_test_mode ?? true);
    $action = $isTest
        ? 'https://sandbox.payfast.co.za/eng/process'
        : ($pf->payfast_live_url ?? 'https://www.payfast.co.za/eng/process');

    // ---- Calculate booking amount ----
    $notes = json_decode($booking->notes, true) ?? [];
    $unit = $notes['rental_unit'] ?? 'day';
    $qty = (int) ($notes['rental_quantity'] ?? 1);
    $extraDays = (int) ($notes['extra_days'] ?? 0);
    $vehicleUnitPrice = (float) ($notes['vehicle_unit_price'] ?? 0);

    $basePrice = $vehicleUnitPrice * $qty;
    $extraDaysPrice = 0;

    if ($extraDays > 0) {
        if ($unit === 'day') {
            $extraDaysPrice = $vehicleUnitPrice * $extraDays;
        } elseif ($unit === 'week') {
            $extraDaysPrice = ($vehicleUnitPrice / 7) * $extraDays;
        } elseif ($unit === 'month') {
            $extraDaysPrice = ($vehicleUnitPrice / 30) * $extraDays;
        }
    }

    $addonsSummary = $notes['addons_summary'] ?? [];
    $addonsTotal = collect($addonsSummary)->sum('price_total');

    $totalAmount = $basePrice + $extraDaysPrice + $addonsTotal;
    $amount = number_format($totalAmount, 2, '.', ''); // Always 2 decimals

    // ---- Payment identifiers & URLs (make sure returnUrl exists before using) ----
    $m_payment_id = 'book-' . $booking->id . '-' . Str::random(6);

    // safe fallback if vehicle missing
    if ($booking->vehicle && isset($booking->vehicle->id)) {
        $returnUrl = route('fleet.view', ['vehicle' => $booking->vehicle->id]);
    } else {
        $returnUrl = url('/'); // fallback
    }

    $cancelUrl = url('/payment/cancel');
    $notifyUrl = route('payfast.booking.notify', [], true); // ✅ absolute URL

    // ---- Build fields using the correct DB column names ----
    $fields = [
        'merchant_id'   => $pf->payfast_merchant_id,
        'merchant_key'  => $pf->payfast_merchant_key,
        'return_url'    => $returnUrl,
        'cancel_url'    => $cancelUrl,
        'notify_url'    => $notifyUrl,                      // ✅
        'm_payment_id'  => $m_payment_id,
        'amount'        => $amount,
        'item_name'     => substr('Deposit for booking ' . ($booking->reference ?? $booking->id), 0, 100),
        'name_first'    => $request->input('name', $booking->customer->name ?? 'Guest'),
        'email_address' => $request->input('email', $booking->customer->email ?? 'test@example.com'),
        'custom_str1'   => (string) $booking->id,
    ];

    // ---- Generate signature (include passphrase only for signature if present) ----
    $sigData = $fields;
    if (! empty($pf->payfast_passphrase)) {
        $sigData['passphrase'] = $pf->payfast_passphrase;
    }

    ksort($sigData);
    $pairs = [];
    foreach ($sigData as $key => $val) {
        if ($val === '' || $val === null) {
            continue;
        }
        $pairs[] = $key . '=' . urlencode(trim($val));
    }
    $fields['signature'] = md5(implode('&', $pairs));

    // ---- Update booking ----

    $booking->update([
        'payment_method'     => 'payfast',   // method user chose; final success set in ITN
        'payfast_payment_id' => $m_payment_id,
        'deposit_expected'   => $totalAmount,
    ]);

    return response()->json(['success' => true, 'action' => $action, 'fields' => $fields]);
}



public function payfastBookingReturn(Request $request)
{
    $bookingId = $request->input('custom_str1');
    $booking = Booking::with('vehicle')->find($bookingId);

    return redirect()
        ->route('fleet.view', ['vehicle' => $booking->vehicle->id])
        ->with('payfast_success', 'Your booking deposit payment was successful!');
}


public function payfastBookingCancel(Request $request)
{
    return view('payments.cancel'); // same view as purchases
}

public function payfastBookingNotify(Request $request)
{
    // Make sure this route is CSRF-exempt
    Log::info('💡 PayFast Booking ITN received', $request->all());

    $bookingId = $request->input('custom_str1');
    $booking   = \App\Models\Booking::with(['vehicle','customer'])->find($bookingId);

    if (!$booking) {
        Log::error('PayFast Booking Notify: Booking not found', ['booking_id' => $bookingId]);
        return response('Booking not found', 404);
    }

    // TODO: Verify signature, amount, and source IP per PayFast docs
    $paymentStatus = strtolower($request->input('payment_status', ''));
    $amountPaid    = (float) $request->input('amount_gross', 0);
    $pfPaymentId   = $request->input('pf_payment_id');
    $mPaymentId    = $request->input('m_payment_id');

    if ($paymentStatus === 'complete' && $amountPaid > 0) {
        $booking->update([
            'status'             => 'completed',
            'payment_method'     => 'payfast',   // ✅ ensure saved
            'payment_status'     => 'succeeded',
            'paid_at'            => now(),
            'payfast_payment_id' => $mPaymentId ?: $booking->payfast_payment_id,
            // optionally store $pfPaymentId in a generic gateway ref column
        ]);

        Log::info('✅ PayFast Booking ITN: marked completed', [
            'booking_id' => $booking->id,
            'amount'     => $amountPaid,
        ]);

        // ✅ Send emails after success
        $this->sendBookingEmails($booking, (float) $amountPaid);

    } else {
        Log::warning('⚠️ PayFast Booking ITN: not complete', [
            'booking_id'    => $booking->id,
            'paymentStatus' => $paymentStatus,
            'amount_gross'  => $request->input('amount_gross'),
        ]);
    }

    return response('OK');
}





}
