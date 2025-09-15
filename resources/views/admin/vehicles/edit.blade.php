@extends('adminlte::page')

@section('title', 'Edit Vehicle')

@section('content_header')
    <h1>Edit Vehicle</h1>

    @if ($errors->any())
        <div class="alert alert-danger mt-3">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
@stop

@section('content')
<form action="{{ route('vehicles.update', $vehicle->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <!-- Basic Info -->
    <div class="container my-3">
        <div class="card card-white bg-white shadow-sm">
            <div class="card-header py-2 text-center">
                <h3 class="mb-0">Basic Information</h3>
            </div>
            <div class="card-body p-3">
                <div class="row">
                    <div class="form-group col-md-4 mb-2">
                        <label for="name" class="mb-1">Vehicle Name</label>
                        <input type="text" name="name" id="name" class="form-control form-control-sm"
                               value="{{ old('name', $vehicle->name) }}" required>
                    </div>
                    <div class="form-group col-md-4 mb-2">
                        <label for="model" class="mb-1">Model</label>
                        <input type="text" name="model" id="model" class="form-control form-control-sm"
                               value="{{ old('model', $vehicle->model) }}">
                    </div>
                    <div class="form-group col-md-4 mb-2">
                        <label for="year" class="mb-1">Year</label>
                        <input type="number" name="year" id="year" class="form-control form-control-sm"
                               value="{{ old('year', $vehicle->year) }}">
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-4 mb-2">
                        <label for="type" class="mb-1">Type</label>
                        <input type="text" name="type" id="type" class="form-control form-control-sm"
                               value="{{ old('type', $vehicle->type) }}">
                    </div>
                    <div class="form-group col-md-4 mb-2">
                        <label for="location" class="mb-1">Location</label>
                        <input type="text" name="location" id="location" class="form-control form-control-sm"
                               value="{{ old('location', $vehicle->location) }}">
                    </div>
                    <div class="form-group col-md-4 mb-2">
                        <label for="description" class="mb-1">Description</label>
                        <textarea name="description" id="description" rows="1"
                                  class="form-control form-control-sm">{{ old('description', $vehicle->description) }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Technical Specs -->
    <div class="container card card-white bg-white my-3">
        <div class="card-header py-2 d-flex justify-content-center">
            <h3 class="card-title mb-0 w-100 text-center">Technical Specs</h3>
        </div>
        <div class="card-body p-3">
            <div class="row">
                <div class="form-group col-md-4 mb-2">
                    <label for="transmission" class="mb-1">Transmission</label>
                    <select name="transmission" id="transmission" class="form-control form-control-sm">
                        <option value="">Select</option>
                        <option value="Automatic" {{ old('transmission', $vehicle->transmission) == 'Automatic' ? 'selected' : '' }}>Automatic</option>
                        <option value="Manual" {{ old('transmission', $vehicle->transmission) == 'Manual' ? 'selected' : '' }}>Manual</option>
                    </select>
                </div>
                <div class="form-group col-md-4 mb-2">
                    <label for="fuel_type" class="mb-1">Fuel Type</label>
                    <select name="fuel_type" id="fuel_type" class="form-control form-control-sm">
                        <option value="">Select</option>
                        <option value="Petrol" {{ old('fuel_type', $vehicle->fuel_type) == 'Petrol' ? 'selected' : '' }}>Petrol</option>
                        <option value="Diesel" {{ old('fuel_type', $vehicle->fuel_type) == 'Diesel' ? 'selected' : '' }}>Diesel</option>
                        <option value="Hybrid" {{ old('fuel_type', $vehicle->fuel_type) == 'Hybrid' ? 'selected' : '' }}>Hybrid</option>
                        <option value="Electric" {{ old('fuel_type', $vehicle->fuel_type) == 'Electric' ? 'selected' : '' }}>Electric</option>
                    </select>
                </div>
                <div class="form-group col-md-4 mb-2">
                    <label for="drive_type" class="mb-1">Drive Type</label>
                    <select name="drive_type" id="drive_type" class="form-control form-control-sm">
                        <option value="">Select</option>
                        <option value="FWD" {{ old('drive_type', $vehicle->drive_type) == 'FWD' ? 'selected' : '' }}>FWD</option>
                        <option value="RWD" {{ old('drive_type', $vehicle->drive_type) == 'RWD' ? 'selected' : '' }}>RWD</option>
                        <option value="AWD" {{ old('drive_type', $vehicle->drive_type) == 'AWD' ? 'selected' : '' }}>AWD</option>
                        <option value="4WD" {{ old('drive_type', $vehicle->drive_type) == '4WD' ? 'selected' : '' }}>4WD</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-4 mb-2">
                    <label for="seats" class="mb-1">Seats</label>
                    <input type="number" name="seats" id="seats" class="form-control form-control-sm"
                           value="{{ old('seats', $vehicle->seats) }}">
                </div>
                <div class="form-group col-md-4 mb-2">
                    <label for="mileage" class="mb-1">Mileage</label>
                    <input type="number" name="mileage" id="mileage" class="form-control form-control-sm"
                           value="{{ old('mileage', $vehicle->mileage) }}">
                </div>
                <div class="form-group col-md-4 mb-2">
                    <label for="engine" class="mb-1">Engine</label>
                    <input type="text" name="engine" id="engine" class="form-control form-control-sm"
                           value="{{ old('engine', $vehicle->engine) }}">
                </div>
            </div>
        </div>
    </div>

    <!-- Images -->
    <div class="container card card-white bg-white my-3">
        <div class="card-header py-2 d-flex justify-content-center">
            <h3 class="card-title mb-0 w-100 text-center">Images</h3>
        </div>
        <div class="card-body p-3">
            <div class="form-group mb-3">
                <label for="main_image" class="mb-1">Main Photo</label><br>
                @if($vehicle->main_image_url)
                    <img src="{{ asset($vehicle->main_image_url) }}" alt="Main Image" width="100" class="mb-2">
                @endif
                <input type="file" name="main_image" class="form-control form-control-sm">
            </div>
            <div class="form-group mb-2">
                <label class="mb-1">Additional Photos</label><br>
                @if($vehicle->images && count($vehicle->images))
                    @foreach($vehicle->images as $img)
                        <img src="{{ asset($img->url) }}" alt="Image" width="80" class="mr-2 mb-2 img-thumbnail">
                    @endforeach
                @endif
                <input type="file" name="images[]" class="form-control form-control-sm mt-2" multiple>
            </div>
        </div>
    </div>

    <!-- Pricing -->
    <div class="container card bg-white my-3">
        <div class="card-header py-2 d-flex justify-content-center bg-warning">
            <h3 class="card-title mb-0 w-100 text-center">Rental Pricing</h3>
        </div>
        <div class="card-body p-3">
            <div class="row">
                <div class="form-group col-md-4 mb-2">
                    <label for="rental_price_day" class="mb-1">Daily</label>
                    <input type="number" name="rental_price_day" id="rental_price_day"
                           class="form-control form-control-sm"
                           value="{{ old('rental_price_day', $vehicle->rental_price_day) }}">
                </div>
                <div class="form-group col-md-4 mb-2">
                    <label for="rental_price_week" class="mb-1">Weekly</label>
                    <input type="number" name="rental_price_week" id="rental_price_week"
                           class="form-control form-control-sm"
                           value="{{ old('rental_price_week', $vehicle->rental_price_week) }}">
                </div>
                <div class="form-group col-md-4 mb-2">
                    <label for="rental_price_month" class="mb-1">Monthly</label>
                    <input type="number" name="rental_price_month" id="rental_price_month"
                           class="form-control form-control-sm"
                           value="{{ old('rental_price_month', $vehicle->rental_price_month) }}">
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-6 mb-2">
                    <label for="booking_lead_days" class="mb-1">Booking Lead Time (days)</label>
                    <input type="number" name="booking_lead_days" id="booking_lead_days"
                           class="form-control form-control-sm"
                           value="{{ old('booking_lead_days', $vehicle->booking_lead_days) }}">
                </div>
                <div class="form-group col-md-6 mb-2">
                    <label for="status" class="mb-1">Status</label>
                    <select name="status" id="status" class="form-control form-control-sm">
                        <option value="available" {{ old('status', $vehicle->status) == 'available' ? 'selected' : '' }}>Available</option>
                        <option value="rented" {{ old('status', $vehicle->status) == 'rented' ? 'selected' : '' }}>Rented</option>
                        <option value="maintenance" {{ old('status', $vehicle->status) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        <option value="sold" {{ old('status', $vehicle->status) == 'sold' ? 'selected' : '' }}>Sold</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Sale Fields -->
    <div class="form-group mb-2">
        <label class="mb-1 d-block">Is For Sale?</label>
        <div class="custom-control custom-radio d-inline mr-3">
            <input class="custom-control-input" type="radio" id="forSaleNo" name="is_for_sale" value="0"
                   {{ old('is_for_sale', $vehicle->is_for_sale) == 0 ? 'checked' : '' }}>
            <label for="forSaleNo" class="custom-control-label">No</label>
        </div>
        <div class="custom-control custom-radio d-inline">
            <input class="custom-control-input" type="radio" id="forSaleYes" name="is_for_sale" value="1"
                   {{ old('is_for_sale', $vehicle->is_for_sale) == 1 ? 'checked' : '' }}>
            <label for="forSaleYes" class="custom-control-label">Yes</label>
        </div>

        <div id="sale-fields" class="row mt-2" style="display: none;">
            <div class="form-group col-md-6 mb-2">
                <label for="purchase_price" class="mb-1">Purchase Price</label>
                <input type="number" name="purchase_price" id="purchase_price"
                       class="form-control form-control-sm"
                       value="{{ old('purchase_price', $vehicle->purchase_price) }}">
            </div>
            <div class="form-group col-md-6 mb-2">
                <label for="deposit_amount" class="mb-1">Deposit Amount</label>
                <input type="number" name="deposit_amount" id="deposit_amount"
                       class="form-control form-control-sm"
                       value="{{ old('deposit_amount', $vehicle->deposit_amount) }}">
            </div>
        </div>
    </div>

    <!-- Submit -->
    <div class="card-footer py-2">
        <button type="submit" class="btn btn-sm btn-primary">
            <i class="fas fa-save"></i> Update
        </button>
        <a href="{{ route('vehicles.index') }}" class="btn btn-sm btn-secondary">Cancel</a>
    </div>
</form>
@stop

@section('js')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const saleYes = document.getElementById("forSaleYes");
        const saleNo = document.getElementById("forSaleNo");
        const saleFields = document.getElementById("sale-fields");

        function toggleSaleFields() {
            if (saleYes.checked) {
                saleFields.style.display = "flex";
            } else {
                saleFields.style.display = "none";
            }
        }

        toggleSaleFields();
        saleYes.addEventListener("change", toggleSaleFields);
        saleNo.addEventListener("change", toggleSaleFields);
    });
</script>
@stop
