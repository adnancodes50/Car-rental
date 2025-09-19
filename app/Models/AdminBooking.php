<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminBooking extends Model
{
    use HasFactory;

    protected $table = 'admin_bookings';

    protected $fillable = [
    'vehicle_id',
    'start_date',
    'end_date',
    'type',
    'customer_reference',
    'notes',
];


    /**
     * Each booking belongs to a vehicle.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicles::class);
    }
}
