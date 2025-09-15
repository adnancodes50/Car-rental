@extends('adminlte::page')

@section('title', 'Add-On Inventory')

@section('content_header')
<h1 class="fw-bold">Add-On Inventory</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Card -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title mb-0">Manage Add-Ons</h3>
    <a href="{{ route('inventry.create') }}" class="btn btn-primary btn-sm ms-auto">
        <i class="fas fa-plus-circle"></i> Add New Add-On
    </a>
</div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Simple Table -->
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Qty Total</th>
                        <th>Price (Day)</th>
                        <th>Price (Week)</th>
                        <th>Price (Month)</th>
                        <th style="width: 120px;" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($addOns as $addOn)
                        <tr>
                            <td class="fw-bold">{{ $addOn->name }}</td>
                            <td>{{ Str::limit($addOn->description, 50) }}</td>
                            <td>{{ $addOn->qty_total }}</td>
                            <td>R{{ number_format($addOn->price_day, 2) }}</td>
                            <td>R{{ number_format($addOn->price_week, 2) }}</td>
                            <td>R{{ number_format($addOn->price_month, 2) }}</td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <!-- View -->
                                    {{-- <button class="btn btn-sm btn-info d-flex align-items-center justify-content-center"
                                        style="width:32px; height:32px;">
                                        <i class="fas fa-eye"></i>
                                    </button> --}}

  <!-- Edit -->
<a href="{{ route('inventry.edit', $addOn->id) }}" 
   class="btn btn-sm btn-warning d-flex align-items-center justify-content-center"
   style="width:32px; height:32px;">
    <i class="fas fa-edit"></i>
</a>



                                    <!-- Delete -->
                                    <button class="btn btn-sm btn-danger d-flex align-items-center justify-content-center"
                                        style="width:32px; height:32px;">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No add-ons found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop