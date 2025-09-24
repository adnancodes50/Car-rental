<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Landing;
use App\Models\Vehicles;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Storage;
use App\Models\AdminBooking;

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
        $vehicles = Vehicles::all();
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
    $file = $request->file('main_image');
    $filename = time() . '_' . $file->getClientOriginalName();
    $file->move(public_path('storage/vehicles'), $filename); // Move directly to public/storage/vehicles
    $vehicle->update(['main_image_url' => "/storage/vehicles/{$filename}"]);
}


        // Handle additional images
        if ($request->hasFile('images')) {
    foreach ($request->file('images') as $index => $image) {
        $filename = time() . '_' . $image->getClientOriginalName();
        $image->move(public_path('storage/vehicles'), $filename);
        $vehicle->addImage("/storage/vehicles/{$filename}", $index + 1);
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

        if ($request->hasFile('main_image')) {
    if ($vehicle->main_image_url) {
        // Delete old image
        $oldPath = public_path($vehicle->main_image_url);
        if (file_exists($oldPath)) unlink($oldPath);
    }
    $file = $request->file('main_image');
    $filename = time() . '_' . $file->getClientOriginalName();
    $file->move(public_path('storage/vehicles'), $filename);
    $vehicle->update(['main_image_url' => "/storage/vehicles/{$filename}"]);
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

        if ($request->hasFile('images')) {
    foreach ($request->file('images') as $index => $image) {
        $filename = time() . '_' . $image->getClientOriginalName();
        $image->move(public_path('storage/vehicles'), $filename);
        $vehicle->addImage("/storage/vehicles/{$filename}", $index + 1);
    }
}


        return redirect()->route('vehicles.index')
            ->with('success', '✅ Vehicle updated successfully!');
    }

    
   // App\Http\Controllers\YourController.php
public function view(Vehicles $vehicle)
{
    $addOns = \App\Models\AddOn::all();

    $bookedRanges = Booking::where('vehicle_id', $vehicle->id)
        ->where('status', '!=', 'cancelled')
        ->get(['start_date', 'end_date'])
        ->map(fn($b) => ['from' => $b->start_date, 'to' => $b->end_date]);

    $landing = Landing::first(); // <-- owner phone/settings

    return view('view', compact('vehicle', 'addOns', 'bookedRanges', 'landing'));
}





    public function show(Vehicles $vehicle)
    {
        // Eager load bookings to avoid N+1 queries
        $vehicle->load([
            'adminBookings' => function ($query) {
                $query->select('id', 'vehicle_id', 'start_date', 'end_date', 'type', 'customer_reference', 'notes');
            }
        ]);

        // Alias for convenience in the view
        $bookings = $vehicle->adminBookings;

        // Prepare booked dates for calendar/datepicker
        $bookedDates = $bookings->map(function ($b) {
            return [
                'start' => $b->start_date,
                'end' => $b->end_date,
            ];
        });

        return view('admin.vehicles.show', compact('vehicle', 'bookings', 'bookedDates'));
    }






    public function destroy(Vehicles $vehicle)
    {
        $vehicle->delete();

        return redirect()->route('vehicles.index')
            ->with('success', 'Vehicle deleted successfully!');
    }



    public function storeBooking(Request $request, Vehicles $vehicle)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'type' => 'required|in:maintenance,internal,purchaser',
            'customer_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['vehicle_id'] = $vehicle->id;

        $booking = AdminBooking::create($validated);

        // Redirect back with SweetAlert success message
        return redirect()->back()->with('success', 'Booking/Block added successfully!');
    }

    public function destroyBooking($vehicleId, $bookingId)
    {
        $booking = AdminBooking::where('vehicle_id', $vehicleId)->findOrFail($bookingId);
        $booking->delete();

        return redirect()
            ->route('vehicles.show', $vehicleId)
            ->with('success', '❌ Booking removed successfully!');
    }









}
