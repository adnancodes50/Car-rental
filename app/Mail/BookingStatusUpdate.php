<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\EmailTemplate;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingStatusUpdate extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;
    public string $status;

    public function __construct(Booking $booking, string $status)
    {
        $this->booking = $booking->loadMissing('equipment', 'customer', 'location');
        $this->status = ucfirst($status);
    }

    public function build(): self
    {
        $booking = $this->booking;
        $equipment = $booking->equipment;
        $customer = $booking->customer;
        $location = $booking->location;

        $statusLower = strtolower($this->status);
        $totalPrice = (float) ($booking->total_price ?? 0);
        $reference = $booking->reference ?: ('BK-' . $booking->id);
        $equipmentName = $equipment?->name ?? '';
        $locationName = $location?->name ?? '';

        /* --------------------------
         * MAIN DATA PASSED TO PLACEHOLDERS
         * -------------------------- */
        $data = [
            'app_name'         => config('app.name', 'Our Site'),
            'year'             => date('Y'),
            'logo_url'         => asset('vendor/adminlte/dist/img/logo.png'),

            // Customer
            'customer_name'    => $customer->name ?? 'Customer',

            // Booking Details
            'booking_reference'=> $reference,
            'start_date'       => $this->formatDate($booking->start_date),
            'end_date'         => $this->formatDate($booking->end_date),
            'status'           => $this->status,
            'paid_now'         => number_format($totalPrice, 2),
            'total_amount'     => number_format($totalPrice, 2),

            // Equipment + Location
            'equipment_name'   => $equipmentName,
            'location_name'    => $locationName,

            // Receipt
            // 'receipt_url'      => route('receipt.view', $booking->id),

            // Template-specific fields (these come from EmailTemplate)
            'header_text'      => '',
            'lead_title'       => '',
            'lead_line'        => '',
            'summary_title'    => '',
            'footer_message'   => '',
        ];

        /* --------------------------
         * RAW DATA (HTML ALLOWED)
         * -------------------------- */
        $raw = [
            'item_rows'      => '',
            'vehicle_row'    => '',
        ];

        /* --------------------------
         * RESOLVE WHICH TEMPLATE TO LOAD
         * booking_cancelled, booking_pending, etc.
         * -------------------------- */
        $statusKey = strtolower($this->status);
        $triggerStatus = [
            'completed' => 'complete',
            'canceled'  => 'cancelled',
        ][$statusKey] ?? $statusKey;

        $trigger = 'booking_' . $triggerStatus;

        $template = EmailTemplate::for($trigger, 'customer');

        if ($template) {

            // Inject template custom fields into $data
            $data['header_text']    = $template->header_text ?? '';
            $data['lead_title']     = $template->lead_title ?? '';
            $data['lead_line']      = $template->lead_line ?? '';
            $data['summary_title']  = $template->summary_title ?? '';
            $data['footer_message'] = $template->footer_message ?? '';

            /* --------------------------
             * REPLACE ALL PLACEHOLDERS
             * -------------------------- */
            $subject = $this->replacePlaceholders($template->subject, $data);
            $body    = $this->replacePlaceholders($template->body, $data);
            $body    = $this->replacePlaceholders($body, $raw, [], false);

            return $this->subject($subject)->html($body);
        }

        /* --------------------------
         * FALLBACK (if template missing)
         * -------------------------- */
        $fallbackSubject = sprintf(
            '%s - Booking %s (%s)',
            config('app.name'),
            $this->status,
            $reference
        );

        return $this->subject($fallbackSubject)
            ->view('emails.booking_status', $data + $raw);
    }

    protected function replacePlaceholders(string $text, array $values, array $rawKeys = [], bool $escapeAll = true): string
    {
        foreach ($values as $key => $val) {
            $replacement = (in_array($key, $rawKeys, true) || !$escapeAll)
                ? (string) $val
                : e((string) $val);

            $text = str_replace('{{' . $key . '}}', $replacement, $text);
        }

        return $text;
    }

    protected function formatDate($value): string
    {
        if (empty($value)) {
            return 'To be confirmed';
        }

        try {
            return Carbon::parse($value)->toFormattedDateString();
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }
}
