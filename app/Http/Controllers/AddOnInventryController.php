<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AddOn;
use App\Models\Location;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class AddOnInventryController extends Controller
{
    public function index()
    {
        $today = Carbon::today()->toDateString();

        $addOns = AddOn::query()
            ->with(['location', 'category']) 
            ->withSum('reservations as total_booked_qty', 'qty')
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
        // ✅ Fetch all categories and locations for dropdowns
        $categories = Category::where('status', 'active')->get();
        $locations = Location::where('status', 'active')->get();

        return view('admin.inventry.create', compact('categories', 'locations'));
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
            'location_id' => 'required|exists:locations,id',   // ✅ added
            'category_id' => 'required|exists:categories,id',   // ✅ added
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $ext = $file->getClientOriginalExtension();
            $baseName = Str::slug($request->input('name', 'addon')) . '-' . time();
            $filename = "{$baseName}.{$ext}";

            $file->move(public_path('storage/addon'), $filename);
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
        // ✅ Pass categories and locations to edit form
        $categories = Category::where('status', 'active')->get();
        $locations = Location::where('status', 'active')->get();

        return view('admin.inventry.edit', compact('addon', 'categories', 'locations'));
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
            'location_id' => 'required|exists:locations,id',   // ✅ added
            'category_id' => 'required|exists:categories,id',   // ✅ added
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $ext = $file->getClientOriginalExtension();
            $baseName = Str::slug($request->input('name', 'addon')) . '-' . time();
            $filename = "{$baseName}.{$ext}";

            $file->move(public_path('storage/addon'), $filename);
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
