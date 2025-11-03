<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    // IMPORTANT: your table name is non-standard
    protected $table = 'equipment_purchase';

    protected $fillable = [
        'customer_id',
        'vehicle_id',
        'total_price',
        'payment_method',
        'deposit_paid',
    ];

    protected $casts = [
        'deposit_paid' => 'float',
        'total_price'  => 'float',
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

}
