<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Booking Reminder</title>
</head>
<body style="margin:0;padding:0;background:#f4f6f8;font-family:system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8;padding:24px;">
        <tr>
            <td align="center">
                <table width="600" style="background:#fff;border-radius:12px;overflow:hidden;">
                    <tr>
                        <td style="background:#111;color:#fff;padding:16px 24px;">
                            <h2>Booking Reminder</h2>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px;">
                            <p>Hi {{ $customer_name }},</p>
                            <p>Your booking <strong>{{ $booking_reference }}</strong> for <strong>{{ $equipment_name }}</strong> will end on <strong>{{ $end_date }}</strong>.</p>
                            <p>Please make any final arrangements as needed.</p>
                            <p>Thank you for using {{ $app_name }}!</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#f8f9fb;color:#666;padding:16px;text-align:center;font-size:12px;">
                            © {{ $year }} {{ $app_name }}. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
