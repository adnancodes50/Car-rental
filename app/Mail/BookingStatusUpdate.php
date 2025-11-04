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
        $itemDisplay = $equipment?->name ?? '';

        $itemRows = $this->buildRow('Equipment', $itemDisplay);
        $itemRows .= $this->buildRow('Location', $location?->name ?? '');
        $itemRows .= $this->buildRow('Units Booked', $booking->booked_stock ? (string) $booking->booked_stock : null);

        $data = [
            'app_name' => config('app.name', 'Our Site'),
            'customer_name' => $customer->name ?? 'Customer',
            'booking_id' => (string) $booking->id,
            'booking_reference' => $reference,
            'booking_reference_paren' => $booking->reference ? '(' . $booking->reference . ')' : '',
            'item_name' => $itemDisplay,
            'vehicle_name' => $itemDisplay,
            'location_name' => $location?->name ?? '',
            'start_date' => $this->formatDate($booking->start_date),
            'end_date' => $this->formatDate($booking->end_date),
            'status' => $this->status,
            'paid_now' => number_format($totalPrice, 2),
            'total_amount' => number_format($totalPrice, 2),
            'logo_url' => asset('vendor/adminlte/dist/img/logo.png'),
            'year' => date('Y'),
            'status_message' => in_array($statusLower, ['canceled', 'cancelled'], true)
                ? 'Unfortunately, your booking has been canceled. Please contact our support team if you have any questions or need assistance.'
                : "Your booking status has been updated to {$this->status}.",
        ];

        $raw = [
            'item_rows' => $itemRows,
            'vehicle_row' => $itemRows,
            'receipt_button' => '',
        ];

        $statusKey = strtolower($this->status);
        $triggerStatus = [
            'completed' => 'complete',
            'canceled' => 'cancelled',
        ][$statusKey] ?? $statusKey;

        $trigger = 'booking_' . $triggerStatus;
        $template = EmailTemplate::for($trigger, 'customer');

        if ($template) {
            $subject = $this->replacePlaceholders($template->subject, $data);
            $body = $this->replacePlaceholders($template->body, $data, array_keys($raw));
            $body = $this->replacePlaceholders($body, $raw, [], false);

            return $this->subject($subject)->html($body);
        }

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
            $replacement = (in_array($key, $rawKeys, true) || !$escapeAll) ? (string) $val : e((string) $val);
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

    protected function buildRow(string $label, ?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return '<tr>
            <td style="padding:6px 0;color:#555;">' . e($label) . '</td>
            <td style="padding:6px 0;text-align:right;color:#111;">' . e($value) . '</td>
        </tr>';
    }
}
