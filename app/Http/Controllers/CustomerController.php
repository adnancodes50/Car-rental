<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Purchase;
use App\Models\Booking;
use Illuminate\Http\Request;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingStatusUpdate;
use App\Models\EmailLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\EquipmentStock;



class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::all();
        return view('admin.customer.index', compact('customers'));
    }



public function getCustomerDetails($id)
{
    $customer = Customer::withCount(['bookings', 'purchase'])
        ->withSum('purchase as total_purchase_deposit', 'deposit_paid')
        ->withSum('purchase as total_purchase_price', 'total_price')
        ->findOrFail($id);

    $bookings = $customer->bookings()
        ->with([
            'equipment.stocks.location',
            'equipment.bookings' => function ($query) {
                $query->select('id', 'equipment_id', 'location_id', 'start_date', 'end_date', 'booked_stock', 'status');
            },
            'location',
        ])
        ->latest()
        ->get();

    $bookings->each(function ($booking) {
        $equipment = $booking->equipment;
        if (!$equipment) {
            $booking->setAttribute('location_options', []);
            $booking->setAttribute('location_stock_map', []);
            $booking->setAttribute('location_booking_map', []);
            $booking->setAttribute('location_fully_booked', []);
            $booking->setAttribute('global_fully_booked', []);
            $booking->setAttribute('initial_available_stock', null);
            return;
        }

        $locationOptions = $equipment->stocks
            ->map(function ($stock) {
                if (!$stock->location_id) {
                    return null;
                }

                return [
                    'id' => (string) $stock->location_id,
                    'name' => $stock->location?->name ?? 'Location',
                    'stock' => (int) ($stock->stock ?? 0),
                ];
            })
            ->filter()
            ->unique('id')
            ->values();

        $locationStockMap = $locationOptions
            ->mapWithKeys(fn ($option) => [$option['id'] => (int) ($option['stock'] ?? 0)])
            ->toArray();

        $relatedBookings = ($equipment->bookings ?? collect())
            ->filter(fn ($b) => $b->id !== $booking->id)
            ->filter(function ($b) {
                $status = strtolower($b->status ?? '');
                return !in_array($status, ['cancelled', 'canceled']);
            })
            ->filter(fn ($b) => !empty($b->location_id));

        $locationBookingMap = $relatedBookings
            ->groupBy(fn ($b) => (string) $b->location_id)
            ->map(function ($group) {
                return $group->map(function ($b) {
                    return [
                        'from' => Carbon::parse($b->start_date)->toDateString(),
                        'to' => Carbon::parse($b->end_date)->toDateString(),
                        'units' => (int) ($b->booked_stock ?? 1),
                    ];
                })->values();
            })
            ->toArray();

        $availability = $this->computeAvailability($locationBookingMap, $locationStockMap);

        $booking->setAttribute('location_options', $locationOptions->toArray());
        $booking->setAttribute('location_stock_map', $locationStockMap);
        $booking->setAttribute('location_booking_map', $locationBookingMap);
        $booking->setAttribute('location_fully_booked', $availability['by_location']);
        $booking->setAttribute('global_fully_booked', $availability['global']);
        $booking->setAttribute(
            'initial_available_stock',
            $this->calculateAvailableUnits(
                $booking->location_id,
                $booking->start_date,
                $booking->end_date,
                $locationStockMap,
                $locationBookingMap
            )
        );
    });

    $purchases = $customer->purchase()->latest()->get();

    // Load this customer's email logs
    $emailLogs = EmailLog::where('customer_id', $customer->id)
        ->orderByDesc('sent_at')
        ->get();

    $customer->total_booking_price = $customer->bookings()
        ->where('status', 'confirmed')
        ->sum('total_price');

    $customer->grand_total_spent =
        ($customer->total_booking_price ?? 0) + ($customer->total_purchase_price ?? 0);

    return view('admin.customer.customerDetails', compact('customer', 'bookings', 'purchases', 'emailLogs'));
}

