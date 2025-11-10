<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Equipment;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index()
    {
        try {
            $locations = Location::all();
            $categories = Category::withCount('equipment')->get();

            return view('admin.categories.index', compact('categories', 'locations'));
        } catch (\Throwable $e) {
            Log::error('Category index error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Something went wrong while loading categories.');
        }
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        try {
            Log::info('Category store started', ['data' => $request->all()]);

            $data = $this->validateCategory($request);
            Log::info('Category validation passed', ['validated_data' => $data]);

            if ($request->hasFile('image_file')) {
                $data['image'] = $request->file('image_file')->store('category', 'public');
                Log::info('Category image stored', ['path' => $data['image']]);
            }

            Category::create($data);
            Log::info('Category created successfully', ['name' => $data['name']]);

            return redirect()
                ->route('categories.index')
                ->with('success', 'Category created successfully.');
        } catch (\Throwable $e) {
            Log::error('Category store error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withInput()->with('error', 'Failed to create category. Check log for details.');
        }
    }

   public function storeEquipmentFromModal(Request $request)
{
    try {
        // Normalize stocks
        if ($request->has('stocks')) {
            $request->merge([
                'stocks' => array_map(function ($value) {
                    return $value === '' ? 0 : (int) $value;
                }, $request->stocks)
            ]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'category_id' => ['required', 'exists:categories,id'],
            'status' => ['required', 'in:active,inactive'],
            'stocks' => ['nullable', 'array'],
            'stocks.*' => ['integer', 'min:0'],
        ]);

        if ($request->hasFile('image_file')) {
            $data['image'] = $request->file('image_file')->store('equipment', 'public');
        }

        unset($data['image_file'], $data['stocks']);

        $equipment = Equipment::create($data);

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

        Log::info('Equipment created successfully', ['equipment' => $equipment->name]);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Equipment added successfully.');
    } catch (\Throwable $e) {
        Log::error('Equipment store error: ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);
        return back()->withInput()->with('error', 'Failed to create equipment. Check log for details.');
    }
}


    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        try {
            Log::info('Category update started', ['id' => $category->id, 'data' => $request->all()]);

            $data = $this->validateCategory($request);
            Log::info('Category validation passed', ['validated_data' => $data]);

            if ($request->hasFile('image_file')) {
                if ($category->image && Storage::disk('public')->exists($category->image)) {
                    Storage::disk('public')->delete($category->image);
                    Log::info('Old image deleted', ['old_image' => $category->image]);
                }

                $data['image'] = $request->file('image_file')->store('category', 'public');
                Log::info('New image uploaded', ['new_image' => $data['image']]);
            }

            $category->update($data);
            Log::info('Category updated successfully', ['id' => $category->id, 'name' => $category->name]);

            return redirect()
                ->route('categories.index')
                ->with('success', 'Category updated successfully.');
        } catch (\Throwable $e) {
            Log::error('Category update error: ' . $e->getMessage(), [
                'id' => $category->id ?? null,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withInput()->with('error', 'Failed to update category. Check log for details.');
        }
    }

    public function destroy(Category $category)
    {
        try {
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
                Log::info('Category image deleted', ['id' => $category->id]);
            }

            $category->delete();
            Log::info('Category deleted', ['id' => $category->id, 'name' => $category->name]);

            return redirect()
                ->route('categories.index')
                ->with('success', 'Category deleted successfully.');
        } catch (\Throwable $e) {
            Log::error('Category delete error: ' . $e->getMessage(), [
                'id' => $category->id ?? null,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Failed to delete category. Check log for details.');
        }
    }

    /**
     * Validate category creation/update
     */
    private function validateCategory(Request $request): array
    {
        try {
            // ✅ STEP 1: Clean numeric inputs before validation
            $numericFields = [
                'daily_price',
                'weekly_price',
                'monthly_price',
                'deposit_price',
                'total_amount',
            ];

            foreach ($numericFields as $field) {
                $value = $request->input($field);
                if ($value !== null && $value !== '') {
                    $clean = preg_replace('/[^\d.\-]/', '', $value);
                    $request->merge([$field => $clean]);
                } else {
                    $request->merge([$field => null]);
                }
            }

            // ✅ STEP 2: Validate input
            $data = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'short_description' => ['nullable', 'string', 'max:500'],
                'image_file' => [
                    'nullable',
                    'file',
                    'mimetypes:image/jpeg,image/png,image/jpg,image/gif,image/webp,image/svg+xml,image/x-icon',
                    'max:4096',
                ],
                'image' => ['nullable', 'string', 'max:255'],
                'status' => ['required', 'in:active,inactive'],
                'daily_price' => ['nullable', 'numeric', 'min:0'],
                'weekly_price' => ['nullable', 'numeric', 'min:0'],
                'monthly_price' => ['nullable', 'numeric', 'min:0'],
'is_for_sale' => ['nullable'],
                'deposit_price' => ['nullable', 'numeric', 'min:0'],
                'total_amount' => ['nullable', 'numeric', 'min:0'],
            ]);

            // ✅ STEP 3: Handle checkbox boolean logic
            $data['is_for_sale'] = $request->boolean('is_for_sale');

            // ✅ STEP 4: If not for sale, clear sale fields
            if (! $data['is_for_sale']) {
                $data['deposit_price'] = null;
                $data['total_amount'] = null;
            }

            unset($data['image_file']);

            Log::info('Category validation success', ['clean_data' => $data]);
            return $data;
        } catch (\Throwable $e) {
            Log::error('Category validation error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e; // rethrow so controller can handle redirect
        }
    }
}
