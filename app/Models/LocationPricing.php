<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationPricing extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_location_id',
        'to_location_id',
        'transfer_fee',
        'status',
    ];
    public function fromLocation(){
        return $this->belongsTo(Location::class, 'from_location_id');
    }
    public function toLocation()
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }
}
