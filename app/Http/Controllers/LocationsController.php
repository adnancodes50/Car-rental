<?php

namespace App\Http\Controllers;

use App\Models\Location;
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

        Location::create($data);

        return redirect()
            ->route('locations.index')
            ->with('success', 'Location created successfully.');
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
        return redirect()
            ->route('locations.index')
            ->with('success', 'Location deleted.');
    }
}
