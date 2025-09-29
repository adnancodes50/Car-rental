@extends('adminlte::page')

@section('title', 'Add Vehicle')

@section('content_header')



@section('content')
    <h1 class="container fw-bold mt-0 ">Add New Vehicle</h1>

    <form action="{{ route('vehicles.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Basic Info -->
        <!-- Basic Info -->
        <div class="container card card-white my-3">
            <div class="card-header py-2 text-center">
                <h3 class="mb-0">Basic Information</h3>
            </div>

            <div class="card-body p-3">
                <div class="row">
                    <div class="form-group col-md-4 mb-2">
                        <label for="name" class="mb-1">Vehicle Name</label>
                        <input type="text" name="name" id="name" class="form-control form-control-sm"
                            value="{{ old('name') }}" required>
                    </div>
                    <div class="form-group col-md-4 mb-2">
                        <label for="model" class="mb-1">Model</label>
                        <input type="text" name="model" id="model" class="form-control form-control-sm"
                            value="{{ old('model') }}">
                    </div>
                    <div class="form-group col-md-4 mb-2">
                        <label for="year" class="mb-1">Year</label>
                        <input type="number" name="year" id="year" class="form-control form-control-sm"
                            value="{{ old('year') }}">
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-4 mb-2">
                        <label for="type" class="mb-1">Type</label>
                        <input type="text" name="type" id="type" class="form-control form-control-sm"
                            value="{{ old('type') }}">
                    </div>
                    <div class="form-group col-md-4 mb-2">
                        <label for="location" class="mb-1">Location</label>
                        <input type="text" name="location" id="location" class="form-control form-control-sm"
                            value="{{ old('location') }}">
                    </div>
                    <div class="form-group col-md-4 mb-2">
                        <label for="description" class="mb-1">Description</label>
                        <textarea name="description" id="description" rows="1" class="form-control form-control-sm">{{ old('description') }}</textarea>
                    </div>
                </div>


            </div>
        </div>


        <!-- Technical Specs -->
        <div class="container card card-white my-3">
            <div class="card-header py-2 text-center">
                <h3 class="mb-0">Technical Specs</h3>
            </div>
            <div class="card-body p-3">
                <div class="row">
                    <!-- Transmission -->
                    <div class="form-group col-md-4 mb-2">
                        <label for="transmission" class="mb-1">Transmission</label>
                        <select name="transmission" id="transmission" class="form-control form-control-sm">
                            <option value="">Select</option>
                            <option value="Automatic" {{ old('transmission') == 'Automatic' ? 'selected' : '' }}>Automatic
                            </option>
                            <option value="Manual" {{ old('transmission') == 'Manual' ? 'selected' : '' }}>Manual</option>
                        </select>
                    </div>

                    <!-- Fuel Type -->
                    <div class="form-group col-md-4 mb-2">
                        <label for="fuel_type" class="mb-1">Fuel Type</label>
                        <select name="fuel_type" id="fuel_type" class="form-control form-control-sm">
                            <option value="">Select</option>
                            <option value="Petrol" {{ old('fuel_type') == 'Petrol' ? 'selected' : '' }}>Petrol</option>
                            <option value="Diesel" {{ old('fuel_type') == 'Diesel' ? 'selected' : '' }}>Diesel</option>
                            <option value="Hybrid" {{ old('fuel_type') == 'Hybrid' ? 'selected' : '' }}>Hybrid</option>
                            <option value="Electric" {{ old('fuel_type') == 'Electric' ? 'selected' : '' }}>Electric
                            </option>
                        </select>
                    </div>

                    <!-- Drive Type -->
                    <div class="form-group col-md-4 mb-2">
                        <label for="drive_type" class="mb-1">Drive Type</label>
                        <select name="drive_type" id="drive_type" class="form-control form-control-sm">
                            <option value="">Select</option>
                            <option value="FWD" {{ old('drive_type') == 'FWD' ? 'selected' : '' }}>FWD (Front-Wheel
                                Drive)</option>
                            <option value="RWD" {{ old('drive_type') == 'RWD' ? 'selected' : '' }}>RWD (Rear-Wheel
                                Drive)</option>
                            <option value="AWD" {{ old('drive_type') == 'AWD' ? 'selected' : '' }}>AWD (All-Wheel Drive)
                            </option>
                            <option value="4WD" {{ old('drive_type') == '4WD' ? 'selected' : '' }}>4WD (Four-Wheel
                                Drive)</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-4 mb-2">
                        <label for="seats" class="mb-1">Seats</label>
                        <input type="number" name="seats" id="seats" class="form-control form-control-sm"
                            value="{{ old('seats') }}">
                    </div>
                    <div class="form-group col-md-4 mb-2">
                        <label for="mileage" class="mb-1">Mileage</label>
                        <input type="number" name="mileage" id="mileage" class="form-control form-control-sm"
                            value="{{ old('mileage') }}">
                    </div>
                    <div class="form-group col-md-4 mb-2">
                        <label for="engine" class="mb-1">Engine</label>
                        <input type="text" name="engine" id="engine" class="form-control form-control-sm"
                            value="{{ old('engine') }}">
                    </div>
                </div>
            </div>
        </div>


        <div class="container card card-white bg-white my-3">
            <div class="card-header py-2 text-center">
                <h3 class="mb-2">Images</h3>
            </div>
            <div class="card-body p-3">
                <!-- Main Photo -->
                <div class="form-group mb-3">
                    <label for="main_image" class="mb-1">Main Photo</label>
                    <input type="file" name="main_image" class="form-control form-control-sm">
                </div>

                <!-- Additional Photos -->
                <div class="form-group mb-2">
                    <label class="mb-1">Additional Photos</label>
                    <div id="image-container">
                        <input type="file" name="images[]" class="form-control form-control-sm mb-2">
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-success mt-1" id="addImageBtn">
                        <i class="fas fa-plus"></i> Add Image
                    </button>
                </div>
            </div>
        </div>

        <!-- Pricing -->
        <div class="container card bg-white my-3">
            <div class="card-header py-2 text-center">
                <h3 class="mb-0">Rental Pricing</h3>
            </div>
            <div class="card-body p-3">
                <!-- Row 1 -->
                <div class="row">
                    <div class="form-group col-md-4 mb-2">
                        <label for="rental_price_day" class="mb-1">Daily</label>
                        <input type="number" name="rental_price_day" id="rental_price_day"
                            class="form-control form-control-sm" value="{{ old('rental_price_day') }}">
                    </div>
                    <div class="form-group col-md-4 mb-2">
                        <label for="rental_price_week" class="mb-1">Weekly</label>
                        <input type="number" name="rental_price_week" id="rental_price_week"
                            class="form-control form-control-sm" value="{{ old('rental_price_week') }}">
                    </div>
                    <div class="form-group col-md-4 mb-2">
                        <label for="rental_price_month" class="mb-1">Monthly</label>
                        <input type="number" name="rental_price_month" id="rental_price_month"
                            class="form-control form-control-sm" value="{{ old('rental_price_month') }}">
                    </div>
                </div>

                <!-- Row 2 -->
                <div class="row">
                    <div class="form-group col-md-6 mb-2">
                        <label for="booking_lead_days" class="mb-1">Booking Lead Time (days)</label>
                        <input type="number" name="booking_lead_days" id="booking_lead_days"
                            class="form-control form-control-sm" value="{{ old('booking_lead_days', 0) }}">
                    </div>
                    <div class="form-group col-md-6 mb-2">
                        <label for="status" class="mb-1">Status</label>
                        <select name="status" id="status" class="form-control form-control-sm">
                            <option value="available" {{ old('status') === 'available' ? 'selected' : '' }}>Available
                            </option>
                            <option value="rented" {{ old('status') === 'rented' ? 'selected' : '' }}>Rented</option>
                            <option value="maintenance" {{ old('status') === 'maintenance' ? 'selected' : '' }}>
                                Maintenance</option>
                            <option value="sold" {{ old('status') === 'sold' ? 'selected' : '' }}>Sold</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="container card card-white bg-white my-3">
            <div class="card-header py-2 text-center">
                <h3 class="mb-0">Vehicle Features</h3>
            </div>
            <div class="card-body p-3">
                <div id="features-container" class="row">
                    <div class="col-md-6 feature-item mb-2">
                        <div class="input-group input-group-sm">
                            <input type="text" name="features[]" class="form-control" placeholder="Enter a feature">
                            <button type="button"
                                class="btn btn-sm btn-outline-danger remove-feature-btn ml-1">×</button>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-success mt-2" id="addFeatureBtn">Add
                    Feature</button>
            </div>
        </div>




        <div class="form-group  container">
            <label class="mb-1 d-block">Is For Sale?</label>

            <!-- Toggle Switch -->
            <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="forSaleToggle" name="is_for_sale"
                    value="1" {{ old('is_for_sale', 0) == 1 ? 'checked' : '' }}>
                <label class="custom-control-label" for="forSaleToggle">For Sale</label>
            </div>


            <!-- Sale fields (hidden by default) -->
            <div id="sale-fields" class="row mt-2" style="display: none;">
                <div class="form-group col-md-6 mb-2">
                    <label for="purchase_price" class="mb-1">Purchase Price</label>
                    <input type="number" name="purchase_price" id="purchase_price" class="form-control form-control-sm"
                        value="{{ old('purchase_price') }}">
                </div>
                <div class="form-group col-md-6 mb-2">
                    <label for="deposit_amount" class="mb-1">Deposit Amount</label>
                    <input type="number" name="deposit_amount" id="deposit_amount" class="form-control form-control-sm"
                        value="{{ old('deposit_amount') }}">
                </div>
            </div>
        </div>




        <!-- Submit -->
        <div class="card-footer py-2 container d-flex justify-content-end">
            <a href="{{ route('vehicles.index') }}" class="btn btn-sm btn-secondary mr-1 py-2 text-center">Cancel</a>

            <button type="submit" class="btn btn-dark btn-sm btn-hover-dark mr-1 py-2 text-center">
                <i class="fas fa-save"></i> Save
            </button>
        </div>

    </form>
