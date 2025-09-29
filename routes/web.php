<?php

use App\Http\Controllers\AddOnInventryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\LandingSettingController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\PayfastController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\PaymentSettingsController;
use App\Http\Controllers\StripeSettingController;
use App\Http\Controllers\PayfastSettingController;
use App\Http\Controllers\SystemSettingController;



Route::get('/', [VehicleController::class, 'frontendIndex'])->name('home');
Route::get('/fleet/{vehicle}', [VehicleController::class, 'view'])
    ->name('fleet.view');

Route::get('/home', function () {
    return redirect()->route('admin.dashboard.index');
});

Route::group(['middleware' => ['auth']], function () {

    Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');

    });

    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/edit', [ProfileController::class, 'update'])->name('profile.update');
});



// use App\Http\Controllers\VehicleController;

Route::group([
    'middleware' => ['auth'],
    'prefix' => 'vehicles',
    'as' => 'vehicles.'
], function () {
    Route::get('/', [VehicleController::class, 'index'])->name('index');
    Route::get('/create', [VehicleController::class, 'create'])->name('create');
    Route::post('/', [VehicleController::class, 'store'])->name('store');
    Route::get('/{vehicle}/edit', [VehicleController::class, 'edit'])->name('edit');
    Route::put('/{vehicle}', [VehicleController::class, 'update'])->name('update');
    Route::get('/{vehicle}', [VehicleController::class, 'show'])->name('show');
    Route::delete('/{vehicle}', [VehicleController::class, 'destroy'])->name('destroy');
Route::post('/{vehicle}/bookings', [VehicleController::class, 'storeBooking'])->name('bookings.store');
Route::delete('/{vehicle}/bookings/{booking}', [VehicleController::class, 'destroyBooking'])
    ->name('bookings.destroy');

});



Route::group([
    'middleware' => ['auth'],
    'prefix' => 'inventry',
    'as' => 'inventry.'
], function () {
    Route::get('/', [AddOnInventryController::class, 'index'])->name('index');
    Route::get('/create', [AddOnInventryController::class, 'create'])->name('create');
    Route::post('/', [AddOnInventryController::class, 'store'])->name('store');

    // use {addon} everywhere for implicit model binding to App\Models\AddOn
    Route::get('/{addon}/edit', [AddOnInventryController::class, 'edit'])->name('edit');
    Route::put('/{addon}', [AddOnInventryController::class, 'update'])->name('update');

    // reservations page for a specific Add-On (was {vehicle} before)
    Route::get('/{addon}/reservations', [AddOnInventryController::class, 'view'])->name('view');

    Route::delete('/{addon}', [AddOnInventryController::class, 'destroy'])->name('destroy')
         ->whereNumber('addon'); // optional, keeps it from matching 'create'
});








Route::group([
    'middleware' => ['auth'],
    'prefix' => 'customers',
    'as' => 'customers.'
], function () {

    // Customer Dashboard/List
    // Blade icon example: <i class="ri-user-line"></i>
    Route::get('/', [CustomerController::class, 'index'])->name('index');
    Route::get('/{id}', [CustomerController::class, 'getCustomerDetails'])->name('details');
    Route::delete('/{id}', [CustomerController::class, 'destroy'])->name('destroy');

});



Route::group([
    'middleware' => ['auth'],
    'prefix' => 'systemsetting',
    'as' => 'systemsetting.'
], function () {
    Route::get('/edit', [SystemSettingController::class, 'edit'])->name('edit');
    Route::post('/update', [SystemSettingController::class, 'update'])->name('update');
});



// Route::group([
//     'middleware' => ['auth'],
//     'prefix' => 'payfast',
//     'as' => 'payfast.'
// ], function () {
//     Route::get('/edit', [PayfastSettingController::class, 'edit'])->name('edit');
//     Route::post('/update', [PayfastSettingController::class, 'update'])->name('update');
// });






Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    // Landing page settings
    Route::get('/landing-settings', [LandingSettingController::class, 'index'])
        ->name('landing-settings.index');

    Route::post('/landing-settings', [LandingSettingController::class, 'update'])
        ->name('landing-settings.update');
});


//frontend booking and purchase routes
Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
Route::post('/bookings/{booking}/pay-with-stripe', [BookingController::class, 'payWithStripe'])
    ->name('bookings.pay.stripe');

Route::post('/purchase', [PurchaseController::class, 'store'])->name('purchase.store');
Route::post('/purchase/{purchase}/pay-with-stripe', [PurchaseController::class, 'payWithStripe'])
    ->name('purchase.pay.stripe');

// Create the PayFast request + redirect (returns fields to auto-submit to PayFast)
// Start PayFast payment
Route::post('/purchase/{purchase}/payfast/init', [PurchaseController::class, 'initPayfast'])
    ->name('purchase.payfast.init');

// Return URL after successful payment
Route::match(['GET','POST'], '/payment/success', [PurchaseController::class, 'payfastReturn'])
    ->name('payfast.return');

// Cancel URL if user cancels
Route::match(['GET','POST'], '/payment/cancel', [PurchaseController::class, 'payfastCancel'])
    ->name('payfast.cancel');

Route::post('/purchase/payfast/notify', [PurchaseController::class, 'payfastNotify'])
    ->name('purchase.payfast.notify');



    //FOR BOOKING PAYFAST
    Route::post('/payfast/booking/init/{booking}', [BookingController::class, 'initPayfastBooking'])->name('payfast.booking.init');
Route::post('/payfast/booking/notify', [BookingController::class, 'payfastBookingNotify'])->name('payfast.booking.notify');
Route::get('/payfast/booking/return', [BookingController::class, 'payfastBookingReturn'])->name('payfast.booking.return');
Route::get('/payfast/booking/cancel', [BookingController::class, 'payfastBookingCancel'])->name('payfast.booking.cancel');

require __DIR__ . '/auth.php';




















