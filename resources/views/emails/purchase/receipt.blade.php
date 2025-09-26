@component('mail::message')
# Thank you for your payment

Hi {{ $purchase->customer?->name ?? 'Customer' }},

We’ve received your payment.

**Purchase #{{ $purchase->id }}**
**Vehicle:** {{ $purchase->vehicle?->name }}
**Paid now:** R{{ number_format($paidNow, 2) }}
**Deposit paid total:** R{{ number_format($purchase->deposit_paid ?? 0, 2) }}
**Remaining (after deposit):** R{{ number_format($remaining, 2) }}

@component('mail::panel')
If you have any questions, just reply to this email — we’re happy to help.
@endcomponent

Thanks,
{{ config('app.name') }}
@endcomponent
