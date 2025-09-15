<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'url',
        'sort_order',
    ];

    /**
     * Each image belongs to one vehicle.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicles::class);
    }
}
