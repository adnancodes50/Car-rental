@extends('adminlte::page')

@section('title', 'Equipment')

@section('content_header')
    <h1 class="container text-bold">Equipment</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex w-100 align-items-center">
                <h3 class="card-title mb-0">All Equipment</h3>
            </div>
        </div>

        <div class="card-body p-2">
            <div class="table-responsive">
                <table id="equipmentTable" class="table table-striped mb-0 w-100">
                    <thead>
                        <tr>
                            <th style="width: 80px;">Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            @foreach ($locations as $location)
                                <th>{{ $location->name }} Stock</th>
                            @endforeach
                            <th>Total Stock</th>
                            <th>Status</th>
                            <th style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($equipment as $item)
                            <tr>
                                <td>
                                    @if ($item->image)
                                        <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" width="50">
                                    @endif
                                </td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->category->name ?? '-' }}</td>

                                {{-- Stock per location --}}
                                @foreach ($locations as $location)
                                    @php
                                        $stockRecord = $item->stocks->firstWhere('location_id', $location->id);
                                    @endphp
                                    <td>{{ $stockRecord ? $stockRecord->stock : 0 }}</td>
                                @endforeach

                                {{-- Total stock --}}
                                <td>{{ $item->stocks->sum('stock') }}</td>

                                <td>
                                    @if ($item->status === 'active')
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-secondary">Inactive</span>
                                    @endif
                                </td>

                                <td>
                                    <a href="{{ route('equipment.edit', $item) }}" class="btn btn-outline-primary btn-sm action-btn ml-1">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('equipment.destroy', $item) }}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-outline-danger btn-sm action-btn ml-1 delete-btn">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td></td> {{-- Image --}}
                                <td></td> {{-- Name --}}
                                <td></td> {{-- Category --}}
                                @foreach ($locations as $location)
                                    <td></td> {{-- Stock per location --}}
                                @endforeach
                                <td></td> {{-- Total Stock --}}
                                <td></td> {{-- Status --}}
                                <td class="text-center">No equipment found.</td> {{-- Actions --}}
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
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<style>
    #equipmentTable td,
    #equipmentTable th {
        padding: 12px 15px;
        vertical-align: middle;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable only if the table exists
    if ($('#equipmentTable tbody tr').length) {
        $('#equipmentTable').DataTable({
            "columnDefs": [
                { "orderable": false, "targets": {{ 3 + $locations->count() + 2 }} } // Actions column
            ],
            "pageLength": 10
        });
    }

    // SweetAlert success message
    @if (session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '{{ session('success') }}',
            timer: 2500,
            showConfirmButton: false
        });
    @endif

    // Delete confirmation
    $('.delete-btn').on('click', function(e) {
        e.preventDefault();
        let form = $(this).closest('form');

        Swal.fire({
            title: 'Are you sure?',
            text: "This equipment will be deleted permanently.",
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
});
</script>
@stop
