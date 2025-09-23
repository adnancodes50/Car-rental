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
use Validator;


class BookingController extends Controller
{
    /**
     * Store a new booking (customer + booking + add_on_reservations).
     */
public function store(Request $request)
{
    $validated = $request->validate([
        'vehicle_id' => 'required|exists:vehicles,id',
        'rental_unit' => 'required|in:day,week,month',
        'rental_quantity' => 'required|integer|min:1',
        'rental_start_date' => 'required|date',
        'extra_days' => 'nullable|integer|min:0',
        'name' => 'required|string|max:255',
        'email' => 'nullable|email|max:255',
        'phone' => 'nullable|string|max:50',
        'country' => 'nullable|string|max:100',
        'add_ons' => 'nullable|array',
        'add_ons.*' => 'nullable|integer|min:0',
    ]);

    DB::beginTransaction();

    try {
        // --- 1) Customer ---
        if (!empty($validated['email'])) {
            $customer = Customer::firstOrCreate(
                ['email' => $validated['email']],
                [
                    'name' => $validated['name'],
                    'phone' => $validated['phone'] ?? null,
                    'country' => $validated['country'] ?? null,
                ]
            );
        } elseif (!empty($validated['phone'])) {
            $customer = Customer::firstOrCreate(
                ['phone' => $validated['phone']],
                [
                    'name' => $validated['name'],
                    'email' => $validated['email'] ?? null,
                    'country' => $validated['country'] ?? null,
                ]
            );
        } else {
            $customer = Customer::create([
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'country' => $validated['country'] ?? null,
            ]);
        }

        // --- 2) Vehicle, dates, pricing ---
        $vehicle = Vehicles::findOrFail($validated['vehicle_id']);
        $unit = $validated['rental_unit'];
        $qty = (int) $validated['rental_quantity'];
        $extraDays = (int) ($validated['extra_days'] ?? 0);
        $start = Carbon::parse($validated['rental_start_date']);

        // ✅ Corrected end date calculation
        switch ($unit) {
            case 'day':
                $end = (clone $start)->addDays($qty + $extraDays);
                break;

            case 'week':
                $end = (clone $start)->addWeeks($qty)->addDays($extraDays);
                break;

            case 'month':
                $end = (clone $start)->addMonths($qty)->addDays($extraDays);
                break;
        }

        $priceField = 'rental_price_' . $unit;
        $vehicleUnitPrice = (int) ($vehicle->{$priceField} ?? 0);

        // Base price (per unit)
        $basePrice = $vehicleUnitPrice * $qty;

        // Extra days price (daily equivalent if week/month)
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

        // --- 3) Add-ons ---
        $addonsTotal = 0;
        $addOnReservations = [];
        foreach (($validated['add_ons'] ?? []) as $addOnId => $selectedQty) {
            $selectedQty = (int) $selectedQty;
            if ($selectedQty <= 0) continue;

            $addOn = AddOn::lockForUpdate()->find($addOnId);
            if (!$addOn || $addOn->qty_total < $selectedQty) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "Not enough stock for add-on ID {$addOnId}."
                ], 422);
            }

            $addOnUnitPrice = (int) ($addOn->{'price_' . $unit} ?? 0);

            // Base addon price
            $priceTotal = $addOnUnitPrice * $selectedQty * $qty;

            // Extra days addon price (same daily formula)
            if ($extraDays > 0) {
                if ($unit === 'day') {
                    $priceTotal += $addOnUnitPrice * $selectedQty * $extraDays;
                } elseif ($unit === 'week') {
                    $priceTotal += ($addOnUnitPrice / 7) * $selectedQty * $extraDays;
                } elseif ($unit === 'month') {
                    $priceTotal += ($addOnUnitPrice / 30) * $selectedQty * $extraDays;
                }
            }

            $addonsTotal += $priceTotal;

            $addOnReservations[] = [
                'add_on_id' => $addOn->id,
                'qty' => $selectedQty,
                'price_total' => $priceTotal,
            ];

            $addOn->decrement('qty_total', $selectedQty);
        }

        // --- Total ---
        $totalPrice = $basePrice + $extraDaysPrice + $addonsTotal;

        // --- 4) Booking ---
        $booking = Booking::create([
            'vehicle_id' => $vehicle->id,
            'customer_id' => $customer->id,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'type' => 'rental',
            'status' => 'pending',
            'reference' => 'BK-' . strtoupper(Str::random(8)),
            'notes' => json_encode([
                'rental_unit' => $unit,
                'rental_quantity' => $qty,
                'extra_days' => $extraDays,
                'vehicle_unit_price' => $vehicleUnitPrice,
                'addons_summary' => $addOnReservations,
            ]),
            'total_price' => $totalPrice,
            'extra_days' => $extraDays,
        ]);

        foreach ($addOnReservations as $r) {
            AddOnReservation::create([
                'add_on_id' => $r['add_on_id'],
                'booking_id' => $booking->id,
                'qty' => $r['qty'],
                'price_total' => $r['price_total'],
            ]);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Booking created successfully.',
            'booking_id' => $booking->id,
            'reference' => $booking->reference,
            'redirect' => route('fleet.view', $vehicle->id),
        ]);

    } catch (\Throwable $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}




