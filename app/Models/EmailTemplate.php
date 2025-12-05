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
     * Fetch template by trigger & recipient type.
     */
    public static function for(string $trigger, string $recipient)
    {
        return self::where('trigger', $trigger)
                   ->where('recipient', $recipient)
                   ->where('enabled', 1)
                   ->first();
    }

    /**
     * Render placeholders in body safely.
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
     * Render placeholders in subject.
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
