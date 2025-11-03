@extends('adminlte::page')

@section('title', 'Customers')

@section('content_header')
    <h1 class="text-bold container">Customers</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0 text-bold">All Customers</h3>
            </div>

            <hr>

            <div class="card-body">
                <div class="table-responsive">
                    @php
                        use Carbon\Carbon;
                        $today = Carbon::today();
                        // We'll collect active bookings per customer so we can render collapses after the table
                        $activeMap = [];
                    @endphp

                    <table id="customersTable" class="table table-striped table-hover align-middle text-sm w-100">
                        <thead class="table-light text-uppercase text-muted">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Country</th>
                                <th style="min-width: 240px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($customers as $customer)
                                @php
                                    $activeBookings = $customer->bookings()
                                        ->whereDate('start_date', '<=', $today)
                                        ->whereDate('end_date', '>=', $today)
                                        ->with(['equipment', 'location', 'category'])
                                        ->get();

                                    // stash for later (to render outside the table)
                                    $activeMap[$customer->id] = $activeBookings;
                                @endphp

                                <tr>
                                    <td>{{ $customer->name }}</td>
                                    <td>{{ $customer->email }}</td>
                                    <td>{{ $customer->phone }}</td>
                                    <td>{{ $customer->country }}</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            {{-- Always go to details page --}}
                                            <a href="{{ route('customers.details', $customer->id) }}"
                                               class="btn btn-success btn-sm fw-semibold">
                                                <i class="bi bi-person-lines-fill"></i>
                                                View Details
                                            </a>

                                            {{-- Collapse toggle for active bookings --}}
                                            @if ($activeBookings->isNotEmpty())
                                                <button
                                                    class="btn btn-outline-secondary btn-sm fw-semibold"
                                                    type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#bookings-{{ $customer->id }}"
                                                    aria-expanded="false"
                                                    aria-controls="bookings-{{ $customer->id }}">
                                                    <i class="bi bi-calendar-event"></i>
                                                    Active Bookings ({{ $activeBookings->count() }})
                                                </button>
                                            @else
                                                <button class="btn btn-secondary btn-sm fw-semibold" type="button" disabled>
                                                    <i class="bi bi-calendar-x"></i>
                                                    No Active Bookings
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- Collapsible sections rendered OUTSIDE the table so DataTables won't touch them --}}
                    @foreach ($customers as $customer)
                        @php
                            $activeBookings = $activeMap[$customer->id] ?? collect();
                        @endphp

                        <div id="bookings-{{ $customer->id }}" class="collapse booking-details-row mt-2">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    @if ($activeBookings->isEmpty())
                                        <div class="text-muted small">No active bookings for this customer.</div>
                                    @else
                                        <div class="table-responsive border rounded-3 p-2 bg-light">
                                            <table class="table table-sm mb-0 align-middle">
                                                <thead class="table-secondary">
                                                    <tr>
                                                        <th>Reference</th>
                                                        <th>Equipment</th>
                                                        <th>Location</th>
                                                        <th>Category</th>
                                                        <th>Start</th>
                                                        <th>End</th>
                                                        <th>Go to Details</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($activeBookings as $booking)
                                                        <tr>
                                                            <td>{{ $booking->reference ?? 'N/A' }}</td>
                                                            <td>{{ $booking->equipment->name ?? 'N/A' }}</td>
                                                            <td>{{ $booking->location->name ?? 'N/A' }}</td>
                                                            <td>{{ $booking->category->name ?? 'N/A' }}</td>
                                                            <td>{{ \Carbon\Carbon::parse($booking->start_date)->format('Y-m-d') }}</td>
                                                            <td>{{ \Carbon\Carbon::parse($booking->end_date)->format('Y-m-d') }}</td>
                                                            <td>
                                                                <a href="{{ route('customers.details', $customer->id) }}"
                                                                   class="btn btn-outline-primary btn-sm fw-semibold">
                                                                    <i class="bi bi-box-arrow-up-right"></i>
                                                                    View Details
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                    {{-- /Collapsibles --}}
                </div>
            </div>
        </div>
    </div>

    <style>
        table.table-hover tbody tr:hover {
            background-color: rgba(255, 193, 7, 0.1);
            transition: background-color 0.2s ease-in-out;
        }

        /* Separate container for collapses; not inside the DataTable */
        .booking-details-row {
            background-color: #f9f9f9;
        }
    </style>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#customersTable').DataTable({
                responsive: true,
                pageLength: 10,
                ordering: false,
                columnDefs: [{ orderable: false, targets: [4] }],
            });
            // No need to touch the collapses: Bootstrap handles them, and they’re outside the table.
        });
    </script>
@stop
