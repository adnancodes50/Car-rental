<?php

use App\Http\Controllers\AddOnInventryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\LandingSettingController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VehicleController;


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

    Route::get('/{addon}/edit', [AddOnInventryController::class, 'edit'])->name('edit');
    Route::put('/{addon}', [AddOnInventryController::class, 'update'])->name('update');
    // Route::get('/{vehicle}', [AddOnInventryController::class, 'show'])->name('show');
Route::delete('/{addon}', [AddOnInventryController::class, 'destroy'])->name('destroy');
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







    Route::get('/pay', function () {
    $data = [
        'merchant_id' => config('payfast.merchant_id'),
        'merchant_key' => config('payfast.merchant_key'),
        'return_url' => url('/payment/success'),
        'cancel_url' => url('/payment/cancel'),
        'notify_url' => url('/payment/notify'),

        'amount' => number_format(100, 2, '.', ''), // Example amount
        'item_name' => 'Test Car Rental Payment',
    ];

    // Generate signature
    $query = http_build_query($data);
    if (config('payfast.passphrase')) {
        $query .= '&passphrase=' . urlencode(config('payfast.passphrase'));
    }
    $data['signature'] = md5($query);

    // PayFast sandbox URL
    $payfastUrl = config('payfast.testmode')
        ? 'https://sandbox.payfast.co.za/eng/process'
        : 'https://www.payfast.co.za/eng/process';

    return view('payfast.checkout', compact('data', 'payfastUrl'));
});

Route::get('/payment/success', fn() => '✅ Payment Successful');
Route::get('/payment/cancel', fn() => '❌ Payment Cancelled');
Route::post('/payment/notify', function () {
    return response('OK');
});





require __DIR__ . '/auth.php';




















