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

}