private function computeAvailability(array $locationBookingMap, array $locationStockMap): array
{
    $locationFullyBooked = [];
    $dailyUsage = [];
    $consideredLocations = array_keys(array_filter($locationStockMap, fn ($stock) => (int) $stock > 0));

    foreach ($locationStockMap as $locationId => $stock) {
        $stock = (int) $stock;
        if ($stock <= 0) {
            $locationFullyBooked[$locationId] = [];
            continue;
        }

        $bookings = $locationBookingMap[$locationId] ?? [];
        $daily = [];

        foreach ($bookings as $booking) {
            if (empty($booking['from']) || empty($booking['to'])) {
                continue;
            }

            $start = Carbon::parse($booking['from'])->startOfDay();
            $end = Carbon::parse($booking['to'])->startOfDay();

            for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
                $key = $day->toDateString();
                $daily[$key] = ($daily[$key] ?? 0) + (int) ($booking['units'] ?? 1);
            }
        }

        $dailyUsage[$locationId] = $daily;
        $fullDates = array_keys(array_filter($daily, fn ($count) => $count >= $stock));
        $locationFullyBooked[$locationId] = $this->datesToRanges($fullDates);
    }

    $globalFullDates = [];

    if (!empty($consideredLocations)) {
        $allDates = [];

        foreach ($dailyUsage as $usage) {
            $allDates = array_merge($allDates, array_keys($usage));
        }

        $allDates = array_values(array_unique($allDates));
        sort($allDates);

        foreach ($allDates as $date) {
            $allBooked = true;

            foreach ($consideredLocations as $locId) {
                $stock = (int) ($locationStockMap[$locId] ?? 0);
                if ($stock <= 0) {
                    continue;
                }

                $reserved = $dailyUsage[$locId][$date] ?? 0;
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
            $current = [
                'from' => $date,
                'to' => $date,
            ];
        }
    }

    $ranges[] = $current;

    return $ranges;
}

private function calculateAvailableUnits(?int $locationId, ?string $start, ?string $end, array $locationStockMap, array $locationBookingMap): ?int
{
    if (!$locationId) {
        return null;
    }

    $key = (string) $locationId;
    $stock = (int) ($locationStockMap[$key] ?? 0);
    if ($stock <= 0) {
        return $stock;
    }

    if (!$start || !$end) {
        return $stock;
    }

    $startDate = Carbon::parse($start)->startOfDay();
    $endDate = Carbon::parse($end)->startOfDay();

    if ($endDate->lt($startDate)) {
        return 0;
    }

    $bookings = $locationBookingMap[$key] ?? [];
    $reserved = 0;

    foreach ($bookings as $booking) {
        if (empty($booking['from']) || empty($booking['to'])) {
            continue;
        }

        $bookingStart = Carbon::parse($booking['from'])->startOfDay();
        $bookingEnd = Carbon::parse($booking['to'])->startOfDay();

        if (!($bookingEnd->lt($startDate) || $endDate->lt($bookingStart))) {
            $reserved += (int) ($booking['units'] ?? 1);
        }
    }

    $available = $stock - $reserved;
    return $available < 0 ? 0 : $available;
}




public function update(Request $request, $id)
{
    $customer = Customer::findOrFail($id);

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'nullable|email|max:255',
        'phone' => 'nullable|string|max:50',
        'country' => 'nullable|string|max:100',
         'notes' => 'nullable|string|max:1000',
    ]);

    $customer->update($validated);

    if ($request->expectsJson() || $request->ajax()) {
    return response()->json([
        'success' => true,
        'message' => 'Customer updated successfully!',
        'data' => $customer,
    ]);
}
    return redirect()->back()->with('success', 'Customer updated successfully!');
}



