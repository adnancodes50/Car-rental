<?php

// use App\Http\Controllers\AddOnInventryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EmailTempleteController;
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
use App\Http\Controllers\LocationsController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ForgetPasswordController;
use App\Http\Controllers\CommunicationController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\HomeController;

use App\Http\Controllers\EquipmentPurchaseController;



Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/category/{id}', [HomeController::class, 'show'])->name('category.show');
Route::get('/equipment/{equipment}', [HomeController::class, 'viewEquipment'])
    ->name('equipment.view');

    Route::get('/categories', [CategoryController::class, 'view'])->name('categories.view');

Route::get('/home', function () {
    return redirect()->route('admin.dashboard.index');
})->name('home');

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['auth']], function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');
});

Route::group(['middleware' => ['auth']], function () {


    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/edit', [ProfileController::class, 'update'])->name('profile.update');
});


// routes/web.php


Route::prefix('equipment-purchase')->group(function () {
    Route::post('/store', [EquipmentPurchaseController::class, 'store'])->name('equipment.purchase.store');
    Route::post('/{purchaseId}/pay-with-stripe', [EquipmentPurchaseController::class, 'payWithStripe'])->name('equipment.purchase.stripe');
    Route::post('/{purchase}/payfast/init', [EquipmentPurchaseController::class, 'initPayfast'])->name('equipment.purchase.payfast.init');
});
Route::post('/equipment-purchase/payfast/notify', [EquipmentPurchaseController::class, 'payfastNotify'])->name('equipment.purchase.payfast.notify');







Route::group([
    'middleware' => ['auth'],
    'prefix' => 'customers',
    'as' => 'customers.'
], function () {
    Route::get('/', [CustomerController::class, 'index'])->name('index');
Route::get('/{id}', [CustomerController::class, 'getCustomerDetails'])->name('details');
    Route::delete('/{id}', [CustomerController::class, 'destroy'])->name('destroy');
    Route::patch('/{id}', [CustomerController::class, 'update'])->name('update');


    Route::patch('/bookings/{booking}/status', [CustomerController::class, 'updateBookingStatus'])
        ->name('bookings.updateStatus');


    Route::patch('/bookings/{booking}/dates', [CustomerController::class, 'updateBookingDates'])
        ->name('bookings.updateDates');
});


