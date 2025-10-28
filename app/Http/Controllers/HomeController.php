<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
  use App\Models\Category;
use App\Models\Landing;

class HomeController extends Controller
{


public function index()
{
    $categories = Category::all();
    $settings = Landing::first();
    return view('spa', compact('categories', 'settings'));
}

}
