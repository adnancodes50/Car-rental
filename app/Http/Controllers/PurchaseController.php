<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\Customer;
use Log;
use Str;
use Stripe\Stripe;
use Stripe\PaymentIntent;

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




    public function payWithStripe(Request $request, $purchaseId)
    {
        $request->validate([
            'payment_method_id' => 'required|string',
        ]);

        $purchase = Purchase::with('vehicle')->findOrFail($purchaseId);

        // Make sure we have a deposit amount
        $requiredDeposit = null;

        // Prefer a deposit stored on the purchase (if you saved it at creation time),
        // otherwise fall back to the current vehicle’s deposit_amount.
        if (!empty($purchase->deposit_amount)) {
            $requiredDeposit = (float) $purchase->deposit_amount;
        } elseif ($purchase->vehicle && !empty($purchase->vehicle->deposit_amount)) {
            $requiredDeposit = (float) $purchase->vehicle->deposit_amount;
        }

        if ($requiredDeposit === null || $requiredDeposit <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'No valid deposit amount found for this purchase.',
            ], 422);
        }

        // If some deposit already paid, only charge the remainder (optional)
        $alreadyPaid = (float) ($purchase->deposit_paid ?? 0);
        $toCharge = max($requiredDeposit - $alreadyPaid, 0);

        if ($toCharge <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Deposit already paid for this purchase.',
            ], 422);
        }

        // Stripe expects the amount in *cents*
        $amountInCents = (int) round($toCharge * 100);

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            // Create a PaymentIntent for the DEPOSIT only
            $paymentIntent = PaymentIntent::create([
                'amount' => $amountInCents,
                'currency' => 'zar', // Rands
                'payment_method' => $request->payment_method_id,
                'confirmation_method' => 'manual',
                'confirm' => true,
                'description' => "Deposit for Purchase #{$purchase->id}",
                'metadata' => [
                    'purchase_id' => (string) $purchase->id,
                    'customer_id' => (string) ($purchase->customer_id ?? ''),
                    'type' => 'purchase_deposit',
                ],
                'payment_method_types' => ['card'],
                // Expand so you can optionally store safe card / receipt info if desired
                'expand' => ['payment_method', 'charges.data.balance_transaction'],
            ]);

            if ($paymentIntent->status === 'succeeded') {
                // Add this paid amount to the stored deposit
                $newDepositPaid = $alreadyPaid + $toCharge;

                $purchase->update([
                    'payment_method' => 'stripe',
                    'deposit_paid' => $newDepositPaid,   // ✅ store what was actually paid
                    // Optional additional fields if you have them:
                    // 'payment_status'            => 'deposit_paid',
                    // 'stripe_payment_intent_id'  => $paymentIntent->id,
                    // 'stripe_payment_method_id'  => $paymentIntent->payment_method?->id,
                    // 'stripe_charge_id'          => $paymentIntent->charges->data[0]->id ?? null,
                    // 'card_brand'                => $paymentIntent->payment_method?->card?->brand,
                    // 'card_last4'                => $paymentIntent->payment_method?->card?->last4,
                    // 'card_exp_month'            => $paymentIntent->payment_method?->card?->exp_month,
                    // 'card_exp_year'             => $paymentIntent->payment_method?->card?->exp_year,
                    // 'receipt_url'               => $paymentIntent->charges->data[0]->receipt_url ?? null,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Deposit payment successful.',
                    'purchase_id' => $purchase->id,
                    'paid' => $toCharge,
                    'deposit_due' => max($requiredDeposit - $newDepositPaid, 0),
                ]);
            }

            // 3D Secure / next action
            return response()->json([
                'success' => false,
                'requires_action' => true,
                'payment_intent_client_secret' => $paymentIntent->client_secret,
            ]);

        } catch (\Throwable $e) {
            Log::error('Stripe deposit error for purchase', [
                'purchase_id' => $purchase->id,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function initPayfast(Request $request, Purchase $purchase)
    {
        $purchase->load('vehicle');

        // ---- figure out how much to charge (deposit logic like Stripe) ----
        $requiredDeposit = null;
        if (!empty($purchase->deposit_amount)) {
            $requiredDeposit = (float) $purchase->deposit_amount;
        } elseif ($purchase->vehicle && !empty($purchase->vehicle->deposit_amount)) {
            $requiredDeposit = (float) $purchase->vehicle->deposit_amount;
        }

        if ($requiredDeposit === null || $requiredDeposit <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'No valid deposit amount found for this purchase.',
            ], 422);
        }

        $alreadyPaid = (float) ($purchase->deposit_paid ?? 0);
        $toCharge = max($requiredDeposit - $alreadyPaid, 0);

        if ($toCharge <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Deposit already paid for this purchase.',
            ], 422);
        }

        // ---- PayFast config & endpoint ----
        $pf = config('payfast');
        $isTest = filter_var($pf['testmode'], FILTER_VALIDATE_BOOLEAN);
        $action = $isTest ? $pf['urls']['sandbox'] : $pf['urls']['live'];

        // Unique id you can use to reconcile later
        $m_payment_id = 'pur-' . $purchase->id . '-' . Str::random(6);

        // Amount must be formatted 2-decimals dot
        $amount = number_format($toCharge, 2, '.', '');

        // Build the data array PayFast expects
        $pf = config('payfast');

        // Dynamically build return URL to fleet view
        $returnUrl = route('fleet.view', ['vehicle' => $purchase->vehicle->id]);

        $fields = [
            'merchant_id' => $pf['merchant_id'],
            'merchant_key' => $pf['merchant_key'],
            'return_url' => $returnUrl, // ✅ dynamic redirect
            'cancel_url' => url($pf['cancel_url']),
            'notify_url' => route('purchase.payfast.notify')
            ,
            'm_payment_id' => $m_payment_id,
            'amount' => $amount,
            'item_name' => 'Deposit for ' . $purchase->vehicle->name,
            'name_first' => $request->input('name', ''),
            'email_address' => $request->input('email', ''),
            'custom_str1' => (string) $purchase->id,
        ];


        // Generate signature (sorted, urlencoded, MD5; include passphrase if set)
        $sigData = $fields;
        if (!empty($pf['passphrase'])) {
            $sigData['passphrase'] = $pf['passphrase'];
        }
        ksort($sigData);
        $pairs = [];
        foreach ($sigData as $key => $val) {
            if ($val === '' || $val === null)
                continue;
            $pairs[] = $key . '=' . urlencode(trim($val));
        }
        $fields['signature'] = md5(implode('&', $pairs));

        // Optionally mark purchase as "pending via PayFast"
        $purchase->update([
            'payment_method' => 'payfast',
            'payfast_payment_id' => $m_payment_id,
            'deposit_expected' => $toCharge,
            // 'payment_status'      => 'pending',
        ]);

        // Return endpoint + fields so the front-end can auto-submit a POST form
        return response()->json([
            'success' => true,
            'action' => $action,
            'fields' => $fields,
        ]);
    }

