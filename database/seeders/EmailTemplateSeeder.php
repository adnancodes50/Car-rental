<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailTemplate;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            // 🔹 Purchase - Customer
            [
                'trigger'   => 'purchase',
                'recipient' => 'customer',
                'name'      => 'Customer Purchase Receipt',
                'subject'   => 'Your purchase confirmation #{{purchase_id}}',
                'body'      => '
                    <p>Hi {{customer_name}},</p>
                    <p>Thank you for purchasing {{vehicle_name}}.</p>
                    <p>Total Paid: <strong>{{amount}}</strong></p>
                    <p>We will contact you shortly with more details.</p>
                    <p>— LandyWorldWide Team</p>
                ',
                'enabled'   => true,
            ],

            // 🔹 Purchase - Admin
            [
                'trigger'   => 'purchase',
                'recipient' => 'admin',
                'name'      => 'Admin Purchase Notification',
                'subject'   => 'New purchase by {{customer_name}}',
                'body'      => '
                    <p>Hello Admin,</p>
                    <p>A new purchase has been made:</p>
                    <ul>
                        <li>Purchase ID: {{purchase_id}}</li>
                        <li>Customer: {{customer_name}} ({{customer_email}})</li>
                        <li>Vehicle: {{vehicle_name}}</li>
                        <li>Amount: {{amount}}</li>
                    </ul>
                ',
                'enabled'   => true,
            ],

            // 🔹 Booking - Customer
            [
                'trigger'   => 'booking',
                'recipient' => 'customer',
                'name'      => 'Customer Booking Confirmation',
                'subject'   => 'Booking confirmed for {{vehicle_name}}',
                'body'      => '
                    <p>Hi {{customer_name}},</p>
                    <p>Your booking for <strong>{{vehicle_name}}</strong> has been confirmed.</p>
                    <p>Booking Date: {{booking_date}}</p>
                    <p>We look forward to serving you!</p>
                ',
                'enabled'   => true,
            ],

            // 🔹 Booking - Admin
            [
                'trigger'   => 'booking',
                'recipient' => 'admin',
                'name'      => 'Admin Booking Alert',
                'subject'   => 'New booking created by {{customer_name}}',
                'body'      => '
                    <p>Hello Admin,</p>
                    <p>A new booking has been made:</p>
                    <ul>
                        <li>Booking ID: {{booking_id}}</li>
                        <li>Customer: {{customer_name}} ({{customer_email}})</li>
                        <li>Vehicle: {{vehicle_name}}</li>
                        <li>Date: {{booking_date}}</li>
                    </ul>
                ',
                'enabled'   => true,
            ],
        ];

        foreach ($templates as $data) {
            EmailTemplate::updateOrCreate(
                ['trigger' => $data['trigger'], 'recipient' => $data['recipient']],
                $data
            );
        }
    }
}