public function payWithStripe(Request $request, Booking $booking)
{
    $request->validate([
        'payment_method_id' => 'required|string',
    ]);

    if (!in_array($booking->status, ['pending', 'processing'])) {
        return response()->json([
            'success' => false,
            'message' => 'This booking cannot be paid (status: '.$booking->status.').',
        ], 422);
    }

    // Ensure we have customer's email for receipts
    $booking->loadMissing('customer');

    $stripe   = new StripeClient(config('services.stripe.secret'));
    $currency = $booking->currency ?: 'zar';
    $amount   = (int) round($booking->total_price * 100); // cents
    $receipt  = optional($booking->customer)->email;

    // Stripe requires minimum amount >= 50 (i.e., R0.50)
    if ($amount < 50) {
        return response()->json([
            'success' => false,
            'message' => 'Amount too low to charge. (Minimum R0.50)',
        ], 422);
    }

    try {
        if (!$booking->stripe_payment_intent_id) {
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
        } else {
            $pi = $stripe->paymentIntents->retrieve($booking->stripe_payment_intent_id, [
                'expand' => ['payment_method', 'charges.data.balance_transaction'],
            ]);

            if ($pi->status !== 'succeeded') {
                $pi = $stripe->paymentIntents->confirm($pi->id, [
                    'payment_method' => $request->input('payment_method_id'),
                ]);
            }
        }

        if ($pi->status === 'requires_action' && isset($pi->next_action) && $pi->next_action->type === 'use_stripe_sdk') {
            $booking->update(['status' => 'processing']);
            return response()->json([
                'success'                      => false,
                'requires_action'              => true,
                'payment_intent_client_secret' => $pi->client_secret,
            ]);
        }

        if ($pi->status === 'succeeded') {
            $pm     = $pi->payment_method ?? null;
            $card   = $pm && isset($pm->card) ? $pm->card : null;
            $charge = $pi->charges->data[0] ?? null;

            $booking->update([
                'status'                    => 'completed',
                'payment_method'            => 'stripe',
                'payment_status'            => 'succeeded',
                'currency'                  => $pi->currency,
                'paid_at'                   => now(),

                'stripe_payment_intent_id'  => $pi->id,
                'stripe_payment_method_id'  => $pm ? $pm->id : null,
                'stripe_charge_id'          => $charge ? $charge->id : null,

                'card_brand'                => $card ? $card->brand : null,
                'card_last4'                => $card ? $card->last4 : null,
                'card_exp_month'            => $card ? $card->exp_month : null,
                'card_exp_year'             => $card ? $card->exp_year : null,

                'receipt_url'               => $charge ? ($charge->receipt_url ?? null) : null,
            ]);

            return response()->json([
                'success'    => true,
                'message'    => 'Payment successful.',
                'booking_id' => $booking->id,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Payment could not be completed. Status: '.$pi->status,
        ], 422);

    } catch (ApiErrorException $e) {
        // Stripe API error (card_declined, invalid_request, etc.)
        Log::error('Stripe error on booking payment', [
            'booking_id' => $booking->id,
            'code'       => $e->getError() ? $e->getError()->code : null,
            'param'      => $e->getError() ? $e->getError()->param : null,
            'message'    => $e->getMessage(),
        ]);
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    } catch (\Throwable $e) {
        // Any other server error
        Log::error('Booking payWithStripe crashed', [
            'booking_id' => $booking->id,
            'message'    => $e->getMessage(),
        ]);
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}



public function initPayfastBooking(Request $request, $bookingId)
{
    // Force JSON response
    if (!$request->expectsJson()) {
        $request->headers->set('Accept', 'application/json');
    }

    // Load booking with vehicle and customer
    $booking = Booking::with(['vehicle', 'customer'])->find($bookingId);
    if (!$booking) {
        return response()->json([
            'success' => false,
            'message' => 'Booking not found.',
        ], 404);
    }

    // Calculate deposit / amount exactly as store method did
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

    // ---- PayFast config & endpoint ----
    $pf = config('payfast');
    $isTest = filter_var($pf['testmode'], FILTER_VALIDATE_BOOLEAN);
    $action = $isTest ? $pf['urls']['sandbox'] : $pf['urls']['live'];

    // Unique ID
    $m_payment_id = 'book-' . $booking->id . '-' . Str::random(6);

    // Format amount to 2 decimals
    $amount = number_format($totalAmount, 2, '.', '');

    $returnUrl = route('fleet.view', ['vehicle' => $booking->vehicle->id]);

    $fields = [
        'merchant_id'   => $pf['merchant_id'],
        'merchant_key'  => $pf['merchant_key'],
        'return_url'    => $returnUrl,
        'cancel_url'    => url($pf['cancel_url']),
        'notify_url'    => url($pf['notify_url']),
        'm_payment_id'  => $m_payment_id,
        'amount'        => $amount,
        'item_name'     => 'Deposit for booking ' . $booking->reference,
        'name_first'    => $request->input('name', $booking->customer->name ?? ''),
        'email_address' => $request->input('email', $booking->customer->email ?? ''),
        'custom_str1'   => (string) $booking->id,
    ];

    // Generate signature
    $sigData = $fields;
    if (!empty($pf['passphrase'])) {
        $sigData['passphrase'] = $pf['passphrase'];
    }
    ksort($sigData);
    $pairs = [];
    foreach ($sigData as $key => $val) {
        if ($val === '' || $val === null) continue;
        $pairs[] = $key . '=' . urlencode(trim($val));
    }
    $fields['signature'] = md5(implode('&', $pairs));

    $booking->update([
        'payment_method'     => 'payfast',
        'payfast_payment_id' => $m_payment_id,
        'deposit_expected'   => $totalAmount,
    ]);

    return response()->json([
        'success' => true,
        'action'  => $action,
        'fields'  => $fields,
    ]);
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
    // TODO: Verify signature, source IPs, and amount
    // Then mark booking as paid (increment deposit_paid, etc.)
    return response('OK');
}




}
