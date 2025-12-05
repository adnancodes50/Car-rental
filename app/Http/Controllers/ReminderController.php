<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmailTemplate;
use App\Models\SystemSetting;
use App\Mail\DynamicEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class ReminderController extends Controller
{
    /**
     * API: Send email based on template trigger and recipient.
     */
    public function sendEmail(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'trigger' => 'required|string',
            'recipient_email' => 'required|email',
            'recipient_type' => 'required|string', // customer/admin
            'data' => 'nullable|array',
        ]);

        $trigger = $request->trigger;
        $recipientEmail = $request->recipient_email;
        $recipientType = $request->recipient_type;
        $data = $request->data ?? [];

        // Fetch email template
        $template = EmailTemplate::for($trigger, $recipientType);
        if (!$template) {
            return response()->json([
                'message' => 'Template not found or disabled.',
            ], 404);
        }

        // Render subject & body
        $subject = $template->renderSubject($data);
        $body = $template->renderBody($data);

        // Load SMTP settings from DB
        $settings = SystemSetting::first();
        if (!$settings || !$settings->mail_enabled) {
            return response()->json([
                'message' => 'Mail sending is disabled in system settings.',
            ], 403);
        }

        // Configure SMTP dynamically
        Config::set('mail.mailers.smtp.host', $settings->mail_host);
        Config::set('mail.mailers.smtp.port', $settings->mail_port);
        Config::set('mail.mailers.smtp.encryption', $settings->mail_encryption);
        Config::set('mail.mailers.smtp.username', $settings->mail_username);
        Config::set('mail.mailers.smtp.password', $settings->mail_password);
        Config::set('mail.from.address', $settings->mail_from_address);
        Config::set('mail.from.name', $settings->mail_from_name);

        // Send email with exception handling
        try {
            Mail::mailer('smtp')->to($recipientEmail)->send(new DynamicEmail($subject, $body));
        } catch (\Exception $e) {
            Log::error('Failed to send email', [
                'to' => $recipientEmail,
                'trigger' => $trigger,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error sending email.',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Email sent successfully!',
        ]);
    }
}
