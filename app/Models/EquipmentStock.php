<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentStock extends Model
{
    use HasFactory;

    protected $table = 'equipment_stocks';

    protected $fillable = [
        'equipment_id',
        'location_id',
        'stock',
    ];

    protected $casts = [
        'stock' => 'integer',
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
}
