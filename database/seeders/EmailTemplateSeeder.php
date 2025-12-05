<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailTemplate;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        // Base HTML template shell
        $baseShell = <<<HTML
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>{{title_text}}</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
    body { margin:0; padding:0; background:#f4f6f8; font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; }
    .container { max-width:640px; margin:24px auto; background:#ffffff; border-radius:14px; overflow:hidden; }
    .header { background:#111; padding:16px 24px; color:#fff; }
    .content { padding:24px; }
    .summary { background:#fafafa; border:1px solid #eee; border-radius:12px; padding:16px; margin:16px 0; }
    .footer { background:#f8f9fb; padding:16px 24px; text-align:center; color:#9aa1a9; font-size:12px; }
    table { width:100%; border-collapse: collapse; }
    .price-line { border-bottom:1px solid #f8f9fa; padding:6px 0; }
    .price-line:last-child { border-bottom:none; }
</style>
</head>
<body>
<div class="container">
    <!-- Header -->
    <div class="header">
        <table>
            <tr>
                <td align="left">
                    <img src="{{logo_url}}" alt="Logo" width="70" height="70" style="display:block;border:0;max-width:120px;">
                </td>
                <td align="right" style="color:#fff;font-weight:500;font-size:14px;">
                    {{header_text}}
                </td>
            </tr>
        </table>
    </div>

    <!-- Content -->
    <div class="content">
        <h1 style="margin:0 0 8px;font-weight:700;font-size:22px;">{{lead_title}}</h1>
        <p style="margin:0;font-size:16px;color:#111;">
            {{lead_line}}
        </p>

        <!-- Booking/Purchase Summary -->
        <div class="summary">
            <div style="font-weight:600;font-size:15px;color:#111;margin-bottom:12px;">{{summary_title}}</div>
            <table>
                {{reference_row}}
                {{equipment_row}}
                {{location_row}}
                {{dates_row}}
                {{quantity_row}}
                {{status_row}}
                {{money_rows}}
            </table>
        </div>

        <!-- Action Button -->
        {{receipt_button}}

        <!-- Footer Message -->
        <p style="margin:16px 0 0;font-size:13px;color:#666;">
            {{footer_message}}
        </p>
    </div>

    <!-- Footer -->
    <div class="footer">
        © {{year}} {{app_name}}. All rights reserved.
    </div>
</div>
</body>
</html>
HTML;

        // ---------------------------
        // Template rows and sections
        // ---------------------------

        // Booking reference row
        $bookingReferenceRow = <<<HTML
<tr class="price-line">
    <td style="color:#555;">Booking #</td>
    <td style="text-align:right;color:#111;font-weight:600;">
        {{booking_reference}}
    </td>
</tr>
HTML;

        // Purchase reference row
        $purchaseReferenceRow = <<<HTML
<tr class="price-line">
    <td style="color:#555;">Purchase #</td>
    <td style="text-align:right;color:#111;font-weight:600;">
        {{purchase_id}}
    </td>
</tr>
HTML;

        // Equipment row
        $equipmentRow = <<<HTML
<tr class="price-line">
    <td style="color:#555;">Equipment</td>
    <td style="text-align:right;color:#111;">
        {{equipment_name}}
    </td>
</tr>
HTML;

        // Location row
        $locationRow = <<<HTML
<tr class="price-line">
    <td style="color:#555;">Location</td>
    <td style="text-align:right;color:#111;">
        {{location_name}}
    </td>
</tr>
HTML;

        // Dates row for bookings
        $datesRow = <<<HTML
<tr class="price-line">
    <td style="color:#555;">Rental Period</td>
    <td style="text-align:right;color:#111;">
        {{start_date}} – {{end_date}}
    </td>
</tr>
HTML;

        // Quantity row for purchases
        $quantityRow = <<<HTML
<tr class="price-line">
    <td style="color:#555;">Quantity</td>
    <td style="text-align:right;color:#111;">
        {{quantity}}
    </td>
</tr>
HTML;

        // Status row
        $statusRow = <<<HTML
<tr class="price-line">
    <td style="color:#555;">Status</td>
    <td style="text-align:right;color:#111;">
        {{status}}
    </td>
</tr>
HTML;

        // Money rows for booking receipt
        $moneyRowsBooking = <<<HTML
<tr class="price-line">
    <td style="color:#555;">Paid Now</td>
    <td style="text-align:right;color:#111;"><strong>R{{paid_now}}</strong></td>
</tr>
<tr class="price-line">
    <td style="color:#555;">Total Amount</td>
    <td style="text-align:right;color:#111;">R{{total_amount}}</td>
</tr>
HTML;

        // Money rows for purchase receipt
        $moneyRowsPurchase = <<<HTML
<tr class="price-line">
    <td style="color:#555;">Deposit Paid</td>
    <td style="text-align:right;color:#111;"><strong>R{{paid_now}}</strong></td>
</tr>
<tr class="price-line">
    <td style="color:#555;">Total Deposit</td>
    <td style="text-align:right;color:#111;">R{{deposit_paid}}</td>
</tr>
<tr class="price-line">
    <td style="color:#555;">Total Price</td>
    <td style="text-align:right;color:#111;">R{{total_amount}}</td>
</tr>
HTML;

        // Receipt button
        $receiptButton = <<<HTML
<div style="text-align:center;margin:20px 0;">
    <a href="{{receipt_url}}" style="background:#CF9B4D;color:white;padding:12px 24px;text-decoration:none;border-radius:6px;font-weight:600;display:inline-block;">
        View Receipt
    </a>
</div>
HTML;

        // ---------------------------
        // 8 Email Templates
        // ---------------------------
        $records = [
            // 1. Booking Receipt - Customer
            [
                'trigger' => 'booking_receipt',
                'recipient' => 'customer',
                'name' => 'Booking Receipt (Customer)',
                'subject' => '{{app_name}} • Booking Receipt ({{booking_reference}})',
                'body' => str_replace(
                    [
                        '{{title_text}}', '{{header_text}}', '{{lead_title}}', '{{lead_line}}',
                        '{{summary_title}}', '{{reference_row}}', '{{equipment_row}}', '{{location_row}}',
                        '{{dates_row}}', '{{quantity_row}}', '{{status_row}}', '{{money_rows}}',
                        '{{receipt_button}}', '{{footer_message}}'
                    ],
                    [
                        'Booking Payment Receipt',
                        'Booking Receipt',
                        'Thanks for your booking!',
                        'Hi {{customer_name}}, We received <strong>R{{paid_now}}</strong> for your equipment rental.',
                        'Booking Summary',
                        $bookingReferenceRow,
                        $equipmentRow,
                        $locationRow,
                        $datesRow,
                        '', // No quantity row for bookings
                        $statusRow,
                        $moneyRowsBooking,
                        $receiptButton,
                        'We\'ll be in touch with next steps. Enjoy using the equipment!'
                    ],
                    $baseShell
                ),
                'enabled' => true,
            ],

            // 2. Booking Receipt - Admin
            [
                'trigger' => 'booking_receipt',
                'recipient' => 'admin',
                'name' => 'Booking Receipt (Admin)',
                'subject' => '{{app_name}} • New Booking Payment ({{booking_reference}})',
                'body' => str_replace(
                    [
                        '{{title_text}}', '{{header_text}}', '{{lead_title}}', '{{lead_line}}',
                        '{{summary_title}}', '{{reference_row}}', '{{equipment_row}}', '{{location_row}}',
                        '{{dates_row}}', '{{quantity_row}}', '{{status_row}}', '{{money_rows}}',
                        '{{receipt_button}}', '{{footer_message}}'
                    ],
                    [
                        'New Booking Payment',
                        'New Booking',
                        'New booking payment received',
                        'A payment of <strong>R{{paid_now}}</strong> was received from {{customer_name}}.',
                        'Booking Details',
                        $bookingReferenceRow,
                        $equipmentRow,
                        $locationRow,
                        $datesRow,
                        '', // No quantity row for bookings
                        $statusRow,
                        $moneyRowsBooking,
                        '', // No receipt button for admin
                        'Customer has been notified via email.'
                    ],
                    $baseShell
                ),
                'enabled' => true,
            ],

            // 3. Purchase Receipt - Customer
            [
                'trigger' => 'purchase_receipt',
                'recipient' => 'customer',
                'name' => 'Purchase Receipt (Customer)',
                'subject' => '{{app_name}} • Purchase Receipt (#{{purchase_id}})',
                'body' => str_replace(
                    [
                        '{{title_text}}', '{{header_text}}', '{{lead_title}}', '{{lead_line}}',
                        '{{summary_title}}', '{{reference_row}}', '{{equipment_row}}', '{{location_row}}',
                        '{{dates_row}}', '{{quantity_row}}', '{{status_row}}', '{{money_rows}}',
                        '{{receipt_button}}', '{{footer_message}}'
                    ],
                    [
                        'Purchase Receipt',
                        'Purchase Receipt',
                        'Thank you for your purchase!',
                        'Hi {{customer_name}}, We received your deposit payment of <strong>R{{paid_now}}</strong>.',
                        'Purchase Summary',
                        $purchaseReferenceRow,
                        $equipmentRow,
                        $locationRow,
                        '', // No dates for purchases
                        $quantityRow,
                        $statusRow,
                        $moneyRowsPurchase,
                        $receiptButton,
                        'We\'ll contact you shortly to arrange equipment collection.'
                    ],
                    $baseShell
                ),
                'enabled' => true,
            ],

            // 4. Purchase Receipt - Admin
            [
                'trigger' => 'purchase_receipt',
                'recipient' => 'admin',
                'name' => 'Purchase Receipt (Admin)',
                'subject' => '{{app_name}} • New Equipment Purchase (#{{purchase_id}})',
                'body' => str_replace(
                    [
                        '{{title_text}}', '{{header_text}}', '{{lead_title}}', '{{lead_line}}',
                        '{{summary_title}}', '{{reference_row}}', '{{equipment_row}}', '{{location_row}}',
                        '{{dates_row}}', '{{quantity_row}}', '{{status_row}}', '{{money_rows}}',
                        '{{receipt_button}}', '{{footer_message}}'
                    ],
                    [
                        'New Equipment Purchase',
                        'New Purchase',
                        'New equipment purchase received',
                        'A deposit of <strong>R{{paid_now}}</strong> was received from {{customer_name}}.',
                        'Purchase Details',
                        $purchaseReferenceRow,
                        $equipmentRow,
                        $locationRow,
                        '', // No dates for purchases
                        $quantityRow,
                        $statusRow,
                        $moneyRowsPurchase,
                        '', // No receipt button for admin
                        'Customer has been notified via email.'
                    ],
                    $baseShell
                ),
                'enabled' => true,
            ],

            // 5. Booking Status - Pending (Customer)
            [
                'trigger' => 'booking_pending',
                'recipient' => 'customer',
                'name' => 'Booking Pending (Customer)',
                'subject' => '{{app_name}} • Booking Pending ({{booking_reference}})',
                'body' => str_replace(
                    [
                        '{{title_text}}', '{{header_text}}', '{{lead_title}}', '{{lead_line}}',
                        '{{summary_title}}', '{{reference_row}}', '{{equipment_row}}', '{{location_row}}',
                        '{{dates_row}}', '{{quantity_row}}', '{{status_row}}', '{{money_rows}}',
                        '{{receipt_button}}', '{{footer_message}}'
                    ],
                    [
                        'Booking Pending',
                        'Booking Pending',
                        'Your booking is pending',
                        'Hi {{customer_name}}, Your equipment rental booking has been received and is pending payment confirmation.',
                        'Booking Details',
                        $bookingReferenceRow,
                        $equipmentRow,
                        $locationRow,
                        $datesRow,
                        '', // No quantity row for bookings
                        $statusRow,
                        $moneyRowsBooking,
                        '', // No receipt button for pending
                        'Please complete your payment to confirm your booking.'
                    ],
                    $baseShell
                ),
                'enabled' => true,
            ],

            // 6. Booking Status - Confirmed (Customer)
            [
                'trigger' => 'booking_confirmed',
                'recipient' => 'customer',
                'name' => 'Booking Confirmed (Customer)',
                'subject' => '{{app_name}} • Booking Confirmed ({{booking_reference}})',
                'body' => str_replace(
                    [
                        '{{title_text}}', '{{header_text}}', '{{lead_title}}', '{{lead_line}}',
                        '{{summary_title}}', '{{reference_row}}', '{{equipment_row}}', '{{location_row}}',
                        '{{dates_row}}', '{{quantity_row}}', '{{status_row}}', '{{money_rows}}',
                        '{{receipt_button}}', '{{footer_message}}'
                    ],
                    [
                        'Booking Confirmed',
                        'Booking Confirmed',
                        'Your booking is confirmed!',
                        'Hi {{customer_name}}, Your equipment rental has been confirmed and payment received.',
                        'Booking Summary',
                        $bookingReferenceRow,
                        $equipmentRow,
                        $locationRow,
                        $datesRow,
                        '', // No quantity row for bookings
                        $statusRow,
                        $moneyRowsBooking,
                        $receiptButton,
                        'We look forward to serving you! Contact us if you have any questions.'
                    ],
                    $baseShell
                ),
                'enabled' => true,
            ],

            // 7. Booking Status - Complete (Customer)
            [
                'trigger' => 'booking_complete',
                'recipient' => 'customer',
                'name' => 'Booking Complete (Customer)',
                'subject' => '{{app_name}} • Booking Complete ({{booking_reference}})',
                'body' => str_replace(
                    [
                        '{{title_text}}', '{{header_text}}', '{{lead_title}}', '{{lead_line}}',
                        '{{summary_title}}', '{{reference_row}}', '{{equipment_row}}', '{{location_row}}',
                        '{{dates_row}}', '{{quantity_row}}', '{{status_row}}', '{{money_rows}}',
                        '{{receipt_button}}', '{{footer_message}}'
                    ],
                    [
                        'Booking Complete',
                        'Booking Complete',
                        'Booking completed successfully',
                        'Hi {{customer_name}}, Your equipment rental has been completed. Thank you for choosing us!',
                        'Booking Summary',
                        $bookingReferenceRow,
                        $equipmentRow,
                        $locationRow,
                        $datesRow,
                        '', // No quantity row for bookings
                        $statusRow,
                        $moneyRowsBooking,
                        $receiptButton,
                        'We hope you had a great experience. We look forward to serving you again!'
                    ],
                    $baseShell
                ),
                'enabled' => true,
            ],

            // 8. Booking Status - Cancelled (Customer)
            [
                'trigger' => 'booking_cancelled',
                'recipient' => 'customer',
                'name' => 'Booking Cancelled (Customer)',
                'subject' => '{{app_name}} • Booking Cancelled ({{booking_reference}})',
                'body' => str_replace(
                    [
                        '{{title_text}}', '{{header_text}}', '{{lead_title}}', '{{lead_line}}',
                        '{{summary_title}}', '{{reference_row}}', '{{equipment_row}}', '{{location_row}}',
                        '{{dates_row}}', '{{quantity_row}}', '{{status_row}}', '{{money_rows}}',
                        '{{receipt_button}}', '{{footer_message}}'
                    ],
                    [
                        'Booking Cancelled',
                        'Booking Cancelled',
                        'Booking cancelled',
                        'Hi {{customer_name}}, Unfortunately, your equipment rental booking has been cancelled.',
                        'Booking Details',
                        $bookingReferenceRow,
                        $equipmentRow,
                        $locationRow,
                        $datesRow,
                        '', // No quantity row for bookings
                        $statusRow,
                        $moneyRowsBooking,
                        '', // No receipt button for cancelled
                        'Please contact our support team if you have any questions or would like to rebook.'
                    ],
                    $baseShell
                ),
                'enabled' => true,
            ],
            // 9. Booking Upcoming Reminder (Customer)
[
    'trigger' => 'booking_upcoming',
    'recipient' => 'customer',
    'name' => 'Booking Upcoming Reminder (Customer)',
    'subject' => '{{app_name}} • Upcoming Booking Reminder ({{booking_reference}})',
    'body' => str_replace(
        [
            '{{title_text}}', '{{header_text}}', '{{lead_title}}', '{{lead_line}}',
            '{{summary_title}}', '{{reference_row}}', '{{equipment_row}}', '{{location_row}}',
            '{{dates_row}}', '{{quantity_row}}', '{{status_row}}', '{{money_rows}}',
            '{{receipt_button}}', '{{footer_message}}'
        ],
        [
            'Upcoming Booking Reminder',
            'Upcoming Booking',
            'Your booking is coming up!',
            'Hi {{customer_name}}, This is a friendly reminder that your equipment rental is scheduled to end in 7 days.',
            'Booking Summary',
            $bookingReferenceRow,
            $equipmentRow,
            $locationRow,
            $datesRow,
            '', // No quantity row for bookings
            $statusRow,
            $moneyRowsBooking,
            $receiptButton,
            'Please make sure everything is ready. Contact us if you need assistance or want to make changes.'
        ],
        $baseShell
    ),
    'enabled' => true,
],

        ];

        // ---------------------------
        // Insert or update all records
        // ---------------------------
        foreach ($records as $rec) {
            EmailTemplate::updateOrCreate(
                [
                    'trigger' => $rec['trigger'],
                    'recipient' => $rec['recipient']
                ],
                [
                    'name' => $rec['name'],
                    'subject' => $rec['subject'],
                    'body' => $rec['body'],
                    'enabled' => $rec['enabled'],
                ]
            );
        }

        $this->command->info('✅ 9 Email templates seeded successfully!');
        $this->command->info('📧 Templates created:');
        $this->command->info('   1. Booking Receipt (Customer)');
        $this->command->info('   2. Booking Receipt (Admin)');
        $this->command->info('   3. Purchase Receipt (Customer)');
        $this->command->info('   4. Purchase Receipt (Admin)');
        $this->command->info('   5. Booking Pending (Customer)');
        $this->command->info('   6. Booking Confirmed (Customer)');
        $this->command->info('   7. Booking Complete (Customer)');
        $this->command->info('   8. Booking Cancelled (Customer)');
        $this->command->info('   9. Booking Upcoming Reminder (Customer)');
    }
}
