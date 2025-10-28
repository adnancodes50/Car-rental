<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;

    // Enable mass assignment for these fields
    protected $fillable = [
        'name',
        'image',
        'description',
        'location_id',
        'category_id',
        'status',
        'stock', // Added stock field
    ];

    /**
     * Relationship: Equipment belongs to a Category
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Relationship: Equipment belongs to a Location
     */
    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
}
