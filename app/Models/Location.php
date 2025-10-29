<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $table = 'locations';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'status',
    ];

    /* -------------------------------------------------
     | Relationships
     |--------------------------------------------------*/

    /**
     * Equipment stock rows for this location (pivot rows).
     */
    public function equipmentStocks()
    {
        return $this->hasMany(EquipmentStock::class, 'location_id');
    }

    /**
     * All equipment available here (through the equipment_stocks pivot).
     *
     * NOTE:
     * - If your pivot table does NOT have timestamps, remove ->withTimestamps()
     * - If your pivot is named `equipment_stock` (singular), change the table name below.
     */
    public function equipment()
    {
        return $this->belongsToMany(
            Equipment::class,
            'equipment_stocks',   // pivot table
            'location_id',        // FK to locations.id on pivot
            'equipment_id'        // FK to equipment.id on pivot
        )
        ->withPivot('stock');
        // ->withTimestamps(); // uncomment only if your pivot has created_at/updated_at
    }

    /**
     * Dynamic pricing matrices (optional).
     */
    public function outgoingPrices()
    {
        return $this->hasMany(LocationPricing::class, 'from_location_id');
    }

    public function incomingPrices()
    {
        return $this->hasMany(LocationPricing::class, 'to_location_id');
    }

    /**
     * Equipment purchases that used this location’s stock (direct relation).
     * Table: equipment_purchase (model: EquipmentPurchase)
     */
    public function equipmentPurchases()
    {
        return $this->hasMany(EquipmentPurchase::class, 'location_id');
    }

    /**
     * Vehicle purchases for vehicles that are located at this location.
     *
     * This assumes:
     *  - vehicles table has a location_id column
     *  - purchases table has a vehicle_id column
     *  - models are App\Models\Vehicles (vehicle) and App\Models\Purchase (purchase)
     *
     * hasManyThrough args:
     *   hasManyThrough(
     *       FinalModel::class,        // Purchase
     *       IntermediateModel::class, // Vehicles
     *       foreignKeyOnIntermediate, // Vehicles.location_id
     *       foreignKeyOnFinal,        // Purchase.vehicle_id
     *       localKeyOnThisModel,      // Location.id
     *       localKeyOnIntermediate    // Vehicles.id
     *   )
     */
    public function vehiclePurchases()
    {
        return $this->hasManyThrough(
            Purchase::class,
            Vehicles::class,
            'location_id',  // Vehicles.location_id -> this Location
            'vehicle_id',   // Purchases.vehicle_id -> the Vehicle
            'id',           // Location.id
            'id'            // Vehicles.id
        );
    }

    /**
     * (Optional) Vehicles physically located here.
     * Uncomment if you want a direct relation to vehicles.
     */
    // public function vehicles()
    // {
    //     return $this->hasMany(Vehicles::class, 'location_id');
    // }
}
