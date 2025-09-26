@component('mail::message')
# New Purchase Payment Received

**Purchase #{{ $purchase->id }}**
**Customer:** {{ $purchase->customer?->name }} ({{ $purchase->customer?->email }})
**Vehicle:** {{ $purchase->vehicle?->name }}
**Paid now:** R{{ number_format($paidNow, 2) }}
**Deposit paid total:** R{{ number_format($purchase->deposit_paid ?? 0, 2) }}
**Remaining (after deposit):** R{{ number_format($remaining, 2) }}

@endcomponent
