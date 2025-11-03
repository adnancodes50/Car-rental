<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Purchase;
use App\Models\Booking;
use Illuminate\Http\Request;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingStatusUpdate;
use App\Models\EmailLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\EquipmentStock;



class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::all();
        return view('admin.customer.index', compact('customers'));
    }



public function getCustomerDetails($id)
{
    $customer = Customer::withCount(['bookings', 'purchase'])
        ->withSum('purchase as total_purchase_deposit', 'deposit_paid')
        ->withSum('purchase as total_purchase_price', 'total_price')
        ->findOrFail($id);

    $bookings  = $customer->bookings()->latest()->get();
    $purchases = $customer->purchase()->latest()->get();

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
        'admin_note'   => 'nullable|string|max:1000',
        'booked_stock' => 'nullable|integer|min:1',
        'location_id'  => 'nullable|integer',
    ]);

    return DB::transaction(function () use ($validated, $booking) {
        $start       = Carbon::parse($validated['start_date']);
        $end         = Carbon::parse($validated['end_date']);
        $locationId  = $validated['location_id'] ?? $booking->location_id;
        $equipmentId = $booking->equipment_id;
        $requested   = (int) ($validated['booked_stock'] ?? $booking->booked_stock ?? 1);

        if (!$equipmentId) {
            return response()->json(['success' => false, 'message' => 'Missing equipment.'], 422);
        }

        // Lock stock row to prevent concurrent updates
        $stockRow = EquipmentStock::where('equipment_id', $equipmentId)
            ->where('location_id', $locationId)
            ->lockForUpdate()
            ->first();

        if (!$stockRow) {
            return response()->json(['success' => false, 'message' => 'No inventory record found.'], 422);
        }

        $totalStock = (int) $stockRow->stock;

        // Count already booked units overlapping this date range
        $sumBooked = Booking::where('equipment_id', $equipmentId)
            ->where('location_id', $locationId)
            ->where('id', '!=', $booking->id)
            ->whereDate('start_date', '<=', $end)
            ->whereDate('end_date', '>=', $start)
            ->sum('booked_stock');

        $available = $totalStock - $sumBooked;

        if ($requested > $available) {
            return response()->json([
                'success' => false,
                'message' => "Not enough stock. Available: {$available}, requested: {$requested}."
            ], 422);
        }

        // Update booking record
        $booking->update([
            'start_date'   => $start->toDateString(),
            'end_date'     => $end->toDateString(),
            'admin_note'   => $validated['admin_note'] ?? $booking->admin_note,
            'booked_stock' => $requested,
        ]);

        return response()->json([
            'success'    => true,
            'message'    => 'Booking updated successfully.',
            'available'  => $available - $requested,
        ]);
    });
}
// helper JSON error
protected function errorResponse($msg, $code = 422)
{
    return response()->json(['success' => false, 'message' => $msg], $code);
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
