<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailTemplate;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        // ---------------------------
        // Base HTML template for customer/admin
        // ---------------------------
        $baseShell = <<<HTML
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>{{title_text}}</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin:0;padding:0;background:#f4f6f8;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8;">
<tr>
<td align="center" style="padding:24px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;background:#ffffff;border-radius:14px;overflow:hidden;">

<!-- Header -->
<tr>
<td style="background:#111;padding:16px 24px;">
<table width="100%">
<tr>
<td align="left">
<img src="{{logo_url}}" alt="Logo" width="70" height="70" style="display:block;border:0;max-width:120px;">
</td>
<td align="right" style="color:#fff;font:500 14px/1.4 system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;">
{{header_text}}
</td>
</tr>
</table>
</td>
</tr>

<!-- Greeting -->
<tr>
<td style="padding:24px 24px 8px;font:400 16px/1.6 system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;color:#111;">
<h1 style="margin:0 0 8px;font:700 22px/1.3 system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;">
{{lead_title}}
</h1>
<p style="margin:0;">
Hi {{customer_name}}, {{lead_line}}
</p>
</td>
</tr>

<!-- Booking Summary -->
<tr>
<td style="padding:16px 24px 0;">
<table width="100%" role="presentation" cellpadding="0" cellspacing="0" style="background:#fafafa;border:1px solid #eee;border-radius:12px;">
<tr>
<td style="padding:16px 16px 4px;font:600 15px/1 system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;color:#111;">Booking Summary</td>
</tr>
<tr>
<td style="padding:0 16px 16px;">
<table width="100%" role="presentation" cellpadding="0" cellspacing="0" style="font:400 14px/1.6 system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;color:#222;">
<tr>
<td style="padding:6px 0;color:#555;">Booking #</td>
<td style="padding:6px 0;text-align:right;color:#111;">
{{booking_id}} {{booking_reference_paren}}
</td>
</tr>
{!! vehicle_row !!}
<tr>
<td style="padding:6px 0;color:#555;">Dates</td>
<td style="padding:6px 0;text-align:right;color:#111;">
{{start_date}} – {{end_date}}
</td>
</tr>
<tr>
<td style="padding:6px 0;color:#555;">Status</td>
<td style="padding:6px 0;text-align:right;color:#111;">
{{status}}
</td>
</tr>
{{money_rows}}
{!! receipt_button !!}
</table>
</td>
</tr>
</table>
</td>
</tr>

<!-- Footer -->
<tr>
<td style="padding:16px 24px 24px;font:400 13px/1.6 system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;color:#666;">
We’ll be in touch with next steps. Enjoy the ride!
</td>
</tr>

<tr>
<td style="background:#f8f9fb;padding:16px 24px;font:400 12px/1.6 system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;color:#9aa1a9;text-align:center;">
© {{year}} {{app_name}}. All rights reserved.
</td>
</tr>

</table>
</td>
</tr>
</table>
</body>
</html>
HTML;

        // ---------------------------
        // Money rows for booking/payment
        // ---------------------------
        $moneyRowsBookingReceipt = <<<HTML
<tr>
<td style="padding:6px 0;color:#555;">Paid Now</td>
<td style="padding:6px 0;text-align:right;color:#111;"><strong>R{{paid_now}}</strong></td>
</tr>
<tr>
<td style="padding:6px 0;color:#555;">Total Amount</td>
<td style="padding:6px 0;text-align:right;color:#111;">R{{total_amount}}</td>
</tr>
HTML;

        $moneyRowsPurchaseReceipt = <<<HTML
