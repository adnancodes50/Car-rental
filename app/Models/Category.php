<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        'name',
        'image',
        'short_description',
        'status',
        // New pricing columns
        'daily_price',
        'weekly_price',
        'monthly_price',
        // Sale-related columns
        'is_for_sale',
        'deposit_price',
        'total_amount',
    ];

    public function vehicles()
    {
        return $this->hasMany(Vehicles::class, 'category_id');
    }

    public function addOns()
    {
        return $this->hasMany(AddOn::class, 'category_id');
    }

    public function equipment()
{
    return $this->hasMany(Equipment::class, 'category_id');
}

}
