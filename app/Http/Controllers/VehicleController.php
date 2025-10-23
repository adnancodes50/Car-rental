<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Landing;
use App\Models\Vehicles;
use App\Models\Category;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Storage;
use App\Models\AdminBooking;
use Illuminate\Support\Facades\DB;        // ✅ correct

class VehicleController extends Controller
{

    public function frontendIndex()
{
    $vehicles = Vehicles::available()->latest()->paginate(20);
    $addOns   = \App\Models\AddOn::all();
    $settings = Landing::first();

    return view('spa', compact('vehicles', 'addOns', 'settings'));
}



   public function index()
    {

$vehicles = Vehicles::with(['category:id,name', 'branch:id,name'])->get();
        // dd('$vehicles');
        return view('admin.vehicles.index', compact('vehicles'));
    }

    public function create()
    {
        // ✅ provide lists for selects
        $categories = Category::orderBy('name')->get(['id','name']);
        $locations  = Location::orderBy('name')->get(['id','name']);
        return view('admin.vehicles.create', compact('categories','locations'));
    }

    public function store(Request $request)
    {
        // Validate (now includes category_id & location_id)
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'model'              => 'nullable|string|max:255',
            'year'               => 'nullable|digits:4|integer',
            'type'               => 'nullable|string|max:255',
            'description'        => 'nullable|string',
            // ⛔️ removed old "location" text field; we now use location_id
            'transmission'       => 'nullable|string|max:255',
            'fuel_type'          => 'nullable|string|max:255',
            'drive_type'         => 'nullable|string|max:255',
            'seats'              => 'nullable|integer',
            'mileage'            => 'nullable|integer',
            'engine'             => 'nullable|string|max:255',
            'is_for_sale'        => 'nullable|boolean',
            'rental_price_day'   => 'nullable|numeric',
            'rental_price_week'  => 'nullable|numeric',
            'rental_price_month' => 'nullable|numeric',
            'booking_lead_days'  => 'nullable|integer',
            'purchase_price'     => 'nullable|numeric',
            'deposit_amount'     => 'nullable|numeric',
            'status'             => 'required|in:available,rented,maintenance,sold',
            'main_image'         => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
            'images.*'           => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
            'features'           => 'nullable|array',
            'features.*'         => 'string|max:255',

            // ✅ NEW
            'category_id'        => 'required|exists:categories,id',
            'location_id'        => 'required|exists:locations,id',
        ]);

        unset($validated['main_image'], $validated['images']);

        // Checkbox -> boolean
        $validated['is_for_sale'] = $request->boolean('is_for_sale');

        // Create vehicle
        $vehicle = Vehicles::create($validated);

        // Main image
        if ($request->hasFile('main_image')) {
            $file = $request->file('main_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('storage/vehicles'), $filename);
            $vehicle->update(['main_image_url' => "/storage/vehicles/{$filename}"]);
        }

        // Additional images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $filename = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('storage/vehicles'), $filename);
                $vehicle->addImage("/storage/vehicles/{$filename}", $index + 1);
            }
        }

        return redirect()->route('vehicles.index')
            ->with('success', 'Vehicle created successfully!');
    }




 public function edit(Vehicles $vehicle)
    {
        $categories = Category::orderBy('name')->get(['id','name']);
        $locations  = Location::orderBy('name')->get(['id','name']);
        return view('admin.vehicles.edit', compact('vehicle','categories','locations'));
    }

    public function update(Request $request, Vehicles $vehicle)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'model'              => 'nullable|string|max:255',
            'year'               => 'nullable|digits:4|integer',
            'type'               => 'nullable|string|max:255',
            'description'        => 'nullable|string',
            'transmission'       => 'nullable|string|max:255',
            'fuel_type'          => 'nullable|string|max:255',
            'drive_type'         => 'nullable|string|max:255',
            'seats'              => 'nullable|integer',
            'mileage'            => 'nullable|integer',
            'engine'             => 'nullable|string|max:255',
            'is_for_sale'        => 'nullable|boolean',
            'rental_price_day'   => 'nullable|numeric',
            'rental_price_week'  => 'nullable|numeric',
            'rental_price_month' => 'nullable|numeric',
            'booking_lead_days'  => 'nullable|integer',
            'purchase_price'     => 'nullable|numeric',
            'deposit_amount'     => 'nullable|numeric',
            'status'             => 'required|in:available,rented,maintenance,sold',
            'main_image'         => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
            'images.*'           => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
            'features'           => 'nullable|array',
            'features.*'         => 'string|max:255',

            // ✅ NEW
            'category_id'        => 'required|exists:categories,id',
            'location_id'        => 'required|exists:locations,id',
        ]);

        unset($validated['main_image'], $validated['images']);

        $validated['is_for_sale'] = $request->boolean('is_for_sale');

        $vehicle->update($validated);

        // Main image replace
        if ($request->hasFile('main_image')) {
            if ($vehicle->main_image_url) {
                $oldPath = public_path($vehicle->main_image_url);
                if (file_exists($oldPath)) @unlink($oldPath);
            }
            $file = $request->file('main_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('storage/vehicles'), $filename);
            $vehicle->update(['main_image_url' => "/storage/vehicles/{$filename}"]);
        }

        // Remove images marked for deletion
        if ($request->filled('removed_images')) {
            foreach ((array)$request->removed_images as $imgId) {
                $image = $vehicle->images()->find($imgId);
                if ($image) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $image->url));
                    $image->delete();
                }
            }
        }

        // New additional images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $filename = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('storage/vehicles'), $filename);
                $vehicle->addImage("/storage/vehicles/{$filename}", $index + 1);
            }
        }

        return redirect()->route('vehicles.index')
            ->with('success', 'Vehicle updated successfully!');
    }






