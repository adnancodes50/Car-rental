<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddOnReservation extends Model
{
    protected $table = 'add_on_reservations';

    protected $fillable = [
        'add_on_id',
        'booking_id',
        'qty',
        'price_total',
        'start_date',
        'end_date',
        'extra_days',
    ];

    public function addOn()
    {
        return $this->belongsTo(AddOn::class, 'add_on_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id')->with('customer');
    }
}


