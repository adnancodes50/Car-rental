{{-- resources/views/admin/booking/show.blade.php --}}
@extends('adminlte::page')

@section('title', 'Booking Details')

@section('content')
<div class="container-fluid">
    {{-- Header: Booking Details + Back Button --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 text-bold mt-3">Booking Details</h1>
        <a href="{{ route('bookings.index') }}" class="btn btn-secondary btn-sm mt-3">
            <i class="fas fa-arrow-left py-2"></i> Back
        </a>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body">
            {{-- Customer & Vehicle Info --}}
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5 class="text-uppercase text-muted mb-3">Customer Information</h5>
                    <p><strong>Name:</strong> {{ $booking->customer->name ?? '-' }}</p>
                    <p><strong>Email:</strong> {{ $booking->customer->email ?? '-' }}</p>
                    <p><strong>Phone:</strong> {{ $booking->customer->phone ?? '-' }}</p>
                </div>

                <div class="col-md-6">
                    <h5 class="text-uppercase text-muted mb-3">Vehicle & Booking Info</h5>
                    <p><strong>Vehicle:</strong> {{ $booking->vehicle->name ?? '-' }}</p>
                    <p><strong>Type:</strong> {{ ucfirst($booking->type) }}</p>
                    <p><strong>Status:</strong>
                        @if($booking->status === 'confirmed')
                            <span class="badge bg-success">Confirmed</span>
                        @elseif($booking->status === 'pending')
                            <span class="badge bg-warning text-dark">Pending</span>
                        @elseif($booking->status === 'cancelled')
                            <span class="badge bg-danger">Cancelled</span>
                        @endif
                    </p>
                </div>
            </div>

            <hr>

            {{-- Dates & Pricing --}}
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5 class="text-uppercase text-muted mb-3">Dates</h5>
                    <p><strong>Start Date:</strong> {{ \Carbon\Carbon::parse($booking->start_date)->format('d M Y') }}</p>
                    <p><strong>End Date:</strong> {{ \Carbon\Carbon::parse($booking->end_date)->format('d M Y') }}</p>
                </div>

                <div class="col-md-6">
                    <h5 class="text-uppercase text-muted mb-3">Pricing</h5>
                    <p><strong>Total Price:</strong> <span class="text-success">${{ number_format($booking->total_price, 2) }}</span></p>
                    <p><strong>Extra Days:</strong> {{ $booking->extra_days ?? 0 }}</p>
                </div>
            </div>

            {{-- Add-Ons --}}
            @if($booking->addOns->count() > 0)
            <hr>
            <h5 class="text-uppercase text-muted mb-3">Add-Ons</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle text-sm">
                    <thead class="table-light text-uppercase text-muted">
                        <tr>
                            <th>#</th>
                            <th>Add-On Name</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($booking->addOns as $index => $addOn)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $addOn->name }}</td>
                            <td>{{ $addOn->pivot->qty }}</td>
                            <td>${{ number_format($addOn->pivot->price_total / $addOn->pivot->qty, 2) }}</td>
                            <td>${{ number_format($addOn->pivot->price_total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="text-end fw-bold">
                Total Add-Ons: ${{ number_format($booking->addOns->sum(fn($a) => $a->pivot->price_total), 2) }}
            </p>
            @endif

            {{-- Notes
            @if($booking->notes)
            <hr>
            <h5 class="text-uppercase text-muted mb-2">Notes</h5>
            <div class="p-3 bg-light rounded">
                {{ $booking->notes }}
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

    table.table th, table.table td {
        vertical-align: middle;
        font-size: 0.9rem;
    }
</style>
@stop