<tr>
<td style="padding:6px 0;color:#555;">Paid Now</td>
<td style="padding:6px 0;text-align:right;color:#111;"><strong>R{{paid_now}}</strong></td>
</tr>
<tr>
<td style="padding:6px 0;color:#555;">Total Deposit Paid</td>
<td style="padding:6px 0;text-align:right;color:#111;">R{{deposit_paid}}</td>
</tr>
HTML;

        // ---------------------------
        // All email templates
        // ---------------------------
        $records = [
            // Booking Receipt Customer
            [
                'trigger' => 'booking_receipt',
                'recipient' => 'customer',
                'name' => 'Booking Receipt (Customer)',
                'subject' => '{{app_name}} • Booking Receipt ({{booking_reference}})',
                'body' => str_replace(
                    ['{{title_text}}','{{header_text}}','{{lead_title}}','{{lead_line}}','{{money_rows}}'],
                    ['Booking Payment Receipt','Booking Receipt','Thanks for your booking','We received <strong>R{{paid_now}}</strong>.', $moneyRowsBookingReceipt],
                    $baseShell
                ),
                'enabled' => true,
            ],
            // Booking Receipt Admin
            [
                'trigger' => 'booking_receipt',
                'recipient' => 'admin',
                'name' => 'Booking Receipt (Admin)',
                'subject' => '{{app_name}} • Payment Received ({{booking_reference}})',
                'body' => str_replace(
                    ['{{title_text}}','{{header_text}}','{{lead_title}}','{{money_rows}}'],
                    ['Payment Received (Booking)','Payment Received','A payment was just received', $moneyRowsBookingReceipt],
                    $baseShell
                ),
                'enabled' => true,
            ],
            // Purchase Receipt Customer
            [
                'trigger' => 'purchase_receipt',
                'recipient' => 'customer',
                'name' => 'Purchase Receipt (Customer)',
                'subject' => '{{app_name}} • Deposit Receipt (#{{purchase_id}})',
                'body' => str_replace(
                    ['{{title_text}}','{{header_text}}','{{lead_title}}','{{lead_line}}','{{money_rows}}'],
                    ['Purchase Deposit Receipt','Purchase Receipt','Thank you for your deposit','We received your payment.', $moneyRowsPurchaseReceipt],
                    $baseShell
                ),
                'enabled' => true,
            ],
            // Purchase Deposit Admin
            [
                'trigger' => 'purchase_deposit',
                'recipient' => 'admin',
                'name' => 'Purchase Deposit (Admin)',
                'subject' => '{{app_name}} • New Deposit Received (#{{purchase_id}})',
                'body' => str_replace(
                    ['{{title_text}}','{{header_text}}','{{lead_title}}','{{money_rows}}'],
                    ['New Purchase Deposit','New Purchase Deposit','A new deposit has been received', $moneyRowsPurchaseReceipt],
                    $baseShell
                ),
                'enabled' => true,
            ],
        ];

        // ---------------------------
        // Booking Status Templates (Customer)
        // ---------------------------
        $bookingStatuses = [
            'pending' => 'Booking Pending',
            'confirmed' => 'Booking Confirmed',
            'complete' => 'Booking Complete',
            'cancelled' => 'Booking Cancelled',
        ];

        foreach ($bookingStatuses as $statusTrigger => $statusName) {
            $records[] = [
                'trigger' => "booking_{$statusTrigger}",
                'recipient' => 'customer',
                'name' => $statusName,
                'subject' => "{{app_name}} • {$statusName} ({{booking_reference}})",
                'body' => str_replace(
                    ['{{title_text}}','{{header_text}}','{{lead_title}}','{{lead_line}}','{{money_rows}}'],
                    [
                        $statusName,
                        $statusName,
                        "Your booking is now {$statusTrigger}",
                        $statusTrigger === 'cancelled'
                            ? "Unfortunately, your booking has been canceled. Please contact our support team if you have any questions."
                            : "This is an update on your booking status.",
                        $moneyRowsBookingReceipt
                    ],
                    $baseShell
                ),
                'enabled' => true,
            ];
        }

        // ---------------------------
        // Insert or update all records
        // ---------------------------
        foreach ($records as $rec) {
            EmailTemplate::updateOrCreate(
                ['trigger' => $rec['trigger'], 'recipient' => $rec['recipient']],
                [
                    'name' => $rec['name'],
                    'subject' => $rec['subject'],
                    'body' => $rec['body'],
                    'enabled' => $rec['enabled'],
                ]
            );
        }
    }
}