public function view(Vehicles $vehicle)
{
    // 1) Add-on reservations (unchanged)
    $addOns = \App\Models\AddOn::with([
        'reservations' => function ($query) {
            $query->whereHas('booking', function ($bq) {
                $bq->where(function ($w) {
                    $w->whereNull('status')
                      ->orWhereNotIn(\DB::raw('LOWER(status)'), ['cancelled','canceled','completed']);
                });
            })->select('id','add_on_id','booking_id','qty','start_date','end_date');
        },
    ])->get();

    // 2) Vehicle blocked ranges from active PUBLIC bookings (unchanged)
    $bookedRangesPublic = Booking::where('vehicle_id', $vehicle->id)
        ->where(function ($q) {
            $q->whereNull('status')
              ->orWhereNotIn(\DB::raw('LOWER(status)'), ['cancelled','canceled','completed']);
        })
        ->get(['start_date','end_date'])
        ->map(fn ($b) => [
            'from' => Carbon::parse($b->start_date)->toDateString(),
            'to'   => Carbon::parse($b->end_date)->toDateString(),
        ])
        ->values();

    // 2b) Vehicle blocked ranges from ADMIN bookings (NEW)
    // If you want to block *all* admin bookings, keep as-is.
    // If you only want some types, add ->whereIn('type', ['maintenance','internal','purchaser'])
    $bookedRangesAdmin = AdminBooking::where('vehicle_id', $vehicle->id)
        ->get(['start_date','end_date','type'])
        ->map(fn ($b) => [
            'from' => Carbon::parse($b->start_date)->toDateString(),
            'to'   => Carbon::parse($b->end_date)->toDateString(),
        ])
        ->values();

    // Merge public + admin blocks
    $bookedRanges = $bookedRangesPublic->concat($bookedRangesAdmin)->values();

    $landing = Landing::first();

    // 3) Recompute fully-booked ranges per Add-on (unchanged)
    $addonFullyBooked = [];
    $today = Carbon::today();
    foreach ($addOns as $addOn) {
        if (($addOn->qty_total ?? 0) <= 0) {
            $addonFullyBooked[$addOn->id] = [];
            $addOn->setAttribute('available_today', 0);
            continue;
        }

        $dailyTotals = [];
        $reservedToday = 0;

        foreach ($addOn->reservations as $reservation) {
            if (!$reservation->start_date || !$reservation->end_date) continue;

            $start = Carbon::parse($reservation->start_date)->startOfDay();
            $end   = Carbon::parse($reservation->end_date)->startOfDay();

            if ($start->lte($today) && $end->gte($today)) {
                $reservedToday += (int) $reservation->qty;
            }

            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $key = $date->toDateString();
            $dailyTotals[$key] = ($dailyTotals[$key] ?? 0) + (int) $reservation->qty;
        }
    }

    $addOn->setAttribute('available_today', max(($addOn->qty_total ?? 0) - $reservedToday, 0));
    $addOn->setAttribute('daily_totals_map', $dailyTotals);

    if (empty($dailyTotals)) {
        $addonFullyBooked[$addOn->id] = [];
        continue;
    }

        $fullyBookedDates = array_keys(array_filter(
            $dailyTotals,
            fn ($count) => $count >= $addOn->qty_total
        ));
        sort($fullyBookedDates);

        $ranges = [];
        $currentRange = null;
        foreach ($fullyBookedDates as $dateStr) {
            if ($currentRange === null) {
                $currentRange = ['from' => $dateStr, 'to' => $dateStr];
                continue;
            }
            $expectedNext = Carbon::parse($currentRange['to'])->addDay()->toDateString();
            if ($expectedNext === $dateStr) {
                $currentRange['to'] = $dateStr;
            } else {
                $ranges[] = $currentRange;
                $currentRange = ['from' => $dateStr, 'to' => $dateStr];
            }
        }
        if ($currentRange !== null) $ranges[] = $currentRange;

        $addonFullyBooked[$addOn->id] = $ranges;
    }

    // 4) Lead-day logic (unchanged)
    $leadDays = max((int)($vehicle->booking_lead_days ?? 0), 0);
    $minSelectableDate = Carbon::today()->addDays($leadDays)->toDateString();

    $leadDayBlocks = [];
    if ($leadDays > 0) {
        $leadDayBlocks[] = [
            'from' => Carbon::today()->toDateString(),
            'to'   => Carbon::today()->addDays($leadDays - 1)->toDateString(),
        ];
    }

    // Merge disabled ranges: public + admin + lead-day blocks
    $disabledRanges = collect($bookedRanges)->concat($leadDayBlocks)->values();

    // payment configs (as-is)
    $paymentConfig = \App\Models\SystemSetting::first() ?: new \App\Models\SystemSetting([
        'stripe_enabled' => false,
        'payfast_enabled' => false,
    ]);
    $stripeConfig = $paymentConfig;
    $payfastConfig = $paymentConfig;

    return view('view', compact(
        'vehicle',
        'addOns',
        'bookedRanges',      // now includes admin blocks too
        'landing',
        'addonFullyBooked',
        'paymentConfig',
        'stripeConfig',
        'payfastConfig',
        'leadDays',
        'minSelectableDate',
        'disabledRanges'
    ));
}








