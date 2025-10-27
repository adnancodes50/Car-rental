<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{

    public function index()
    {
        $categories = Category::all();
        return view('admin.categories.index', compact('categories'));
    }


    public function create()
    {
        return view('admin.categories.create');
    }


public function store(Request $request)
{
    $data = $request->validate([
        'name'              => ['required','string','max:255'],
        'short_description' => ['nullable','string','max:500'], // changed from required → nullable
        'image_file'        => ['nullable','image','mimes:jpeg,png,jpg,gif,svg','max:2048'],
        'image'             => ['nullable','string','max:255'],
        'status'            => ['required','in:active,inactive'],
    ]);

    if ($request->hasFile('image_file')) {
        $data['image'] = $request->file('image_file')->store('category', 'public');
    }

    Category::create($data);

    return redirect()->route('categories.index')->with('success', 'Category created successfully.');
}



    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }



public function update(Request $request, Category $category)
{
    $data = $request->validate([
        'name'              => ['required','string','max:255'],
        'short_description' => ['nullable','string','max:500'],
        'image_file'        => ['nullable','image','mimes:jpeg,png,jpg,gif,svg','max:2048'], // file upload
        'image'             => ['nullable','string','max:255'], // FontAwesome class
        'status'            => ['required','in:active,inactive'],
    ]);

    // Handle new uploaded image
    if ($request->hasFile('image_file')) {
        // Delete old file if exists
        if ($category->image && Storage::disk('public')->exists($category->image)) {
            Storage::disk('public')->delete($category->image);
        }
        $data['image'] = $request->file('image_file')->store('category', 'public');
    }

    $category->update($data);

    return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
}





    // public function destroy(Category $category)
    // {

    //     if ($category->image && Storage::disk('public')->exists($category->image)) {
    //         Storage::disk('public')->delete($category->image);
    //     }

    //     $category->delete();

    //     return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    // }


    public function destroy(Category $category)
    {
        if ($category->image && Storage::disk('public')->exists($category->image)){
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();



        return Route::redirect()->route('categories.index')->with('success', 'Category Deleted Successfully.');

    }
}
