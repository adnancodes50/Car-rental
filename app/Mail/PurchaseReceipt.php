<?php
namespace App\Mail;
use App\Models\Purchase;
use Illuminate\Mail\Mailable;

class PurchaseReceipt extends Mailable {
    public function __construct(public Purchase $purchase, public float $paidNow) {}
    public function build() {
        return $this->subject('Your Vehicle Deposit Receipt #'.$this->purchase->id)
                    ->view('emails.purchase_receipt');
    }
}




// >>> \App\Services\MailConfigurator::apply();
// => true

// >>> use Illuminate\Support\Facades\Mail;

// >>> Mail::raw('SMTP OK', function ($m) {
// ...     $m->to('danimehar4749@gmail.com')->subject('Ping');
// ... });
// => null
