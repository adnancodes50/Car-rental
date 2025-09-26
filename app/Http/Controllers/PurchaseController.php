<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\Customer;
use App\Models\PayfastSetting;
use Log;
use Str;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Illuminate\Support\Facades\Mail;
use App\Mail\PurchaseReceipt;
use App\Mail\OwnerPurchaseAlert;


class PurchaseController extends Controller
{
    /**
     * Store a new purchase
     */
    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'country' => 'required|string|max:100',
            'total_price' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string|max:255',
            'deposit_paid' => 'nullable|numeric|min:0',
        ]);

        // Create or find the customer
        $customer = Customer::firstOrCreate(
            ['email' => $request->email],
            [
                'name' => $request->name,
                'phone' => $request->phone,
                'country' => $request->country,
            ]
        );

        // Create the purchase
        $purchase = Purchase::create([
            'customer_id' => $customer->id,
            'vehicle_id' => $request->vehicle_id,
            'total_price' => $request->total_price,
            'payment_method' => $request->payment_method,
            'deposit_paid' => $request->deposit_paid ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'purchase_id' => $purchase->id,
            'message' => 'Purchase saved successfully.'
        ]);
    }



// use App\Models\StripeSetting;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Mail;
// use App\Mail\PurchaseReceipt;
// use App\Mail\OwnerPurchaseAlert;

public function payWithStripe(Request $request, $purchaseId)
{
    $request->validate([
        'payment_method_id' => 'required|string',
    ]);

    $purchase = Purchase::with(['vehicle', 'customer'])->findOrFail($purchaseId);

    // 1) Resolve deposit
    $requiredDeposit = $purchase->deposit_amount ?? $purchase->vehicle->deposit_amount ?? null;

    if (!$requiredDeposit || $requiredDeposit <= 0) {
        return response()->json([
            'success' => false,
            'message' => 'No valid deposit amount found for this purchase.',
        ], 422);
    }

    // 2) Calculate remaining deposit
    $alreadyPaid = (float) ($purchase->deposit_paid ?? 0);
    $toCharge    = max((float) $requiredDeposit - $alreadyPaid, 0);

    if ($toCharge <= 0) {
        return response()->json([
            'success' => false,
            'message' => 'Deposit already paid for this purchase.',
        ], 422);
    }

    $amountInCents = (int) round($toCharge * 100);

    // 3) Load Stripe secret from DB
    $stripe = \App\Models\StripeSetting::first();

if (!$stripe || empty($stripe->stripe_secret)) {
    return response()->json([
        'success' => false,
        'message' => 'Stripe secret key not configured in database.'
    ], 500);
}

\Stripe\Stripe::setApiKey($stripe->stripe_secret);


    try {
        // 4) Create PaymentIntent
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount'               => $amountInCents,
            'currency'             => 'zar',
            'payment_method'       => $request->payment_method_id,
            'confirmation_method'  => 'manual',
            'confirm'              => true,
            'description'          => "Deposit for Purchase #{$purchase->id}",
            'metadata'             => [
                'purchase_id' => (string) $purchase->id,
                'customer_id' => (string) ($purchase->customer_id ?? ''),
                'type'        => 'purchase_deposit',
            ],
            'payment_method_types' => ['card'],
            'receipt_email'        => $purchase->customer->email ?? null,
            'expand'               => ['payment_method', 'charges.data.balance_transaction'],
        ]);

        // 5) Handle 3D Secure / SCA
        if ($paymentIntent->status !== 'succeeded') {
            return response()->json([
                'success' => false,
                'requires_action' => true,
                'payment_intent_client_secret' => $paymentIntent->client_secret,
            ]);
        }

        // 6) Update DB with payment info
        $newDepositPaid = $alreadyPaid + $toCharge;
        $charge    = $paymentIntent->charges->data[0] ?? null;
        $pm        = $paymentIntent->payment_method ?? null;
        $card      = $pm->card ?? null;
        $receipt   = $charge->receipt_url ?? null;

        $purchase->update([
            'payment_method'             => 'stripe',
            'deposit_paid'               => $newDepositPaid,
            'payment_status'             => 'deposit_paid',
            'stripe_payment_intent_id'   => $paymentIntent->id,
            'stripe_payment_method_id'   => $pm->id ?? null,
            'stripe_charge_id'           => $charge->id ?? null,
            'card_brand'                 => $card->brand ?? null,
            'card_last4'                 => $card->last4 ?? null,
            'card_exp_month'             => $card->exp_month ?? null,
            'card_exp_year'              => $card->exp_year ?? null,
            'receipt_url'                => $receipt,
        ]);

        // 7) Send receipts (non-blocking)
        try {
            $ownerEmail = env('OWNER_EMAIL', config('mail.from.address'));

            if ($purchase->customer?->email) {
                Mail::to($purchase->customer->email)
                    ->send(new PurchaseReceipt($purchase, $toCharge));
            }
            if ($ownerEmail) {
                Mail::to($ownerEmail)
                    ->send(new OwnerPurchaseAlert($purchase, $toCharge));
            }
        } catch (\Throwable $mailErr) {
            \Log::warning('Email send failed after Stripe payment', [
                'purchase_id' => $purchase->id,
                'error'       => $mailErr->getMessage(),
            ]);
        }

        return response()->json([
            'success'      => true,
            'message'      => 'Deposit payment successful.',
            'purchase_id'  => $purchase->id,
            'paid'         => $toCharge,
            'deposit_due'  => max($requiredDeposit - $newDepositPaid, 0),
            'receipt_url'  => $receipt,
        ]);

    } catch (\Throwable $e) {
    \Log::error('Stripe payment error', [
        'purchase_id' => $purchase->id,
        'error'       => $e->getMessage(),
    ]);

    return response()->json([
        'success' => false,
        'message' => $e->getMessage(),
    ], 500);
}

}



