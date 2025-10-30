<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Booking;
use Illuminate\Http\Request;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingStatusUpdate;
use App\Models\EmailLog;



class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::all();
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

    // ✅ Load this customer's email logs
    $emailLogs = EmailLog::where('customer_id', $customer->id)
        ->orderByDesc('sent_at')
        ->get();

    $customer->total_booking_price = $customer->bookings()
        ->where('status', 'confirmed')
        ->sum('total_price');

    $customer->grand_total_spent =
        ($customer->total_booking_price ?? 0) + ($customer->total_purchase_price ?? 0);

    return view('admin.customer.customerDetails', compact('customer', 'bookings', 'purchases', 'emailLogs'));
}


public function update(Request $request, $id)
{
    $customer = Customer::findOrFail($id);

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'nullable|email|max:255',
        'phone' => 'nullable|string|max:50',
        'country' => 'nullable|string|max:100',
         'notes' => 'nullable|string|max:1000',
    ]);

    $customer->update($validated);

    if ($request->expectsJson() || $request->ajax()) {
    return response()->json([
        'success' => true,
        'message' => 'Customer updated successfully!',
        'data' => $customer,
    ]);
}
    return redirect()->back()->with('success', 'Customer updated successfully!');
}


public function updateBookingDates(Request $request, Booking $booking)
{
    $validated = $request->validate([
        'start_date'   => 'required|date',
        'end_date'     => 'required|date|after_or_equal:start_date',
        'total_price'  => 'nullable|numeric',
        'admin_note'   => 'nullable|string|max:1000', // ✅ added for admin note
    ]);

    $vehicle = $booking->vehicle;
    $vehicleId = $vehicle->id;

    // ✅ Check overlapping bookings
    $overlapping = \App\Models\Booking::where('vehicle_id', $vehicleId)
        ->where('id', '!=', $booking->id)
        ->where(function ($query) use ($validated) {
            $query->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
                ->orWhere(function ($q) use ($validated) {
                    $q->where('start_date', '<=', $validated['start_date'])
                      ->where('end_date', '>=', $validated['end_date']);
                });
        })
        ->first();

    if ($overlapping) {
        return response()->json([
            'success' => false,
            'message' => "This date range overlaps with another booking from {$overlapping->start_date} to {$overlapping->end_date}.",
        ], 422);
    }

    // ✅ Recalculate total price
    $start = new \Carbon\Carbon($validated['start_date']);
    $end   = new \Carbon\Carbon($validated['end_date']);
    $days  = $start->diffInDays($end) + 1;

    $dailyRate = $vehicle->rental_price_day ?? 0;
    $totalPrice = $days * $dailyRate;

    // ✅ Update booking including admin note
    $booking->update([
        'start_date'   => $validated['start_date'],
        'end_date'     => $validated['end_date'],
        'total_price'  => $totalPrice,
        'admin_note'   => $validated['admin_note'] ?? $booking->admin_note, // ✅
    ]);

    return response()->json([
        'success'      => true,
        'message'      => 'Booking updated successfully.',
        'start_date'   => $booking->start_date,
        'end_date'     => $booking->end_date,
        'total_price'  => $booking->total_price,
        'admin_note'   => $booking->admin_note,
    ]);
}





    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }


public function updateBookingStatus(Request $request, Booking $booking)
{
    $validated = $request->validate([
        'status' => 'required|in:pending,confirmed,completed,canceled',
    ]);

    $booking->status = $validated['status'];
    $booking->save();

    // --- Dynamic SMTP setup ---
    $settings = SystemSetting::first(); // assuming only one row

    if ($settings && $settings->mail_enabled && $booking->customer?->email) {
        Config::set('mail.mailers.smtp.host', $settings->mail_host);
        Config::set('mail.mailers.smtp.port', $settings->mail_port);
        Config::set('mail.mailers.smtp.username', $settings->mail_username);
        Config::set('mail.mailers.smtp.password', $settings->mail_password);
        Config::set('mail.mailers.smtp.encryption', $settings->mail_encryption);
        Config::set('mail.from.address', $settings->mail_from_address ?? 'no-reply@example.com');
        Config::set('mail.from.name', $settings->mail_from_name ?? config('app.name'));

        try {
            // Use explicit 'smtp' mailer
            Mail::mailer('smtp')->to($booking->customer->email)
                ->send(new BookingStatusUpdate($booking, $booking->status));

            \Log::info('Booking status email sent successfully to '.$booking->customer->email);

        } catch (\Exception $e) {
            // Log full exception details for debugging
            \Log::error('Failed to send booking status email', [
                'booking_id' => $booking->id,
                'customer_email' => $booking->customer->email,
                'status' => $booking->status,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    if ($request->wantsJson() || $request->ajax()) {
        return response()->json([
            'success' => true,
            'message' => 'Booking status updated and email attempted.',
            'status'  => $booking->status,
            'start'   => $booking->start_date ? (string)$booking->start_date : null,
            'end'     => $booking->end_date ? (string)$booking->end_date : null,
        ]);
    }

    return back()->with('success', 'Booking status updated and email attempted.');
}



}
