<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\Customer;
use App\Models\Vehicle;

class PurchaseController extends Controller
{
    /**
     * Store a new purchase
     */
    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id'    => 'required|exists:vehicles,id',
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255',
            'phone'         => 'required|string|max:50',
            'country'       => 'required|string|max:100',
            'total_price'   => 'required|numeric|min:0',
            'payment_method'=> 'nullable|string|max:255',
            'deposit_paid'  => 'nullable|numeric|min:0',
        ]);

        // Create or find the customer
        $customer = Customer::firstOrCreate(
            ['email' => $request->email], // unique key (change if needed)
            [
                'name'    => $request->name,
                'phone'   => $request->phone,
                'country' => $request->country,
            ]
        );

        // Create the purchase
        $purchase = Purchase::create([
            'customer_id'   => $customer->id,
            'vehicle_id'    => $request->vehicle_id,
            'total_price'   => $request->total_price,
            'payment_method'=> $request->payment_method,
            'deposit_paid'  => $request->deposit_paid ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'purchase_id' => $purchase->id,
            'message' => 'Purchase saved successfully.'
        ]);
    }
}