Route::group([
    'middleware' => ['auth'],
    'prefix' => 'email',
    'as' => 'email.'
], function () {
    Route::get('/', [EmailTempleteController::class, 'index'])->name('index');
    Route::get('/create', [EmailTempleteController::class, 'create'])->name('create');
    Route::post('/', [EmailTempleteController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [EmailTempleteController::class, 'edit'])->name('edit');
    Route::put('/{id}', [EmailTempleteController::class, 'update'])->name('update');
});





Route::group([
    'middleware' => ['auth', function ($request, $next) {
        if (! $request->user() || ! $request->user()->hasRole('super admin')) {
            abort(403);
        }
        return $next($request);
    }],
    'prefix' => 'locations',
    'as' => 'locations.',
], function () {
    Route::get('/', [LocationsController::class, 'index'])->name('index');
    Route::get('/create', [LocationsController::class, 'create'])->name('create');
    Route::get('/{location}/view', [LocationsController::class, 'view'])->name('view');
    Route::post('/', [LocationsController::class, 'store'])->name('store');
    Route::get('/{location}/edit', [LocationsController::class, 'edit'])->name('edit');
    Route::put('/{location}', [LocationsController::class, 'update'])->name('update');
    Route::patch('/{location}', [LocationsController::class, 'update'])->name('update.partial');
    Route::put('/location-pricings/{pricing}', [LocationsController::class, 'updateprice'])->name('location-pricings.update');
    Route::delete('/{location}', [LocationsController::class, 'destroy'])->name('destroy');
});









Route::group([
    'middleware' => ['auth'],
    'prefix'     => 'categories',
    'as'         => 'categories.',
], function () {
    Route::get('/', [CategoryController::class, 'index'])->name('index');
    Route::get('/create', [CategoryController::class, 'create'])->name('create');
    Route::post('/store', [CategoryController::class, 'store'])->name('store');
    Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
    Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
    Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');

    // Store equipment with stock per location
    Route::post('/store-equipment', [CategoryController::class, 'storeEquipmentFromModal'])
        ->name('storeEquipmentFromModal');
});


Route::group([
    'middleware' => ['auth'],
    'prefix' => 'booking',
    'as' => 'bookings.'
], function () {
    Route::get('/', [BookingController::class, 'index'])->name('index');
    Route::get('bookings/{booking}', [BookingController::class, 'show'])->name('show');

});


Route::group([
    'middleware' => ['auth'],
    'prefix' => 'equipment',
    'as' => 'equipment.'
], function () {
    Route::get('/', [EquipmentController::class, 'index'])->name('index');
    Route::get('/create', [EquipmentController::class, 'create'])->name('create');
    Route::post('/', [EquipmentController::class, 'store'])->name('store');
    Route::get('/{equipment}/edit', [EquipmentController::class, 'edit'])->name('edit');
    Route::put('/{equipment}', [EquipmentController::class, 'update'])->name('update');
    Route::delete('/{equipment}', [EquipmentController::class, 'destroy'])->name('destroy');
});




Route::group([
    'middleware' => ['auth'],
    'prefix' => 'systemsetting',
    'as' => 'systemsetting.'
], function () {
    Route::get('/edit', [SystemSettingController::class, 'edit'])->name('edit');
    Route::post('/update', [SystemSettingController::class, 'update'])->name('update');
});



Route::group([
    'middleware' => ['auth'],
    'prefix' => 'forget-password',
    'as' => 'forget-password.'
], function () {
    Route::get('/edit', [ForgetPasswordController::class, 'edit'])->name('edit');
    Route::post('/update', [ForgetPasswordController::class, 'update'])->name('update');
});


// ✅ Company Setting — only accessible to Super Admin
Route::group([
    'middleware' => ['auth', function ($request, $next) {
        if (! $request->user() || ! $request->user()->hasRole('super admin')) {
            abort(403, 'You do not have permission to access this page.');
        }
        return $next($request);
    }],
    'prefix' => 'company-setting',
    'as' => 'company-setting.'
], function () {
    Route::get('/edit', [CompanyController::class, 'edit'])->name('edit');
    Route::post('/update', [CompanyController::class, 'update'])->name('update');
});



Route::group([
    'middleware' => ['auth'],
    'prefix' => 'communication-setting',
    'as' => 'communication-setting.'
], function () {


    Route::get('/', [CommunicationController::class, 'index'])->name('index');


    Route::post('/send', [CommunicationController::class, 'sendBulkEmail'])->name('send');

});




Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {

    Route::get('/landing-settings', [LandingSettingController::class, 'index'])
        ->name('landing-settings.index');

    Route::post('/landing-settings', [LandingSettingController::class, 'update'])
        ->name('landing-settings.update');
});


Route::post('/bookings/addon-availability', [BookingController::class, 'addonAvailability'])
    ->name('bookings.addon-availability');
Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
Route::post('/bookings/{booking}/pay-with-stripe', [BookingController::class, 'payWithStripe'])
    ->name('bookings.pay.stripe');

Route::post('/purchase', [PurchaseController::class, 'store'])->name('purchase.store');
Route::post('/purchase/{purchase}/pay-with-stripe', [PurchaseController::class, 'payWithStripe'])
    ->name('purchase.pay.stripe');


Route::post('/purchase/{purchase}/payfast/init', [PurchaseController::class, 'initPayfast'])
    ->name('purchase.payfast.init');


Route::match(['GET', 'POST'], '/payment/success', [PurchaseController::class, 'payfastReturn'])
    ->name('payfast.return');


Route::match(['GET', 'POST'], '/payment/cancel', [PurchaseController::class, 'payfastCancel'])
    ->name('payfast.cancel');

Route::post('/purchase/payfast/notify', [PurchaseController::class, 'payfastNotify'])
    ->name('purchase.payfast.notify');



Route::post('/payfast/booking/init/{booking}', [BookingController::class, 'initPayfastBooking'])->name('payfast.booking.init');
Route::post('/payfast/booking/notify', [BookingController::class, 'payfastBookingNotify'])->name('payfast.booking.notify');
Route::get('/payfast/booking/return', [BookingController::class, 'payfastBookingReturn'])->name('payfast.booking.return');
Route::get('/payfast/booking/cancel', [BookingController::class, 'payfastBookingCancel'])->name('payfast.booking.cancel');

require __DIR__ . '/auth.php';

