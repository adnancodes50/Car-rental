<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Equipment;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index()
    {
        $locations = Location::all();
        $categories = Category::withCount('equipment')->get();

        return view('admin.categories.index', compact('categories', 'locations'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateCategory($request);

        if ($request->hasFile('image_file')) {
            $data['image'] = $request->file('image_file')->store('category', 'public');
        }

        Category::create($data);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category created successfully.');
    }

public function storeEquipmentFromModal(Request $request)
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

    if ($request->hasFile('image_file')) {
        $data['image'] = $request->file('image_file')->store('equipment', 'public');
    }

    unset($data['image_file'], $data['stocks']);

    // Create the equipment item
    $equipment = \App\Models\Equipment::create($data);

    // Create or update stock for each location
    if ($request->filled('stocks')) {
        foreach ($request->stocks as $locationId => $stock) {
            if ($stock > 0) {
                \App\Models\EquipmentStock::updateOrCreate(
                    [
                        'equipment_id' => $equipment->id,
                        'location_id' => $locationId,
                    ],
                    ['stock' => $stock]
                );
            }
        }
    }

    return redirect()
        ->route('categories.index')
        ->with('success', 'Equipment added successfully.');
}



    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $this->validateCategory($request);

        if ($request->hasFile('image_file')) {
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }

            $data['image'] = $request->file('image_file')->store('category', 'public');
        }

        $category->update($data);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        if ($category->image && Storage::disk('public')->exists($category->image)) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    private function validateCategory(Request $request): array
    {
        $numericFields = [
            'daily_price',
            'weekly_price',
            'monthly_price',
            'deposit_price',
            'total_amount',
        ];

        foreach ($numericFields as $field) {
            if ($request->has($field) && $request->input($field) === '') {
                $request->merge([$field => null]);
            }
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'image_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'image' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
            'daily_price' => ['nullable', 'numeric', 'min:0'],
            'weekly_price' => ['nullable', 'numeric', 'min:0'],
            'monthly_price' => ['nullable', 'numeric', 'min:0'],
            'is_for_sale' => ['nullable', 'boolean'],
            'deposit_price' => ['nullable', 'numeric', 'min:0'],
            'total_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $data['is_for_sale'] = $request->boolean('is_for_sale');

        if (! $data['is_for_sale']) {
            $data['deposit_price'] = null;
            $data['total_amount'] = null;
        }

        unset($data['image_file']);

        return $data;
    }
}
