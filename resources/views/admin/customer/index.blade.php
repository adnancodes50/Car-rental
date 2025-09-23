@extends('adminlte::page')

@section('title', 'Customers')

@section('content_header')
<h1 class="text-bold container">Customers</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Card -->
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0 text-bold">All Customers</h3>
        </div>

        <hr>

        <div class="card-body">
            <!-- Responsive Table -->
            <div class="table-responsive">
                <table id="customersTable" class="table table-striped table-hover align-middle text-sm w-100">
                    <thead class="table-light text-uppercase text-muted">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Country</th>
                            <th>Active Booking</th>
                            <th class="text-center" style="width:80px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $customer)
                            <tr>
                                <td>{{ $customer->name }}</td>
                                <td>{{ $customer->email }}</td>
                                <td>{{ $customer->phone }}</td>
                                <td>{{ $customer->country }}</td>

                                <!-- Active Booking Column -->
                                <td class="text-center align-middle">
                                    @if($customer->activeBookingCount() > 0)
                                        <span class="badge py-1 text-white"
                                            style="background-color: rgb(18, 158, 151); font-size: 0.9rem;">
                                            <i class="fas fa-calendar-check me-1"></i>
                                            {{ $customer->activeBookingCount() }}
                                            booking{{ $customer->activeBookingCount() > 1 ? 's' : '' }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-calendar-alt me-1"></i> 0
                                        </span>
                                    @endif
                                </td>


                                <!-- Actions Column -->
                                <td class="text-center">
                                    <a href="{{ route('customers.details', $customer->id) }}"
                                        class="btn btn-outline-info btn-sm action-btn" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
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

    /* Action button styling */
    .action-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 6px;
        padding: 0;
    }

    .action-btn:hover {
        background-color: #fff !important;
    }

    .btn-outline-info:hover i {
        color: #0dcaf0;
    }

    .action-btn i {
        font-size: 16px;
    }

    /* Fix alignment issue */
    /* Keep vertical alignment consistent */


</style>
@stop

@section('css')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
@stop

@section('js')
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<script>
    $(document).ready(function () {
        $('#customersTable').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            order: [[0, 'asc']],
            columnDefs: [
                { orderable: false, targets: [5] },   // Disable ordering on Actions
                { searchable: false, targets: [5] },  // Disable search on Actions
                { targets: 0, responsivePriority: 1 },
                { targets: 4, responsivePriority: 2 } // Keep Active Booking high priority
            ],
        });
    });
</script>
@stop
