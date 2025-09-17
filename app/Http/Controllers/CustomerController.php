<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
public function index()
{
    // Eager load bookings to avoid N+1 problem
    $customers = Customer::with('bookings')->get();
    return view('admin.customer.index', compact('customers'));
}



public function getCustomerDetails($id)
{
    $customer = Customer::withCount(['bookings', 'purchases'])->findOrFail($id);

    // Fetch all bookings
    $bookings = $customer->bookings()->latest()->get();

    // Fetch all completed purchases
    $purchases = $customer->purchases()->latest()->get();

    // Total price from completed bookings
    $customer->total_booking_price = $customer->bookings()
        ->where('status', 'completed')
        ->sum('total_price');

    // Total price from purchases
    $customer->total_purchase_price = $customer->purchases()
        ->sum('total_price');

    // Total deposit paid from purchases
    $customer->total_purchase_deposit = $customer->purchases()
        ->sum('deposit_paid');

    // Grand total spent
    $customer->grand_total_spent = $customer->total_booking_price + $customer->total_purchase_price;

    return view('admin.customer.customerDetails', compact('customer', 'bookings', 'purchases'));
}


    public function destroy($id)
{
    $customer = Customer::findOrFail($id);
    $customer->delete();

    return redirect()->route('customers.index')
        ->with('success', 'Customer deleted successfully.');
}


}
