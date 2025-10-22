<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'country',
        'notes',
    ];

    /**
     * A customer can have many bookings.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function hasActiveBooking()
    {
        return $this->bookings->whereIn('status', ['pending', 'confirmed'])->count() > 0;
    }

   public function activeBookingCount()
{
    return $this->bookings()
        ->whereDate('start_date', '<=', now())   // already started
        ->whereDate('end_date', '>=', now())     // not yet ended
        ->whereIn('status', ['confirmed', 'ongoing']) // only valid active statuses
        ->count();
}




}
