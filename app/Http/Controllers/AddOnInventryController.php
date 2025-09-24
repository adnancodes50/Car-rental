<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AddOn;
use Storage;
use Str;

class AddOnInventryController extends Controller
{
public function index()
{
    // Eager load bookings to avoid N+1 problem
    $addOns = AddOn::with('bookings')->get();

    return view('admin.inventry.index', compact('addOns'));
}



    public function create()
    {
        return view('admin.inventry.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'qty_total' => 'required|integer|min:0',
            'price_day' => 'required|numeric|min:0',
            'price_week' => 'required|numeric|min:0',
            'price_month' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $ext = $file->getClientOriginalExtension();
            $baseName = Str::slug($request->input('name', 'addon')) . '-' . time();
            $filename = "{$baseName}.{$ext}";

            // ✅ save directly to public/storage/addon
            $file->move(public_path('storage/addon'), $filename);

            // save relative path so you can use asset()
            $validated['image_url'] = "storage/addon/{$filename}";
        }

        AddOn::create($validated);

        return redirect()->route('inventry.index')
            ->with('success', 'Add-On created successfully!');
    }


public function view(AddOn $addon)
{

// dd($addon);
    $addon->load(['bookings.customer']); // keep this
    $reservations = \App\Models\AddOnReservation::with(['booking.customer'])
        ->where('add_on_id', $addon->id)
        ->get();

// dd($reservations);

    return view('admin.inventry.view', compact('addon', 'reservations'));
}


    public function edit(AddOn $addon)
    {
        return view('admin.inventry.edit', compact('addon'));
    }

    public function update(Request $request, AddOn $addon)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'qty_total' => 'required|integer|min:0',
            'price_day' => 'required|numeric|min:0',
            'price_week' => 'required|numeric|min:0',
            'price_month' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $ext = $file->getClientOriginalExtension();
            $baseName = Str::slug($request->input('name', 'addon')) . '-' . time();
            $filename = "{$baseName}.{$ext}";

            // save to public/storage/addon
            $file->move(public_path('storage/addon'), $filename);

            // update image_url
            $validated['image_url'] = "storage/addon/{$filename}";
        }

        $addon->update($validated);

        return redirect()->route('inventry.index')
            ->with('success', 'Add-On updated successfully!');
    }



    public function destroy(AddOn $addon)
    {
        $addon->delete();

        return redirect()->route('inventry.index')
            ->with('success', 'Add-On deleted successfully!');
    }

}
