<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DynamicEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $subjectText;
    public $bodyHtml;

    public function __construct(string $subjectText, string $bodyHtml)
    {
        $this->subjectText = $subjectText;
        $this->bodyHtml = $bodyHtml;
    }

    public function build()
    {
        return $this->subject($this->subjectText)
                    ->html($this->bodyHtml);
    }
}
