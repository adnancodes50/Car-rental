<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Models\SystemSetting;
use App\Mail\BookingUpcomingReminder;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendUpcomingBookingReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:send-upcoming {days=7}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send booking reminders X days before booking end date using SMTP settings from system_settings table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // --- Load SMTP settings from DB ---
        $settings = SystemSetting::first();

        if (!$settings || !$settings->mail_enabled) {
            $this->error('SMTP settings not found or mail is disabled in system_settings.');
            return;
        }

        // --- Configure mailer dynamically ---
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.host' => $settings->mail_host,
            'mail.mailers.smtp.port' => $settings->mail_port,
            'mail.mailers.smtp.encryption' => $settings->mail_encryption ?: null,
            'mail.mailers.smtp.username' => $settings->mail_username,
            'mail.mailers.smtp.password' => $settings->mail_password,
            'mail.from.address' => $settings->mail_from_address,
            'mail.from.name' => $settings->mail_from_name,
        ]);

        // Purge the mailer so Laravel reloads the new SMTP settings
        \Mail::purge('smtp');

        $daysBefore = (int) $this->argument('days');
        $today = Carbon::today();
        $targetDate = $today->copy()->addDays($daysBefore);

        // Find bookings ending exactly in X days
        $reminders = Booking::whereDate('end_date', $targetDate)->get();

        if ($reminders->isEmpty()) {
            $this->info("No bookings ending in {$daysBefore} day(s).");
            return;
        }

        foreach ($reminders as $booking) {
            if ($booking->customer && $booking->customer->email) {
                try {
                    // Explicitly use the SMTP mailer
                    Mail::mailer('smtp')->to($booking->customer->email)
                        ->send(new BookingUpcomingReminder($booking));

                    $this->info("Reminder sent to: {$booking->customer->email} (Booking ID: {$booking->id})");
                } catch (\Exception $e) {
                    $this->error("Failed to send reminder to {$booking->customer->email}: {$e->getMessage()}");
                }
            } else {
                $this->warn("Booking ID {$booking->id} has no customer or email.");
            }
        }

        $this->info('Booking reminder process completed.');
    }
}
