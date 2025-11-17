@extends('adminlte::page')

@section('title', 'Purchase Details')

@section('content')
    <div class="container-fluid">
        {{-- Header: Purchase Details + Back Button --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h3 text-bold mt-3">Purchase Details</h1>
            <a href="{{ route('purchases.index') }}" class="btn btn-secondary btn-sm mt-3">
                <i class="fas fa-arrow-left py-2"></i> Back
            </a>
        </div>

        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body">
                {{-- Customer & Equipment Info --}}
                <div class="row mb-4">
                    <div class="col-md-4">
                        <h5 class="text-uppercase text-muted mb-3">Customer Information</h5>
                        <p><strong>Name:</strong> {{ $purchase->customer->name ?? '-' }}</p>
                        <p><strong>Email:</strong> {{ $purchase->customer->email ?? '-' }}</p>
                        <p><strong>Phone:</strong> {{ $purchase->customer->phone ?? '-' }}</p>
                    </div>

                    <div class="col-md-4">
                        <h5 class="text-uppercase text-muted mb-3">Equipment & Purchase Info</h5>
                        <p><strong>Equipment:</strong> {{ $purchase->equipment->name ?? '-' }}</p>
                        <p><strong>Quantity:</strong> {{ $purchase->quantity }}</p>
                        <p><strong>Status:</strong>
                            @php
                                $statusMap = [
                                    'pending' => ['label' => 'Pending', 'class' => 'badge bg-warning text-dark'],
                                    'completed' => ['label' => 'Completed', 'class' => 'badge bg-success'],
                                    'canceled' => ['label' => 'Canceled', 'class' => 'badge bg-danger'],
                                ];
                                $statusData = $statusMap[$purchase->payment_status] ?? [
                                    'label' => ucfirst($purchase->payment_status ?? 'Unknown'),
                                    'class' => 'badge bg-success',
                                ];
                            @endphp
                            <span class="{{ $statusData['class'] }}">{{ $statusData['label'] }}</span>
                        </p>
                        <p><strong>Payment Method:</strong> {{ $purchase->payment_method ?? '-' }}</p>
                    </div>


                    <div class="col-md-4">
                        <h5 class="text-uppercase text-muted mb-3">Pricing</h5>
                        <p><strong>Total Price:</strong> <span
                                class="text-success">R{{ number_format($purchase->total_price, 2) }}</span></p>
                        <p><strong>Deposit Expected:</strong> R{{ number_format($purchase->deposit_expected, 2) }}</p>
                        <p><strong>Deposit Paid:</strong> R{{ number_format($purchase->deposit_paid, 2) }}</p>
                    </div>
                </div>

                <hr>


                {{-- <div class="col-md-6">
                    <h5 class="text-uppercase text-muted mb-3">Dates</h5>
                    <p><strong>Paid At:</strong> {{ $purchase->paid_at ? \Carbon\Carbon::parse($purchase->paid_at)->format('d M Y H:i') : '-' }}</p>
                </div> --}}




                {{-- Optional: Payment Details --}}
                {{-- @if ($purchase->payment_details)
            <hr>
            <div class="row mb-4">
                <div class="col-12">
                    <h5 class="text-uppercase text-muted mb-3">Payment Details</h5>
                    <pre>{{ json_encode($purchase->payment_details, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
            @endif --}}
            </div>
        </div>
    </div>

    <style>
        .card-body h5 {
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        table.table th,
        table.table td {
            vertical-align: middle;
            font-size: 0.9rem;
        }
    </style>
@stop
