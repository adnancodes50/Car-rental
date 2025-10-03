<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\Customer;
use App\Models\PayfastSetting;
use Log;
use Illuminate\Support\Facades\Schema;
use Str;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Illuminate\Support\Facades\Mail;
use App\Mail\PurchaseReceipt;
use App\Mail\OwnerPurchaseAlert;

use Illuminate\Support\Facades\Config;
use App\Models\SystemSetting;



class PurchaseController extends Controller
{
    /**
     * Store a new purchase
     */
  public function store(Request $request)
{
    // ✅ Same validation style as Booking (email:rfc,filter + phone regex)
    $validated = $request->validate([
        'vehicle_id'     => ['required','exists:vehicles,id'],
        'name'           => ['required','string','max:255'],
        'email'          => ['required','email:rfc,filter','max:255'],
        'phone'          => ['required','regex:/^\+?[0-9]{1,4}(?:[\s-]?[0-9]{2,4}){2,4}$/'],
        'country'        => ['required','string','max:100'],

        'total_price'    => ['required','numeric','min:0'],
        'payment_method' => ['nullable','string','max:255'],
        'deposit_paid'   => ['nullable','numeric','min:0'],
    ], [
        // Nice message like in booking flow
        'phone.regex'    => 'Use digits with optional spaces or dashes, e.g. +27 123 456 7890.',
    ]);

    // ✅ Same customer creation logic as Booking (prefer email, fallback to phone)
    if (!empty($validated['email'])) {
        $customer = Customer::firstOrCreate(
            ['email' => $validated['email']],
            [
                'name'    => $validated['name'],
                'phone'   => $validated['phone'] ?? null,
                'country' => $validated['country'] ?? null,
            ]
        );
    } elseif (!empty($validated['phone'])) {
        $customer = Customer::firstOrCreate(
            ['phone' => $validated['phone']],
            [
                'name'    => $validated['name'],
                'email'   => $validated['email'] ?? null,
                'country' => $validated['country'] ?? null,
            ]
        );
    } else {
        // Fallback (shouldn’t hit because both are required above)
        $customer = Customer::create([
            'name'    => $validated['name'],
            'email'   => $validated['email'] ?? null,
            'phone'   => $validated['phone'] ?? null,
            'country' => $validated['country'] ?? null,
        ]);
    }

    // Create the purchase
    $purchase = Purchase::create([
        'customer_id'    => $customer->id,
        'vehicle_id'     => $validated['vehicle_id'],
        'total_price'    => (float) $validated['total_price'],
        'payment_method' => $validated['payment_method'] ?? null,
        'deposit_paid'   => (float) ($validated['deposit_paid'] ?? 0),
    ]);

    return response()->json([
        'success'     => true,
        'purchase_id' => $purchase->id,
        'message'     => 'Purchase saved successfully.',
    ]);
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
 * Resolve owner email with sensible fallbacks.
 */
private function resolveOwnerEmail(?SystemSetting $settings = null): ?string
{
    $settings = $settings ?: SystemSetting::first();

    // If you added 'mail_owner_address' in system_settings, prefer it.
    if (!empty($settings?->mail_owner_address)) {
        return $settings->mail_owner_address;
    }

    // Fallback to from.address or env
    return $settings?->mail_from_address
        ?: (config('mail.from.address') ?: env('OWNER_EMAIL'));
}

/**
 * Send emails to customer and owner (safe to call in Stripe success & PayFast ITN).
 */
private function sendPurchaseEmails($purchase, float $paidNow): void
{
    try {
        $settings = SystemSetting::first();
        $this->configureMailerFromSettings($settings);
        $ownerEmail = $this->resolveOwnerEmail($settings);

        if ($purchase->customer?->email) {
            Mail::to($purchase->customer->email)
                ->send(new PurchaseReceipt($purchase, $paidNow));
        }

        if ($ownerEmail) {
            Mail::to($ownerEmail)
                ->send(new OwnerPurchaseAlert($purchase, $paidNow));
        }
    } catch (\Throwable $mailErr) {
        Log::warning('✉️ Email send failed', [
            'purchase_id' => $purchase->id ?? null,
            'error'       => $mailErr->getMessage(),
        ]);
    }
}




public function payWithStripe(Request $request, $purchaseId)
{
    $request->validate([
        'payment_method_id' => 'required|string',
    ]);

    $purchase = \App\Models\Purchase::with(['vehicle', 'customer'])->findOrFail($purchaseId);

    // 1) Deposit rules
    $requiredDeposit = $purchase->deposit_amount ?? $purchase->vehicle->deposit_amount ?? null;
    if (!$requiredDeposit || $requiredDeposit <= 0) {
        return response()->json([
            'success' => false,
            'message' => 'No valid deposit amount found for this purchase.',
        ], 422);
    }

    $alreadyPaid = (float) ($purchase->deposit_paid ?? 0);
    $toCharge    = max((float) $requiredDeposit - $alreadyPaid, 0);
    if ($toCharge <= 0) {
        return response()->json([
            'success' => false,
            'message' => 'Deposit already paid for this purchase.',
        ], 422);
    }

    $amountInCents = (int) round($toCharge * 100);

    // 2) Stripe config from DB
    $settings = SystemSetting::first();
    if (!$settings || empty($settings->stripe_secret)) {
        return response()->json([
            'success' => false,
            'message' => 'Stripe secret key not configured.',
        ], 500);
    }

    \Stripe\Stripe::setApiKey($settings->stripe_secret);

    try {
        // 3) Create + confirm PaymentIntent
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
        ]);

        // 4) Handle 3DS
       // 4) Handle 3DS
if ($paymentIntent->status !== 'succeeded') {
    return response()->json([
        'success' => false,
        'requires_action' => true,
        'payment_intent_client_secret' => $paymentIntent->client_secret,
    ]);
}

// 5) Update purchase + vehicle
$newDepositPaid = $alreadyPaid + $toCharge;
$pmId   = $paymentIntent->payment_method ?? null;
$charge = $paymentIntent->charges->data[0] ?? null;

$purchase->update([
    'payment_method'             => 'stripe',
    'deposit_paid'               => $newDepositPaid,
    'payment_status'             => 'paid', // finalize deposit
    'stripe_payment_intent_id'   => $paymentIntent->id,
    'stripe_payment_method_id'   => is_string($pmId) ? $pmId : null,
    'stripe_charge_id'           => $charge->id ?? null,
    'card_brand'                 => $charge?->payment_method_details?->card?->brand,
    'card_last4'                 => $charge?->payment_method_details?->card?->last4,
    'card_exp_month'             => $charge?->payment_method_details?->card?->exp_month,
    'card_exp_year'              => $charge?->payment_method_details?->card?->exp_year,
    'receipt_url'                => $charge?->receipt_url,
]);

// mark vehicle as sold/unavailable
if ($purchase->vehicle && in_array('status', $purchase->vehicle->getFillable() ?? [])) {
    $purchase->vehicle->status = 'sold';
    $purchase->vehicle->save();
}

// 6) Emails (customer + owner) using SMTP from DB
$this->sendPurchaseEmails($purchase, $toCharge);

// 7) Tell the frontend where to go next
return response()->json([
    'success'      => true,
    'message'      => 'Deposit payment successful.',
    'purchase_id'  => $purchase->id,
    'paid'         => $toCharge,
    'receipt_url'  => $charge?->receipt_url,
    'redirect_to'  => url('/'), // main URL
]);

    } catch (\Stripe\Exception\CardException $ce) {
        Log::error('Stripe card error', ['purchase_id' => $purchase->id, 'error' => $ce->getMessage()]);
        return response()->json(['success' => false, 'message' => $ce->getMessage()], 402);
    } catch (\Throwable $e) {
        Log::error('Stripe payment error', ['purchase_id' => $purchase->id, 'error' => $e->getMessage()]);
        return response()->json(['success' => false, 'message' => 'Payment failed. ' . $e->getMessage()], 500);
    }
}




