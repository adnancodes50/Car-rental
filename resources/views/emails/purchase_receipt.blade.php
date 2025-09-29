{{-- resources/views/emails/purchase_receipt.blade.php --}}
@php($v = $purchase->vehicle)
<h2>Thank you for your deposit</h2>
<p>Hi {{ $purchase->customer?->name ?? 'Customer' }},</p>
<p>We received <strong>R{{ number_format($paidNow,2) }}</strong> for your vehicle:</p>
<ul>
  <li>{{ $v?->name }} ({{ $v?->year }} {{ $v?->model }})</li>
  <li>Purchase #: {{ $purchase->id }}</li>
</ul>
@if($purchase->receipt_url)
<p>Receipt: <a href="{{ $purchase->receipt_url }}">View</a></p>
@endif
<p>We’ll contact you to complete the process.</p>
