<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'description',
        // 'location_id', // optional, can remove if using stock per location
        'category_id',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function stocks()
    {
        return $this->hasMany(EquipmentStock::class);
    }

    public function stockForLocation($locationId)
    {
        return $this->stocks()->where('location_id', $locationId)->first()?->stock ?? 0;
    }

      public function locations()
    {
        return $this->belongsToMany(Location::class, 'equipment_stocks')
                    ->withPivot('stock')
                    ->withTimestamps();
    }
}
