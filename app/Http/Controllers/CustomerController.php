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
    $customer = Customer::withCount(['bookings', 'purchases'])
        ->withSum('purchases as total_purchase_deposit', 'deposit_paid')
        ->withSum('purchases as total_purchase_price', 'total_price')
        ->findOrFail($id);

    $bookings  = $customer->bookings()->latest()->get();
    $purchases = $customer->purchases()->latest()->get();

    $customer->total_booking_price = $customer->bookings()
        ->where('status', 'completed')
        ->sum('total_price');

    $customer->grand_total_spent =
        ($customer->total_booking_price ?? 0) + ($customer->total_purchase_price ?? 0);

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
