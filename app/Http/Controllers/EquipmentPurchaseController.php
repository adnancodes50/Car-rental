<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EquipmentPurchase;
use App\Models\Equipment;
use App\Models\EquipmentStock;
use App\Models\Customer;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Mail\PurchaseReceipt;
use App\Mail\OwnerPurchaseAlert;

class EquipmentPurchaseController extends Controller
{
    /** Create equipment purchase (no payment yet) */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'equipment_id' => ['required','exists:equipment,id'],
            'location_id'  => ['required','exists:locations,id'],
            'quantity'     => ['required','integer','min:1'],
            'name'         => ['required','string','max:255'],
            'email'        => ['required','email:rfc,filter','max:255'],
            'phone'        => ['required','regex:/^\+?[0-9]{1,4}(?:[\s-]?[0-9]{2,4}){2,4}$/'],
            'country'      => ['required','string','max:100'],
            'total_price'  => ['nullable','numeric','min:0'], // client-provided but we will override from server
        ]);

        $equipment = Equipment::with('category')->findOrFail($validated['equipment_id']);

        $equipmentStatus = $equipment->status ?? null;
        if ($equipmentStatus && strtolower((string) $equipmentStatus) !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'This equipment is no longer available for purchase.',
            ], 422);
        }

        $locationId = (int) $validated['location_id'];
        $quantity   = (int) $validated['quantity'];

        $stockRow = EquipmentStock::where('equipment_id', $equipment->id)
            ->where('location_id', $locationId)
            ->first();

        if (! $stockRow) {
            return response()->json([
                'success' => false,
                'message' => 'Selected location has no available stock for this equipment.',
            ], 422);
        }

        if ($stockRow->stock < $quantity) {
            return response()->json([
                'success' => false,
                'message' => "Only {$stockRow->stock} item(s) available at {$stockRow->location?->name}.",
            ], 422);
        }

        $unitPrice = (float) ($equipment->sale_price ?? 0);
        if ($unitPrice <= 0) {
            $unitPrice = (float) ($equipment->purchase_price ?? 0);
        }

        $unitDeposit = (float) ($equipment->deposit_amount ?? 0);
        if ($unitDeposit <= 0) {
            $unitDeposit = (float) ($equipment->category?->deposit_price ?? 0);
        }

        $calculatedTotal   = $unitPrice * $quantity;
        $calculatedDeposit = $unitDeposit > 0 ? $unitDeposit * $quantity : null;

        $customer = Customer::firstOrCreate(
            ['email' => $validated['email']],
            ['name' => $validated['name'], 'phone' => $validated['phone'], 'country' => $validated['country']]
        );

        $purchase = EquipmentPurchase::create([
            'equipment_id'     => $equipment->id,
            'customer_id'      => $customer->id,
            'location_id'      => $locationId,
            'quantity'         => $quantity,
            'total_price'      => $calculatedTotal,
            'deposit_expected' => $calculatedDeposit,
            'deposit_paid'     => 0,
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

        $purchase = EquipmentPurchase::with(['equipment','customer','location'])->findOrFail($purchaseId);
        $equipment = $purchase->equipment;

        if ($purchase->location_id) {
            $stockRow = EquipmentStock::where('equipment_id', $purchase->equipment_id)
                ->where('location_id', $purchase->location_id)
                ->first();

            if (! $stockRow) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected location has no available stock for this equipment.',
                ], 422);
            }

            if ($stockRow->stock < $purchase->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => "Only {$stockRow->stock} item(s) remain at {$purchase->location?->name}.",
                ], 422);
            }
        }

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

            DB::transaction(function () use ($purchase, $toCharge, $alreadyPaid, $pi, $charge) {
                $purchase->refresh();

                $stockBefore = null;
                $stockAfter  = null;

                if ($purchase->location_id) {
                    $stockRow = EquipmentStock::where('equipment_id', $purchase->equipment_id)
                        ->where('location_id', $purchase->location_id)
                        ->lockForUpdate()
                        ->first();

                    if ($stockRow) {
                        $stockBefore   = (int) $stockRow->stock;
                        $stockRow->stock = max($stockBefore - (int) $purchase->quantity, 0);
                        $stockRow->save();
                        $stockAfter    = (int) $stockRow->stock;
                    }
                }

                $purchase->fill([
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
                    'stock_before'               => $stockBefore,
                    'stock_after'                => $stockAfter,
                ]);
                $purchase->save();

                $purchase->loadMissing('equipment');

                if ($purchase->equipment) {
                    $remaining = EquipmentStock::where('equipment_id', $purchase->equipment_id)->sum('stock');
                    if ($remaining <= 0 && isset($purchase->equipment->status) && in_array('status', $purchase->equipment->getFillable() ?? [])) {
                        $purchase->equipment->status = 'inactive';
                        $purchase->equipment->save();
                    }
                }
            });

            $purchase->refresh();

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
        $purchase->load('equipment','customer','location');

        if ($purchase->location_id) {
            $stockRow = EquipmentStock::where('equipment_id', $purchase->equipment_id)
                ->where('location_id', $purchase->location_id)
                ->first();

            if (! $stockRow) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected location has no available stock for this equipment.',
                ], 422);
            }

            if ($stockRow->stock < $purchase->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => "Only {$stockRow->stock} item(s) remain at {$purchase->location?->name}.",
                ], 422);
            }
        }
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
        $purchase   = EquipmentPurchase::with(['equipment','customer','location'])->find($purchaseId);
        if (!$purchase) return response('Purchase not found', 404);

        $paymentStatus = strtolower($request->input('payment_status', ''));
        $amountPaid    = (float) $request->input('amount_gross', 0);

        if ($paymentStatus === 'complete' && $amountPaid > 0) {
            DB::transaction(function () use ($purchase, $amountPaid) {
                $purchase->refresh();

                $stockBefore = null;
                $stockAfter  = null;

                if ($purchase->location_id) {
                    $stockRow = EquipmentStock::where('equipment_id', $purchase->equipment_id)
                        ->where('location_id', $purchase->location_id)
                        ->lockForUpdate()
                        ->first();

                    if ($stockRow) {
                        $stockBefore    = (int) $stockRow->stock;
                        $stockRow->stock = max($stockBefore - (int) $purchase->quantity, 0);
                        $stockRow->save();
                        $stockAfter     = (int) $stockRow->stock;
                    }
                }

                $purchase->fill([
                    'payment_status' => 'paid',
                    'payment_method' => 'payfast',
                    'deposit_paid'   => ($purchase->deposit_paid ?? 0) + $amountPaid,
                    'stock_before'   => $stockBefore,
                    'stock_after'    => $stockAfter,
                ]);
                $purchase->save();

                $purchase->loadMissing('equipment');

                if ($purchase->equipment) {
                    $remaining = EquipmentStock::where('equipment_id', $purchase->equipment_id)->sum('stock');
                    if ($remaining <= 0 && isset($purchase->equipment->status) && in_array('status', $purchase->equipment->getFillable() ?? [])) {
                        $purchase->equipment->status = 'inactive';
                        $purchase->equipment->save();
                    }
                }
            });

            // $this->sendPurchaseEmailsEquipment($purchase, $amountPaid);
        }

        return response('OK');
    }
}


