<?php

use Illuminate\Http\Request;
use App\Http\Controllers\EquipmentPurchaseController;
use App\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReminderController;

// Manual email send
Route::post('/send-email', [ReminderController::class, 'sendEmail']);

// Upcoming bookings next 7 days
Route::get('/cron/booking-upcoming', [ReminderController::class, 'sendUpcomingBookingReminders']);

// Completed bookings last 7 days
Route::get('/cron/booking-complete', [ReminderController::class, 'sendCompletedBookingEmails']);

// Completed bookings next 7 days (today + 6 days) with email + JSON output
Route::get('/cron/booking-complete-next-7-days', [ReminderController::class, 'sendUpcomingCompletedBookingsEmails']);

// Optional: Send email for all bookings (testing)
Route::get('/cron/send-all-bookings-email', [ReminderController::class, 'sendEmailForAllBookings']);

// PayFast notification endpoints - MUST disable CSRF and authentication
Route::post('/equipment-purchase/payfast/notify', [EquipmentPurchaseController::class, 'payfastNotify'])
    ->withoutMiddleware(['auth:sanctum', 'csrf']) // CRITICAL: Remove auth and CSRF
    ->name('equipment.purchase.payfast.notify');

Route::post('/payfast/booking/notify', [BookingController::class, 'payfastBookingNotify'])
    ->withoutMiddleware(['auth:sanctum', 'csrf']) // CRITICAL: Remove auth and CSRF
    ->name('payfast.booking.notify');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
