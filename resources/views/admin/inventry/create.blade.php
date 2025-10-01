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
                <form id="addonForm" action="{{ route('inventry.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Add-On Name and Quantity -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Add-On Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g., Rooftop Tent">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Total Quantity Available</label>
                            <input type="number" name="qty_total" class="form-control" min="0" value="1">
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
                        <input type="file" name="image" id="image" class="form-control" accept="image/*">
                        <small class="text-muted">Upload an image for the add-on</small>
                    </div>

                    <!-- Prices -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Price per Day (ZAR)</label>
                            <input type="number" name="price_day" step="0.01" class="form-control" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Price per Week (ZAR)</label>
                            <input type="number" name="price_week" step="0.01" class="form-control" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Price per Month (ZAR)</label>
                            <input type="number" name="price_month" step="0.01" class="form-control" value="0">
                        </div>
                    </div>

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

@section('css')
<style>
    .error {
        color: #dc3545;
        font-size: 0.9em;
        margin-top: 4px;
        display: block;
    }
</style>
@stop

@section('js')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/additional-methods.min.js"></script>
<script>
$(document).ready(function () {
    // jQuery Validation
    $("#addonForm").validate({
        rules: {
            name: { required: true, maxlength: 255 },
            qty_total: { required: true, digits: true, min: 0 },
            description: { maxlength: 1000 },
            price_day: { required: true, number: true, min: 0 },
            price_week: { required: true, number: true, min: 0 },
            price_month: { required: true, number: true, min: 0 },
            image: {
                accept: "jpg,jpeg,png,gif,webp",
                filesize: 2 * 1024 * 1024 // 2MB
            }
        },
        messages: {
            name: { required: "Add-On name is required", maxlength: "Max 255 characters allowed" },
            qty_total: { required: "Quantity is required", digits: "Must be a whole number", min: "Must be 0 or higher" },
            description: { maxlength: "Description too long (max 1000 characters)" },
            price_day: { required: "Price per day required", number: "Must be a number", min: "Must be ≥ 0" },
            price_week: { required: "Price per week required", number: "Must be a number", min: "Must be ≥ 0" },
            price_month: { required: "Price per month required", number: "Must be a number", min: "Must be ≥ 0" },
            image: { accept: "Only jpg, jpeg, png, gif, webp allowed", filesize: "Max size 2MB" }
        },
        errorPlacement: function(error, element) {
            error.insertAfter(element);
        },
        submitHandler: function(form) {
            form.submit();
        }
    });

    // Custom filesize rule
    $.validator.addMethod("filesize", function(value, element, param) {
        return this.optional(element) || (element.files[0].size <= param);
    });
});
</script>
@stop
