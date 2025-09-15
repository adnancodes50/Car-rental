@extends('adminlte::page')

@section('title', 'Customers')

@section('content_header')
<h1 class="fw-bold">Customers</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Card -->
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white border-0">
            <h3 class="card-title mb-0 fw-bold">All Customers</h3>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Modern Responsive Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle text-sm border rounded-3">
                    <thead class="table-light text-uppercase text-muted">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Country</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                            <tr class="fw-normal">
                                <td class="fw-semibold">{{ $customer->name }}</td>
                                <td>{{ $customer->email }}</td>
                                <td>{{ $customer->phone }}</td>
                                <td>{{ $customer->country }}</td>
    <td class="text-center d-flex align-items-center justify-content-center" style="height: 40px;">
    <a href="#" class="text-info" style="font-size: 22px;">
        <i class="fas fa-eye"></i>
    </a>
</td>


                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">No customers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<style>
    /* Optional modern hover effect for rows */
    table.table-hover tbody tr:hover {
        background-color: rgba(255, 193, 7, 0.1); /* subtle yellow */
        transition: background-color 0.2s ease-in-out;
    }

    /* Rounded table headers */
    table thead th {
        border-top-left-radius: 0.5rem;
        border-top-right-radius: 0.5rem;
    }

    /* Optional striped effect */
    table.table-striped tbody tr:nth-of-type(odd) {
        background-color: #f8f9fa;
    }
</style>
@stop