public function updateBookingDates(Request $request, Booking $booking)
{
    $validated = $request->validate([
        'start_date'   => 'required|date',
        'end_date'     => 'required|date|after_or_equal:start_date',
        'admin_note'   => 'nullable|string|max:1000',
        'booked_stock' => 'required|integer|min:1',
        'location_id'  => 'nullable|integer|exists:locations,id',
    ]);

    return DB::transaction(function () use ($validated, $booking) {
        $start = Carbon::parse($validated['start_date'])->startOfDay();
        $end = Carbon::parse($validated['end_date'])->startOfDay();
        $locationId = $validated['location_id'] ?? $booking->location_id;
        $equipmentId = $booking->equipment_id;
        $requested = (int) ($validated['booked_stock'] ?? $booking->booked_stock ?? 1);

        if (!$equipmentId || !$locationId) {
            return response()->json(['success' => false, 'message' => 'Missing equipment or location.'], 422);
        }

        $stockRow = EquipmentStock::where('equipment_id', $equipmentId)
            ->where('location_id', $locationId)
            ->lockForUpdate()
            ->first();

        if (!$stockRow) {
            return response()->json(['success' => false, 'message' => 'No inventory record found.'], 422);
        }

        $totalStock = (int) $stockRow->stock;

        $sumBooked = Booking::where('equipment_id', $equipmentId)
            ->where('location_id', $locationId)
            ->where('id', '!=', $booking->id)
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhereNotIn(DB::raw('LOWER(status)'), ['cancelled', 'canceled']);
            })
            ->whereDate('start_date', '<=', $end)
            ->whereDate('end_date', '>=', $start)
            ->sum('booked_stock');

        $available = $totalStock - (int) $sumBooked;

        if ($requested > $available) {
            return response()->json([
                'success' => false,
                'message' => "Not enough stock. Available: {$available}, requested: {$requested}.",
            ], 422);
        }

        $booking->update([
            'start_date'   => $start->toDateString(),
            'end_date'     => $end->toDateString(),
            'admin_note'   => $validated['admin_note'] ?? $booking->admin_note,
            'booked_stock' => $requested,
            'location_id'  => $locationId,
        ]);

        return response()->json([
            'success'     => true,
            'message'     => 'Booking updated successfully.',
            'available'   => $available - $requested,
            'location_id' => $locationId,
        ]);
    });
}
// helper JSON error
protected function errorResponse($msg, $code = 422)
{
    return response()->json(['success' => false, 'message' => $msg], $code);
}





    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }


public function updateBookingStatus(Request $request, Booking $booking)
{
    $validated = $request->validate([
        'status' => 'required|in:pending,confirmed,completed,canceled',
    ]);

    $booking->status = $validated['status'];
    $booking->save();

    // --- Dynamic SMTP setup ---
    $settings = SystemSetting::first(); // assuming only one row

    if ($settings && $settings->mail_enabled && $booking->customer?->email) {
        Config::set('mail.mailers.smtp.host', $settings->mail_host);
        Config::set('mail.mailers.smtp.port', $settings->mail_port);
        Config::set('mail.mailers.smtp.username', $settings->mail_username);
        Config::set('mail.mailers.smtp.password', $settings->mail_password);
        Config::set('mail.mailers.smtp.encryption', $settings->mail_encryption);
        Config::set('mail.from.address', $settings->mail_from_address ?? 'no-reply@example.com');
        Config::set('mail.from.name', $settings->mail_from_name ?? config('app.name'));

        try {
            $mailable = new BookingStatusUpdate($booking->fresh('vehicle', 'customer'), $booking->status);
            Mail::mailer('smtp')->to($booking->customer->email)->send($mailable);

            $renderedBody = method_exists($mailable, 'render') ? $mailable->render() : '';
            $emailSubject = $mailable->subject ?? ('Booking status updated to ' . ucfirst($booking->status));

            EmailLog::create([
                'customer_id' => $booking->customer_id,
                'subject'     => $emailSubject,
                'body'        => $renderedBody,
                'sent_at'     => now(),
                'sent_by'     => $request->user()?->id,
            ]);

            \Log::info('Booking status email sent successfully to '.$booking->customer->email, [
                'booking_id' => $booking->id,
                'status'     => $booking->status,
            ]);

        } catch (\Throwable $e) {
            EmailLog::create([
                'customer_id' => $booking->customer_id,
                'subject'     => 'Booking status update failed to send',
                'body'        => $e->getMessage(),
                'sent_at'     => now(),
                'sent_by'     => $request->user()?->id,
            ]);

            \Log::error('Failed to send booking status email', [
                'booking_id' => $booking->id,
                'customer_email' => $booking->customer->email,
                'status' => $booking->status,
                'error' => $e->getMessage(),
            ]);
        }
    } elseif ($booking->customer_id) {
        EmailLog::create([
            'customer_id' => $booking->customer_id,
            'subject'     => 'Booking status update email not sent',
            'body'        => 'Mail delivery skipped because email is missing or mail settings are disabled.',
            'sent_at'     => now(),
            'sent_by'     => $request->user()?->id,
        ]);
    }

    if ($request->wantsJson() || $request->ajax()) {
        return response()->json([
            'success' => true,
            'message' => 'Booking status updated and email attempted.',
            'status'  => $booking->status,
            'start'   => $booking->start_date ? (string)$booking->start_date : null,
            'end'     => $booking->end_date ? (string)$booking->end_date : null,
        ]);
    }

    return back()->with('success', 'Booking status updated and email attempted.');
}



}

