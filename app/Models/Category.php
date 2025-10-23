<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    public $timestamps = true;
    protected $fillable = [
        'name',
        'image',
        'short_description',
        'status',
    ];

public function vehicles()
{
    return $this->hasMany(Vehicles::class, 'category_id');
}

}
