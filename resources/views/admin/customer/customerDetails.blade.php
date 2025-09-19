@extends('adminlte::page')

@section('title', 'Customers')

@section('content_header')
<h1 class=" container text-bold">Customers Detail</h1>

<hr>
@stop



@section('content')
<header>
    <div class="container d-flex justify-content-between align-items-center">
        <!-- Back Button -->
        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Customers
        </a>

        <!-- Delete Form -->
        <form action="{{ route('customers.destroy', $customer->id) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash-alt"></i> Delete Customer
            </button>
        </form>
    </div>
</header>

<body>
    <div class="container mt-4">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white border-0">
                <h3 class="card-title text-bold">Customer Details</h3>
            </div>
            <div class="card-body">
                <div class="row g-4 border py-3 bg-light">
                    <!-- Left Column: Customer Info -->
                    <div class="col-md-6">
                        <div class="p-3  rounded-3">
                            <p><strong>Name:</strong> {{ $customer->name }}</p>
                            <p><strong>Email:</strong> {{ $customer->email ?? 'N/A' }}</p>
                            <p><strong>Phone:</strong> {{ $customer->phone ?? 'N/A' }}</p>
                            <p><strong>Country:</strong> {{ $customer->country ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- Right Column: Stats -->
                    <div class="col-md-6">
                        <div class="row g-3">
                            <div class="col-6 ">
                                <div class="p-3 text-center  mb-2 text-white rounded" style="background-color: #6dce12">
                                    <h6 class="mb-1">Bookings</h6>
                                    <h4 class="fw-bold">{{ $customer->bookings_count ?? 0 }}</h4>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 text-center text-white rounded" style="background-color: #1f2eb4">
                                    <h6 class="mb-1">Total Paid (Bookings)</h6>
                                    <h4 class="fw-bold">
                                        R{{ number_format($customer->total_booking_price ?? 0, 2) }}
                                    </h4>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="p-3 text-center bg-warning text-dark rounded">
                                    <h6 class="mb-1">Total Deposits</h6>
                                    <h4 class="fw-bold">
                                        R{{ number_format(($customer->total_purchase_deposit ?? 0), 2) }}
                                    </h4>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 text-center bg-success text-white rounded">
                                    <h6 class="mb-1">Total Payable (Purchases)</h6>
                                    <h4 class="fw-bold">
                                        R{{ number_format($customer->total_purchase_price ?? 0, 2) }}
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white border-0">
                <h3 class="card-title text-bold">Booking History</h3>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    @forelse($bookings as $booking)
                        <div class="col-md-12">
                            <div class="p-3 border rounded-3 bg-white shadow-sm h-100">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <!-- Vehicle Name -->
                                    <h6 class="text-bold text-black mb-0">
                                        {{ $booking->vehicle->name ?? 'N/A' }}
                                    </h6>

                                    <!-- Status Badge -->
                                    <span class="badge
                                    @if($booking->status == 'completed') bg-success
                                    @elseif($booking->status == 'pending') bg-warning text-dark
                                    @elseif($booking->status == 'canceled') bg-danger
                                    @else bg-info
                                    @endif
                                ">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </div>

                                <!-- Booking Info -->
                                <p class="mb-1 text-muted"><strong>Start Date:</strong> {{ $booking->start_date }}</p>
                                <p class="mb-1 text-muted"><strong>End Date:</strong> {{ $booking->end_date }}</p>
                                <p class="mb-1 text-muted"><strong>Booked On:</strong>
                                    {{ $booking->created_at->format('Y-m-d') }}</p>
                                <p class="mb-0 text-muted"><strong>Total Price:</strong>
                                    R{{ number_format($booking->total_price, 2) }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center text-muted">
                            No booking history available.
                        </div>
                    @endforelse

                </div>
            </div>
        </div>
    </div>




    <div class="container mt-4">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white border-0">
                <h3 class="card-title text-bold">Purchase History</h3>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    @forelse($purchases as $purchase)
                        <div class="col-md-12">
                            <div class="p-3 border rounded-3 bg-white shadow-sm h-100">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <!-- Vehicle Name -->
                                    <h6 class=" text-bold text-black mb-0">
                                        {{ $purchase->vehicle->name ?? 'N/A' }}
                                    </h6>

                                    <!-- Always Purchase Badge -->
                                    <span class="badge bg-primary">Purchase</span>
                                </div>

                                <!-- Purchase Info -->
                                <p class="mb-1 text-muted"><strong>Purchased On:</strong>
                                    {{ $purchase->created_at->format('Y-m-d') }}</p>
                                <p class="mb-1 text-muted"><strong>Total Price:</strong>
                                    R{{ number_format($purchase->total_price, 2) }}</p>
                                <p class="mb-1 text-muted"><strong>Deposit Paid:</strong>
                                    R{{ number_format($purchase->deposit_paid ?? 0, 2) }}</p>
                                <p class="mb-0 text-muted"><strong>Payment Method:</strong>
                                    {{ ucfirst($purchase->payment_method ?? 'N/A') }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center text-muted">
                            No purchase history available.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>


</body>


@stop
