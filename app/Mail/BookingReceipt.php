<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingReceipt extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;
    public float $paidNow;

    /**
     * @param  Booking  $booking
     * @param  float    $paidNow  Amount paid in this transaction
     */
    public function __construct(Booking $booking, float $paidNow = 0.0)
    {
        $this->booking = $booking->loadMissing('vehicle', 'customer');
        $this->paidNow = $paidNow;
    }

    public function build(): self
    {
        $app = config('app.name', 'Our Site');
        $ref = $this->booking->reference ?? ('BK-' . $this->booking->id);

        return $this->subject("{$app} • Booking Receipt ({$ref})")
            ->markdown('emails.receipt');
    }
}
