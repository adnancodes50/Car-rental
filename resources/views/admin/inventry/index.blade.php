@extends('adminlte::page')

@section('title', 'Add-On Inventory')

@section('content_header')
<h1 class="fw-bold">Add-On Inventory</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Card -->
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0 fw-bold">Manage Add-Ons</h3>
            <a href="{{ route('inventry.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus-circle me-1"></i> Add New Add-On
            </a>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle text-sm border rounded-3">
                    <thead class="table-light text-uppercase text-muted">
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Qty Total</th>
                            <th>Price (Day)</th>
                            <th>Price (Week)</th>
                            <th>Price (Month)</th>
                            <th class="text-center" style="width:120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($addOns as $addOn)
                            <tr class="fw-normal">
                                <td class="fw-semibold">{{ $addOn->name }}</td>
                                <td>{{ Str::limit($addOn->description, 50) }}</td>
                                <td>{{ $addOn->qty_total }}</td>
                                <td>R{{ number_format($addOn->price_day, 2) }}</td>
                                <td>R{{ number_format($addOn->price_week, 2) }}</td>
                                <td>R{{ number_format($addOn->price_month, 2) }}</td>
                                <td class="text-center d-flex justify-content-center gap-2" style="height: 40px;">
                                    <!-- Edit -->
                                    <a href="{{ route('inventry.edit', $addOn->id) }}"
                                       class="text-warning d-flex align-items-center justify-content-center"
                                       style="font-size: 18px;">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <!-- Delete -->
                                    <form action="{{ route('inventry.destroy', $addOn->id) }}" method="POST">
    @csrf
    @method('DELETE')
    <button type="submit"
            class="text-danger d-flex align-items-center justify-content-center border-0 bg-transparent"
            style="font-size: 18px;"
            onclick="return confirm('Are you sure you want to delete this add-on?');">
        <i class="fas fa-trash-alt"></i>
    </button>
</form>

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-3">No add-ons found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<style>
    /* Optional modern hover effect */
    table.table-hover tbody tr:hover {
        background-color: rgba(255, 193, 7, 0.1); /* subtle yellow */
        transition: background-color 0.2s ease-in-out;
    }

    table thead th {
        border-top-left-radius: 0.5rem;
        border-top-right-radius: 0.5rem;
    }

    table.table-striped tbody tr:nth-of-type(odd) {
        background-color: #f8f9fa;
    }
</style>
@stop
