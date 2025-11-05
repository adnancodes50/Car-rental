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
                    <p><strong>Vehicle:</strong> {{ $booking->equipment->name ?? '-' }}</p>
                    <p><strong>Type:</strong> {{ ucfirst($booking->type) }}</p>
                    <p><strong>Status:</strong>
                        @php
                            $statusMap = [
                                'confirmed' => ['label' => 'Confirmed', 'class' => 'badge bg-success'],
                                'pending' => ['label' => 'Pending', 'class' => 'badge bg-warning text-dark'],
                                'canceled' => ['label' => 'Canceled', 'class' => 'badge bg-danger'],
                                'completed' => ['label' => 'Completed', 'class' => 'badge bg-success'],
                                'ongoing' => ['label' => 'Ongoing', 'class' => 'badge bg-info text-dark'],
                            ];
                            $statusData = $statusMap[$booking->status] ?? ['label' => ucfirst($booking->status ?? 'Unknown'), 'class' => 'badge bg-secondary'];
                        @endphp
                        <span class="{{ $statusData['class'] }}">{{ $statusData['label'] }}</span>
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
                    <p><strong>Total Price:</strong> <span class="text-success">R{{ number_format($booking->total_price, 2) }}</span></p>
                    <p><strong>Extra Days:</strong> {{ $booking->extra_days ?? 0 }}</p>
                </div>
            </div>
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
