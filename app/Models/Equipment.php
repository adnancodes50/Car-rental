<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;

    // Non-standard plural -> specify table
    protected $table = 'equipment';

    protected $fillable = [
        'name',
        'image',
        'description',
        'category_id',
        'status', // string OR tinyint (0/1)
    ];

    protected $appends = [
        'sale_price',       // category->total_amount
        'deposit_amount',   // category->deposit_price
        'daily_price',
        'weekly_price',
        'monthly_price',
        'is_for_sale_flag',
    ];

    /* -------- Relationships -------- */

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function stocks()
    {
        return $this->hasMany(EquipmentStock::class, 'equipment_id');
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'equipment_stocks', 'equipment_id', 'location_id')
                    ->withPivot('stock')
                    ->withTimestamps();
    }

    public function purchases()
    {
        return $this->hasMany(EquipmentPurchase::class, 'equipment_id');
    }

    /* -------- Helpers / Accessors -------- */

    public function stockForLocation($locationId): int
    {
        return (int) ($this->stocks()->where('location_id', $locationId)->value('stock') ?? 0);
    }

    public function getSalePriceAttribute(): float
    {
        return (float) ($this->category?->total_amount ?? 0);
    }

    public function getDepositAmountAttribute(): float
    {
        return (float) ($this->category?->deposit_price ?? 0);
    }

    public function getDailyPriceAttribute(): float
    {
        return (float) ($this->category?->daily_price ?? 0);
    }

    public function getWeeklyPriceAttribute(): float
    {
        return (float) ($this->category?->weekly_price ?? 0);
    }

    public function getMonthlyPriceAttribute(): float
    {
        return (float) ($this->category?->monthly_price ?? 0);
    }

    public function getIsForSaleFlagAttribute(): bool
    {
        return (bool) ($this->category?->is_for_sale ?? false);
    }
}
