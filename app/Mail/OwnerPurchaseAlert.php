<?php

namespace App\Mail;

use App\Models\Purchase;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OwnerPurchaseAlert extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Purchase $purchase, public float $paidNow)
    {
        //
    }

    public function build()
    {
        return $this->subject('New Purchase Payment Received')
            ->markdown('emails.purchase.owner_alert', [
                'purchase' => $this->purchase->load('vehicle','customer'),
                'paidNow'  => $this->paidNow,
                'remaining'=> max(($this->purchase->total_price ?? 0) - ($this->purchase->deposit_paid ?? 0), 0),
            ]);
    }
}
