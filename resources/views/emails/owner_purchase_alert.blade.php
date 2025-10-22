<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>New Purchase Deposit</title>
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
                    <td style="background:#111;padding:16px 24px;color:#fff;
                               font:500 14px/1.4 system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;">
                        New Purchase Deposit
                    </td>
                </tr>

                <!-- Title -->
                <tr>
                    <td style="padding:20px 24px 8px;
                               font:700 18px/1.4 system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;
                               color:#111;">
                        A new deposit has been received
                    </td>
                </tr>

                <!-- Purchase Info -->
                <tr>
                    <td style="padding:0 24px 16px;">
                        <table width="100%" role="presentation" cellpadding="0" cellspacing="0"
                               style="background:#fafafa;border:1px solid #eee;border-radius:12px;">
                            <tr>
                                <td style="padding:16px;">
                                    <table width="100%" role="presentation" cellpadding="0" cellspacing="0"
                                           style="font:400 14px/1.6 system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;color:#222;">

                                        <tr>
                                            <td style="padding:6px 0;color:#555;">Purchase #</td>
                                            <td style="padding:6px 0;text-align:right;color:#111;">
                                                {{ $purchase_id ?? '{{purchase_id}}' }}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:6px 0;color:#555;">Customer</td>
                                            <td style="padding:6px 0;text-align:right;color:#111;">
                                                {{ $customer_name ?? '{{customer_name}}' }}
                                                {{ $customer_email_paren ?? '{{customer_email_paren}}' }}
                                            </td>
                                        </tr>

                                        {!! $vehicle_row ?? '{{vehicle_row}}' !!}

                                        <tr>
                                            <td style="padding:6px 0;color:#555;">Paid Now</td>
                                            <td style="padding:6px 0;text-align:right;color:#111;">
                                                <strong>R{{ $paid_now ?? '{{paid_now}}' }}</strong>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:6px 0;color:#555;">Total Deposit Paid</td>
                                            <td style="padding:6px 0;text-align:right;color:#111;">
                                                R{{ $deposit_paid ?? '{{deposit_paid}}' }}
                                            </td>
                                        </tr>

                                    </table>
                                </td>
                            </tr>
                        </table>

                        <!-- Receipt Button -->
                        {!! $receipt_button ?? '{{receipt_button}}' !!}
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="background:#f8f9fb;padding:16px 24px;
                               font:400 12px/1.6 system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;
                               color:#9aa1a9;text-align:center;">
                        © {{ $year ?? '{{year}}' }} {{ $app_name ?? '{{app_name}}' }}. All rights reserved.
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>
</body>
</html>
