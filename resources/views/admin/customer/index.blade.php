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
                                <th>Customer Address</th>
                                <th style="min-width: 240px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($customers as $customer)

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
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

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