public function payfastReturn(Request $request)
{
    $purchaseId = $request->input('custom_str1');
    $purchase = Purchase::with('vehicle')->find($purchaseId);

    if (!$purchase) {
        return redirect()->route('fleet.index')
            ->with('error', 'Purchase not found.');
    }

    $amountPaid = (float) $request->input('amount_gross', 0);
    if ($amountPaid > 0) {
        $purchase->deposit_paid = (float) $purchase->deposit_paid + $amountPaid;
        $purchase->payment_status = 'paid';
        $purchase->save();
    }

    return redirect()
        ->route('fleet.view', ['vehicle' => $purchase->vehicle->id])
        ->with('payfast_success', 'Your deposit payment was successful!');
}





    // GET/POST when payer cancels
    public function payfastCancel(Request $request)
    {
        // Show a "Cancelled" page
        return view('payments.cancel');
    }

    // POST IPN (server->server)
 public function payfastNotify(Request $request)
{
    Log::info('💡 PayFast ITN received', $request->all());
    $purchaseId = $request->input('custom_str1');
    $purchase = Purchase::find($purchaseId);

    if (!$purchase) {
        Log::error("PayFast Notify: Purchase not found", ['purchase_id' => $purchaseId]);
        return response('Purchase not found', 404);
    }

    $paymentStatus = strtolower($request->input('payment_status'));
    $amountPaid = (float) $request->input('amount_gross');

    if ($paymentStatus === 'complete' && $amountPaid > 0) {
        $currentPaid = (float) $purchase->deposit_paid; // force float, never null
        $purchase->deposit_paid = $currentPaid + $amountPaid;
        $purchase->payment_status = 'paid';
        $purchase->save();

        Log::info("✅ PayFast Notify: Deposit updated", [
            'purchase_id' => $purchase->id,
            'amount_paid' => $amountPaid,
            'new_deposit_paid' => $purchase->deposit_paid,
        ]);
    } else {
        Log::warning("⚠️ PayFast Notify: Payment not complete or amount missing", [
            'purchase_id' => $purchase->id,
            'payment_status' => $paymentStatus,
            'amount_gross' => $request->input('amount_gross'),
        ]);
    }

    return response('OK');
}




}
