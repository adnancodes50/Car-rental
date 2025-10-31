<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'category_id',
        'equipment_id', // optional if you want to book specific equipment
        'customer_id',
        'start_date',
        'end_date',
        'type',
        'status',
        'reference',
        'admin_note',
        'notes',
        'total_price',
        'extra_days',
    ];

    /* ------------------ Relationships ------------------ */

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function addOns()
    {
        return $this->belongsToMany(AddOn::class, 'add_on_reservations')
                    ->withPivot('qty', 'price_total')
                    ->withTimestamps();
    }
}
