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
        'vehicle_id' => 'required|exists:vehicles,id',
        'rental_unit' => 'required|in:day,week,month',
        'rental_quantity' => 'required|integer|min:1',
        'rental_start_date' => 'required|date',
        'extra_days' => 'nullable|integer|min:0',
        'name' => 'required|string|max:255',
        'email' => 'nullable|email|max:255',
        'phone' => 'nullable|string|max:50',
        'country' => 'nullable|string|max:100',
        'add_ons' => 'nullable|array',
        'add_ons.*' => 'nullable|integer|min:0',
    ]);

    DB::beginTransaction();

    try {
        // --- 1) Customer ---
        if (!empty($validated['email'])) {
            $customer = Customer::firstOrCreate(
                ['email' => $validated['email']],
                [
                    'name' => $validated['name'],
                    'phone' => $validated['phone'] ?? null,
                    'country' => $validated['country'] ?? null,
                ]
            );
        } elseif (!empty($validated['phone'])) {
            $customer = Customer::firstOrCreate(
                ['phone' => $validated['phone']],
                [
                    'name' => $validated['name'],
                    'email' => $validated['email'] ?? null,
                    'country' => $validated['country'] ?? null,
                ]
            );
        } else {
            $customer = Customer::create([
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'country' => $validated['country'] ?? null,
            ]);
        }

        // --- 2) Vehicle, dates, pricing ---
        $vehicle = Vehicles::findOrFail($validated['vehicle_id']);
        $unit = $validated['rental_unit'];
        $qty = (int) $validated['rental_quantity'];
        $extraDays = (int) ($validated['extra_days'] ?? 0);
        $start = Carbon::parse($validated['rental_start_date']);
        $end = (clone $start)->addDays($qty + $extraDays);

        $priceField = 'rental_price_' . $unit;
        $vehicleUnitPrice = (int) ($vehicle->{$priceField} ?? 0);

        // Base price (per unit)
        $basePrice = $vehicleUnitPrice * $qty;

        // Extra days price (daily equivalent if week/month)
        $extraDaysPrice = 0;
        if ($extraDays > 0) {
            if ($unit === 'day') {
                $extraDaysPrice = $vehicleUnitPrice * $extraDays;
            } elseif ($unit === 'week') {
                $extraDaysPrice = ($vehicleUnitPrice / 7) * $extraDays;
            } elseif ($unit === 'month') {
                $extraDaysPrice = ($vehicleUnitPrice / 30) * $extraDays;
            }
        }

        // --- 3) Add-ons ---
        $addonsTotal = 0;
        $addOnReservations = [];
        foreach (($validated['add_ons'] ?? []) as $addOnId => $selectedQty) {
            $selectedQty = (int) $selectedQty;
            if ($selectedQty <= 0) continue;

            $addOn = AddOn::lockForUpdate()->find($addOnId);
            if (!$addOn || $addOn->qty_total < $selectedQty) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "Not enough stock for add-on ID {$addOnId}."
                ], 422);
            }

            $addOnUnitPrice = (int) ($addOn->{'price_' . $unit} ?? 0);

            // Base addon price
            $priceTotal = $addOnUnitPrice * $selectedQty * $qty;

            // Extra days addon price (same daily formula)
            if ($extraDays > 0) {
                if ($unit === 'day') {
                    $priceTotal += $addOnUnitPrice * $selectedQty * $extraDays;
                } elseif ($unit === 'week') {
                    $priceTotal += ($addOnUnitPrice / 7) * $selectedQty * $extraDays;
                } elseif ($unit === 'month') {
                    $priceTotal += ($addOnUnitPrice / 30) * $selectedQty * $extraDays;
                }
            }

            $addonsTotal += $priceTotal;

            $addOnReservations[] = [
                'add_on_id' => $addOn->id,
                'qty' => $selectedQty,
                'price_total' => $priceTotal,
            ];

            $addOn->decrement('qty_total', $selectedQty);
        }

        // --- Total ---
        $totalPrice = $basePrice + $extraDaysPrice + $addonsTotal;

        // --- 4) Booking ---
        $booking = Booking::create([
            'vehicle_id'   => $vehicle->id,
            'customer_id'  => $customer->id,
            'start_date'   => $start->toDateString(),
            'end_date'     => $end->toDateString(),
            'type'         => 'rental',
            'status'       => 'pending',
            'reference'    => 'BK-' . strtoupper(Str::random(8)),
            'notes'        => json_encode([
                'rental_unit'       => $unit,
                'rental_quantity'   => $qty,
                'extra_days'        => $extraDays,
                'vehicle_unit_price'=> $vehicleUnitPrice,
                'addons_summary'    => $addOnReservations,
            ]),
            'total_price'  => $totalPrice,
            'extra_days'   => $extraDays,
        ]);

        foreach ($addOnReservations as $r) {
            AddOnReservation::create([
                'add_on_id'   => $r['add_on_id'],
                'booking_id'  => $booking->id,
                'qty'         => $r['qty'],
                'price_total' => $r['price_total'],
            ]);
        }

        DB::commit();

        return response()->json([
            'success'  => true,
            'message'  => 'Booking created successfully.',
            'reference'=> $booking->reference,
            'redirect' => route('fleet.view', $vehicle->id),
        ]);

    } catch (\Throwable $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}


}
