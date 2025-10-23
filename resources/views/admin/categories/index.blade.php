@extends('adminlte::page')

@section('title', 'Categories')

@section('content_header')
    <h1 class="container text-bold">Categories</h1>
@stop

@section('content')
    <div class="container-fluid">

        {{-- Success Message
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif --}}

        <div class="card">
            {{-- Card Header with Add Category Button --}}
            <div class="card-header">
                <div class="d-flex w-100 align-items-center">
                    <h3 class="card-title mb-0">All Categories</h3>
                    <div class="ml-auto">
                        <a href="{{ route('categories.create') }}" class="btn btn-dark btn-sm">
                            <i class="fas fa-plus"></i> Add Category
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0 w-100">
                        <thead>
                            <tr>
                                <th style="width: 80px;">Image</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th style="width: 150px;">Actions</th>
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
                                    <td>{{ $category->short_description }}</td>
                                    <td>
                                        @if ($category->status === 'active')
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('categories.destroy', $category) }}" method="POST"
                                            class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-danger delete-btn">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>

                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center p-4">No categories yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop



@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- ✅ Show success message --}}
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '{{ session('success') }}',
                confirmButtonColor: '#343a40',
            });
        </script>
    @endif

    {{-- ✅ Show error message --}}
    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '{{ session('error') }}',
                confirmButtonColor: '#343a40',
            });
        </script>
    @endif

    {{-- ✅ Always active delete confirmation --}}
    <script>
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                let form = this.closest('form');
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
        });
    </script>
@stop
