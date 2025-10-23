<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'customer_id',
        'start_date',
        'end_date',
        'type',
        'status',
        'reference',
        'admin_note',
        'notes',
        'total_price',
        'extra_days', // <-- new column added
    ];

    /**
     * A booking belongs to one vehicle.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicles::class);
    }

    /**
     * A booking belongs to one customer.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * A booking can have many add-ons.
     */
    public function addOns()
    {
        return $this->belongsToMany(AddOn::class, 'add_on_reservations')
                    ->withPivot('qty', 'price_total')
                    ->withTimestamps();
    }

}
