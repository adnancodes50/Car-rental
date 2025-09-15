@extends('adminlte::page')

@section('title', 'Create Add-On')

@section('content_header')
    <h1 class="fw-bold">Create Add-On</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm">
        <div class="card-header">
            <h3 class="card-title">Add-On Details</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('inventry.store') }}" method="POST">
                @csrf

                <!-- Add-On Name and Quantity -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Add-On Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g., Rooftop Tent" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Total Quantity Available</label>
                        <input type="number" name="qty_total" class="form-control" min="0" value="1" required>
                    </div>
                </div>

                <!-- Description -->
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="3" class="form-control" placeholder="Describe the add-on and its features..."></textarea>
                </div>

                <!-- Image URL -->
                <div class="mb-3">
                    <label class="form-label">Image URL (Optional)</label>
                    <input type="url" name="image_url" class="form-control" placeholder="e.g., https://example.com/rooftop-tent.jpg">
                    <small class="text-muted">Provide a URL for the add-on image to display in booking flow</small>
                </div>

                <!-- Prices -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Price per Day (ZAR)</label>
                        <input type="number" name="price_day" step="0.01" class="form-control" value="0" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Price per Week (ZAR)</label>
                        <input type="number" name="price_week" step="0.01" class="form-control" value="0" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Price per Month (ZAR)</label>
                        <input type="number" name="price_month" step="0.01" class="form-control" value="0" required>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="d-flex justify-content-start">
                    <button type="submit" class="btn btn-success me-2">
                        <i class="fas fa-save"></i> Create Add-On
                    </button>
                    <a href="{{ route('inventry.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
