<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomerBulkMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subjectText;
    public $bodyContent;

    /**
     * Create a new message instance.
     */
    public function __construct($subjectText, $bodyContent)
    {
        $this->subjectText = $subjectText;
        $this->bodyContent = $bodyContent;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject($this->subjectText)
                    ->view('emails.customer-bulk-mail')
                    ->with(['bodyContent' => $this->bodyContent]);
    }
}
