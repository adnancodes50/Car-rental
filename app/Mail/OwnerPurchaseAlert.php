<?php

namespace App\Mail;

use App\Models\EquipmentPurchase;
use App\Models\EmailTemplate;
use Illuminate\Mail\Mailable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class OwnerPurchaseAlert extends Mailable
{
    use Queueable, SerializesModels;

    public EquipmentPurchase $purchase;
    public float $paidNow;

    public function __construct(EquipmentPurchase $purchase, float $paidNow = 0.0)
    {
        $this->purchase = $purchase->loadMissing('equipment', 'customer');
        $this->paidNow  = $paidNow;
    }

    public function build(): self
    {
        $p    = $this->purchase;
        $e    = $p->equipment;
        $cust = $p->customer;

        // Escaped placeholders
        $data = [
            'app_name'             => config('app.name', 'Our Site'),
            'year'                 => date('Y'),
            'purchase_id'          => (string) $p->id,
            'customer_name'        => $cust->name ?? 'Customer',
            'customer_email_paren' => ($cust && $cust->email) ? ' (' . $cust->email . ')' : '',
            'paid_now'             => number_format($this->paidNow, 2),
            'deposit_paid'         => number_format((float) $p->deposit_paid, 2),
            'logo_url'             => asset('vendor/adminlte/dist/img/logo.png'),
        ];

        // Raw HTML fragments
        $raw = [
            'equipment_row' => $e ? (
                '<tr>
                    <td style="padding:6px 0;color:#555;">Equipment</td>
                    <td style="padding:6px 0;text-align:right;color:#111;">'
                    . e($e->name . (($e->year || $e->model) ? " ({$e->year} {$e->model})" : '')) .
                '</td></tr>'
            ) : '',
            'receipt_button' => !empty($p->receipt_url)
                ? '<div style="text-align:center;padding:18px 0 4px;">
                        <a href="'.e($p->receipt_url).'" target="_blank" rel="noopener"
                           style="display:inline-block;background:#CF9B4D;color:#fff;text-decoration:none;font:600 13px/1 system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;padding:10px 16px;border-radius:10px;">
                          View Receipt
                        </a>
                   </div>'
                : '',
        ];

        // DB template
        $tpl = EmailTemplate::for('purchase_deposit', 'admin')
            ?: EmailTemplate::for('owner_purchase_alert', 'admin');

        if ($tpl) {
            $subject = $this->replacePlaceholders($tpl->subject, $data);
            $body    = $this->replacePlaceholders($tpl->body, $data);
            $body    = $this->replacePlaceholders($body, $raw, [], $escapeAll = false);
            return $this->subject($subject)->html($body);
        }

        // Fallback Blade
        return $this->subject('New Deposit Received #' . $p->id)
                    ->view('emails.owner_purchase_alert', [
                        'purchase' => $this->purchase,
                        'paidNow'  => $this->paidNow,
                    ]);
    }

    protected function replacePlaceholders(
        string $text,
        array $values,
        array $rawKeys = [],
        bool $escapeAll = true
    ): string {
        foreach ($values as $key => $val) {
            $replacement = (in_array($key, $rawKeys, true) || !$escapeAll)
                ? (string) $val
                : e((string) $val);
            $text = str_replace('{{' . $key . '}}', $replacement, $text);
        }
        return $text;
    }
}
