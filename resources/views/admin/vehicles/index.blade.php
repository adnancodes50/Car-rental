@extends('adminlte::page')

@section('title', 'Vehicles Management')

@section('content_header')
    <h1 class="text-bold container">Vehicles</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm border-0 rounded-4">

            <div class="card-header bg-white">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="card-title mb-0 text-bold">Vehicle Table</div>
                    </div>
                    <div class="col-auto text-end">
                        <a href="{{ url('/') }}" target="_blank" class="btn btn-outline-secondary btn-sm me-2">
                            <i class="fas fa-globe me-1"></i> Site
                        </a>
                        <a href="{{ route('vehicles.create') }}" class="btn btn-dark btn-sm">
                            <i class="fas fa-plus me-1"></i> Add
                        </a>
                    </div>
                </div>
            </div>



            <!-- Table -->
            <div class="card-body">
                <div class="table-responsive">
                    <table id="vehiclesTable" class="table table-striped table-hover align-middle text-sm w-100">
                        <thead class="table-light text-uppercase text-muted">
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Model</th>
                                <th>Year</th>
                                <th>Type</th>
                                <th>For Sale</th>
                                <th>Status</th>
                                <th class="text-center" style="width:120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vehicles as $vehicle)
                                <tr>
                                    <td>{{ $vehicle->id }}</td>
                                    <td>
                                        <img src="{{ asset($vehicle->mainImage()) }}" alt="{{ $vehicle->name }}"
                                            class="img-thumbnail rounded"
                                            style="width:50px; height:50px; object-fit:cover;">
                                    </td>
                                    <td class="fw-semibold">{{ $vehicle->name }}</td>
                                    <td>{{ $vehicle->model ?? '-' }}</td>
                                    <td>{{ $vehicle->year ?? '-' }}</td>
                                    <td>{{ $vehicle->type ?? '-' }}</td>
                                    <td>
                                        @if ($vehicle->is_for_sale)
                                            <span class="badge bg-success">Yes</span>
                                        @else
                                            <span class="badge bg-dark">No</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span
                                            class="badge
                            @if ($vehicle->status === 'available') bg-success
                            @elseif($vehicle->status === 'rented') bg-warning
                            @elseif($vehicle->status === 'maintenance') bg-info
                            @else bg-danger @endif">
                                            {{ ucfirst($vehicle->status) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <!-- Actions -->
                                            <a href="{{ route('vehicles.show', $vehicle->id) }}"
                                                class="btn btn-outline-info btn-sm action-btn mr-1" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('vehicles.edit', $vehicle->id) }}"
                                                class="btn btn-outline-warning btn-sm action-btn mr-1" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('vehicles.destroy', $vehicle->id) }}" method="POST"
                                                class="delete-form d-inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm action-btn"
                                                    title="Delete">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                            @endforelse
                        </tbody>

                        @if ($vehicles->isEmpty())
                        @endif


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

        /* Base action buttons */
        .action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 6px;
            padding: 0;
        }

        /* Hover effects */
        .action-btn:hover {
            background-color: #fff !important;
        }

        /* Specific hover colors */
        .btn-outline-info:hover i {
            color: #0dcaf0;
            /* Bootstrap info color */
        }

        .btn-outline-warning:hover i {
            color: #ffc107;
            /* Bootstrap warning color */
        }

        .btn-outline-danger:hover i {
            color: #dc3545;
            /* Bootstrap danger color */
        }

        /* Default icon size & color */
        .action-btn i {
            font-size: 16px;
        }
    </style>
@stop

@section('css')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
@stop

@section('js')
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables + Responsive + Buttons -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Init DataTable
            $('#vehiclesTable').DataTable({
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                order: [
                    [0, 'desc']
                ], // order by ID
                columnDefs: [{
                        orderable: false,
                        targets: [1, 8]
                    },
                    {
                        searchable: false,
                        targets: [1, 8]
                    },
                    {
                        targets: 2,
                        responsivePriority: 1
                    }, // Name
                    {
                        targets: 8,
                        responsivePriority: 2
                    } // Actions
                ],


            });

            // Show success/error alerts
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: @json(session('success')),
                    confirmButtonText: 'OK'
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: @json(session('error')),
                    confirmButtonText: 'OK'
                });
            @endif

            // SweetAlert delete confirmation
            $(document).on('submit', 'form.delete-form', function(e) {
                e.preventDefault();
                let form = this;
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This will permanently delete this vehicle.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@stop
