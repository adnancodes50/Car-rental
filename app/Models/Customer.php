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

    // If you also have a purchases table for vehicles:
   public function purchase()
{
    return $this->hasMany(\App\Models\Purchase::class);
}



    public function bookings()
{
    return $this->hasMany(Booking::class, 'customer_id');
}

}
