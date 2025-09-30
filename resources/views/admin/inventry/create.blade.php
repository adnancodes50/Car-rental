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
                <form action="{{ route('inventry.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Add-On Name and Quantity -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Add-On Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g., Rooftop Tent"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Total Quantity Available</label>
                            <input type="number" name="qty_total" class="form-control" min="0" value="1"
                                required>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="3" class="form-control" placeholder="Describe the add-on and its features..."></textarea>
                    </div>

                    <!-- Image Upload -->
                    <div class="mb-3">
                        <label class="form-label">Upload Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <small class="text-muted">Upload an image for the add-on</small>
                    </div>


                    <!-- Prices -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Price per Day (ZAR)</label>
                            <input type="number" name="price_day" step="0.01" class="form-control" value="0"
                                required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Price per Week (ZAR)</label>
                            <input type="number" name="price_week" step="0.01" class="form-control" value="0"
                                required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Price per Month (ZAR)</label>
                            <input type="number" name="price_month" step="0.01" class="form-control" value="0"
                                required>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <!-- Buttons -->
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('inventry.index') }}" class="btn btn-secondary me-2 mr-1">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-dark">
                            <i class="fas fa-save"></i> Create Add-On
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
@stop
