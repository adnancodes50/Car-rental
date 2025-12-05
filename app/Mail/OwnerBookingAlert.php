<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class OwnerBookingAlert extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;
    public float $paidNow;

    public function __construct(Booking $booking, float $paidNow = 0.0)
    {
        // load equipment instead of vehicle
        $this->booking = $booking->loadMissing('equipment', 'customer');
        $this->paidNow = $paidNow;
    }

    public function build(): self
    {
        $booking = $this->booking;
        $e       = $booking->equipment;
        $cust    = $booking->customer;

        // -------- Escaped placeholders (plain text values) ----------
        $data = [
            'app_name'               => config('app.name', 'Our Site'),
            'year'                   => date('Y'),

            'booking_id'             => (string) $booking->id,
            'booking_reference'      => $booking->reference ?: ('BK-' . $booking->id),
            'booking_reference_paren'=> $booking->reference ? '(' . $booking->reference . ')' : '',

            'customer_name'          => $cust->name ?? 'Customer',
            'customer_email'         => $cust->email ?? '',

            'start_date'             => Carbon::parse($booking->start_date)->toFormattedDateString(),
            'end_date'               => Carbon::parse($booking->end_date)->toFormattedDateString(),
            'status'                 => ucfirst($booking->payment_status ?: ($booking->status ?: 'pending')),

            'paid_now'               => number_format($this->paidNow ?: (float) $booking->total_price, 2),
            'total_amount'           => number_format((float) $booking->total_price, 2),

            'payment_method'         => $booking->payment_method ? ucfirst($booking->payment_method) : '—',
            'logo_url'               => asset('vendor/adminlte/dist/img/logo.png'),
        ];

        // -------- Raw HTML fragments you control (inserted unescaped) ----------
        $raw = [
            'equipment_row' => $e ? (
                '<tr>
                    <td style="padding:6px 0;color:#555;">Equipment</td>
                    <td style="padding:6px 0;text-align:right;color:#111;">'
                    . e($e->name . (($e->year || $e->model) ? " ({$e->year} {$e->model})" : '')) .
                '</td></tr>'
            ) : '',
            'receipt_button' => $booking->receipt_url
                ? '<div style="text-align:center;padding:18px 0 4px;">
                        <a href="'.e($booking->receipt_url).'" target="_blank" rel="noopener"
                           style="display:inline-block;background:#CF9B4D;color:#fff;text-decoration:none;font:600 13px/1 system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;padding:10px 16px;border-radius:10px;">
                          View Receipt
                        </a>
                   </div>'
                : '',
        ];

        // -------- Try DB template(s) --------
        // Preferred: use the "booking_receipt" admin template (payment received).
        $tpl = EmailTemplate::for('booking_receipt', 'admin');

        // Fallback to a dedicated owner alert trigger if you add one later.
        if (!$tpl) {
            $tpl = EmailTemplate::for('owner_booking_alert', 'admin');
        }

        if ($tpl) {
            // Subject: simple key replace (escaped)
            $subject = $this->replacePlaceholders($tpl->subject, $data, []);

            // Body: first inject escaped keys, then inject raw fragments (equipment_row, receipt_button)
            $body = $this->replacePlaceholders($tpl->body, $data, []);
            $body = $this->replacePlaceholders($body, $raw, [], $escapeAll = false);

            return $this->subject($subject)->html($body);
        }

        // -------- Blade fallback (your existing view) --------
        $app = config('app.name', 'Our Site');
        $ref = $booking->reference ?? ('BK-' . $booking->id);

        $subjectLabel = $this->paidNow > 0 ? 'New Booking Paid' : 'New Booking Created';

        return $this->subject("{$app} • {$subjectLabel} ({$ref})")
            ->view('emails.owner_alert', [
                'booking' => $this->booking,
                'paidNow' => $this->paidNow,
            ]);
    }

    /**
     * Tiny placeholder engine:
     * - Escapes by default (safe for HTML)
     * - For known-safe fragments you created in code, pass $escapeAll=false (raw insert)
     */
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
