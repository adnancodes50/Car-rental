@php($v = $booking->vehicle)
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Booking Payment Receipt</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin:0;padding:0;background:#f4f6f8;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8;">
    <tr>
      <td align="center" style="padding:24px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;background:#ffffff;border-radius:14px;overflow:hidden;">
          <tr>
            <td style="background:#111;padding:16px 24px;">
              <table width="100%">
                <tr>
                  <td align="left">
<img
  src="{{ asset('vendor/adminlte/dist/img/logo.png') }}"
  alt="Logo"
  width="70" height="70"
  style="display:block;border:0;max-width:120px;"
>                  </td>
                  <td align="right" style="color:#fff;font:500 14px/1.4 system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;">Booking Receipt</td>
                </tr>
              </table>
            </td>
          </tr>

          <tr>
            <td style="padding:24px 24px 8px;font:400 16px/1.6 system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;color:#111;">
              <h1 style="margin:0 0 8px;font:700 22px/1.3 system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;">Thanks for your booking</h1>
              <p style="margin:0;">Hi {{ $booking->customer?->name ?? 'Customer' }}, we received <strong>R{{ number_format($paidNow ?? (float)$booking->total_price,2) }}</strong>.</p>
            </td>
          </tr>

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
                          {{ $booking->id }} @if($booking->reference) ({{ $booking->reference }}) @endif
                        </td>
                      </tr>
                      @if($v)
                      <tr>
                        <td style="padding:6px 0;color:#555;">Vehicle</td>
                        <td style="padding:6px 0;text-align:right;color:#111;">
                          {{ $v->name }}@if($v->year||$v->model) ({{ $v->year }} {{ $v->model }})@endif
                        </td>
                      </tr>
                      @endif
                      <tr>
                        <td style="padding:6px 0;color:#555;">Dates</td>
                        <td style="padding:6px 0;text-align:right;color:#111;">
                          {{ \Carbon\Carbon::parse($booking->start_date)->toFormattedDateString() }}
                          –
                          {{ \Carbon\Carbon::parse($booking->end_date)->toFormattedDateString() }}
                        </td>
                      </tr>
                      <tr>
                        <td style="padding:6px 0;color:#555;">Status</td>
                        <td style="padding:6px 0;text-align:right;color:#111;">
                          {{ ucfirst($booking->payment_status ?: $booking->status) }}
                        </td>
                      </tr>
                      <tr>
                        <td style="padding:6px 0;color:#555;">Paid Now</td>
                        <td style="padding:6px 0;text-align:right;color:#111;"><strong>R{{ number_format($paidNow ?? (float)$booking->total_price,2) }}</strong></td>
                      </tr>
                      <tr>
                        <td style="padding:6px 0;color:#555;">Total Amount</td>
                        <td style="padding:6px 0;text-align:right;color:#111;">R{{ number_format((float)$booking->total_price,2) }}</td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          @if(!empty($booking->receipt_url))
          <tr>
            <td align="center" style="padding:20px 24px 8px;">
              <a href="{{ $booking->receipt_url }}" target="_blank" rel="noopener"
                 style="display:inline-block;background:#CF9B4D;color:#fff;text-decoration:none;font:600 14px/1 system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;padding:12px 18px;border-radius:10px;">
                View Receipt
              </a>
            </td>
          </tr>
          @endif

          <tr>
            <td style="padding:16px 24px 24px;font:400 13px/1.6 system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;color:#666;">
              We’ll be in touch with next steps. Enjoy the ride!
            </td>
          </tr>

          <tr>
            <td style="background:#f8f9fb;padding:16px 24px;font:400 12px/1.6 system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;color:#9aa1a9;text-align:center;">
              © {{ date('Y') }} LandyWorldWide. All rights reserved.
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
