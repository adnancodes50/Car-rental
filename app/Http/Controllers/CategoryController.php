<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        return view('admin.categories.index');
    }


    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'   => ['required','string','max:255'],
            'email'  => ['nullable','email','max:255'],
            'phone'  => ['nullable','string','max:50'],
            'status' => ['required','in:active,inactive'],
        ]);

    }
}
