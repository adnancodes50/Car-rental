<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StripeSetting extends Model
{
    /**
     * The table associated with the model.
     *
     * (Optional, Laravel will auto-detect "stripe_settings")
     */
    protected $table = 'stripe_settings';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'stripe_key',
        'stripe_secret',
        'stripe_mode',
        'stripe_enabled',
    ];

    /**
     * Casts for specific fields.
     */
    protected $casts = [
        'stripe_enabled' => 'boolean',
    ];
}