public function show(Vehicles $vehicle)
{
    // Load admin bookings for this vehicle (for the side list + events)
    $vehicle->load([
        'adminBookings' => function ($q) {
            $q->select('id','vehicle_id','start_date','end_date','type','customer_reference','notes');
        }
    ]);

    // Public rental bookings that should block (and be shown in the list)
    $publicBookings = Booking::where('vehicle_id', $vehicle->id)
        ->where(function ($q) {
            $q->whereNull('status')
              ->orWhereNotIn(\DB::raw('LOWER(status)'), ['cancelled','canceled','completed']);
        })
        ->get(['id','vehicle_id','start_date','end_date','reference','notes']);

    /* ---------- FullCalendar background events ---------- */

    // Admin background events
    $adminEvents = $vehicle->adminBookings->map(function ($b) {
        $start = \Carbon\Carbon::parse($b->start_date)->toDateString();
        $end   = \Carbon\Carbon::parse($b->end_date)->addDay()->toDateString(); // exclusive
        $color = match (strtolower($b->type ?? '')) {
            'maintenance'           => '#ff6b6b',
            'internal'              => '#f1c40f',
            'purchaser', 'purchase' => '#95a5a6',
            default                 => '#bdc3c7',
        };
        return [
            'title'   => ucfirst($b->type ?? 'Block'),
            'start'   => $start,
            'end'     => $end,
            'display' => 'background',
            'color'   => $color,
            'source'  => 'admin',
            'type'    => $b->type,
        ];
    });

    // Public booking background events
    $publicEvents = $publicBookings->map(function ($b) {
        $start = \Carbon\Carbon::parse($b->start_date)->toDateString();
        $end   = \Carbon\Carbon::parse($b->end_date)->addDay()->toDateString(); // exclusive
        return [
            'title'   => 'Booked',
            'start'   => $start,
            'end'     => $end,
            'display' => 'background',
            'color'   => 'gray',
            'source'  => 'public',
            'type'    => 'booking',
        ];
    });

    // Lead-days block (background + disable range)
    $leadDays   = max((int)($vehicle->booking_lead_days ?? 0), 0);
    $leadEvents = collect();
    $leadRanges = collect();
    if ($leadDays > 0) {
        $today     = \Carbon\Carbon::today();
        $leadFrom  = $today->toDateString();                                // inclusive
        $leadToInc = $today->copy()->addDays($leadDays - 1)->toDateString();// inclusive
        $leadToExc = $today->copy()->addDays($leadDays)->toDateString();    // exclusive

        $leadEvents->push([
            'title'   => 'Lead time',
            'start'   => $leadFrom,
            'end'     => $leadToExc,
            'display' => 'background',
            'color'   => '#34495e',
            'source'  => 'lead',
            'type'    => 'lead',
        ]);

        $leadRanges->push(['start' => $leadFrom, 'end' => $leadToInc]);
    }

    $calendarEvents = $publicEvents->concat($adminEvents)->concat($leadEvents)->values();

    /* ---------- Disable ranges for inputs (inclusive Y-M-D) ---------- */
    $bookedDates = $vehicle->adminBookings->map(fn ($b) => [
        'start' => \Carbon\Carbon::parse($b->start_date)->toDateString(),
        'end'   => \Carbon\Carbon::parse($b->end_date)->toDateString(),
    ])->concat(
        $publicBookings->map(fn ($b) => [
            'start' => \Carbon\Carbon::parse($b->start_date)->toDateString(),
            'end'   => \Carbon\Carbon::parse($b->end_date)->toDateString(),
        ])
    )->concat($leadRanges)->values();

    /* ---------- Side list: merge Admin + Public ---------- */
    $sideList = collect();

    // Admin rows (removable here)
    $sideList = $sideList->concat(
        $vehicle->adminBookings->map(function ($b) {
            return [
                'id'        => $b->id,
                'source'    => 'admin',
                'type'      => strtolower($b->type ?? 'block'),
                'start'     => \Carbon\Carbon::parse($b->start_date)->toDateString(),
                'end'       => \Carbon\Carbon::parse($b->end_date)->toDateString(),
                'ref'       => $b->customer_reference,
                'notes'     => $b->notes,
                'can_delete'=> true,
            ];
        })
    );

    // Public bookings (show-only; do NOT delete from here)
    $sideList = $sideList->concat(
        $publicBookings->map(function ($b) {
            return [
                'id'        => $b->id,
                'source'    => 'public',
                'type'      => 'booking',
                'start'     => \Carbon\Carbon::parse($b->start_date)->toDateString(),
                'end'       => \Carbon\Carbon::parse($b->end_date)->toDateString(),
                'ref'       => $b->reference,
                'notes'     => $b->notes,
                'can_delete'=> false,
            ];
        })
    );

    // Sort newest first (by start date desc)
    $entries = $sideList->sortByDesc('start')->values();

    return view('admin.vehicles.show', [
        'vehicle'        => $vehicle,
        'entries'        => $entries,       // <-- use this in the Blade list
        'bookedDates'    => $bookedDates,
        'calendarEvents' => $calendarEvents,
    ]);
}







    public function destroy(Vehicles $vehicle)
    {
        $vehicle->delete();

        return redirect()->route('vehicles.index')
            ->with('success', 'Vehicle deleted successfully!');
    }




