<?php
namespace App\Mail;
use App\Models\Purchase;
use Illuminate\Mail\Mailable;

class OwnerPurchaseAlert extends Mailable {
    public function __construct(public Purchase $purchase, public float $paidNow) {}
    public function build() {
        return $this->subject('New Deposit Received #'.$this->purchase->id)
                    ->view('emails.owner_purchase_alert');
    }
}
