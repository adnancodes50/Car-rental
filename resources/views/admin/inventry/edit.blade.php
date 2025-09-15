@extends('adminlte::page')

@section('title', 'Edit Add-On')

@section('content_header')
    <h1 class="fw-bold">Edit Add-On</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm">
        <div class="card-header">
            <h3 class="card-title">Update Add-On Details</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('inventry.update', $addon->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Add-On Name and Quantity -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Add-On Name</label>
                        <input type="text" name="name" class="form-control" 
                               value="{{ old('name', $addon->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Total Quantity Available</label>
                        <input type="number" name="qty_total" class="form-control" 
                               min="0" value="{{ old('qty_total', $addon->qty_total) }}" required>
                    </div>
                </div>

                <!-- Description -->
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="3" class="form-control">{{ old('description', $addon->description) }}</textarea>
                </div>

                <!-- Image URL -->
                <div class="mb-3">
                    <label class="form-label">Image URL (Optional)</label>
                    <input type="url" name="image_url" class="form-control" 
                           value="{{ old('image_url', $addon->image_url) }}">
                    <small class="text-muted">Provide a URL for the add-on image to display in booking flow</small>
                </div>

                <!-- Prices -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Price per Day (ZAR)</label>
                        <input type="number" name="price_day" step="0.01" class="form-control" 
                               value="{{ old('price_day', $addon->price_day) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Price per Week (ZAR)</label>
                        <input type="number" name="price_week" step="0.01" class="form-control" 
                               value="{{ old('price_week', $addon->price_week) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Price per Month (ZAR)</label>
                        <input type="number" name="price_month" step="0.01" class="form-control" 
                               value="{{ old('price_month', $addon->price_month) }}" required>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="d-flex justify-content-start">
                    <button type="submit" class="btn btn-warning me-2">
                        <i class="fas fa-save"></i> Update Add-On
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
