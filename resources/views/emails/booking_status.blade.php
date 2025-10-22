<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Booking Status Update</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin:0;padding:0;background:#f4f6f8;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8;">
    <tr>
        <td align="center" style="padding:24px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                   style="max-width:640px;background:#ffffff;border-radius:14px;overflow:hidden;">

                <!-- Header -->
                <tr>
                    <td style="background:#111;padding:16px 24px;">
                        <table width="100%">
                            <tr>
                                <td align="left">
                                    <img src="{{ $logo_url }}" alt="Logo" width="70" height="70" style="display:block;border:0;max-width:120px;">
                                </td>
                                <td align="right" style="color:#fff;font:500 14px/1.4 system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;">
                                    Booking Update
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Greeting -->
                <tr>
                    <td style="padding:24px 24px 8px;font:400 16px/1.6 system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;color:#111;">
                        <h1 style="margin:0 0 8px;font:700 22px/1.3 system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;">
                            {{ $status_message }}
                        </h1>

                        <p style="margin:0;">
                            Hi {{ $customer_name }},
                        </p>
                    </td>
                </tr>

                <!-- Booking Summary -->
                <tr>
                    <td style="padding:16px 24px 0;">
                        <table width="100%" role="presentation" cellpadding="0" cellspacing="0"
                               style="background:#fafafa;border:1px solid #eee;border-radius:12px;">
                            <tr>
                                <td style="padding:16px 16px 4px;font:600 15px/1 system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;color:#111;">
                                    Booking Summary
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:0 16px 16px;">
                                    <table width="100%" role="presentation" cellpadding="0" cellspacing="0"
                                           style="font:400 14px/1.6 system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;color:#222;">
                                        {!! $vehicle_row !!}

                                        <tr>
                                            <td style="padding:6px 0;color:#555;">Booking #</td>
                                            <td style="padding:6px 0;text-align:right;color:#111;">
                                                {{ $booking_id }} {{ $booking_reference_paren }}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:6px 0;color:#555;">Dates</td>
                                            <td style="padding:6px 0;text-align:right;color:#111;">
                                                {{ $start_date }} – {{ $end_date }}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:6px 0;color:#555;">Status</td>
                                            <td style="padding:6px 0;text-align:right;color:#111;">
                                                {{ $status }}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:6px 0;color:#555;">Total Amount</td>
                                            <td style="padding:6px 0;text-align:right;color:#111;">
                                                R{{ $total_amount }}
                                            </td>
                                        </tr>

                                        {!! $receipt_button !!}
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="padding:16px 24px 24px;font:400 13px/1.6 system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;color:#666;">
                        If you have any questions, please contact our support team.
                    </td>
                </tr>

                <tr>
                    <td style="background:#f8f9fb;padding:16px 24px;font:400 12px/1.6 system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;color:#9aa1a9;text-align:center;">
                        © {{ $year }} {{ $app_name }}. All rights reserved.
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>
</body>
</html>
