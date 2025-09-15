<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddOn extends Model
{
    use HasFactory;

    protected $table = 'add_ons';

    protected $fillable = [
        'name',
        'description',
        'image_url',
        'qty_total',
        'price_day',
        'price_week',
        'price_month',
    ];

    /**
     * Accessor for image_url to return a default image if null.
     */
    public function getImageUrlAttribute($value)
    {
        return $value ?? asset('images/default-add-on.png');
    }


     public function bookings()
    {
        return $this->belongsToMany(Booking::class, 'add_on_reservations')
                    ->withPivot('qty', 'price_total')
                    ->withTimestamps();
    }
}
