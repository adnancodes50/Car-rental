<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmailTemplate;
use App\Models\SystemSetting;
use App\Models\Booking;
use App\Mail\DynamicEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ReminderController extends Controller
{
    /**
     * Send email based on template trigger and recipient.
     */
    public function sendEmail(Request $request)
    {
        $request->validate([
            'trigger' => 'required|string',
            'recipient_email' => 'required|email',
            'recipient_type' => 'required|string',
            'data' => 'nullable|array',
        ]);

        $trigger = $request->trigger;
        $recipientEmail = $request->recipient_email;
        $recipientType = $request->recipient_type;
        $data = $request->data ?? [];

        $template = EmailTemplate::for($trigger, $recipientType);
        if (!$template) {
            Log::warning("Email template not found for trigger: {$trigger}");
            return response()->json(['message' => 'Template not found or disabled.'], 404);
        }

        $subject = $template->renderSubject($data);
        $body = $template->renderBody($data);

        $settings = SystemSetting::first();
        if (!$settings || !$settings->mail_enabled) {
            return response()->json(['message' => 'Mail sending is disabled.'], 403);
        }

        Config::set('mail.mailers.smtp.host', $settings->mail_host);
        Config::set('mail.mailers.smtp.port', $settings->mail_port);
        Config::set('mail.mailers.smtp.encryption', $settings->mail_encryption);
        Config::set('mail.mailers.smtp.username', $settings->mail_username);
        Config::set('mail.mailers.smtp.password', $settings->mail_password);
        Config::set('mail.from.address', $settings->mail_from_address);
        Config::set('mail.from.name', $settings->mail_from_name);

        try {
            Mail::mailer('smtp')->to($recipientEmail)->send(new DynamicEmail($subject, $body));
            Log::info("Email sent to {$recipientEmail} for trigger {$trigger}");
        } catch (\Exception $e) {
            Log::error("Failed to send email to {$recipientEmail}", ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error sending email.', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Email sent successfully!']);
    }

    /**
     * Fetch bookings completed in the next 7 days and send email.
     */
/**
 * Fetch bookings that will complete in the next 7 days (excluding today) and send "upcoming" email.
 */
public function sendUpcomingCompletedBookingsEmails()
{
    $startDate = Carbon::tomorrow();            // start from tomorrow
    $endDate   = Carbon::today()->addDays(6);   // next 6 days after today

    // Only fetch bookings with status 'confirmed' or 'complete'
    $bookings = Booking::with(['customer', 'equipment', 'location'])
        ->whereBetween('end_date', [$startDate, $endDate])
        ->whereIn('status', ['confirmed', 'complete'])
        ->get();

    if ($bookings->isEmpty()) {
        return response()->json(['message' => 'No upcoming confirmed/completed bookings in the next 7 days.']);
    }

    $result = [];
    foreach ($bookings as $booking) {
        // Use the "booking_upcoming" trigger for your email template
        $this->sendBookingEmail($booking, 'booking_upcoming');

        $result[] = [
            'customer_name' => $booking->customer->name,
            'customer_email' => $booking->customer->email,
            'booking_reference' => $booking->reference,
            'equipment_name' => $booking->equipment->name,
            'location_name' => $booking->location->name,
            'start_date' => $booking->start_date->format('Y-m-d'),
            'end_date' => $booking->end_date->format('Y-m-d'),
            'status' => $booking->status
        ];
    }

    return response()->json([
        'message' => 'Upcoming confirmed/completed bookings fetched and emails sent successfully.',
        'total_bookings' => $bookings->count(),
        'bookings' => $result
    ]);
}



    /**
     * Helper function to send email for a booking
     */
    private function sendBookingEmail($booking, $trigger)
    {
        $data = [
            'customer_name'    => $booking->customer->name,
            'booking_reference'=> $booking->reference,
            'equipment_name'   => $booking->equipment->name,
            'location_name'    => $booking->location->name,
            'start_date'       => $booking->start_date->format('Y-m-d'),
            'end_date'         => $booking->end_date->format('Y-m-d'),
            'paid_now'         => $booking->paid_amount ?? 0,
            'total_amount'     => $booking->total_amount ?? 0,
            'status'           => $booking->status,
        ];

        $fakeRequest = new Request([
            'trigger'         => $trigger,
            'recipient_email' => $booking->customer->email,
            'recipient_type'  => 'customer',
            'data'            => $data
        ]);

        $this->sendEmail($fakeRequest);
    }
}
