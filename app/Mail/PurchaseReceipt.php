<?php

namespace App\Mail;

use App\Models\EquipmentPurchase;
use App\Models\EmailTemplate;
use Illuminate\Mail\Mailable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class PurchaseReceipt extends Mailable
{
    use Queueable, SerializesModels;

    public EquipmentPurchase $purchase;
    public float $paidNow;

    public function __construct(EquipmentPurchase $purchase, float $paidNow = 0.0)
    {
        // Load relations
        $this->purchase = $purchase->loadMissing('equipment', 'customer');
        $this->paidNow  = $paidNow;
    }

    public function build(): self
    {
        $p    = $this->purchase;
        $e    = $p->equipment;
        $cust = $p->customer;

        // Escaped placeholders (plain text)
        $data = [
            'app_name'     => config('app.name', 'Our Site'),
            'year'         => date('Y'),
            'logo_url'     => asset('vendor/adminlte/dist/img/logo.png'),
            'purchase_id'  => (string) $p->id,
            'customer_name'=> $cust->name ?? 'Customer',
            'paid_now'     => number_format($this->paidNow, 2),
            'deposit_paid' => number_format((float) $p->deposit_paid, 2),
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
                ? '<tr><td align="center" style="padding:20px 24px 8px;">
                        <a href="'.e($p->receipt_url).'" target="_blank" rel="noopener"
                           style="display:inline-block;background:#CF9B4D;color:#fff;text-decoration:none;font:600 14px/1 system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;padding:12px 18px;border-radius:10px;">
                          View Receipt
                        </a>
                   </td></tr>'
                : '',
        ];

        // DB template
        $tpl = EmailTemplate::for('purchase_receipt', 'customer');

        if ($tpl) {
            $subject = $this->replacePlaceholders($tpl->subject, $data);
            $body    = $this->replacePlaceholders($tpl->body, $data);
            $body    = $this->replacePlaceholders($body, $raw, [], $escapeAll = false); // inject raw HTML
            return $this->subject($subject)->html($body);
        }

        // Fallback Blade
        return $this->subject('Your Equipment Deposit Receipt #' . $p->id)
                    ->view('emails.purchase_receipt', [
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
