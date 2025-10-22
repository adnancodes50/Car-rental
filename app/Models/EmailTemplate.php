<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = [
        'trigger',
        'recipient',
        'name',
        'subject',
        'body',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    /**
     * Quick helper: fetch a template by trigger & recipient.
     */
    public static function for(string $trigger, string $recipient)
{
    return self::where('trigger', $trigger)
               ->where('recipient', $recipient)
               ->where('enabled', 1)
               ->first();
}


    /**
     * Replace placeholders (e.g. {{customer_name}}) with data.
     * Escapes values by default (safe for HTML injection).
     */
    public function renderBody(array $data = []): string
    {
        $body = $this->body;
        foreach ($data as $key => $value) {
            $body = str_replace('{{' . $key . '}}', e($value), $body);
        }
        return $body;
    }

    /**
     * Replace placeholders in the subject.
     */
    public function renderSubject(array $data = []): string
    {
        $subject = $this->subject;
        foreach ($data as $key => $value) {
            $subject = str_replace('{{' . $key . '}}', $value, $subject);
        }
        return $subject;
    }
}
