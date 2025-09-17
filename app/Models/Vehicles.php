<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicles extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'model',
        'year',
        'type',
        'description',
        'location',
        'transmission',
        'fuel_type',
        'drive_type',
        'seats',
        'mileage',
        'engine',
        'main_image_url',
        'is_for_sale',
        'rental_price_day',
        'rental_price_week',
        'rental_price_month',
        'booking_lead_days',
        'purchase_price',
        'deposit_amount',
        'status',
        'features', // ✅ Add the JSON column here

    ];

     protected $casts = [
        'features' => 'array', // <-- this converts JSON to array automatically
    ];

    /**
     * A vehicle can have many images.
     */
    public function images()
    {
        return $this->hasMany(VehicleImage::class, 'vehicle_id'); // explicit foreign key
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'vehicle_id');
    }


    // app/Models/Vehicle.php

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }



    /**
     * Get the main image (fallback to first gallery image).
     */
    public function mainImage()
    {
        return $this->main_image_url
            ?? $this->images()->orderBy('sort_order')->first()?->url;
    }

    /**
     * Add a new image to the vehicle.
     */
    public function addImage(string $url, int $sortOrder = 0): VehicleImage
    {
        return $this->images()->create([
            'url' => $url,
            'sort_order' => $sortOrder,
        ]);
    }

    /**
     * Remove all images for the vehicle.
     */
    public function clearImages(): bool
    {
        return $this->images()->delete();
    }
}
