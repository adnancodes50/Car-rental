<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Models\SystemSetting;
use App\Models\Customer;
use App\Models\Purchase;              // vehicle purchases table (existing)
use App\Models\Vehicles;              // vehicles
use App\Models\Equipment;             // equipment
use App\Models\EquipmentStock;        // per-location stock
use App\Models\EquipmentPurchase;     // new per-equipment purchases

use App\Mail\PurchaseReceipt;
use App\Mail\OwnerPurchaseAlert;

class PurchaseController extends Controller
{
    /* ===================== STORE ===================== */

    public function store(Request $request)
    {
        // Accept either vehicle_id OR equipment_id (+ location_id + quantity for equipment)
        $validated = $request->validate([
            'vehicle_id'     => ['nullable','exists:vehicles,id'],
            'equipment_id'   => ['nullable','exists:equipment,id'],
            'location_id'    => ['nullable','exists:locations,id'],   // required for equipment
            'quantity'       => ['nullable','integer','min:1'],       // required for equipment
            'name'           => ['required','string','max:255'],
            'email'          => ['required','email:rfc,filter','max:255'],
            'phone'          => ['required','regex:/^\+?[0-9]{1,4}(?:[\s-]?[0-9]{2,4}){2,4}$/'],
            'country'        => ['required','string','max:100'],
            'total_price'    => ['nullable','numeric','min:0'],
            'payment_method' => ['nullable','string','max:255'],
            'deposit_paid'   => ['nullable','numeric','min:0'],
        ], [
            'phone.regex' => 'Use digits with optional spaces or dashes, e.g. +27 123 456 7890.',
        ]);

        if (empty($validated['vehicle_id']) && empty($validated['equipment_id'])) {
            return response()->json(['success'=>false,'message'=>'Missing item (vehicle or equipment).'], 422);
        }

        // Create/find customer
        $customer = Customer::firstOrCreate(
            ['email' => $validated['email']],
            [
                'name'    => $validated['name'],
                'phone'   => $validated['phone'] ?? null,
                'country' => $validated['country'] ?? null,
            ]
        );

        /* -------- VEHICLE BRANCH -------- */
        if (!empty($validated['vehicle_id'])) {
            $vehicle = Vehicles::findOrFail($validated['vehicle_id']);
            if (($vehicle->status ?? null) === 'sold') {
                return response()->json(['success'=>false,'message'=>'This vehicle has already been sold.'], 422);
            }

            $serverTotalPrice = (float) ($vehicle->purchase_price ?? 0);
            $serverDeposit    = (float) ($vehicle->deposit_amount ?? 0);

            $purchase = Purchase::create([
                'customer_id'     => $customer->id,
                'vehicle_id'      => $vehicle->id,
                'total_price'     => $serverTotalPrice,
                'deposit_expected'=> $serverDeposit > 0 ? $serverDeposit : null,
                'deposit_paid'    => (float) ($validated['deposit_paid'] ?? 0),
                'payment_method'  => $validated['payment_method'] ?? null,
                'payment_status'  => 'pending',
            ]);

            return response()->json([
                'success'     => true,
                'purchase_id' => $purchase->id,
                'message'     => 'Purchase saved successfully.',
            ]);
        }

        /* -------- EQUIPMENT BRANCH (per LOCATION + QUANTITY) -------- */
        $equipment   = Equipment::with('category')->findOrFail($validated['equipment_id']);
        if (($equipment->status ?? null) === 'sold') {
            return response()->json(['success'=>false,'message'=>'This equipment has already been sold.'], 422);
        }
        $locationId = $validated['location_id'] ?? null;
        $quantity   = (int) ($validated['quantity'] ?? 1);
        if (!$locationId) return response()->json(['success'=>false,'message'=>'Location is required.'], 422);
        if ($quantity < 1) return response()->json(['success'=>false,'message'=>'Quantity must be at least 1.'], 422);

        $stockRow = EquipmentStock::where('equipment_id', $equipment->id)->where('location_id', $locationId)->lockForUpdate()->first();
        if (!$stockRow) return response()->json(['success'=>false,'message'=>'Selected location has no stock record for this equipment.'], 422);
        if ($stockRow->stock < $quantity) {
            return response()->json(['success'=>false,'message'=>"Insufficient stock at location. Available: {$stockRow->stock}"], 422);
        }

        $unitPrice   = (float) ($equipment->sale_price ?? 0);
        $unitDeposit = (float) ($equipment->deposit_amount ?? 0);

        $purchase = EquipmentPurchase::create([
            'customer_id'     => $customer->id,
            'equipment_id'    => $equipment->id,
            'location_id'     => $locationId,
            'quantity'        => $quantity,
            'total_price'     => $unitPrice * $quantity,
            'deposit_expected'=> $unitDeposit > 0 ? $unitDeposit * $quantity : null,
            'deposit_paid'    => 0,
            'payment_status'  => 'pending',
            'payment_method'  => null,
            // stock_before/after will be filled on payment success
        ]);

        return response()->json([
            'success'       => true,
            'purchase_id'   => $purchase->id,
            'message'       => 'Purchase saved successfully.',
        ]);
    }

