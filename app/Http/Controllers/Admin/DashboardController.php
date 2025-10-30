<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\Location;
use App\Models\Purchase;
use Illuminate\View\View;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Total number of bookings
        $totalBookings = Booking::count();

        $totalItems = Equipment::count();

        $totalBranches = Location::count();

        $totalCategories = Category::count();

        // Total booking amount (sum of total_price column)
        // $totalBookingAmount = Booking::sum('total_price');
        $totalBookingAmount = Booking::sum('total_price');

        // dd( $totalBookingAmount);

        // Active bookings based on today's date
         $today = carbon::today();
        $activeBookings = Booking::whereDate('start_date', '<=', $today)
                         ->whereDate('end_date', '>=', $today )
                         ->count();



        // ✅ Total customers
        $totalCustomers = Customer::count();

        // ✅ Total purchases
        // $totalPurchases = Purchase::count();
        // $totalPurchases = Purchase::count();

        // ✅ Total purchase amount
        // $totalPurchaseAmount = Purchase::sum('total_price');

        $totalBookingAmount = Booking::sum('total_price');

        // $totalPurchaseAmount = Purchase::sum('total_price');

        // $totalEarnings = $totalBookingAmount + $totalPurchaseAmount;

        return view('admin.dashboard.index', compact(
            'totalBookings',
            'totalBookingAmount',
            'activeBookings',
            'totalCustomers',
            // 'totalPurchases',
            // 'totalPurchaseAmount',
            // 'totalEarnings',
            // 'totalPurchaseAmount',
            // 'totalEarnings',
              'totalCategories',
              'totalItems',
              'totalBranches'
        ));
    }
}
