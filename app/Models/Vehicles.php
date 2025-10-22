<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicles extends Model
{
    use HasFactory;

    // (optional) only if your table is non-standard. Default matches 'vehicles':
    // protected $table = 'vehicles';

    protected $fillable = [
        'name','model','year','type','description','location','transmission','fuel_type',
        'drive_type','seats','mileage','engine','main_image_url','is_for_sale',
        'rental_price_day','rental_price_week','rental_price_month','booking_lead_days',
        'purchase_price','deposit_amount','status','features',
    ];

    protected $casts = [
        'features' => 'array',
    ];

    /** -------------------------
     *  Relationships
     *  -------------------------
     */

    /** @return HasMany<VehicleImage> */
    public function images(): HasMany
    {
        return $this->hasMany(VehicleImage::class, 'vehicle_id');
    }

    /** @return HasMany<Booking> */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'vehicle_id');
    }

    /** @return HasMany<Purchase> */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'vehicle_id');
    }

    /** @return HasMany<AdminBooking> */
    public function adminBookings(): HasMany
    {
        return $this->hasMany(AdminBooking::class, 'vehicle_id');
    }

    /** -------------------------
     *  Accessors / Helpers
     *  -------------------------
     */

    /**
     * Returns the main image URL, falling back to the first gallery image,
     * then to a local placeholder if nothing is set.
     */
    public function mainImage(): string
    {
        if (!empty($this->main_image_url)) {
            return $this->main_image_url;
        }

        $first = $this->images()->orderBy('sort_order')->first();
        if ($first && !empty($first->url)) {
            return $first->url;
        }

        // Adjust to your actual placeholder path if needed.
        return asset('images/vehicle-placeholder.jpg');
    }

    /** -------------------------
     *  Scopes
     *  -------------------------
     */

    /**
     * Available vehicles:
     *  - Not explicitly marked as 'sold' (or status is null)
     *  - Do NOT have any AdminBooking with type = 'purchase'
     */
    public function scopeAvailable(Builder $q): Builder
    {
        return $q
            ->where(fn ($w) => $w->whereNull('status')->orWhere('status', '!=', 'sold'))
            ->whereDoesntHave('adminBookings', function ($sub) {
                $sub->where('type', 'purchase');
                // If you track booking status and want to ignore cancelled purchases, add:
                // ->where('status', '!=', 'cancelled');
            });
    }

    /** -------------------------
     *  Mutators / Actions
     *  -------------------------
     */

    /**
     * Attach an image to this vehicle. Accepts an UploadedFile (stores it on the public disk)
     * or a direct URL/string path. Auto-assigns sort_order unless provided.
     */
    public function addImage(UploadedFile|string $fileOrUrl, ?int $sortOrder = null): VehicleImage
    {
        if ($fileOrUrl instanceof UploadedFile) {
            // store on the public disk, e.g. storage/app/public/vehicle-images/xyz.jpg
            $storedPath = $fileOrUrl->store('vehicle-images', 'public');
            $url = Storage::url($storedPath); // "/storage/vehicle-images/xyz.jpg"
        } else {
            // already a URL or path
            $url = (string) $fileOrUrl;
        }

        // auto-increment sort_order if not provided
        if ($sortOrder === null) {
            $max = $this->images()->max('sort_order');
            $sortOrder = is_null($max) ? 0 : ($max + 1);
        }

        $image = $this->images()->create([
            'url'        => $url,
            'sort_order' => $sortOrder,
        ]);

        // if no main image yet, set this as main
        if (empty($this->main_image_url)) {
            $this->update(['main_image_url' => $url]);
        }

        return $image;
    }
}
