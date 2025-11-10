@extends('adminlte::page')

@section('title', 'Categories')

@section('content_header')
    <h1 class="container text-bold">Categories</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex w-100 align-items-center">
                <h3 class="card-title mb-0">All Categories</h3>
                <div class="ml-auto">
                    <a href="{{ route('categories.create') }}" class="btn btn-dark btn-sm">
                        <i class="fas fa-plus"></i> Add New
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body p-2">
            <div class="table-responsive">
                <table id="categoriesTable" class="table table-striped mb-0 w-100">
                    <thead>
                        <tr>
                            <th style="width: 80px;">Image</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Daily</th>
                            <th>Weekly</th>
                            <th>Monthly</th>
                            <th>For Sale</th>
                            <th>Total items</th>
                            <th style="width: 220px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>
                                    @if ($category->image)
                                        @if (str_contains($category->image, 'fa-'))
                                            <i class="{{ $category->image }} fa-lg"></i>
                                        @else
                                            <img src="{{ asset('storage/' . $category->image) }}"
                                                alt="{{ $category->name }}" width="50">
                                        @endif
                                    @endif
                                </td>
                                <td>{{ $category->name }}</td>
                                <td>
                                    <span class="badge badge-{{ $category->status === 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($category->status) }}
                                    </span>
                                </td>
                                <td>R{{ $category->daily_price ?? '-' }}</td>
                                <td>R{{ $category->weekly_price ?? '-' }}</td>
                                <td>R{{ $category->monthly_price ?? '-' }}</td>
                                <td>{{ $category->is_for_sale ? 'Yes' : 'No' }}</td>
                                <td>{{ $category->equipment_count }}</td>
                                <td class="d-flex align-items-center">
                                    <button type="button" class="btn btn-outline-info btn-sm action-btn ml-1 add-equipment-btn"
                                        data-toggle="modal" data-target="#addEquipmentModal"
                                        data-category-id="{{ $category->id }}">
                                        Add Item <i class="fas fa-plus"></i>
                                    </button>

                                    <a href="{{ route('categories.edit', $category) }}"
                                        class="btn btn-outline-primary btn-sm action-btn ml-1" title="edit">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <form action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-outline-danger btn-sm action-btn ml-1 delete-btn"
                                            title="delete"
                                            {{ $category->equipment->count() > 0 ? 'disabled title=Cannot delete category with items' : '' }}>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td></td> {{-- Image --}}
                                <td></td> {{-- Name --}}
                                <td></td> {{-- Status --}}
                                <td></td> {{-- Daily --}}
                                <td></td> {{-- Weekly --}}
                                <td></td> {{-- Monthly --}}
                                <td></td> {{-- For Sale --}}
                                <td></td> {{-- Total items --}}
                                <td class="text-center">No categories yet.</td> {{-- Actions --}}
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Equipment Modal -->
        <div class="modal fade" id="addEquipmentModal" tabindex="-1" aria-labelledby="addEquipmentModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form action="{{ route('categories.storeEquipmentFromModal') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="addEquipmentModalLabel">Add Equipment</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>

                        <div class="modal-body">
                            <div class="form-group">
                                <label>Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label>Image</label>
                                <input type="file" name="image_file" class="form-control-file">
                            </div>

                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control"></textarea>
                            </div>

                            <div class="form-group">
                                <label>Category <span class="text-danger">*</span></label>
                                <select id="equipmentCategorySelect" name="category_id" class="form-control" required>
                                    <option value="">Select Category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>

                            <hr>
                            <h5 class="mb-3">Stock per Location</h5>
                            <div class="form-row">
                                @foreach ($locations as $location)
                                    <div class="form-group col-md-6">
                                        <label>{{ $location->name }}</label>
                                        <input type="number" name="stocks[{{ $location->id }}]" class="form-control" min="0" value="0">
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-dark">Save Equipment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<style>
    #categoriesTable td,
    #categoriesTable th {
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
    // Initialize DataTable
    if ($('#categoriesTable tbody tr').length) {
        $('#categoriesTable').DataTable({
            "columnDefs": [
                { "orderable": false, "targets": 8 } // Actions column
            ],
            "pageLength": 10
        });
    }

    // SweetAlert success/error messages
    @if (session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '{{ session('success') }}',
            timer: 2500,
            showConfirmButton: false
        });
    @endif

    @if (session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '{{ session('error') }}',
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
            text: "This category will be deleted permanently.",
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

    // Auto-select category in modal when Add Item is clicked
    $('.add-equipment-btn').on('click', function() {
        const categoryId = $(this).data('category-id');
        $('#equipmentCategorySelect').val(categoryId);
    });
});
</script>
@stop