// use App\Models\PayfastSetting;

public function initPayfast(Request $request, Purchase $purchase)
{
    $purchase->load('vehicle');

    // Get settings from DB (first enabled one)
    $pf = PayfastSetting::where('enabled', true)->first();

    if (!$pf) {
        return response()->json([
            'success' => false,
            'message' => 'PayFast is not configured.',
        ], 422);
    }

    // Work out deposit logic
    $requiredDeposit = $purchase->deposit_amount ?? $purchase->vehicle->deposit_amount ?? null;
    if (!$requiredDeposit || $requiredDeposit <= 0) {
        return response()->json([
            'success' => false,
            'message' => 'No valid deposit amount found.',
        ], 422);
    }

    $alreadyPaid = (float) ($purchase->deposit_paid ?? 0);
    $toCharge = max($requiredDeposit - $alreadyPaid, 0);

    if ($toCharge <= 0) {
        return response()->json([
            'success' => false,
            'message' => 'Deposit already paid.',
        ], 422);
    }

    // Sandbox vs Live
    $action = $pf->test_mode
        ? 'https://sandbox.payfast.co.za/eng/process'
        : 'https://www.payfast.co.za/eng/process';

    $m_payment_id = 'pur-' . $purchase->id . '-' . \Str::random(6);
    $amount = number_format($toCharge, 2, '.', '');

    $returnUrl = route('fleet.view', ['vehicle' => $purchase->vehicle->id]);

    $fields = [
        'merchant_id'   => $pf->merchant_id,
        'merchant_key'  => $pf->merchant_key,
        'return_url'    => $returnUrl,
        'cancel_url'    => url('/payments/cancel'),
        'notify_url'    => route('purchase.payfast.notify'),
        'm_payment_id'  => $m_payment_id,
        'amount'        => $amount,
        'item_name'     => 'Deposit for ' . $purchase->vehicle->name,
        'name_first'    => $request->input('name', ''),
        'email_address' => $request->input('email', ''),
        'custom_str1'   => (string) $purchase->id,
    ];

    // Generate signature
    $sigData = $fields;
    if (!empty($pf->passphrase)) {
        $sigData['passphrase'] = $pf->passphrase;
    }
    ksort($sigData);
    $pairs = [];
    foreach ($sigData as $key => $val) {
        if ($val === '' || $val === null) continue;
        $pairs[] = $key . '=' . urlencode(trim($val));
    }
    $fields['signature'] = md5(implode('&', $pairs));

    // Save state
    $purchase->update([
        'payment_method'     => 'payfast',
        'payfast_payment_id' => $m_payment_id,
        'deposit_expected'   => $toCharge,
    ]);

    return response()->json([
        'success' => true,
        'action'  => $action,
        'fields'  => $fields,
    ]);
}


