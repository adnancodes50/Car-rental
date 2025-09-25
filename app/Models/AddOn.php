<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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


    //  public function bookings()
    // {
    //     return $this->belongsToMany(Booking::class, 'add_on_reservations')
    //                 ->withPivot('qty', 'price_total')
    //                 ->withTimestamps();
    // }

    // app/Models/AddOn.php

public function getTotalBookedAttribute()
{
    return $this->bookings->sum(function ($booking) {
        return (int) $booking->pivot->qty;
    });
}

public function getRemainingQtyAttribute()
{
    return max($this->qty_total - $this->total_booked, 0);
}

// public function getActiveBookingsAttribute()
// {
//     $today = Carbon::today();

//     return $this->bookings()
//         ->where('start_date', '<=', $today)
//         ->where('end_date', '>=', $today)
//         ->count();
// }




    public function reservations()
    {
        // explicit FK helps avoid surprises
        return $this->hasMany(AddOnReservation::class, 'add_on_id');
    }

    public function bookings()
    {
        // if you still need the many-to-many view
        return $this->belongsToMany(Booking::class, 'add_on_reservations', 'add_on_id', 'booking_id')
                    ->withPivot('qty', 'price_total', 'start_date', 'end_date', 'extra_days')
                    ->withTimestamps();
    }
}

