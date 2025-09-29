{{-- resources/views/emails/owner_purchase_alert.blade.php --}}
@php($v = $purchase->vehicle)
<h2>New deposit received</h2>
<ul>
  <li>Purchase #: {{ $purchase->id }}</li>
  <li>Customer: {{ $purchase->customer?->name }} ({{ $purchase->customer?->email }})</li>
  <li>Vehicle: {{ $v?->name }} ({{ $v?->year }} {{ $v?->model }})</li>
  <li>Paid now: R{{ number_format($paidNow,2) }}</li>
  <li>Total deposit paid: R{{ number_format($purchase->deposit_paid,2) }}</li>
</ul>
@if($purchase->receipt_url)
<p>Receipt: <a href="{{ $purchase->receipt_url }}">View</a></p>
@endif
