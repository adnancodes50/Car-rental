<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Vehicles;
use App\Models\AddOn;
use App\Models\AddOnReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Store a new booking (customer + booking + add_on_reservations).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id'      => 'required|exists:vehicles,id',
            // rental_unit: 'day' | 'week' | 'month'
            'rental_unit'     => 'required|in:day,week,month',
            'rental_quantity' => 'required|integer|min:1',
            'rental_start_date' => 'required|date',
            // customer
            'name'            => 'required|string|max:255',
            'email'           => 'nullable|email|max:255',
            'phone'           => 'nullable|string|max:50',
            'country'         => 'nullable|string|max:100',
            // add_ons is optional; format: add_ons[<id>] => qty
            'add_ons'         => 'nullable|array',
            'add_ons.*'       => 'nullable|integer|min:0',
        ]);

        // transaction to avoid stock race conditions
        DB::beginTransaction();

        try {
            // 1) find or create customer (prefer email, then phone)
            if (!empty($validated['email'])) {
                $customer = Customer::firstOrCreate(
                    ['email' => $validated['email']],
                    ['name' => $validated['name'], 'phone' => $validated['phone'] ?? null, 'country' => $validated['country'] ?? null]
                );
            } elseif (!empty($validated['phone'])) {
                $customer = Customer::firstOrCreate(
                    ['phone' => $validated['phone']],
                    ['name' => $validated['name'], 'email' => $validated['email'] ?? null, 'country' => $validated['country'] ?? null]
                );
            } else {
                $customer = Customer::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'] ?? null,
                    'phone' => $validated['phone'] ?? null,
                    'country' => $validated['country'] ?? null,
                ]);
            }

            // 2) compute dates, base price
            $vehicle = Vehicles::findOrFail($validated['vehicle_id']);
            $unit = $validated['rental_unit']; // day/week/month
            $qty = (int)$validated['rental_quantity'];
            $start = Carbon::parse($validated['rental_start_date']);

            // compute end_date (exclusive or inclusive depending on your logic - here we add qty)
            if ($unit === 'day') {
                $end = (clone $start)->addDays($qty);
            } elseif ($unit === 'week') {
                $end = (clone $start)->addWeeks($qty);
            } else { // month
                $end = (clone $start)->addMonths($qty);
            }

            // get vehicle unit price field names: rental_price_day/week/month in your Blade
            $priceField = 'rental_price_' . $unit;
            $vehicleUnitPrice = (int) ($vehicle->{$priceField} ?? 0);
            $basePrice = $vehicleUnitPrice * $qty;

            // 3) process add-ons
            $addOnsInput = $validated['add_ons'] ?? [];
            $addonsTotal = 0;
            $addOnReservations = []; // store each reservation for later insert

            foreach ($addOnsInput as $addOnId => $selectedQty) {
                $selectedQty = (int)$selectedQty;
                if ($selectedQty <= 0) continue;

                // lock add-on row for update to avoid concurrency issues
                $addOn = AddOn::lockForUpdate()->find($addOnId);
                if (!$addOn) {
                    DB::rollBack();
                    return back()->withInput()->withErrors(['add_ons' => "Add-on #{$addOnId} not found."]);
                }

                if ($addOn->qty_total < $selectedQty) {
                    DB::rollBack();
                    return back()->withInput()->withErrors(['add_ons' => "Not enough stock for {$addOn->name}."]);
                }

                $addOnUnitPrice = (int) ($addOn->{'price_' . $unit} ?? 0);
                $priceTotal = $addOnUnitPrice * $selectedQty * $qty; // unit price * qty selected * rental quantity

                $addonsTotal += $priceTotal;

                $addOnReservations[] = [
                    'add_on_id'   => $addOn->id,
                    'qty'         => $selectedQty,
                    'price_total' => $priceTotal,
                ];

                // decrement stock
                $addOn->decrement('qty_total', $selectedQty);
            }

            $totalPrice = $basePrice + $addonsTotal;

            // 4) create booking
            $booking = Booking::create([
                'vehicle_id' => $vehicle->id,
                'customer_id' => $customer->id,
                'start_date' => $start->toDateString(),
                'end_date'   => $end->toDateString(),
                'type'       => 'rental', // your enum: rental|maintenance|block
                'status'     => 'pending',
                'reference'  => 'BK-' . strtoupper(Str::random(8)),
                // store some structured details in notes (optional)
                'notes'      => json_encode([
                    'rental_unit' => $unit,
                    'rental_quantity' => $qty,
                    'vehicle_unit_price' => $vehicleUnitPrice,
                    'addons_summary' => array_map(function($r) {
                        return [
                            'add_on_id' => $r['add_on_id'],
                            'qty' => $r['qty'],
                            'price_total' => $r['price_total'],
                        ];
                    }, $addOnReservations)
                ]),
                'total_price' => $totalPrice,
            ]);

            // 5) create add_on_reservations rows
            foreach ($addOnReservations as $r) {
                AddOnReservation::create([
                    'add_on_id' => $r['add_on_id'],
                    'booking_id' => $booking->id,
                    'qty' => $r['qty'],
                    'price_total' => $r['price_total'],
                ]);
            }

            DB::commit();

            return redirect()->route('vehicle.show', $vehicle->id)
                             ->with('success', 'Booking created successfully. Reference: ' . $booking->reference);

        } catch (\Throwable $e) {
            DB::rollBack();
            // log($e->getMessage()); // optionally log
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