@stop




@section('css')
    <style>
        .custom-control-input:checked~.custom-control-label::before {
            background-color: #000 !important;
            border-color: #000 !important;
        }

        .custom-control-label::after {
            background-color: #fff !important;
        }
    </style>
@endsection



@section('js')
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: @json(session('success')),
                timer: 2500,
                showConfirmButton: false
            });
        </script>
    @endif

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            // ===== Sale fields toggle =====
            const toggle = document.getElementById('forSaleToggle');
            const saleFields = document.getElementById('sale-fields');

            if (toggle) {
                saleFields.style.display = toggle.checked ? 'flex' : 'none';
                toggle.addEventListener('change', function() {
                    saleFields.style.display = this.checked ? 'flex' : 'none';
                });
            } else {
                const saleYes = document.getElementById("forSaleYes");
                const saleNo = document.getElementById("forSaleNo");

                function toggleSaleFields() {
                    saleFields.style.display = saleYes.checked ? "flex" : "none";
                }

                toggleSaleFields();
                saleYes.addEventListener("change", toggleSaleFields);
                saleNo.addEventListener("change", toggleSaleFields);
            }

            // ===== Add multiple image inputs =====
            const addImageBtn = document.getElementById("addImageBtn");
            const imageContainer = document.getElementById("image-container");

            if (addImageBtn) {
                addImageBtn.addEventListener("click", function() {
                    const newInput = document.createElement("input");
                    newInput.type = "file";
                    newInput.name = "images[]";
                    newInput.className = "form-control form-control-sm mb-2";
                    imageContainer.appendChild(newInput);
                });
            }

            // ===== Dynamic Features (2-column layout) =====
            const addFeatureBtn = document.getElementById("addFeatureBtn");
            const featuresContainer = document.getElementById("features-container");

            function addFeatureInput(value = "") {
                const colDiv = document.createElement("div");
                colDiv.className = "col-md-6 feature-item mb-2";

                colDiv.innerHTML = `
            <div class="input-group input-group-sm">
                <input type="text" name="features[]" class="form-control" placeholder="Enter a feature" value="${value}">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-feature-btn ml-1">×</button>
            </div>
        `;

                // Remove listener
                colDiv.querySelector(".remove-feature-btn").addEventListener("click", function() {
                    colDiv.remove();
                });

                featuresContainer.appendChild(colDiv);
            }

            if (addFeatureBtn) {
                addFeatureBtn.addEventListener("click", function() {
                    addFeatureInput();
                });
            }

            // Attach remove functionality to any preloaded features
            featuresContainer.querySelectorAll(".remove-feature-btn").forEach(btn => {
                btn.addEventListener("click", function() {
                    btn.closest(".feature-item").remove();
                });
            });

        });
    </script>
@stop