public function storeBooking(Request $request, Vehicles $vehicle)
{
    // 1) Validate input (now using 'purchaser')
    $validated = $request->validate([
        'start_date'         => 'required|date',
        'end_date'           => 'required|date|after_or_equal:start_date',
        'type'               => 'required|in:maintenance,internal,purchaser',
        'customer_reference' => 'nullable|string|max:255',
        'notes'              => 'nullable|string',
    ]);

    // (Optional safety) If someone ever posts 'purchase', normalize to 'purchaser'
    if (($validated['type'] ?? null) === 'purchase') {
        $validated['type'] = 'purchaser';
    }

    // 2) Persist atomically
    return DB::transaction(function () use ($validated, $vehicle) {
        // Prevent duplicate purchaser rows for the same vehicle
        if ($validated['type'] === 'purchaser') {
            $alreadyPurchased = AdminBooking::where('vehicle_id', $vehicle->id)
                ->where('type', 'purchaser')
                ->exists();

            if ($alreadyPurchased) {
                return back()->withErrors([
                    'type' => 'This vehicle already has a purchaser record.',
                ])->withInput();
            }
        }

        // Create admin booking
        $validated['vehicle_id'] = $vehicle->id;
        $booking = AdminBooking::create($validated);

        // If it’s 'purchaser' ⇒ mark vehicle as sold
        if ($booking->type === 'purchaser' && $vehicle->status !== 'sold') {
            $vehicle->update(['status' => 'sold']);
        }

        return redirect()->back()->with('success', 'Booking/Block added successfully!');
    });
}





    public function destroyBooking($vehicleId, $bookingId)
    {
        $booking = AdminBooking::where('vehicle_id', $vehicleId)->findOrFail($bookingId);
        $booking->delete();

        return redirect()
            ->route('vehicles.show', $vehicleId)
            ->with('success', 'Booking removed successfully!');
    }









}
