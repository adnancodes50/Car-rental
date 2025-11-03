<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\Landing;
use Carbon\Carbon;

class HomeController extends Controller
{


public function index()
{
    $categories = Category::all();
    $settings = Landing::first();
    return view('spa', compact('categories', 'settings'));
}

public function show($id)
{
    $category = Category::findOrFail($id);

    // Eager load stocks and locations
    $equipments = Equipment::with(['stocks.location'])->where('category_id', $id)->get();

    $settings = Landing::first(); // for hero image

    return view('user.view', compact('category', 'equipments', 'settings'));
}


  public function viewEquipment(Equipment $equipment)
    {
        $settings = Landing::first();
        $equipment->load([
            'category',
            'stocks.location',
            'bookings' => function ($query) {
                $query->select('id', 'equipment_id', 'start_date', 'end_date')
                    ->orderBy('start_date');
            },
        ]);

        $bookedRanges = $equipment->bookings->map(function ($booking) {
            return [
                'from' => Carbon::parse($booking->start_date)->toDateString(),
                'to' => Carbon::parse($booking->end_date)->toDateString(),
            ];
        })->values();

        return view('user.show', compact('equipment', 'settings', 'bookedRanges'));
    }


}
