{{-- resources/views/admin/booking/index.blade.php --}}
@extends('adminlte::page')

@section('title', 'All Bookings')

@section('content_header')
<h1 class="text-bold container">All Bookings</h1>
@stop

@section('content')
<div class="container-fluid">

    <div class="card shadow-sm border-0 rounded-4 h-100vh">
        <h2 class="text-center mb-0 mt-4">Bookings</h2>

        <div class="card-body">
            <div class="table-responsive">
                <table id="bookingsTable" class="table table-striped table-hover align-middle text-sm w-100">
                    <thead class="table-light text-uppercase text-muted">
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Vehicle</th>
                            <th>Reference</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Total Price</th>
                            <th class="text-center" style="width:80px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookings as $booking)
                        <tr>
                            <td>{{ $booking->id }}</td>
                            <td>{{ $booking->customer->name ?? '-' }} ({{ $booking->customer->email ?? '-' }})</td>
                            <td>{{ $booking->vehicle->name ?? '-' }}</td>
                            <td>{{$booking->vehicle->reference}}</td>
                            <td>{{ \Carbon\Carbon::parse($booking->start_date)->format('d M Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($booking->end_date)->format('d M Y') }}</td>
                            <td>
                                @if($booking->status === 'confirmed')
                                    <span class="badge bg-success py-1">Confirmed</span>
                                @elseif($booking->status === 'pending')
                                    <span class="badge bg-warning py-1">Pending</span>
                                @elseif($booking->status === 'cancelled')
                                    <span class="badge bg-danger py-1">Cancelled</span>
                                    @elseif($booking->status === 'completed')
                                    <span class="badge bg-success py-1">Completed</span>
                                @endif
                            </td>
                            <td>${{ number_format($booking->total_price, 2) }}</td>
                            <td class="text-center">
                                <a href="{{ route('bookings.show', $booking->id) }}" class="btn btn-outline-info btn-sm">
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
    .action-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 6px;
        padding: 0;
    }
    .action-btn i {
        font-size: 16px;
    }
</style>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    $('#bookingsTable').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        order: [[0, 'desc']],
        columnDefs: [
            { orderable: false, targets: [7] },
            { searchable: false, targets: [7] }
        ]
    });

    // Flash messages with SweetAlert
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: @json(session('success')),
            confirmButtonColor: '#198754'
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: @json(session('error')),
            confirmButtonColor: '#dc3545'
        });
    @endif
});
</script>
@stop
