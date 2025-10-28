<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;
    protected $table = 'locations';




    public $timestamps = false;
    protected $fillable = [
        'name',
        'email',
        'phone',
        'status',
    ];

    public function vehicles()
{
return $this->hasMany(Vehicle::class, 'location_id');
}

 public function outgoingPrices()
    {
        return $this->hasMany(LocationPricing::class, 'from_location_id');
    }

    public function incomingPrices()
    {
        return $this->hasMany(LocationPricing::class, 'to_location_id');
    }

      public function addOns()
    {
        return $this->hasMany(AddOn::class, 'location_id');
    }

    public function equipment()
{
    return $this->hasMany(Equipment::class, 'location_id');
}


}
