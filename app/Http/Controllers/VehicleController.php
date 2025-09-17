<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Landing;
use App\Models\Vehicles;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Storage;

class VehicleController extends Controller
{

    public function frontendIndex()
    {
        $vehicles = Vehicles::latest()->get();
        $addOns = \App\Models\AddOn::all();
        $settings = Landing::first();


        // Pass bookedDates to the view
        return view('spa', compact('vehicles', 'addOns', 'settings'));
    }


    public function index()
    {
        $vehicles = Vehicles::latest()->simplePaginate(6);
        return view('admin.vehicles.index', compact('vehicles'));
    }

    public function create()
    {
        return view('admin.vehicles.create');
    }

   public function store(Request $request)
{
    // dd($request->all());

    // Validate
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'model' => 'nullable|string|max:255',
        'year' => 'nullable|digits:4|integer',
        'type' => 'nullable|string|max:255',
        'description' => 'nullable|string',
        'location' => 'nullable|string|max:255',
        'transmission' => 'nullable|string|max:255',
        'fuel_type' => 'nullable|string|max:255',
        'drive_type' => 'nullable|string|max:255',
        'seats' => 'nullable|integer',
        'mileage' => 'nullable|integer',
        'engine' => 'nullable|string|max:255',
        'is_for_sale' => 'nullable|boolean',
        'rental_price_day' => 'nullable|numeric',
        'rental_price_week' => 'nullable|numeric',
        'rental_price_month' => 'nullable|numeric',
        'booking_lead_days' => 'nullable|integer',
        'purchase_price' => 'nullable|numeric',
        'deposit_amount' => 'nullable|numeric',
        'status' => 'required|in:available,rented,maintenance,sold',
        'main_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        'images.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        'features' => 'nullable|array', // <-- validate as array
        'features.*' => 'string|max:255', // each feature is a string
    ]);

    unset($validated['main_image'], $validated['images']);

    // Create vehicle
    $vehicle = Vehicles::create($validated);

    // Handle main image
    if ($request->hasFile('main_image')) {
        $mainPath = $request->file('main_image')->store('vehicles', 'public');
        $vehicle->update(['main_image_url' => "/storage/{$mainPath}"]);
    }

    // Handle additional images
    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $index => $image) {
            $path = $image->store('vehicles', 'public');
            $vehicle->addImage("/storage/{$path}", $index + 1);
        }
    }

    return redirect()->route('vehicles.index')
        ->with('success', '✅ Vehicle created successfully!');
}



    public function edit(Vehicles $vehicle)
    {
        return view('admin.vehicles.edit', compact('vehicle'));
    }

public function update(Request $request, Vehicles $vehicle)
{
    // 1️⃣ Validate input
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'model' => 'nullable|string|max:255',
        'year' => 'nullable|digits:4|integer',
        'type' => 'nullable|string|max:255',
        'description' => 'nullable|string',
        'location' => 'nullable|string|max:255',
        'transmission' => 'nullable|string|max:255',
        'fuel_type' => 'nullable|string|max:255',
        'drive_type' => 'nullable|string|max:255',
        'seats' => 'nullable|integer',
        'mileage' => 'nullable|integer',
        'engine' => 'nullable|string|max:255',
        'is_for_sale' => 'nullable|boolean',
        'rental_price_day' => 'nullable|numeric',
        'rental_price_week' => 'nullable|numeric',
        'rental_price_month' => 'nullable|numeric',
        'booking_lead_days' => 'nullable|integer',
        'purchase_price' => 'nullable|numeric',
        'deposit_amount' => 'nullable|numeric',
        'status' => 'required|in:available,rented,maintenance,sold',
        'main_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        'images.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        'features' => 'nullable|array',
        'features.*' => 'string|max:255',
    ]);

    // 2️⃣ Remove images from validated array
    unset($validated['main_image'], $validated['images']);

    // 3️⃣ Update vehicle basic fields
    $vehicle->update($validated);

    // 4️⃣ Handle main image upload
    if ($request->hasFile('main_image')) {
        // Delete old main image if exists
        if ($vehicle->main_image_url) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $vehicle->main_image_url));
        }
        $mainPath = $request->file('main_image')->store('vehicles', 'public');
        $vehicle->update(['main_image_url' => "/storage/{$mainPath}"]);
    }

    // 5️⃣ Remove images marked for deletion
    if ($request->has('removed_images')) {
        foreach ($request->removed_images as $imgId) {
            $image = $vehicle->images()->find($imgId);
            if ($image) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $image->url));
                $image->delete();
            }
        }
    }

    // 6️⃣ Handle additional uploaded images
    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $index => $image) {
            $path = $image->store('vehicles', 'public');
            $vehicle->addImage("/storage/{$path}", $index + 1);
        }
    }

    return redirect()->route('vehicles.index')
        ->with('success', '✅ Vehicle updated successfully!');
}


    public function view(Vehicles $vehicle)
    {
        $addOns = \App\Models\AddOn::all();

        // Get all bookings for this vehicle
        $bookedRanges = Booking::where('vehicle_id', $vehicle->id)
            ->where('status', '!=', 'cancelled') // exclude cancelled bookings
            ->get(['start_date', 'end_date'])
            ->map(function ($b) {
                return [
                    'from' => $b->start_date,
                    'to' => $b->end_date,
                ];
            });

        return view('view', compact('vehicle', 'addOns', 'bookedRanges'));
    }




    public function show(Vehicles $vehicle)
    {
        return view('admin.vehicles.show', compact('vehicle'));
    }



    public function destroy(Vehicles $vehicle)
    {
        $vehicle->delete();

        return redirect()->route('vehicles.index')
            ->with('success', 'Vehicle deleted successfully!');
    }




}
