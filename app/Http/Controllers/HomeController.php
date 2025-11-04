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

        // Generate booked ranges & per-location availability
        $fullyBooked = $this->getFullyBookedDates($locationBookings, $locationOptions);
        $bookedRanges = $fullyBooked['global'];
        $locationFullyBooked = $fullyBooked['by_location'];

        return view('user.show', compact(
            'equipment',
            'settings',
            'bookedRanges',
            'locationOptions',
            'locationBookings',
            'locationFullyBooked'
        ));
    }

    /**
     * Get dates where equipment is fully booked at any location
     */
    private function getFullyBookedDates($locationBookings, $locationOptions)
    {
        $locationFullyBooked = [];
        $dailyUsage = [];
        $consideredLocations = $locationOptions
            ->filter(fn ($loc) => !empty($loc['id']) && (int) ($loc['stock'] ?? 0) > 0)
            ->map(fn ($loc) => (string) $loc['id'])
            ->values();

        foreach ($locationBookings as $locationId => $bookings) {
            $locationStock = (int) ($locationOptions->firstWhere('id', $locationId)['stock'] ?? 0);
            if ($locationStock <= 0) {
                $locationFullyBooked[(string) $locationId] = [];
                continue;
            }

            $daily = [];

            foreach ($bookings as $booking) {
                $start = Carbon::parse($booking['from'])->startOfDay();
                $end = Carbon::parse($booking['to'])->startOfDay();

                for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
                    $key = $day->toDateString();
                    $daily[$key] = ($daily[$key] ?? 0) + (int) ($booking['units'] ?? 1);
                }
            }

            $dailyUsage[(string) $locationId] = $daily;
            $fullDates = array_keys(array_filter($daily, fn ($count) => $count >= $locationStock));
            $locationFullyBooked[(string) $locationId] = $this->datesToRanges($fullDates);
        }

        $globalFullDates = [];

        if ($consideredLocations->isNotEmpty()) {
            $allDates = collect($dailyUsage)
                ->flatMap(fn ($usage) => array_keys($usage))
                ->unique()
                ->values()
                ->all();

            sort($allDates);

            foreach ($allDates as $date) {
                $allBooked = true;

                foreach ($consideredLocations as $locId) {
                    $stock = (int) ($locationOptions->firstWhere('id', $locId)['stock'] ?? 0);
                    if ($stock <= 0) {
                        continue;
                    }

                    $locationDaily = $dailyUsage[$locId] ?? [];
                    $reserved = $locationDaily[$date] ?? 0;
                    if ($reserved < $stock) {
                        $allBooked = false;
                        break;
                    }
                }

                if ($allBooked) {
                    $globalFullDates[] = $date;
                }
            }
        }

        return [
            'global' => $this->datesToRanges($globalFullDates),
            'by_location' => $locationFullyBooked,
        ];
    }

    /**
     * Convert a list of date strings into merged ranges
     */
    private function datesToRanges(array $dates): array
    {
        if (empty($dates)) {
            return [];
        }

        sort($dates);
        $ranges = [];
        $current = [
            'from' => $dates[0],
            'to' => $dates[0],
        ];

        for ($i = 1; $i < count($dates); $i++) {
            $date = $dates[$i];
            $expected = Carbon::parse($current['to'])->addDay()->toDateString();

            if ($date === $expected) {
                $current['to'] = $date;
            } else {
                $ranges[] = $current;
                $current = ['from' => $date, 'to' => $date];
            }
        }

        $ranges[] = $current;

        return $ranges;
    }
}
