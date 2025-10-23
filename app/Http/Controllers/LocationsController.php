<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\LocationPricing;
use Illuminate\Http\Request;

class LocationsController extends Controller
{
    public function index()
    {
        $locations = Location::orderBy('id')->get();
        return view('admin.locations.index', compact('locations'));
    }

    public function create()
    {
        return view('admin.locations.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'   => ['required','string','max:255'],
            'email'  => ['nullable','email','max:255'],
            'phone'  => ['nullable','string','max:50'],
            'status' => ['required','in:active,inactive'],
        ]);

        // ✅ 1. Create the new location
      $newLocation = Location::create($data);
$newLocation->refresh();

$existingLocations = Location::where('id', '!=', $newLocation->id)->get();

\DB::transaction(function () use ($newLocation, $existingLocations) {
    foreach ($existingLocations as $location) {
        LocationPricing::create([
            'from_location_id' => $newLocation->id,
            'to_location_id'   => $location->id,
            'transfer_fee'     => 0,
            'status'           => 'active',
        ]);

        LocationPricing::create([
            'from_location_id' => $location->id,
            'to_location_id'   => $newLocation->id,
            'transfer_fee'     => 0,
            'status'           => 'active',
        ]);
    }
});



        return redirect()
            ->route('locations.index')
            ->with('success', 'Location created successfully and pricing entries added.');
    }

    public function edit(Location $location)
    {
        return view('admin.locations.edit', compact('location'));
    }

    public function update(Request $request, Location $location)
    {
        $data = $request->validate([
            'name'   => ['required','string','max:255'],
            'email'  => ['nullable','email','max:255'],
            'phone'  => ['nullable','string','max:50'],
            'status' => ['required','in:active,inactive'],
        ]);

        $location->update($data);

        return redirect()
            ->route('locations.index')
            ->with('success', 'Location updated successfully.');
    }

    public function destroy(Location $location)
    {
        $location->delete();

        // Optional: also delete pricing records related to this location
        LocationPricing::where('from_location_id', $location->id)
            ->orWhere('to_location_id', $location->id)
            ->delete();

        return redirect()
            ->route('locations.index')
            ->with('success', 'Location deleted successfully.');
    }
}
