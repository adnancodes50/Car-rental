<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayfastSetting extends Model
{
    protected $table = 'payfast_settings';

    protected $fillable = [
        'merchant_id',
        'merchant_key',
        'passphrase',
        'test_mode',
        'enabled',
    ];

    protected $casts = [
        'test_mode' => 'boolean',
        'enabled'   => 'boolean',
    ];
}
