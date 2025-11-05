<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name','email','phone','country', 'notes',
    ];

    public function equipmentPurchases()
    {
        return $this->hasMany(EquipmentPurchase::class, 'customer_id');
    }




    public function bookings()
{
    return $this->hasMany(Booking::class, 'customer_id');
}

}
