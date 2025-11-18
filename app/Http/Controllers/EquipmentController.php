<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Equipment;
use App\Models\Category;
use App\Models\Location;
use App\Models\EquipmentStock;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\DB;

class EquipmentController extends Controller
{
    /**
     * Display a listing of the equipment.
     */
public function index(Request $request)
{
    $query = Equipment::with([
        'category',
        'stocks',
        'bookings' => function ($q) {
            $q->whereDate('end_date', '>=', now()); // ⬅️ only future or active bookings
        },
        'bookings.customer',
        'purchases.customer',
    ]);

    if ($request->filled('category_id')) {
        $query->where('category_id', $request->category_id);
    }

    $equipment = $query->get();

    $categories = Category::all();
    $locations  = Location::all();

    return view('admin.equipment.index', compact('equipment', 'categories', 'locations'));
}




    /**
     * Show the form for creating new equipment.
     */
    public function create()
    {
        $categories = Category::where('status', 'active')->get();
        $locations  = Location::where('status', 'active')->get();
        return view('admin.equipment.create', compact('categories', 'locations'));
    }

    /**
     * Store a newly created equipment.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image_file'  => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'category_id' => ['required', 'exists:categories,id'],
            'location_id' => ['required', 'exists:locations,id'],
            'status'      => ['required', 'in:active,inactive'],
            'stock'       => ['required', 'integer', 'min:0'], // Added stock validation
        ]);

        if ($request->hasFile('image_file')) {
            $data['image'] = $request->file('image_file')->store('equipment', 'public');
        }

        Equipment::create($data);

        return redirect()->route('equipment.index')->with('success', 'Equipment created successfully.');
    }

    /**
     * Show the form for editing equipment.
     */
    public function edit(Equipment $equipment)
    {
        $categories = Category::where('status', 'active')->get();
        $locations  = Location::where('status', 'active')->get();
        return view('admin.equipment.edit', compact('equipment', 'categories', 'locations'));
    }

    /**
     * Update the specified equipment.
     */


public function update(Request $request, Equipment $equipment)
{
    $data = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'description' => ['nullable', 'string'],
        'image_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        'category_id' => ['required', 'exists:categories,id'],
        'status' => ['required', 'in:active,inactive'],
        'stocks' => ['nullable', 'array'],
        'stocks.*' => ['nullable', 'integer', 'min:0'],
    ]);

    // ✅ Handle new image upload
    if ($request->hasFile('image_file')) {
        if ($equipment->image && Storage::disk('public')->exists($equipment->image)) {
            Storage::disk('public')->delete($equipment->image);
        }
        $data['image'] = $request->file('image_file')->store('equipment', 'public');
    }

    unset($data['image_file'], $data['stocks']);

    // ✅ Update the equipment
    $equipment->update($data);

    // ✅ Update stocks per location
    if ($request->filled('stocks')) {
        foreach ($request->stocks as $locationId => $stock) {
            if ($stock > 0) {
                EquipmentStock::updateOrCreate(
                    [
                        'equipment_id' => $equipment->id,
                        'location_id' => $locationId,
                    ],
                    ['stock' => $stock]
                );
            } else {
                // Optional: If stock is 0, delete record
                EquipmentStock::where('equipment_id', $equipment->id)
                              ->where('location_id', $locationId)
                              ->delete();
            }
        }
    }

    return redirect()
        ->route('equipment.index')
        ->with('success', 'Equipment updated successfully.');
}




public function destroy(Equipment $equipment)
{
    DB::transaction(function () use ($equipment) {

        // Delete related purchases
        DB::table('equipment_purchase')->where('equipment_id', $equipment->id)->delete();

        // Delete main image if exists
        if ($equipment->image && Storage::disk('public')->exists($equipment->image)) {
            Storage::disk('public')->delete($equipment->image);
        }

        // Delete the equipment row
        DB::table('equipment')->where('id', $equipment->id)->delete();
    });

    return redirect()->route('equipment.index')
        ->with('success', 'Equipment and related purchases deleted successfully.');
}

}
