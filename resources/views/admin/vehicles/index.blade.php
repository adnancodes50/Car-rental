@extends('adminlte::page')

@section('title', 'Vehicles Management')

@section('content_header')
<h1>Vehicles</h1>
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@stop

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <p class="mb-0">
            Manage your vehicles below. You can add, edit, or remove vehicles from the system.
        </p>
        <div class="d-flex">
            <a href="{{ url('/') }}" target="_blank" class="btn btn-secondary mr-2">
                <i class="fas fa-globe"></i> View Website
            </a>
            <a href="{{ route('vehicles.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Vehicle
            </a>
        </div>
    </div>

    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Model</th>
                    <th>Year</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th style="width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vehicles as $vehicle)
                    <tr>
                        <td>{{ $vehicle->id }}</td>
                        <td>
                            <img src="{{ asset($vehicle->mainImage()) }}" alt="{{ $vehicle->name }}" class="img-thumbnail"
                                style="width: 50px; height: 50px; object-fit: cover;">
                        </td>


                        <td>{{ $vehicle->name }}</td>
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
<td>
    <div class="d-flex">
        <!-- View Button -->
        <a href="{{ route('vehicles.show', $vehicle->id) }}"
           class="btn btn-sm btn-secondary me-1" title="View">
            <i class="fas fa-eye"></i>
        </a>

        <!-- Edit Button -->
        <a href="{{ route('vehicles.edit', $vehicle->id) }}"
           class="btn btn-sm btn-info me-1" title="Edit">
            <i class="fas fa-edit"></i>
        </a>

        <!-- Delete Button -->
        <form action="{{ route('vehicles.destroy', $vehicle->id) }}" method="POST" style="display:inline-block;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger" title="Delete"
                onclick="return confirm('Are you sure you want to delete this vehicle?');">
                <i class="fas fa-trash"></i>
            </button>
        </form>
    </div>
</td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No vehicles found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer">
        {{ $vehicles->links() }} {{-- Laravel pagination --}}
    </div>
</div>
@stop

@section('css')
{{-- Custom CSS if needed --}}
@stop

@section('js')
<script>
    console.log("Vehicles Index loaded!");
</script>
@stop