// use App\Models\PayfastSetting;

public function initPayfast(Request $request, \App\Models\Purchase $purchase)
{
    $purchase->load('vehicle');

    $settings = SystemSetting::first();
    if (!$settings || !$settings->payfast_enabled) {
        return response()->json(['success' => false, 'message' => 'PayFast is not configured.'], 422);
    }

    $requiredDeposit = $purchase->deposit_amount ?? $purchase->vehicle->deposit_amount ?? null;
    if (!$requiredDeposit || $requiredDeposit <= 0) {
        return response()->json(['success' => false, 'message' => 'No valid deposit amount found.'], 422);
    }

    $alreadyPaid = (float) ($purchase->deposit_paid ?? 0);
    $toCharge    = max($requiredDeposit - $alreadyPaid, 0);
    if ($toCharge <= 0) {
        return response()->json(['success' => false, 'message' => 'Deposit already paid.'], 422);
    }

    $action = $settings->payfast_test_mode
        ? 'https://sandbox.payfast.co.za/eng/process'
        : ($settings->payfast_live_url ?: 'https://www.payfast.co.za/eng/process');

    $m_payment_id = 'pur-' . $purchase->id . '-' . Str::random(6);
    $amount       = number_format($toCharge, 2, '.', '');
    $returnUrl    = route('fleet.view', ['vehicle' => $purchase->vehicle->id]);

    $fields = [
        'merchant_id'   => $settings->payfast_merchant_id,
        'merchant_key'  => $settings->payfast_merchant_key,
        'return_url'    => $returnUrl,
        'cancel_url'    => url('/payments/cancel'),
'notify_url' => route('purchase.payfast.notify', [], true),
        'm_payment_id'  => $m_payment_id,
        'amount'        => $amount,
        'item_name'     => 'Deposit for ' . ($purchase->vehicle->name ?? 'Vehicle'),
        'name_first'    => $request->input('name', ''),
        'email_address' => $request->input('email', ''),
        'custom_str1'   => (string) $purchase->id, // our purchase ID
    ];

    // Signature
    $sigData = $fields;
    if (!empty($settings->payfast_passphrase)) {
        $sigData['passphrase'] = $settings->payfast_passphrase;
    }
    ksort($sigData);
    $pairs = [];
    foreach ($sigData as $key => $val) {
        if ($val === '' || $val === null) continue;
        $pairs[] = $key . '=' . urlencode(trim($val));
    }
    $fields['signature'] = md5(implode('&', $pairs));

    // Persist chosen method + expected amount
    $purchase->update([
        'payment_method'     => 'payfast',
        'payfast_payment_id' => $m_payment_id,
        'deposit_expected'   => $toCharge,
        'payment_status'     => $purchase->payment_status ?: 'pending',
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
    $purchase   = \App\Models\Purchase::with('vehicle','customer')->find($purchaseId);

    if (!$purchase) {
        return redirect(url('/'))->with('error', 'Purchase not found.');
    }

    $message = $purchase->payment_status === 'paid'
        ? 'Your deposit payment was successful!'
        : 'We are processing your payment. You’ll receive email confirmation shortly.';

    return redirect(url('/'))->with('payfast_success', $message);
}


public function payfastCancel(Request $request)
{
    return view('payments.cancel');
}


public function payfastNotify(Request $request)
{
Log::info('ITN HIT', $request->all());

    $purchaseId = $request->input('custom_str1');
    $purchase   = \App\Models\Purchase::with(['vehicle','customer'])->find($purchaseId);

    if (!$purchase) {
        Log::error("PayFast Notify: Purchase not found", ['purchase_id' => $purchaseId]);
        return response('Purchase not found', 404);
    }

    // TODO: (recommended) perform full PayFast validation (IPN verification, signature, source IP)

    $paymentStatus = strtolower($request->input('payment_status', ''));
    $amountPaid    = (float) $request->input('amount_gross', 0);
    $pfPaymentId   = $request->input('pf_payment_id'); // PayFast reference
    $mPaymentId    = $request->input('m_payment_id');  // your unique ref

   if ($paymentStatus === 'complete' && $amountPaid > 0) {
    $purchase->payment_status     = 'paid';
    $purchase->payment_method     = 'payfast';
    $purchase->save();

    // also mark vehicle as sold/unavailable
    if ($purchase->vehicle && in_array('status', $purchase->vehicle->getFillable() ?? [])) {
        $purchase->vehicle->status = 'sold';
        $purchase->vehicle->save();
    }

    // Send emails via SMTP from DB
    $this->sendPurchaseEmails($purchase, $amountPaid);
}
 else {
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
// >>> (new \App\Http\Controllers\PurchaseController)->configureMailerFromSettings();
// >>> Mail::raw('SMTP OK', fn($m) => $m->to('you@example.com')->subject('Ping'));
