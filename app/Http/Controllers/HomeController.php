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
                $query->select('id', 'equipment_id', 'location_id', 'start_date', 'end_date', 'notes', 'booked_stock', 'status')
                    ->whereNotIn('status', ['cancelled'])
                    ->orderBy('start_date');
            },
        ]);

        $bookedRanges = $equipment->bookings->map(function ($booking) {
            return [
                'from' => Carbon::parse($booking->start_date)->toDateString(),
                'to' => Carbon::parse($booking->end_date)->toDateString(),
            ];
        })->values();

        $locationOptions = $equipment->stocks->map(function ($stock) {
            return [
                'id' => $stock->location?->id,
                'name' => $stock->location?->name ?? 'Location',
                'stock' => (int) ($stock->stock ?? 0),
            ];
        })->filter(fn ($item) => !empty($item['id']))->values();

        $locationBookings = $equipment->bookings
            ->groupBy('location_id')
            ->map(function ($bookings) {
                return $bookings->map(function ($booking) {
                    $notes = [];
                    if (!empty($booking->notes)) {
                        $decoded = json_decode($booking->notes, true);
                        if (is_array($decoded)) {
                            $notes = $decoded;
                        }
                    }

                    return [
                        'from' => Carbon::parse($booking->start_date)->toDateString(),
                        'to' => Carbon::parse($booking->end_date)->toDateString(),
                        'units' => (int) (
                            $booking->booked_stock
                            ?? ($notes['units_reserved'] ?? 1)
                        ),
                    ];
                })->values();
            })->filter(fn ($collection, $key) => !is_null($key))->toArray();

        return view('user.show', compact(
            'equipment',
            'settings',
            'bookedRanges',
            'locationOptions',
            'locationBookings'
        ));
    }


}
