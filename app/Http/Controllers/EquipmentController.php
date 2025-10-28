<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Equipment;
use App\Models\Category;
use App\Models\Location;
use Illuminate\Support\Facades\Storage;

class EquipmentController extends Controller
{
    /**
     * Display a listing of the equipment.
     */
    public function index()
    {
        $equipment = Equipment::with(['category', 'location'])->get();
        return view('admin.equipment.index', compact('equipment'));
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
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image_file'  => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'category_id' => ['required', 'exists:categories,id'],
            'location_id' => ['required', 'exists:locations,id'],
            'status'      => ['required', 'in:active,inactive'],
            'stock'       => ['required', 'integer', 'min:0'], // Added stock validation
        ]);

        if ($request->hasFile('image_file')) {
            if ($equipment->image && Storage::disk('public')->exists($equipment->image)) {
                Storage::disk('public')->delete($equipment->image);
            }
            $data['image'] = $request->file('image_file')->store('equipment', 'public');
        }

        $equipment->update($data);

        return redirect()->route('equipment.index')->with('success', 'Equipment updated successfully.');
    }

    /**
     * Remove the specified equipment.
     */
    public function destroy(Equipment $equipment)
    {
        if ($equipment->image && Storage::disk('public')->exists($equipment->image)) {
            Storage::disk('public')->delete($equipment->image);
        }

        $equipment->delete();

        return redirect()->route('equipment.index')->with('success', 'Equipment deleted successfully.');
    }
}