public function payfastReturn(Request $request)
{
    $purchaseId = $request->input('custom_str1');
    $purchase   = Purchase::with('vehicle','customer')->find($purchaseId);

    if (!$purchase) {
        return redirect()->route('fleet.index')
            ->with('error', 'Purchase not found.');
    }

    // ❗ Do NOT mutate amounts here. ITN (notify) is the source of truth.
    // Just show a friendly message. If ITN already landed, you'll see paid status.
    $message = $purchase->payment_status === 'paid'
        ? 'Your deposit payment was successful!'
        : 'We are processing your payment. You’ll receive email confirmation shortly.';

    return redirect()
        ->route('fleet.view', ['vehicle' => $purchase->vehicle->id])
        ->with('payfast_success', $message);
}






    // GET/POST when payer cancels
    public function payfastCancel(Request $request)
    {
        // Show a "Cancelled" page
        return view('payments.cancel');
    }

 public function payfastNotify(Request $request)
{
    Log::info('💡 PayFast ITN received', $request->all());

    $purchaseId = $request->input('custom_str1');
    $purchase   = Purchase::with(['vehicle','customer'])->find($purchaseId);

    if (!$purchase) {
        Log::error("PayFast Notify: Purchase not found", ['purchase_id' => $purchaseId]);
        return response('Purchase not found', 404);
    }

    // You can add full PayFast signature validation here if desired.
    // Skipping for brevity, but recommended for production.

    $paymentStatus = strtolower($request->input('payment_status', ''));
    $amountPaid    = (float) $request->input('amount_gross', 0);
    $pfPaymentId   = $request->input('pf_payment_id'); // PayFast payment ref
    $mPaymentId    = $request->input('m_payment_id');  // your unique ref

    if ($paymentStatus === 'complete' && $amountPaid > 0) {
        // Basic idempotency: if your table has these fields, guard against re-processing:
        // e.g., if ($purchase->last_gateway_txn_id === $pfPaymentId) { return response('OK'); }

        $currentPaid = (float) ($purchase->deposit_paid ?? 0);
        $purchase->deposit_paid       = $currentPaid + $amountPaid;
        $purchase->payment_status     = 'paid';
        $purchase->payment_method     = 'payfast';
        // Optional if your schema has them:
        $purchase->payfast_payment_id = $mPaymentId ?: $purchase->payfast_payment_id;
        $purchase->save();

        Log::info("✅ PayFast Notify: Deposit updated", [
            'purchase_id'       => $purchase->id,
            'amount_paid'       => $amountPaid,
            'new_deposit_paid'  => $purchase->deposit_paid,
        ]);

        // ---- Email customer & owner (non-blocking) ----
        try {
            $ownerEmail = env('OWNER_EMAIL', config('mail.from.address'));

            if ($purchase->customer?->email) {
                Mail::to($purchase->customer->email)
                    ->send(new PurchaseReceipt($purchase, $amountPaid));
            }

            if ($ownerEmail) {
                Mail::to($ownerEmail)
                    ->send(new OwnerPurchaseAlert($purchase, $amountPaid));
            }
        } catch (\Throwable $mailErr) {
            Log::warning('✉️ PayFast ITN: email send failed', [
                'purchase_id' => $purchase->id,
                'error'       => $mailErr->getMessage(),
            ]);
        }

    } else {
        Log::warning("⚠️ PayFast Notify: Payment not complete or amount missing", [
            'purchase_id'   => $purchase->id,
            'payment_status'=> $paymentStatus,
            'amount_gross'  => $request->input('amount_gross'),
        ]);
    }

    return response('OK');
}





}



// php artisan tinker
// >>> Mail::raw('test', fn($m) => $m->to('you@yourmail.com')->subject('SMTP OK'));
