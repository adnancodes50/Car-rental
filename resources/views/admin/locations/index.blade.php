@extends('adminlte::page')

@section('title', 'Locations')

@section('content_header')
    <h1 class="container text-bold">Locations</h1>
@stop

@section('content')
    <div class="container-fluid">

        {{-- Remove old Bootstrap alert and replace with SweetAlert --}}

        <div class="card">
            {{-- Header with button aligned to the right --}}
            <div class="card-header">
                <div class="d-flex w-100 align-items-center">
                    <h3 class="card-title mb-0">All Locations</h3>
                    <div class="ml-auto">
                        <a href="{{ route('locations.create') }}" class="btn btn-dark btn-sm">
                            <i class="fas fa-plus"></i> Add Location
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body p-2">
                <div class="table-responsive">
                    <table id="locationsTable" class="table table-striped mb-0 w-100">
                        <thead>
                            <tr>
                                <th style="width: 60px;">ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($locations as $loc)
                                <tr>
                                    <td>{{ $loc->id }}</td>
                                    <td>{{ $loc->name }}</td>
                                    <td>{{ $loc->email }}</td>
                                    <td>{{ $loc->phone }}</td>
                                    <td>
                                        @if ($loc->status === 'active')
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('locations.edit', $loc) }}" class="btn btn-outline-primary btn-sm action-btn ml-1">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('locations.view', $loc->id) }}" class="btn btn-outline-info btn-sm action-btn ml-1" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <form action="{{ route('locations.destroy', $loc) }}" method="POST"
                                            class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-outline-danger btn-sm action-btn ml-1 btn-delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center p-4">No locations yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop


@section('css')
    <style>
        #locationsTable td,
        #locationsTable th {
            padding: 12px 15px;
            vertical-align: middle;
        }
    </style>
@stop


@section('js')
    {{-- Include SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Include DataTables --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#locationsTable').DataTable({
                "columnDefs": [
                    { "orderable": false, "targets": 5 } // Disable sorting on Actions column
                ],
                "pageLength": 10
            });

            // Delete confirmation with SweetAlert
            $('.btn-delete').on('click', function(e) {
                e.preventDefault();
                let form = $(this).closest('.delete-form');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This location will be permanently deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });

            // SweetAlert success messages for Add, Update, Delete
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: '{{ session('success') }}',
                    timer: 2500,
                    showConfirmButton: false
                });
            @endif
        });
    </script>
@stop
