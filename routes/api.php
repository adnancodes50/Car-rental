<?php

use Illuminate\Http\Request;
use App\Http\Controllers\EquipmentPurchaseController;
use App\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Route;

    use App\Http\Controllers\ReminderController;

    Route::post('/send-email', [ReminderController::class, 'sendEmail']);


Route::post('/equipment-purchase/payfast/notify', [EquipmentPurchaseController::class, 'payfastNotify'])
    ->name('equipment.purchase.payfast.notify');

    Route::post('/payfast/booking/notify', [BookingController::class, 'payfastBookingNotify'])->name('payfast.booking.notify');





Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
