<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EquipmentPurchase;
use App\Models\Equipment;
use App\Models\Customer;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use App\Mail\PurchaseReceipt;
use App\Mail\OwnerPurchaseAlert;

class EquipmentPurchaseController extends Controller
{
    /** Create equipment purchase (no payment yet) */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'equipment_id' => ['required','exists:equipment,id'],
            'name'         => ['required','string','max:255'],
            'email'        => ['required','email:rfc,filter','max:255'],
            'phone'        => ['required','regex:/^\+?[0-9]{1,4}(?:[\s-]?[0-9]{2,4}){2,4}$/'],
            'country'      => ['required','string','max:100'],
            'total_price'  => ['required','numeric','min:0'], // client-provided but we’ll override from server
        ]);

        $equipment = Equipment::findOrFail($validated['equipment_id']);

        if (($equipment->status ?? null) === 'sold') {
            return response()->json(['success' => false, 'message' => 'This equipment has already been sold.'], 422);
        }

        $serverTotal  = (float) ($equipment->purchase_price ?? 0);
        $serverDeposit= (float) ($equipment->deposit_amount ?? 0);

        $customer = Customer::firstOrCreate(
            ['email' => $validated['email']],
            ['name' => $validated['name'], 'phone' => $validated['phone'], 'country' => $validated['country']]
        );

        $purchase = EquipmentPurchase::create([
            'equipment_id'     => $equipment->id,
            'customer_id'      => $customer->id,
            'total_price'      => $serverTotal,
            'deposit_expected' => $serverDeposit > 0 ? $serverDeposit : null,
            'payment_status'   => 'pending',
        ]);

        return response()->json([
            'success'      => true,
            'purchase_id'  => $purchase->id,
            'message'      => 'Equipment purchase created.',
        ]);
    }

    /** Pay by Stripe (deposit) */
    public function payWithStripe(Request $request, $purchaseId)
    {
        $request->validate(['payment_method_id' => 'required|string']);

        $purchase = EquipmentPurchase::with(['equipment','customer'])->findOrFail($purchaseId);
        $equipment = $purchase->equipment;

        $requiredDeposit = (float) ($purchase->deposit_expected ?? $equipment->deposit_amount ?? 0);
        if ($requiredDeposit <= 0) {
            return response()->json(['success' => false, 'message' => 'No valid deposit amount found.'], 422);
        }

        $alreadyPaid = (float) ($purchase->deposit_paid ?? 0);
        $toCharge    = max($requiredDeposit - $alreadyPaid, 0);
        if ($toCharge <= 0) {
            return response()->json(['success' => false, 'message' => 'Deposit already paid.'], 422);
        }

        $settings = SystemSetting::first();
        if (!$settings || empty($settings->stripe_secret)) {
            return response()->json(['success' => false, 'message' => 'Stripe not configured.'], 500);
        }
        \Stripe\Stripe::setApiKey($settings->stripe_secret);

        try {
            $pi = \Stripe\PaymentIntent::create([
                'amount'               => (int) round($toCharge * 100),
                'currency'             => 'zar',
                'payment_method'       => $request->payment_method_id,
                'confirmation_method'  => 'manual',
                'confirm'              => true,
                'description'          => "Deposit for Equipment Purchase #{$purchase->id} ({$purchase->itemDisplayName()})",
                'metadata'             => ['equipment_purchase_id' => (string)$purchase->id, 'type' => 'equipment_purchase_deposit'],
                'payment_method_types' => ['card'],
                'receipt_email'        => $purchase->customer?->email,
            ]);

            if ($pi->status !== 'succeeded') {
                return response()->json([
                    'success' => false,
                    'requires_action' => true,
                    'payment_intent_client_secret' => $pi->client_secret,
                ]);
            }

            $charge = $pi->charges->data[0] ?? null;

            $purchase->update([
                'deposit_paid'               => $alreadyPaid + $toCharge,
                'payment_status'             => 'paid',
                'payment_method'             => 'stripe',
                'stripe_payment_intent_id'   => $pi->id,
                'stripe_payment_method_id'   => is_string($pi->payment_method ?? null) ? $pi->payment_method : null,
                'stripe_charge_id'           => $charge?->id,
                'card_brand'                 => $charge?->payment_method_details?->card?->brand,
                'card_last4'                 => $charge?->payment_method_details?->card?->last4,
                'card_exp_month'             => $charge?->payment_method_details?->card?->exp_month,
                'card_exp_year'              => $charge?->payment_method_details?->card?->exp_year,
                'receipt_url'                => $charge?->receipt_url,
            ]);

            // Mark equipment as sold if you use that status
            if (isset($equipment->status) && in_array('status', $equipment->getFillable() ?? [])) {
                $equipment->status = 'sold';
                $equipment->save();
            }

            // (Optional) send emails (reuse your helpers if you want)
            // $this->sendPurchaseEmailsEquipment($purchase, $toCharge);

            return response()->json([
                'success'     => true,
                'purchase_id' => $purchase->id,
                'paid'        => $toCharge,
                'receipt_url' => $charge?->receipt_url,
                'redirect_to' => url('/'),
            ]);

        } catch (\Stripe\Exception\CardException $ce) {
            return response()->json(['success' => false, 'message' => $ce->getMessage()], 402);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Payment failed. '.$e->getMessage()], 500);
        }
    }

    /** Init PayFast (deposit) — optional, mirrors your vehicle logic */
    public function initPayfast(Request $request, EquipmentPurchase $purchase)
    {
        $purchase->load('equipment','customer');
        $settings = SystemSetting::first();
        if (!$settings || !$settings->payfast_enabled) {
            return response()->json(['success' => false, 'message' => 'PayFast is not configured.'], 422);
        }

        $requiredDeposit = (float) ($purchase->deposit_expected ?? $purchase->equipment->deposit_amount ?? 0);
        if ($requiredDeposit <= 0) {
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

        $m_payment_id = 'eqp-' . $purchase->id . '-' . \Str::random(6);
        $amount       = number_format($toCharge, 2, '.', '');

        $fields = [
            'merchant_id'   => $settings->payfast_merchant_id,
            'merchant_key'  => $settings->payfast_merchant_key,
            'return_url'    => url('/?payfast_success=1'),
            'cancel_url'    => url('/payments/cancel'),
            'notify_url'    => route('equipment.purchase.payfast.notify', [], true),
            'm_payment_id'  => $m_payment_id,
            'amount'        => $amount,
            'item_name'     => 'Deposit for ' . ($purchase->equipment?->name ?? 'Equipment'),
            'name_first'    => $request->input('name', ''),
            'email_address' => $request->input('email', ''),
            'custom_str1'   => (string) $purchase->id,
        ];

        $sigData = $fields;
        if (!empty($settings->payfast_passphrase)) $sigData['passphrase'] = $settings->payfast_passphrase;
        ksort($sigData);
        $pairs = [];
        foreach ($sigData as $k => $v) {
            if ($v === '' || $v === null) continue;
            $pairs[] = $k . '=' . urlencode(trim($v));
        }
        $fields['signature'] = md5(implode('&', $pairs));

        $purchase->update([
            'payment_method'     => 'payfast',
            'payfast_payment_id' => $m_payment_id,
            'deposit_expected'   => $toCharge,
            'payment_status'     => $purchase->payment_status ?: 'pending',
        ]);

        return response()->json(['success' => true, 'action' => $action, 'fields' => $fields]);
    }

    /** PayFast ITN — mark paid & equipment sold */
    public function payfastNotify(Request $request)
    {
        $purchaseId = $request->input('custom_str1');
        $purchase   = EquipmentPurchase::with(['equipment','customer'])->find($purchaseId);
        if (!$purchase) return response('Purchase not found', 404);

        $paymentStatus = strtolower($request->input('payment_status', ''));
        $amountPaid    = (float) $request->input('amount_gross', 0);

        if ($paymentStatus === 'complete' && $amountPaid > 0) {
            $purchase->update([
                'payment_status' => 'paid',
                'payment_method' => 'payfast',
                'deposit_paid'   => ($purchase->deposit_paid ?? 0) + $amountPaid,
            ]);

            $eq = $purchase->equipment;
            if ($eq && isset($eq->status) && in_array('status', $eq->getFillable() ?? [])) {
                $eq->status = 'sold';
                $eq->save();
            }

            // $this->sendPurchaseEmailsEquipment($purchase, $amountPaid);
        }

        return response('OK');
    }
}
