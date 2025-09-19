<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\Customer;
use Log;
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
    $alreadyPaid   = (float) ($purchase->deposit_paid ?? 0);
    $toCharge      = max($requiredDeposit - $alreadyPaid, 0);

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
            'amount'               => $amountInCents,
            'currency'             => 'zar', // Rands
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
            // Expand so you can optionally store safe card / receipt info if desired
            'expand'               => ['payment_method', 'charges.data.balance_transaction'],
        ]);

        if ($paymentIntent->status === 'succeeded') {
            // Add this paid amount to the stored deposit
            $newDepositPaid = $alreadyPaid + $toCharge;

            $purchase->update([
                'payment_method' => 'stripe',
                'deposit_paid'   => $newDepositPaid,   // ✅ store what was actually paid
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
                'success'     => true,
                'message'     => 'Deposit payment successful.',
                'purchase_id' => $purchase->id,
                'paid'        => $toCharge,
                'deposit_due' => max($requiredDeposit - $newDepositPaid, 0),
            ]);
        }

        // 3D Secure / next action
        return response()->json([
            'success'                      => false,
            'requires_action'              => true,
            'payment_intent_client_secret' => $paymentIntent->client_secret,
        ]);

    } catch (\Throwable $e) {
        Log::error('Stripe deposit error for purchase', [
            'purchase_id' => $purchase->id,
            'message'     => $e->getMessage(),
        ]);

        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}


}
