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
use Illuminate\Support\Facades\Session;
use App\Mail\PurchaseReceipt;
use App\Mail\OwnerPurchaseAlert;
use Illuminate\Support\Str;

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
            'phone' => ['required', 'regex:/^\+?[0-9\s]+$/'],
            'country'      => ['required','string','max:100'],
            'total_price'  => ['nullable','numeric','min:0'],
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

                $capturedAmount = (($charge?->amount ?? $pi->amount_received) ?? (int) round($toCharge * 100)) / 100;
                $purchase->fill([
                    'deposit_paid'               => ($purchase->deposit_paid ?? 0) + $capturedAmount,
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
            $this->sendPurchaseEmails($purchase, $toCharge);

            // ✅ Save to session for confirmation page
            session()->put('confirmed_purchase', [
                'id' => $purchase->id,
                'status' => 'success',
                'payment_method' => 'stripe',
                'paid_amount' => $toCharge,
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);

            return response()->json([
                'success'     => true,
                'purchase_id' => $purchase->id,
                'paid'        => $toCharge,
                'receipt_url' => $charge?->receipt_url,
                'redirect_url' => url('/purchase/confirmation/' . $purchase->id), // Redirect to confirmation page
            ]);

        } catch (\Stripe\Exception\CardException $ce) {
            return response()->json(['success' => false, 'message' => $ce->getMessage()], 402);
        } catch (\Throwable $e) {
            Log::error('Stripe payment error', ['error' => $e->getMessage(), 'purchase_id' => $purchase->id]);
            return response()->json(['success' => false, 'message' => 'Payment failed. '.$e->getMessage()], 500);
        }
    }

    /** Init PayFast (deposit) - UPDATED */
    public function initPayfast(Request $request, EquipmentPurchase $purchase)
    {
        $purchase->load('equipment', 'customer', 'location');

        // Validate stock availability
        if ($purchase->location_id) {
            $stockRow = EquipmentStock::where('equipment_id', $purchase->equipment_id)
                ->where('location_id', $purchase->location_id)
                ->first();

            if (!$stockRow || $stockRow->stock < $purchase->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => $stockRow
                        ? "Only {$stockRow->stock} item(s) remain at {$purchase->location?->name}."
                        : 'No stock available at selected location.'
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
        $toCharge = max($requiredDeposit - $alreadyPaid, 0);
        if ($toCharge <= 0) {
            return response()->json(['success' => false, 'message' => 'Deposit already paid.'], 422);
        }

        // PayFast URLs - Redirect to confirmation page after success
        $action = $settings->payfast_test_mode
            ? 'https://sandbox.payfast.co.za/eng/process'
            : ($settings->payfast_live_url ?: 'https://www.payfast.co.za/eng/process');

        $m_payment_id = 'eqp-' . $purchase->id . '-' . Str::random(6);
        $amount = number_format($toCharge, 2, '.', '');

        // Redirect to confirmation page after PayFast
        $returnUrl = url('/purchase/confirmation/' . $purchase->id . '?status=success&payment_method=payfast');
        $cancelUrl = route('equipment.purchase.payfast.cancel', ['purchase_id' => $purchase->id]);
        $notifyUrl = url('/api/equipment-purchase/payfast/notify');

        $fields = [
            'merchant_id'      => $settings->payfast_merchant_id,
            'merchant_key'     => $settings->payfast_merchant_key,
            'return_url'       => $returnUrl,
            'cancel_url'       => $cancelUrl,
            'notify_url'       => $notifyUrl,
            'm_payment_id'     => $m_payment_id,
            'amount'           => $amount,
            'item_name'        => 'Deposit for ' . ($purchase->equipment?->name ?? 'Equipment Purchase #' . $purchase->id),
            'item_description' => 'Equipment deposit payment',
            'name_first'       => $request->input('name', $purchase->customer->name ?? ''),
            'email_address'    => $request->input('email', $purchase->customer->email ?? 'customer@example.com'),
            'custom_str1'      => (string) $purchase->id,
            'custom_str2'      => 'equipment_purchase',
        ];

        // Signature
        $sigData = $fields;
        if (!empty($settings->payfast_passphrase)) {
            $sigData['passphrase'] = $settings->payfast_passphrase;
        }
        ksort($sigData);
        $pairs = [];
        foreach ($sigData as $k => $v) {
            if ($v === '' || $v === null) continue;
            $pairs[] = $k . '=' . urlencode(trim($v));
        }
        $fields['signature'] = md5(implode('&', $pairs));

        // ✅ Save meta on purchase – mark as initiated
        $purchase->update([
            'payment_method'     => 'payfast',
            'payfast_payment_id' => $m_payment_id,
            'deposit_expected'   => $toCharge,
            'payment_status'     => 'initiated',
        ]);

        Log::info('🔄 PAYFAST INITIALIZED', [
            'purchase_id' => $purchase->id,
            'action'      => $action,
            'return_url'  => $returnUrl,
            'cancel_url'  => $cancelUrl,
            'notify_url'  => $notifyUrl,
            'amount'      => $amount,
            'm_payment_id'=> $m_payment_id
        ]);

        return response()->json([
            'success' => true,
            'action'  => $action,
            'fields'  => $fields,
            'debug'   => [
                'purchase_id' => $purchase->id,
                'amount'      => $amount
            ]
        ]);
    }

    // In EquipmentPurchaseController.php - update payfastNotify method
    public function payfastNotify(Request $request)
    {
        Log::info('PAYFAST ITN RECEIVED', ['params' => $request->all()]);

        $purchaseId = $request->input('custom_str1');
        $paymentStatus = strtolower($request->input('payment_status', ''));
        $amountPaid = (float) ($request->input('amount_gross') ?? 0);
        $pfPaymentId = $request->input('pf_payment_id');
        $mPaymentId = $request->input('m_payment_id');

        if (!$purchaseId) {
            Log::error('PAYFAST ITN: Missing purchase ID');
            return response('Missing purchase ID', 400);
        }

        $purchase = EquipmentPurchase::with(['equipment', 'customer', 'location'])->find($purchaseId);
        if (!$purchase) {
            Log::error('PAYFAST ITN: Purchase not found', ['purchase_id' => $purchaseId]);
            return response('Purchase not found', 404);
        }

        $successStatuses = ['complete', 'completed', 'success', 'paid'];

        if (in_array($paymentStatus, $successStatuses) && $amountPaid > 0) {
            DB::transaction(function () use ($purchase, $amountPaid, $pfPaymentId, $mPaymentId, $request) {
                $purchase->refresh();

                if ($purchase->payment_status === 'paid') return;

                $stockBefore = null;
                $stockAfter = null;

                if ($purchase->location_id) {
                    $stockRow = EquipmentStock::where('equipment_id', $purchase->equipment_id)
                        ->where('location_id', $purchase->location_id)
                        ->lockForUpdate()
                        ->first();

                    if ($stockRow) {
                        $stockBefore = (int) $stockRow->stock;
                        $stockRow->stock = max($stockBefore - (int) $purchase->quantity, 0);
                        $stockRow->save();
                        $stockAfter = (int) $stockRow->stock;
                    }
                }

                $purchase->update([
                    'deposit_paid' => $amountPaid,
                    'payment_status' => 'paid',
                    'payment_method' => 'payfast',
                    'payfast_payment_id' => $mPaymentId ?: $pfPaymentId,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'paid_at' => now(),
                    'payment_details' => json_encode($request->all())
                ]);

                if ($purchase->equipment) {
                    $remainingStock = EquipmentStock::where('equipment_id', $purchase->equipment_id)->sum('stock');
                    if ($remainingStock <= 0) {
                        $purchase->equipment->update(['status' => 'inactive']);
                    }
                }
            });

            try {
                $this->sendPurchaseEmails($purchase, $amountPaid);

                // ✅ Save purchase data to session for confirmation page
                session()->put('confirmed_purchase', [
                    'id' => $purchase->id,
                    'status' => 'success',
                    'payment_method' => 'payfast',
                    'paid_amount' => $amountPaid,
                    'timestamp' => now()->format('Y-m-d H:i:s')
                ]);

            } catch (\Throwable $emailError) {
                Log::warning('PAYFAST ITN EMAIL FAILED', ['error' => $emailError->getMessage()]);
            }
        } else {
            Log::warning('PAYFAST ITN: Payment not successful', [
                'purchase_id' => $purchaseId,
                'status' => $paymentStatus,
                'amount' => $amountPaid
            ]);
        }

        return response('OK', 200);
    }

    /**
     * Show purchase confirmation page
     */
    public function showConfirmation($id)
    {
        $purchase = EquipmentPurchase::with(['equipment', 'customer', 'location'])
            ->findOrFail($id);

        // Check session for confirmation status
        $sessionPurchase = session()->get('confirmed_purchase');
        $isConfirmed = $sessionPurchase && $sessionPurchase['id'] == $id && $sessionPurchase['status'] == 'success';

        if (!$isConfirmed && $purchase->payment_status !== 'paid') {
            // If not confirmed, redirect to home
            return redirect('/')->with('error', 'Purchase not found or payment not completed.');
        }

        return view('purchase.confirmation', [
            'purchase' => $purchase,
            'sessionData' => $sessionPurchase
        ]);
    }

    /**
     * PayFast return URL for equipment purchase - UPDATED
     */
    public function payfastPurchaseReturn(Request $request)
    {
        Log::info('🔙 PAYFAST RETURN URL HIT', [
            'all_params' => $request->all(),
            'query_params' => $request->query(),
            'post_params' => $request->post()
        ]);

        $purchaseId = $request->query('purchase_id') ?? $request->input('custom_str1');
        $paymentStatus = $request->input('payment_status', 'pending');

        Log::info('🔍 PAYFAST RETURN: SEARCHING PURCHASE', ['purchase_id' => $purchaseId]);

        if (!$purchaseId) {
            Log::warning('⚠️ PAYFAST RETURN: NO PURCHASE ID FOUND');
            return redirect('/?payment_status=error&message=missing_purchase_id');
        }

        // Find purchase with latest data
        $purchase = EquipmentPurchase::find($purchaseId);

        if (!$purchase) {
            Log::error('❌ PAYFAST RETURN: PURCHASE NOT FOUND', ['purchase_id' => $purchaseId]);
            return redirect('/?payment_status=error&message=purchase_not_found');
        }

        $dbStatus = strtolower($purchase->payment_status ?? 'pending');

        Log::info('📊 PAYFAST RETURN: DATABASE STATUS', [
            'purchase_id' => $purchaseId,
            'db_status' => $dbStatus,
            'deposit_paid' => $purchase->deposit_paid
        ]);

        // Determine final status
        if (in_array($dbStatus, ['paid', 'succeeded', 'complete'])) {
            // Redirect to confirmation page
            return redirect('/purchase/confirmation/' . $purchaseId);
        } else {
            // Still pending, show message
            return redirect('/')->with('info', 'Payment is being processed. You will receive confirmation shortly.');
        }
    }

    /**
     * PayFast cancel URL for equipment purchase
     */
    public function payfastPurchaseCancel(Request $request)
    {
        $purchaseId = $request->query('purchase_id') ?? $request->input('custom_str1');

        Log::info('❌ PAYFAST CANCEL HIT', [
            'purchase_id' => $purchaseId,
            'all_params' => $request->all()
        ]);

        return redirect('/')->with('error', 'Payment was cancelled.');
    }

    /**
     * Restore purchase data (for returning users)
     */
    public function restorePurchaseData($id)
    {
        $purchase = EquipmentPurchase::with(['equipment', 'customer', 'location'])->find($id);

        if (!$purchase) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'purchase' => [
                'id' => $purchase->id,
                'equipment' => [
                    'id' => $purchase->equipment->id,
                    'name' => $purchase->equipment->name,
                    'image' => $purchase->equipment->mainImage(),
                    'price' => $purchase->equipment->sale_price,
                    'deposit' => $purchase->equipment->deposit_amount,
                ],
                'quantity' => $purchase->quantity,
                'location' => [
                    'id' => $purchase->location->id,
                    'name' => $purchase->location->name,
                ],
                'customer' => [
                    'name' => $purchase->customer->name,
                    'email' => $purchase->customer->email,
                    'phone' => $purchase->customer->phone,
                    'country' => $purchase->customer->country,
                ],
                'payment' => [
                    'status' => $purchase->payment_status,
                    'method' => $purchase->payment_method,
                    'deposit_paid' => $purchase->deposit_paid,
                    'total_price' => $purchase->total_price,
                    'paid_at' => $purchase->paid_at,
                    'receipt_url' => $purchase->receipt_url,
                ]
            ]
        ]);
    }

    /**
     * Clear purchase session
     */
    public function clearSession()
    {
        session()->forget('confirmed_purchase');
        return response()->json(['success' => true]);
    }

    /** Email sending for purchases */
    private function sendPurchaseEmails(EquipmentPurchase $purchase, float $paidAmount): void
    {
        try {
            $settings = SystemSetting::first();
            $this->configureMailerFromSettings($settings);
            $ownerEmail = $this->resolveOwnerEmail($settings);

            if ($purchase->customer?->email) {
                Log::info('Sending PurchaseReceipt email', ['to' => $purchase->customer->email, 'purchase_id' => $purchase->id]);
                Mail::to($purchase->customer->email)
                    ->send(new PurchaseReceipt($purchase, $paidAmount));
            }

            if ($ownerEmail) {
                Log::info('Sending OwnerPurchaseAlert email', ['to' => $ownerEmail, 'purchase_id' => $purchase->id]);
                Mail::to($ownerEmail)
                    ->send(new OwnerPurchaseAlert($purchase, $paidAmount));
            }
        } catch (\Throwable $e) {
            Log::warning('Purchase email failed', ['purchase_id' => $purchase->id, 'error' => $e->getMessage()]);
        }
    }

    /** Configure mailer from system settings */
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

    /** Resolve owner email address */
    private function resolveOwnerEmail(?SystemSetting $settings = null): ?string
    {
        $settings = $settings ?: SystemSetting::first();
        return $settings?->mail_owner_address
            ?? $settings?->mail_from_address
            ?? config('mail.from.address')
            ?? null;
    }
}
