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

            <!-- Right side: dropdown menu -->
            <div class="d-flex align-items-center gap-2">
                {{-- <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                        id="customersActions" data-bs-toggle="dropdown" aria-expanded="false">
                        Actions
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="customersActions">
                        <!-- Excel export button goes here -->
                        <li id="exportBtnContainer"></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#">Another action</a></li>
                    </ul>
                </div> --}}
            </div>
        </div>

        <hr>

        <div class="card-body">
            <div class="table-responsive">
                <table id="customersTable" class="table table-striped table-hover align-middle text-sm w-100">
                    <thead class="table-light text-uppercase text-muted">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Country</th>
                            <th>Active Booking</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $customer)
                            <tr>
                                <td>{{ $customer->name }}</td>
                                <td>{{ $customer->email }}</td>
                                <td>{{ $customer->phone }}</td>
                                <td>{{ $customer->country }}</td>
                                <td class="text-center align-middle">
                                    <a href="{{ route('customers.details', $customer->id) }}" class="text-decoration-none">
                                        @php $count = (int) $customer->activeBookingCount(); @endphp
                                        @if($count > 0)
                                            <span class="badge py-1 text-white"
                                                  style="background-color: rgb(18, 158, 151); font-size: 0.9rem;">
                                                <i class="fas fa-calendar-check me-1"></i>
                                                {{ $count }} booking{{ $count > 1 ? 's' : '' }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-calendar-alt me-1"></i> 0
                                            </span>
                                        @endif
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
</style>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<!-- DataTables Buttons & dependencies -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

<script>
$(document).ready(function () {
    var table = $('#customersTable').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        order: [[0, 'asc']],
        columnDefs: [
            { targets: 0, responsivePriority: 1 },
            { targets: 4, responsivePriority: 2 }
        ],
        language: {
            emptyTable: "No customers found."
        },
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                title: 'Customers Export',
                text: '<i class="fas fa-file-excel me-2 text-success"></i> Export to Excel',
                filename: 'customers_export',
                exportOptions: {
                    columns: [0,1,2,3,4]
                }
            }
        ],
        initComplete: function () {
            // Get the Excel button and make it look like a dropdown item
            var excelBtn = table.buttons().container().find('button');
            excelBtn.removeClass().addClass('dropdown-item d-flex align-items-center gap-2');

            // Move into dropdown container
            $('#exportBtnContainer').html(excelBtn);
        }
    });

    // Remove DataTables’ default button container
    $('.dt-buttons').hide();
});
</script>
@stop
