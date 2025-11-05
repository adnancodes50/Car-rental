<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\Location;
use App\Models\EquipmentPurchase;
// use App\Models\Purchase;
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


        $totalPurchaseItems = EquipmentPurchase::sum('quantity');


        $totalPurchases = EquipmentPurchase::sum('total_price');

        $totalCategories = Category::count();

        $totalBookingAmount = Booking::sum('total_price');

        // dd( $totalBookingAmount);

        // Active bookings based on today's date
         $today = carbon::today();
        $activeBookings = Booking::whereDate('start_date', '<=', $today)
                         ->whereDate('end_date', '>=', $today )
                         ->count();



        // ✅ Total customers
        $totalCustomers = Customer::count();

        $totalBookingAmount = Booking::sum('total_price');


        return view('admin.dashboard.index', compact(
            'totalBookings',
            'totalBookingAmount',
            'activeBookings',
            'totalCustomers',
          
              'totalCategories',
              'totalItems',
              'totalBranches',
              'totalPurchases',
              'totalPurchaseItems'
        ));
    }
}
