<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class BookingStatusUpdate extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;
    public string $status;

    public function __construct(Booking $booking, string $status)
    {
        $this->booking = $booking->loadMissing('vehicle', 'customer');
        $this->status = ucfirst($status);
    }

    public function build(): self
    {
        $booking = $this->booking;
        $vehicle = $booking->vehicle;
        $customer = $booking->customer;

        // Placeholder values
        $data = [
            'app_name' => config('app.name', 'Our Site'),
            'customer_name' => $customer->name ?? 'Customer',
            'booking_id' => (string) $booking->id,
            'booking_reference' => $booking->reference ?: ('BK-' . $booking->id),
            'booking_reference_paren' => $booking->reference ? '(' . $booking->reference . ')' : '',
            'vehicle_name' => $vehicle?->name ? ($vehicle->name . (($vehicle->year || $vehicle->model) ? " ({$vehicle->year} {$vehicle->model})" : '')) : '',
            'start_date' => Carbon::parse($booking->start_date)->toFormattedDateString(),
            'end_date' => Carbon::parse($booking->end_date)->toFormattedDateString(),
            'status' => $this->status,
            'paid_now' => number_format((float)$booking->total_price, 2),
            'total_amount' => number_format((float)$booking->total_price, 2),
            'logo_url' => asset('vendor/adminlte/dist/img/logo.png'),
            'year' => date('Y'),
            'status_message' => strtolower($this->status) === 'canceled'
                ? 'Unfortunately, your booking has been canceled. Please contact our support team if you have any questions or need assistance.'
                : "Your booking status has been updated to {$this->status}.",
        ];

        $raw = [
            'vehicle_row' => $vehicle ? (
                '<tr>
                    <td style="padding:6px 0;color:#555;">Vehicle</td>
                    <td style="padding:6px 0;text-align:right;color:#111;">'
                    . e($vehicle->name . (($vehicle->year || $vehicle->model) ? " ({$vehicle->year} {$vehicle->model})" : '')) .
                '</td></tr>'
            ) : '',
            'receipt_button' => '',
        ];

        // Determine DB template trigger
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


        // Fallback Blade view
        return $this->subject(config('app.name') . " • Booking {$this->status} ({$booking->reference})")
            ->view('emails.booking_status', $data + $raw);
    }

    protected function replacePlaceholders(string $text, array $values, array $rawKeys = [], bool $escapeAll = true): string
    {
        foreach ($values as $key => $val) {
            $replacement = (in_array($key, $rawKeys, true) || !$escapeAll) ? (string)$val : e((string)$val);
            $text = str_replace('{{'.$key.'}}', $replacement, $text);
        }
        return $text;
    }
}
