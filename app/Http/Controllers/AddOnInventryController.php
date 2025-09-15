<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AddOn;

class AddOnInventryController extends Controller
{
    public function index()
    {
        $addOns = AddOn::all();
        return view('admin.inventry.index', compact('addOns'));
    }

    public function create()
    {
        return view('admin.inventry.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_url'   => 'nullable|url',
            'qty_total'   => 'required|integer|min:0',
            'price_day'   => 'required|numeric|min:0',
            'price_week'  => 'required|numeric|min:0',
            'price_month' => 'required|numeric|min:0',
        ]);

        AddOn::create($validated);

        return redirect()->route('inventry.index')
            ->with('success', 'Add-On created successfully!');
    }

    public function edit(AddOn $addon)
    {
        return view('admin.inventry.edit', compact('addon'));
    }

    public function update(Request $request, AddOn $addon)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_url'   => 'nullable|url',
            'qty_total'   => 'required|integer|min:0',
            'price_day'   => 'required|numeric|min:0',
            'price_week'  => 'required|numeric|min:0',
            'price_month' => 'required|numeric|min:0',
        ]);

        $addon->update($validated);

        return redirect()->route('inventry.index')
            ->with('success', 'Add-On updated successfully!');
    }
}
