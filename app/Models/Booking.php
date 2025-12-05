<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;


class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'category_id',
        'equipment_id',
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
        'booked_stock',
    ];
 protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'booked_stock' => 'integer',
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


    public function scopeOverlapping(Builder $query, $start, $end)
{
    // Overlap if start <= existing_end AND end >= existing_start
    return $query->where(function ($q) use ($start, $end) {
        $q->where(function ($a) use ($start, $end) {
            $a->whereDate('start_date', '<=', $end)
              ->whereDate('end_date', '>=', $start);
        });
    });

}
}
