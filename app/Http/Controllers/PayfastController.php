<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Purchase;

class PayfastController extends Controller
{
    /** Build PayFast signature for a field array (excluding 'signature'). */
    protected function signature(array $fields, ?string $passphrase): string
    {
        $data = $fields;
        unset($data['signature']);

        // RFC1738 encoding, order by keys not required (http_build_query keeps array order)
        $query = http_build_query($data, '', '&', PHP_QUERY_RFC1738);

        if (!empty($passphrase)) {
            $query .= '&passphrase=' . urlencode($passphrase);
        }

        return md5($query);
    }

    /** Build the PayFast process URL based on testmode. */
    protected function processUrl(): string
    {
        $cfg = config('payfast');
        return $cfg['testmode'] ? $cfg['urls']['sandbox'] : $cfg['urls']['live'];
    }

    /** Start a PayFast payment (typically the deposit). */
    public function initiate(Request $request, Purchase $purchase)
    {
        $cfg = config('payfast');
        // Decide what to charge now: deposit if set, otherwise full price
        $vehiclePrice = (float) ($purchase->total_price ?? 0);
        $deposit      = (float) ($purchase->deposit_amount ?? 0);
        $amountNow    = $deposit > 0 ? $deposit : $vehiclePrice;

        // Keep sane bounds
        $amountNow = max(0, min($amountNow, $vehiclePrice));
        $amount    = number_format($amountNow, 2, '.', '');

        $fields = [
            'merchant_id'    => $cfg['merchant_id'],
            'merchant_key'   => $cfg['merchant_key'],
            'return_url'     => url($cfg['return_url']),
            'cancel_url'     => url($cfg['cancel_url']),
            'notify_url'     => url($cfg['notify_url']),

            // Your identifiers
            'm_payment_id'   => 'PUR-' . $purchase->id,     // your reference
            'amount'         => $amount,
            'item_name'      => 'Vehicle purchase #' . $purchase->id,
            'item_description' => 'Deposit / purchase payment',

            // Optional buyer details (if you have them on Purchase)
            'name_first'     => $purchase->name ?? '',
            'email_address'  => $purchase->email ?? '',
        ];

        $fields['signature'] = $this->signature($fields, $cfg['passphrase']);

        // Render a tiny form that auto-posts to PayFast
        return response()->view('payfast.redirect', [
            'action' => $this->processUrl(),
            'fields' => $fields,
        ]);
    }

    /** Buyer returns after payment (success page) */
    public function return(Request $request)
    {
        // You can show a nice “we’re processing your payment” page;
        // The *definitive* status is handled by notify() (webhook).
        return view('payfast.return');
    }

    /** Buyer cancelled on PayFast */
    public function cancel(Request $request)
    {
        return view('payfast.cancel');
    }

    /** Webhook (ITN) from PayFast with final status */
    public function notify(Request $request)
    {
        $cfg = config('payfast');
        $posted = $request->all();

        // 1) Signature check
        $theirSig = $posted['signature'] ?? '';
        $calcSig  = $this->signature($posted, $cfg['passphrase']);
        if (!hash_equals($theirSig, $calcSig)) {
            Log::warning('PayFast ITN: invalid signature', ['posted' => $posted]);
            return response('Invalid signature', 400);
        }

        // 2) Validate with PayFast (server-to-server)
        $endpoint = $cfg['testmode'] ? 'https://sandbox.payfast.co.za' : 'https://www.payfast.co.za';
        $validate = Http::asForm()->post($endpoint . '/eng/query/validate', $posted);

        if (!$validate->ok() || trim($validate->body()) !== 'VALID') {
            Log::warning('PayFast ITN: validate NOT VALID', ['body' => $validate->body(), 'posted' => $posted]);
            return response('Invalid', 400);
        }

        // 3) Update your DB
        $mPaymentId    = $posted['m_payment_id'] ?? '';
        $paymentStatus = $posted['payment_status'] ?? '';
        $amountGross   = (float) ($posted['amount_gross'] ?? 0);
        $pfPaymentId   = $posted['pf_payment_id'] ?? null;

        // Extract purchase id from your reference
        $purchaseId = (int) str_replace('PUR-', '', $mPaymentId);
        $purchase   = Purchase::find($purchaseId);
        if (!$purchase) {
            Log::warning('PayFast ITN: purchase not found', ['m_payment_id' => $mPaymentId]);
            return response('Not found', 404);
        }

        if ($paymentStatus === 'COMPLETE') {
            // Mark deposit paid (or add to it if you support part-payments)
            $newPaid = (float) ($purchase->deposit_paid ?? 0) + $amountGross;
            $purchase->update([
                'payment_method' => 'payfast',
                'payment_status' => 'succeeded',
                'deposit_paid'   => $newPaid,
                'pf_payment_id'  => $pfPaymentId,
            ]);
        } elseif ($paymentStatus === 'FAILED') {
            $purchase->update(['payment_status' => 'failed']);
        } else {
            $purchase->update(['payment_status' => 'pending']);
        }

        // Must return 200 so PayFast stops retrying
        return response('OK', 200);
    }
}
