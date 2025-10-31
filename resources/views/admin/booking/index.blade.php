{{-- resources/views/admin/booking/index.blade.php --}}
@extends('adminlte::page')

@section('title', 'All Bookings')

@section('content_header')
<h1 class="text-bold container">All Bookings</h1>
@stop

@section('content')
<div class="container-fluid">

    <div class="card shadow-sm border-0 rounded-4 h-100vh">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0 text-bold">All Bookings</h3>
        </div>

        <hr>

        <div class="card-body">
            <div class="table-responsive">
                <table id="bookingsTable" class="table table-striped table-hover align-middle text-sm w-100">
                    <thead class="table-light text-uppercase text-muted">
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Category</th>
                            <th>Equipment</th>
                            <th>Location</th>
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
                            <td>
                                {{ $booking->customer->name ?? '-' }}<br>
                                <small class="text-muted">{{ $booking->customer->email ?? '-' }}</small>
                            </td>
                            <td>{{ $booking->category->name ?? '-' }}</td>
                            <td>{{ $booking->equipment->name ?? '-' }}</td>
                            <td>{{ $booking->location->name ?? '-' }}</td>
                            <td>{{ $booking->reference ?? '-' }}</td>
                            <td data-order="{{ \Carbon\Carbon::parse($booking->start_date)->format('Ymd') }}">
                                {{ \Carbon\Carbon::parse($booking->start_date)->format('d M Y') }}
                            </td>
                            <td data-order="{{ \Carbon\Carbon::parse($booking->end_date)->format('Ymd') }}">
                                {{ \Carbon\Carbon::parse($booking->end_date)->format('d M Y') }}
                            </td>
                            <td>
                                @php
                                    $statusMap = [
                                        'confirmed' => ['label' => 'Confirmed', 'class' => 'badge bg-info py-1'],
                                        'pending' => ['label' => 'Pending', 'class' => 'badge bg-warning py-1 text-dark'],
                                        'canceled' => ['label' => 'Canceled', 'class' => 'badge bg-danger py-1'],
                                        'completed' => ['label' => 'Completed', 'class' => 'badge bg-success py-1'],
                                        'ongoing' => ['label' => 'Ongoing', 'class' => 'badge bg-primary py-1 text-white'],
                                    ];
                                    $statusData = $statusMap[$booking->status] ?? ['label' => ucfirst($booking->status), 'class' => 'badge bg-secondary py-1'];
                                @endphp
                                <span class="{{ $statusData['class'] }}">{{ $statusData['label'] }}</span>
                            </td>
                            <td>${{ number_format($booking->total_price, 2) }}</td>
                            <td class="text-center">
                                <a href="{{ route('bookings.show', $booking->id) }}" class="btn btn-outline-info btn-sm action-btn" title="View Details">
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
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<!-- DataTables Buttons -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    var table = $('#bookingsTable').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        order: [[6, 'asc']], // order by start_date
        columnDefs: [
            { orderable: false, targets: [10] },
            { searchable: false, targets: [10] }
        ],
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                title: 'Bookings Export',
                text: '<i class="fas fa-file-excel me-2 text-success"></i> Export to Excel',
                filename: 'bookings_export',
                exportOptions: {
                    columns: [0,1,2,3,4,5,6,7,8,9]
                }
            }
        ],
        initComplete: function() {
            var excelBtn = table.buttons().container().find('button');
            excelBtn.removeClass().addClass('dropdown-item d-flex align-items-center gap-2');
            $('#exportBtnContainer').html(excelBtn);
        }
    });

    // Remove default DataTables buttons
    $('.dt-buttons').hide();

    // SweetAlert flash messages
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
