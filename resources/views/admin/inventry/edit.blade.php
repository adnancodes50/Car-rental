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
            <form id="addonEditForm" action="{{ route('inventry.update', $addon->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Add-On Name and Quantity -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Add-On Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $addon->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Total Quantity Available</label>
                        <input type="number" name="qty_total" class="form-control" min="0"
                            value="{{ old('qty_total', $addon->qty_total) }}" required>
                    </div>
                </div>

                <!-- Category & Location -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ old('category_id', $addon->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Location</label>
                        <select name="location_id" class="form-control" required>
                            <option value="">Select Location</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location->id }}"
                                    {{ old('location_id', $addon->location_id) == $location->id ? 'selected' : '' }}>
                                    {{ $location->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Description -->
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="3" class="form-control">{{ old('description', $addon->description) }}</textarea>
                </div>

                <!-- Image Upload -->
                <div class="mb-3">
                    <label class="form-label">Upload Image</label><br>
                    @if ($addon->image_url)
                        <img src="{{ asset($addon->image_url) }}" alt="{{ $addon->name }}" class="rounded border mb-2"
                            style="width:80px; height:80px; object-fit:cover;">
                    @endif
                    <input type="file" name="image" class="form-control" accept="image/*">
                    <small class="text-muted">Upload a new image to replace the existing one</small>
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
                <div class="d-flex justify-content-between align-items-center">
    <a href="{{ route('inventry.index') }}" class="btn btn-secondary">
        Cancel
    </a>
    <button type="submit" class="btn btn-dark">
        <i class="fas fa-save"></i> Update Add-On
    </button>
</div>

            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .error { color: #dc3545; font-size: 0.9em; margin-top: 4px; display: block; }
</style>
@stop

@section('js')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
<script>
$(document).ready(function () {
    $("#addonEditForm").validate({
        rules: {
            name: { required: true, maxlength: 255 },
            qty_total: { required: true, digits: true, min: 0 },
            category_id: { required: true },
            location_id: { required: true },
            price_day: { required: true, number: true, min: 0 },
            price_week: { required: true, number: true, min: 0 },
            price_month: { required: true, number: true, min: 0 },
        },
        messages: {
            name: { required: "Add-On name is required" },
            qty_total: { required: "Quantity is required" },
            category_id: { required: "Please select a category" },
            location_id: { required: "Please select a location" },
            price_day: { required: "Price per day is required" },
            price_week: { required: "Price per week is required" },
            price_month: { required: "Price per month is required" },
        },
        errorPlacement: function(error, element) { error.insertAfter(element); },
        submitHandler: function(form) { form.submit(); }
    });
});
</script>
@stop
