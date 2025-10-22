<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    // Fillable fields for mass assignment
    protected $fillable = [
        'customer_id',
        'vehicle_id',
        'total_price',
        'payment_method',
        'deposit_paid',
    ];

    protected $casts = [
        'deposit_paid' => 'float',
    ];

    /**
     * Get the vehicle associated with the purchase
     */
   // app/Models/Purchase.php
public function vehicle()
{
    return $this->belongsTo(\App\Models\Vehicles::class, 'vehicle_id');
}



    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
