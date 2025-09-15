@extends('adminlte::page')

@section('title', 'Vehicles Management')

@section('content_header')
<h1 class="fw-bold">Vehicles</h1>
@if ($errors->any())
    <div class="alert alert-danger rounded-3">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
@stop

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <p class="mb-0 fw-semibold">
                Manage your vehicles below. You can add, edit, or remove vehicles from the system.
            </p>
            <div class="d-flex gap-2">
                <a href="{{ url('/') }}" target="_blank" class="btn btn-secondary btn-sm">
                    <i class="fas fa-globe"></i>
                </a>
                <a href="{{ route('vehicles.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i>
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle text-sm border rounded-3">
                    <thead class="table-light text-uppercase text-muted">
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Model</th>
                            <th>Year</th>
                            <th>Type</th>
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
                                        class="img-thumbnail rounded" style="width:50px; height:50px; object-fit:cover;">
                                </td>
                                <td class="fw-semibold">{{ $vehicle->name }}</td>
                                <td>{{ $vehicle->model ?? '-' }}</td>
                                <td>{{ $vehicle->year ?? '-' }}</td>
                                <td>{{ $vehicle->type ?? '-' }}</td>
                                <td>
                                    <span class="badge
                                        @if($vehicle->status === 'available') bg-success
                                        @elseif($vehicle->status === 'rented') bg-warning
                                        @elseif($vehicle->status === 'maintenance') bg-info
                                        @else bg-danger @endif">
                                        {{ ucfirst($vehicle->status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <!-- View -->
                                        <a href="{{ route('vehicles.show', $vehicle->id) }}" class="text-info" title="View">
                                            <i class="fas fa-eye" style="font-size:18px;"></i>
                                        </a>
                                        <!-- Edit -->
                                        <a href="{{ route('vehicles.edit', $vehicle->id) }}" class="text-warning" title="Edit">
                                            <i class="fas fa-edit" style="font-size:18px;"></i>
                                        </a>
                                        <!-- Delete -->
                                        <form action="{{ route('vehicles.destroy', $vehicle->id) }}" method="POST" class="d-inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-danger border-0 bg-transparent p-0 m-0"
                                                onclick="return confirm('Are you sure you want to delete this vehicle?');" title="Delete">
                                                <i class="fas fa-trash-alt" style="font-size:18px;"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-3">No vehicles found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer">
            {{ $vehicles->links() }} {{-- Laravel pagination --}}
        </div>
    </div>
</div>

<style>
    /* Hover effect for rows */
    table.table-hover tbody tr:hover {
        background-color: rgba(255, 193, 7, 0.1); /* subtle yellow */
        transition: background-color 0.2s ease-in-out;
    }

    /* Optional striped effect */
    table.table-striped tbody tr:nth-of-type(odd) {
        background-color: #f8f9fa;
    }
</style>
@stop

@section('js')
<script>
    console.log("Vehicles Management page loaded!");
</script>
@stop
