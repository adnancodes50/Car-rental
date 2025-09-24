<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddOnReservation extends Model
{
    use HasFactory;

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

    /**
     * Pivot belongs to AddOn.
     */
    public function addOn()
    {
        return $this->belongsTo(AddOn::class);
    }

    /**
     * Pivot belongs to Booking.
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
