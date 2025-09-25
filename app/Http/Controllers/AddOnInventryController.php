<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AddOn;
use Storage;
use Str;
use Illuminate\Support\Carbon;

class AddOnInventryController extends Controller
{
public function index()
{
    $today = Carbon::today()->toDateString();

    $addOns = AddOn::query()
        // total booked quantity across all time
        ->withSum('reservations as total_booked_qty', 'qty')
        // active bookings = reservations where today is between start and end
        ->withCount([
            'reservations as active_bookings' => function ($q) use ($today) {
                $q->whereDate('start_date', '<=', $today)
                  ->whereDate('end_date', '>=', $today);
            }
        ])
        ->get();

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
    $reservations = $addon->reservations()
        ->with(['booking.customer'])
        ->latest('start_date')
        ->get();

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
