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
        $equipments = Equipment::with(['stocks.location'])->where('category_id', $id)->get();
        $settings = Landing::first();

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

        // Get location options
        $locationOptions = $equipment->stocks->map(function ($stock) {
            return [
                'id' => $stock->location?->id,
                'name' => $stock->location?->name ?? 'Location',
                'stock' => (int) ($stock->stock ?? 0),
            ];
        })->filter(fn ($item) => !empty($item['id']))->values();

        // Get location bookings
        $locationBookings = $equipment->bookings
            ->groupBy('location_id')
            ->map(function ($bookings, $locationId) use ($locationOptions) {
                $locationStock = $locationOptions->firstWhere('id', $locationId)['stock'] ?? 0;

                return $bookings->map(function ($booking) use ($locationStock) {
                    $notes = [];
                    if (!empty($booking->notes)) {
                        $decoded = json_decode($booking->notes, true);
                        if (is_array($decoded)) {
                            $notes = $decoded;
                        }
                    }

                    $bookedUnits = (int) (
                        $booking->booked_stock
                        ?? ($notes['units_reserved'] ?? 1)
                    );

                    return [
                        'from' => Carbon::parse($booking->start_date)->toDateString(),
                        'to' => Carbon::parse($booking->end_date)->toDateString(),
                        'units' => $bookedUnits,
                    ];
                })->values();
            })->filter(fn ($collection, $key) => !is_null($key))->toArray();

        // Generate booked ranges - consider a date booked if any booking makes location fully booked
        $bookedRanges = $this->getFullyBookedDates($locationBookings, $locationOptions);

        return view('user.show', compact(
            'equipment',
            'settings',
            'bookedRanges',
            'locationOptions',
            'locationBookings'
        ));
    }

    /**
     * Get dates where equipment is fully booked at any location
     */
    private function getFullyBookedDates($locationBookings, $locationOptions)
    {
        $bookedRanges = [];

        foreach ($locationBookings as $locationId => $bookings) {
            $locationStock = $locationOptions->firstWhere('id', $locationId)['stock'] ?? 0;

            foreach ($bookings as $booking) {
                // If this booking alone uses all available stock, mark these dates as booked
                if ($booking['units'] >= $locationStock) {
                    $bookedRanges[] = [
                        'from' => $booking['from'],
                        'to' => $booking['to'],
                    ];
                }
            }
        }

        // Remove duplicates and merge overlapping ranges
        return $this->mergeRanges($bookedRanges);
    }

    /**
     * Merge overlapping date ranges
     */
    private function mergeRanges($ranges)
    {
        if (empty($ranges)) {
            return [];
        }

        // Sort by start date
        usort($ranges, function ($a, $b) {
            return strcmp($a['from'], $b['from']);
        });

        $merged = [];
        $current = $ranges[0];

        for ($i = 1; $i < count($ranges); $i++) {
            $range = $ranges[$i];
            $currentEnd = Carbon::parse($current['to']);
            $nextStart = Carbon::parse($range['from']);

            if ($nextStart->lte($currentEnd->copy()->addDay())) {
                // Ranges overlap or are adjacent
                $currentEndRange = Carbon::parse($range['to']);
                if ($currentEndRange->gt($currentEnd)) {
                    $current['to'] = $range['to'];
                }
            } else {
                $merged[] = $current;
                $current = $range;
            }
        }

        $merged[] = $current;
        return $merged;
    }
}
