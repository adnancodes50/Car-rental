<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        'name',
        'image',
        'short_description',
        'status',
        // Pricing
        'daily_price',
        'weekly_price',
        'monthly_price',
        // Sale-related
        'is_for_sale',
        'deposit_price',
        'total_amount',
    ];

    protected $casts = [
        'daily_price'   => 'float',
        'weekly_price'  => 'float',
        'monthly_price' => 'float',
        'is_for_sale'   => 'boolean',
        'deposit_price' => 'float',
        'total_amount'  => 'float',
    ];

    /* -------- Relationships -------- */

    public function equipment()
    {
        return $this->hasMany(Equipment::class, 'category_id');
    }

    // If your vehicles table has category_id
    public function vehicles()
    {
        return $this->hasMany(Vehicles::class, 'category_id');
    }

    // If you keep add-ons per category
    public function addOns()
    {
        return $this->hasMany(AddOn::class, 'category_id');
    }
}
