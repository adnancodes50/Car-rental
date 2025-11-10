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
                {{-- Responsive DataTable --}}
                <div class="table-responsive">
                    @php
                        use Carbon\Carbon;
                        $today = Carbon::today();
                    @endphp

                    <table id="customersTable" class="table table-striped table-hover align-middle text-sm w-100 nowrap">
                        <thead class="table-light text-uppercase text-muted">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Customer Address</th>
                                <th style="min-width: 180px;">Action</th>
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
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="{{ route('customers.details', $customer->id) }}"
                                               class="btn btn-outline-success btn-sm action-btn ml-1 fw-semibold">
                                                <i class="bi bi-person-lines-fill"></i>
                                                View Profile
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

        /* Keep collapse button simple */
        table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child:before,
        table.dataTable.dtr-inline.collapsed > tbody > tr > th:first-child:before {
            background-color: transparent !important;
            border: 2px solid #888 !important;
            color: #333 !important;
            box-shadow: none !important;
        }

        /* Optional: tighten up mobile buttons */
        @media (max-width: 768px) {
            .btn {
                width: 100%;
                margin-bottom: 5px;
            }
        }
    </style>
@stop

@section('css')
    {{-- DataTables + Responsive extension --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#customersTable').DataTable({
                responsive: {
                    details: {
                        display: $.fn.dataTable.Responsive.display.childRowImmediate,
                        type: 'inline'
                    }
                },
                autoWidth: false,
                pageLength: 10,
                ordering: false,
                columnDefs: [
                    { orderable: false, targets: [4] }
                ],
                language: {
                    lengthMenu: "_MENU_ per page",
                    search: "Search:",
                    paginate: {
                        previous: "&laquo;",
                        next: "&raquo;"
                    }
                }
            });
        });
    </script>
@stop
