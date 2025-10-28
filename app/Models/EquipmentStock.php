<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'equipment_id',
        'location_id',
        'stock',
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
