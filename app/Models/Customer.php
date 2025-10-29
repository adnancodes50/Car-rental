<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name','email','phone','country',
    ];

    public function equipmentPurchases()
    {
        return $this->hasMany(EquipmentPurchase::class, 'customer_id');
    }

    // If you also have a purchases table for vehicles:
    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'customer_id');
    }
}
