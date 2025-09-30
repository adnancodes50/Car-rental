<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $table = 'system_settings';

    protected $fillable = [
        // Stripe
        'stripe_key',
        'stripe_secret',
        'stripe_mode',
        'stripe_enabled',

        // PayFast
        'payfast_merchant_id',
        'payfast_merchant_key',
        'payfast_passphrase',
        'payfast_test_mode',
        'payfast_enabled',
        'payfast_live_url',

        // SMTP / Mail
        'mail_username',
        'mail_password',
        'mail_host',
        'mail_port',
        'mail_encryption',
        'mail_from_address',
        'mail_from_name',
        'mail_owner_address', // ✅ add this
        'mail_enabled',
    ];

    protected $casts = [
        // booleans
        'stripe_enabled'      => 'bool',
        'payfast_test_mode'   => 'bool',
        'payfast_enabled'     => 'bool',
        'mail_enabled'        => 'bool',

        // numerics
        'mail_port'           => 'integer',
    ];
}



