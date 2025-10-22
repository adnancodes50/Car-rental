<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class BookingReceipt extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;
    public float $paidNow;

    /**
     * @param  Booking  $booking
     * @param  float    $paidNow
     */
    public function __construct(Booking $booking, float $paidNow = 0.0)
    {
        $this->booking = $booking->loadMissing('vehicle', 'customer');
        $this->paidNow = $paidNow;
    }

    public function build(): self
    {
        $booking = $this->booking;
        $v       = $booking->vehicle;
        $cust    = $booking->customer;

        // --- Plain-text placeholders (escaped) ---
        $data = [
            'app_name'               => config('app.name', 'Our Site'),
            'customer_name'          => $cust->name ?? 'Customer',
            'booking_id'             => (string) $booking->id,
            'booking_reference'      => $booking->reference ?: ('BK-' . $booking->id),
            'booking_reference_paren'=> $booking->reference ? '(' . $booking->reference . ')' : '',
            'vehicle_name'           => $v?->name
                                        ? ($v->name . (($v->year || $v->model) ? " ({$v->year} {$v->model})" : ''))
                                        : '',
            'start_date'             => Carbon::parse($booking->start_date)->toFormattedDateString(),
            'end_date'               => Carbon::parse($booking->end_date)->toFormattedDateString(),
            'status'                 => ucfirst($booking->payment_status ?: ($booking->status ?: 'pending')),
            'paid_now'               => number_format($this->paidNow ?: (float) $booking->total_price, 2),
            'total_amount'           => number_format((float) $booking->total_price, 2),
            'logo_url'               => asset('vendor/adminlte/dist/img/logo.png'),
            'year'                   => date('Y'),
        ];

        // --- HTML fragments (raw) you might want to inline into the template body ---
        // If you don't reference {{vehicle_row}} or {{receipt_button}} in the DB template body,
        // these simply won't be used.
        $raw = [
            'vehicle_row' => $v ? (
                '<tr>
                    <td style="padding:6px 0;color:#555;">Vehicle</td>
                    <td style="padding:6px 0;text-align:right;color:#111;">'
                    . e($v->name . (($v->year || $v->model) ? " ({$v->year} {$v->model})" : '')) .
                '</td></tr>'
            ) : '',
            'receipt_button' => $booking->receipt_url
                ? '<tr><td align="center" style="padding:20px 24px 8px;">
                        <a href="'.e($booking->receipt_url).'" target="_blank" rel="noopener"
                           style="display:inline-block;background:#CF9B4D;color:#fff;text-decoration:none;font:600 14px/1 system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;padding:12px 18px;border-radius:10px;">
                          View Receipt
                        </a>
                   </td></tr>'
                : '',
        ];

        // Try DB template for (trigger, recipient)
        $tpl = EmailTemplate::for('booking_receipt', 'customer');

        if ($tpl) {
            // Subject: simple replacement, no HTML expected.
            $subject = $this->replacePlaceholders($tpl->subject, $data, []);

            // Body: escape normal keys, inject "raw" keys as-is
            $body = $this->replacePlaceholders($tpl->body, $data, array_keys($raw));
            $body = $this->replacePlaceholders($body, $raw, [], $escapeAll = false); // inject raw HTML

            return $this->subject($subject)->html($body);
        }

        // Fallback to your Blade view
        $app = config('app.name', 'Our Site');
        $ref = $booking->reference ?? ('BK-' . $booking->id);

        return $this->subject("{$app} • Booking Receipt ({$ref})")
            ->view('emails.receipt', [
                'booking' => $this->booking,
                'paidNow' => $this->paidNow,
            ]);
    }

    /**
     * Lightweight placeholder replacement:
     * - By default, values are escaped (safe for HTML email).
     * - $rawKeys are injected unescaped (for known-safe fragments you build in code).
     *
     * Placeholders look like: {{key}}
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