    /* ===================== MAIL HELPERS (unchanged) ===================== */

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
            'encryption' => $settings->mail_encryption ?: null,
            'username'   => $settings->mail_username,
            'password'   => $settings->mail_password,
            'timeout'    => null,
            'auth_mode'  => null,
        ]);
        return true;
    }

    private function resolveOwnerEmail(?SystemSetting $settings = null): ?string
    {
        $settings = $settings ?: SystemSetting::first();
        if (!empty($settings?->mail_owner_address)) return $settings->mail_owner_address;
        return $settings?->mail_from_address ?: (config('mail.from.address') ?: env('OWNER_EMAIL'));
    }

    private function sendPurchaseEmails($purchase, float $paidNow): void
    {
        try {
            $settings = SystemSetting::first();
            $this->configureMailerFromSettings($settings);
            $ownerEmail = $this->resolveOwnerEmail($settings);

            if ($purchase->customer?->email) {
                Mail::to($purchase->customer->email)->send(new PurchaseReceipt($purchase, $paidNow));
            }
            if ($ownerEmail) {
                Mail::to($ownerEmail)->send(new OwnerPurchaseAlert($purchase, $paidNow));
            }
        } catch (\Throwable $mailErr) {
            Log::warning('✉️ Email send failed', [
                'purchase_id' => $purchase->id ?? null,
                'error'       => $mailErr->getMessage(),
            ]);
        }
    }

    /* ===================== STRIPE: VEHICLES ===================== */

    public function payWithStripe(Request $request, $purchaseId)
    {
        $request->validate(['payment_method_id' => 'required|string']);
        $purchase = Purchase::with(['vehicle','customer'])->findOrFail($purchaseId);

        $requiredDeposit = $purchase->deposit_expected ?? $purchase->vehicle->deposit_amount ?? null;
        if (!$requiredDeposit || $requiredDeposit <= 0) {
            return response()->json(['success'=>false,'message'=>'No valid deposit amount found for this purchase.'], 422);
        }

        $alreadyPaid = (float) ($purchase->deposit_paid ?? 0);
        $toCharge    = max((float) $requiredDeposit - $alreadyPaid, 0);
        if ($toCharge <= 0) return response()->json(['success'=>false,'message'=>'Deposit already paid for this purchase.'], 422);

        $settings = SystemSetting::first();
        if (!$settings || empty($settings->stripe_secret)) {
            return response()->json(['success'=>false,'message'=>'Stripe secret key not configured.'], 500);
        }

        \Stripe\Stripe::setApiKey($settings->stripe_secret);

        try {
            $pi = \Stripe\PaymentIntent::create([
                'amount'               => (int) round($toCharge * 100),
                'currency'             => 'zar',
                'payment_method'       => $request->payment_method_id,
                'confirmation_method'  => 'manual',
                'confirm'              => true,
                'description'          => "Deposit for Purchase #{$purchase->id}",
                'metadata'             => ['purchase_id'=>$purchase->id, 'type'=>'vehicle_purchase_deposit'],
                'payment_method_types' => ['card'],
                'receipt_email'        => $purchase->customer->email ?? null,
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
                'payment_method'             => 'stripe',
                'deposit_paid'               => $alreadyPaid + $toCharge,
                'payment_status'             => 'paid',
                'stripe_payment_intent_id'   => $pi->id,
                'stripe_payment_method_id'   => is_string($pi->payment_method ?? null) ? $pi->payment_method : null,
                'stripe_charge_id'           => $charge->id ?? null,
                'card_brand'                 => $charge?->payment_method_details?->card?->brand,
                'card_last4'                 => $charge?->payment_method_details?->card?->last4,
                'card_exp_month'             => $charge?->payment_method_details?->card?->exp_month,
                'card_exp_year'              => $charge?->payment_method_details?->card?->exp_year,
                'receipt_url'                => $charge?->receipt_url,
            ]);

            // mark vehicle as sold (string or boolean)
            if ($purchase->vehicle) $this->markItemSold($purchase->vehicle);

            $this->sendPurchaseEmails($purchase, $toCharge);

            return response()->json([
                'success'      => true,
                'message'      => 'Deposit payment successful.',
                'purchase_id'  => $purchase->id,
                'paid'         => $toCharge,
                'receipt_url'  => $charge?->receipt_url,
                'redirect_to'  => url('/'),
            ]);
        } catch (\Stripe\Exception\CardException $ce) {
            Log::error('Stripe card error', ['purchase_id' => $purchase->id, 'error' => $ce->getMessage()]);
            return response()->json(['success' => false, 'message' => $ce->getMessage()], 402);
        } catch (\Throwable $e) {
            Log::error('Stripe payment error', ['purchase_id' => $purchase->id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Payment failed. ' . $e->getMessage()], 500);
        }
    }

    /* ===================== STRIPE: EQUIPMENT (by location) ===================== */

    public function payWithStripeEquipment(Request $request, $purchaseId)
    {
        $request->validate(['payment_method_id' => 'required|string']);
        $purchase = EquipmentPurchase::with(['equipment','location','customer'])->findOrFail($purchaseId);

        $requiredDeposit = $purchase->deposit_expected ?? null;
        if (!$requiredDeposit || $requiredDeposit <= 0) {
            return response()->json(['success'=>false,'message'=>'No valid deposit amount found.'], 422);
        }

        $alreadyPaid = (float) ($purchase->deposit_paid ?? 0);
        $toCharge    = max((float) $requiredDeposit - $alreadyPaid, 0);
        if ($toCharge <= 0) return response()->json(['success'=>false,'message'=>'Deposit already paid.'], 422);

        $settings = SystemSetting::first();
        if (!$settings || empty($settings->stripe_secret)) {
            return response()->json(['success'=>false,'message'=>'Stripe secret key not configured.'], 500);
        }
        \Stripe\Stripe::setApiKey($settings->stripe_secret);

        try {
            $res = DB::transaction(function () use ($request, $purchase, $toCharge) {
                $pi = \Stripe\PaymentIntent::create([
                    'amount'               => (int) round($toCharge * 100),
                    'currency'             => 'zar',
                    'payment_method'       => $request->payment_method_id,
                    'confirmation_method'  => 'manual',
                    'confirm'              => true,
                    'description'          => "Deposit for EquipmentPurchase #{$purchase->id}",
                    'metadata'             => ['equipment_purchase_id'=>$purchase->id, 'type'=>'equipment_purchase_deposit'],
                    'payment_method_types' => ['card'],
                    'receipt_email'        => $purchase->customer->email ?? null,
                ]);

                if ($pi->status !== 'succeeded') {
                    return [
                        'requires_action' => true,
                        'client_secret'   => $pi->client_secret,
                    ];
                }

                // Decrement stock at this location
                $stockRow = EquipmentStock::where('equipment_id', $purchase->equipment_id)
                    ->where('location_id', $purchase->location_id)
                    ->lockForUpdate()
                    ->first();

                if (!$stockRow || $stockRow->stock < $purchase->quantity) {
                    throw new \RuntimeException('Insufficient stock at the selected location.');
                }

                $before = (int)$stockRow->stock;
                $after  = $before - (int)$purchase->quantity;
                $stockRow->stock = $after;
                $stockRow->save();

                $charge = $pi->charges->data[0] ?? null;

                $purchase->update([
                    'payment_method'             => 'stripe',
                    'deposit_paid'               => $purchase->deposit_paid + $toCharge,
                    'payment_status'             => 'paid',
                    'stripe_payment_intent_id'   => $pi->id,
                    'stripe_payment_method_id'   => is_string($pi->payment_method ?? null) ? $pi->payment_method : null,
                    'stripe_charge_id'           => $charge->id ?? null,
                    'card_brand'                 => $charge?->payment_method_details?->card?->brand,
                    'card_last4'                 => $charge?->payment_method_details?->card?->last4,
                    'card_exp_month'             => $charge?->payment_method_details?->card?->exp_month,
                    'card_exp_year'              => $charge?->payment_method_details?->card?->exp_year,
                    'receipt_url'                => $charge?->receipt_url,
                    'stock_before'               => $before,
                    'stock_after'                => $after,
                ]);

                // If overall stock across all locations is 0, mark equipment as sold
                $remaining = EquipmentStock::where('equipment_id', $purchase->equipment_id)->sum('stock');
                if ((int)$remaining <= 0 && $purchase->equipment) {
                    $this->markItemSold($purchase->equipment);
                }

                return [
                    'requires_action' => false,
                    'receipt_url'     => $charge?->receipt_url,
                ];
            });

            if ($res['requires_action'] ?? false) {
                return response()->json([
                    'success' => false,
                    'requires_action' => true,
                    'payment_intent_client_secret' => $res['client_secret'],
                ]);
            }

            $this->sendPurchaseEmails($purchase, $toCharge);

            return response()->json([
                'success'      => true,
                'message'      => 'Deposit payment successful.',
                'purchase_id'  => $purchase->id,
                'paid'         => $toCharge,
                'receipt_url'  => $res['receipt_url'] ?? null,
                'redirect_to'  => url('/'),
            ]);
        } catch (\Stripe\Exception\CardException $ce) {
            Log::error('Stripe card error', ['equipment_purchase_id' => $purchase->id, 'error' => $ce->getMessage()]);
            return response()->json(['success' => false, 'message' => $ce->getMessage()], 402);
        } catch (\Throwable $e) {
            Log::error('Stripe payment error', ['equipment_purchase_id' => $purchase->id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Payment failed. ' . $e->getMessage()], 500);
        }
    }

    /* ===================== PAYFAST: VEHICLES ===================== */
    public function initPayfast(Request $request, Purchase $purchase)
    {
        $purchase->load('vehicle','customer');
        $settings = SystemSetting::first();
        if (!$settings || !$settings->payfast_enabled) {
            return response()->json(['success' => false, 'message' => 'PayFast is not configured.'], 422);
        }

        $requiredDeposit = $purchase->deposit_expected ?? $purchase->vehicle->deposit_amount ?? null;
        if (!$requiredDeposit || $requiredDeposit <= 0) {
            return response()->json(['success' => false, 'message' => 'No valid deposit amount found.'], 422);
        }

        $alreadyPaid = (float) ($purchase->deposit_paid ?? 0);
        $toCharge    = max($requiredDeposit - $alreadyPaid, 0);
        if ($toCharge <= 0) return response()->json(['success' => false, 'message' => 'Deposit already paid.'], 422);

        $action = $settings->payfast_test_mode
            ? 'https://sandbox.payfast.co.za/eng/process'
            : ($settings->payfast_live_url ?: 'https://www.payfast.co.za/eng/process');

        $m_payment_id = 'pur-' . $purchase->id . '-' . Str::random(6);
        $amount       = number_format($toCharge, 2, '.', '');
        $returnUrl    = url('/?payfast_success=1');

        $fields = [
            'merchant_id'   => $settings->payfast_merchant_id,
            'merchant_key'  => $settings->payfast_merchant_key,
            'return_url'    => $returnUrl,
            'cancel_url'    => url('/payments/cancel'),
            'notify_url'    => route('purchase.payfast.notify', [], true),
            'm_payment_id'  => $m_payment_id,
            'amount'        => $amount,
            'item_name'     => 'Deposit for ' . ($purchase->vehicle->name ?? 'Vehicle'),
            'name_first'    => $purchase->customer->name ?? '',
            'email_address' => $purchase->customer->email ?? '',
            'custom_str1'   => (string) $purchase->id,
            'custom_str2'   => 'vehicle',
        ];

        $fields['signature'] = $this->pfSignature($fields, $settings->payfast_passphrase ?? null);

        $purchase->update([
            'payment_method'     => 'payfast',
            'payfast_payment_id' => $m_payment_id,
            'deposit_expected'   => $toCharge,
            'payment_status'     => $purchase->payment_status ?: 'pending',
        ]);

        return response()->json(['success'=>true,'action'=>$action,'fields'=>$fields]);
    }

    /* ===================== PAYFAST: EQUIPMENT ===================== */
    public function initPayfastEquipment(Request $request, EquipmentPurchase $purchase)
    {
        $purchase->load('equipment','location','customer');
        $settings = SystemSetting::first();
        if (!$settings || !$settings->payfast_enabled) {
            return response()->json(['success' => false, 'message' => 'PayFast is not configured.'], 422);
        }

        $requiredDeposit = $purchase->deposit_expected ?? null;
        if (!$requiredDeposit || $requiredDeposit <= 0) {
            return response()->json(['success' => false, 'message' => 'No valid deposit amount found.'], 422);
        }

        $alreadyPaid = (float) ($purchase->deposit_paid ?? 0);
        $toCharge    = max($requiredDeposit - $alreadyPaid, 0);
        if ($toCharge <= 0) return response()->json(['success' => false, 'message' => 'Deposit already paid.'], 422);

        $action = $settings->payfast_test_mode
            ? 'https://sandbox.payfast.co.za/eng/process'
            : ($settings->payfast_live_url ?: 'https://www.payfast.co.za/eng/process');

        $m_payment_id = 'epur-' . $purchase->id . '-' . Str::random(6);
        $amount       = number_format($toCharge, 2, '.', '');
        $returnUrl    = url('/?payfast_success=1');

        $fields = [
            'merchant_id'   => $settings->payfast_merchant_id,
            'merchant_key'  => $settings->payfast_merchant_key,
            'return_url'    => $returnUrl,
            'cancel_url'    => url('/payments/cancel'),
            'notify_url'    => route('purchase.payfast.notify', [], true),
            'm_payment_id'  => $m_payment_id,
            'amount'        => $amount,
            'item_name'     => 'Deposit for ' . ($purchase->equipment->name ?? 'Equipment'),
            'name_first'    => $purchase->customer->name ?? '',
            'email_address' => $purchase->customer->email ?? '',
            'custom_str1'   => (string) $purchase->id,
            'custom_str2'   => 'equipment',
        ];

        $fields['signature'] = $this->pfSignature($fields, $settings->payfast_passphrase ?? null);

        $purchase->update([
            'payment_method'     => 'payfast',
            'payfast_payment_id' => $m_payment_id,
            'payment_status'     => $purchase->payment_status ?: 'pending',
        ]);

        return response()->json(['success'=>true,'action'=>$action,'fields'=>$fields]);
    }

    /* ===================== PAYFAST RETURN/CANCEL/NOTIFY ===================== */

    public function payfastReturn(Request $request)
    {
        return redirect(url('/'))->with('payfast_success', 'We are processing your payment. You’ll receive email confirmation shortly.');
    }

    public function payfastCancel(Request $request)
    {
        return view('payments.cancel');
    }

    public function payfastNotify(Request $request)
    {
        Log::info('PayFast ITN', $request->all());

        $id     = $request->input('custom_str1');
        $kind   = $request->input('custom_str2', 'vehicle'); // 'vehicle' or 'equipment'
        $status = strtolower($request->input('payment_status', ''));
        $amount = (float) $request->input('amount_gross', 0);

        if ($kind === 'equipment') {
            $purchase = EquipmentPurchase::with(['equipment','customer','location'])->find($id);
            if (!$purchase) return response('Not found', 404);

            if ($status === 'complete' && $amount > 0) {
                try {
                    DB::transaction(function () use ($purchase, $amount) {
                        // decrement stock at this location
                        $row = EquipmentStock::where('equipment_id',$purchase->equipment_id)
                            ->where('location_id',$purchase->location_id)
                            ->lockForUpdate()
                            ->first();

                        if ($row && $row->stock >= $purchase->quantity) {
                            $before = (int)$row->stock;
                            $after  = $before - (int)$purchase->quantity;
                            $row->stock = $after; $row->save();

                            $purchase->stock_before = $before;
                            $purchase->stock_after  = $after;
                        }

                        $purchase->payment_status = 'paid';
                        $purchase->payment_method = 'payfast';
                        $purchase->deposit_paid   = ($purchase->deposit_paid ?? 0) + $amount;
                        $purchase->save();

                        // mark sold if global stock zero
                        $remain = EquipmentStock::where('equipment_id',$purchase->equipment_id)->sum('stock');
                        if ((int)$remain <= 0 && $purchase->equipment) {
                            $this->markItemSold($purchase->equipment);
                        }
                    });

                    $this->sendPurchaseEmails($purchase, $amount);
                } catch (\Throwable $e) {
                    Log::error('PayFast ITN equipment error', ['id'=>$id,'err'=>$e->getMessage()]);
                }
            }
            return response('OK');
        }

        // vehicle
        $purchase = Purchase::with(['vehicle','customer'])->find($id);
        if (!$purchase) return response('Not found', 404);

        if ($status === 'complete' && $amount > 0) {
            $purchase->payment_status = 'paid';
            $purchase->payment_method = 'payfast';
            $purchase->deposit_paid   = ($purchase->deposit_paid ?? 0) + $amount;
            $purchase->save();

            if ($purchase->vehicle) $this->markItemSold($purchase->vehicle);
            $this->sendPurchaseEmails($purchase, $amount);
        }

        return response('OK');
    }

    /* ===================== HELPERS ===================== */

    private function pfSignature(array $fields, ?string $passphrase): string
    {
        $sigData = $fields;
        if (!empty($passphrase)) $sigData['passphrase'] = $passphrase;
        ksort($sigData);
        $pairs = [];
        foreach ($sigData as $k => $v) {
            if ($v === '' || $v === null) continue;
            $pairs[] = $k . '=' . urlencode(trim($v));
        }
        return md5(implode('&', $pairs));
    }

    private function markItemSold($item): void
    {
        if (!$item) return;
        $current = $item->status;
        $item->status = (is_numeric($current) || in_array($current, [0,1,'0','1'], true)) ? 1 : 'sold';
        $item->save();
    }
}
