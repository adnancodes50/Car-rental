<?php
namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Config;

class MailConfigurator
{
    public static function apply(?SystemSetting $s = null): bool
    {
        $s = $s ?: SystemSetting::first();
        if (!$s || !$s->mail_enabled) return false;
        if (!$s->mail_host || !$s->mail_port || !$s->mail_username || !$s->mail_password) return false;

        Config::set('mail.default', 'smtp');
        Config::set('mail.from.address', $s->mail_from_address ?: config('mail.from.address'));
        Config::set('mail.from.name', $s->mail_from_name ?: config('mail.from.name'));

        Config::set('mail.mailers.smtp', [
            'transport'  => 'smtp',
            'host'       => $s->mail_host,
            'port'       => (int) $s->mail_port,
            'encryption' => $s->mail_encryption ?: null, // 'tls'|'ssl'|null
            'username'   => $s->mail_username,
            'password'   => $s->mail_password,
            'timeout'    => null,
            'auth_mode'  => null,
        ]);
        return true;
    }

    public static function ownerEmail(?SystemSetting $s = null): ?string
    {
        $s = $s ?: SystemSetting::first();
        return $s?->mail_owner_address
            ?: ($s?->mail_from_address ?: (config('mail.from.address') ?: env('OWNER_EMAIL')));
    }
}



// >>> \App\Services\MailConfigurator::apply();
// => true   // should be true if your DB settings are filled and mail_enabled = 1

// >>> Mail::raw('SMTP OK', fn($m) => $m->to('you@example.com')->subject('Ping'));
// => null   // if no exception, it queued/sent
// \Illuminate\Support\Facades\Mail::raw('SMTP OK', function ($m) {
// ...     $m->to('danimehar4749@gmail.com')->subject('Ping');
// ... });
