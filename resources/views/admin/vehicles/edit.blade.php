@extends('adminlte::page')

@section('title', 'Edit Vehicle')

@section('content_header')
    <h1 class="container">Edit Vehicle</h1>

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
        <div class="container card card-white bg-white my-3">
            <div class="card-header py-2 text-center">
                <h3 class="mb-0">Basic Information</h3>
            </div>
            <div class="card-body p-3">
                <div class="row">
                    <div class="form-group col-md-4 mb-2">
                        <label for="name">Vehicle Name</label>
                        <input type="text" name="name" id="name" class="form-control form-control-sm"
                               value="{{ old('name', $vehicle->name) }}" required>
                    </div>
                    <div class="form-group col-md-4 mb-2">
                        <label for="model">Model</label>
                        <input type="text" name="model" id="model" class="form-control form-control-sm"
                               value="{{ old('model', $vehicle->model) }}">
                    </div>
                    <div class="form-group col-md-4 mb-2">
                        <label for="year">Year</label>
                        <input type="number" name="year" id="year" class="form-control form-control-sm"
                               value="{{ old('year', $vehicle->year) }}">
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-4 mb-2">
                        <label for="type">Type</label>
                        <input type="text" name="type" id="type" class="form-control form-control-sm"
                               value="{{ old('type', $vehicle->type) }}">
                    </div>

                    {{-- ✅ Category select --}}
                    <div class="form-group col-md-4 mb-2">
                        <label for="category_id">Category</label>
                        <select name="category_id" id="category_id" class="form-control form-control-sm">
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}"
                                    {{ (string)old('category_id', $vehicle->category_id) === (string)$cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- ✅ Location select (replaces old text input) --}}
                    <div class="form-group col-md-4 mb-2">
                        <label for="location_id">Location</label>
                        <select name="location_id" id="location_id" class="form-control form-control-sm">
                            <option value="">Select Location</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}"
                                    {{ (string)old('location_id', $vehicle->location_id) === (string)$loc->id ? 'selected' : '' }}>
                                    {{ $loc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-12 mb-2">
                        <label for="description">Description</label>
                        <textarea name="description" id="description" rows="2" class="form-control form-control-sm">{{ old('description', $vehicle->description) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Technical Specs -->
        <div class="container card card-white bg-white my-3">
            <div class="card-header py-2 text-center">
                <h3 class="mb-0">Technical Specs</h3>
            </div>
            <div class="card-body p-3">
                <div class="row">
                    <div class="form-group col-md-4 mb-2">
                        <label for="transmission">Transmission</label>
                        <select name="transmission" id="transmission" class="form-control form-control-sm">
                            <option value="">Select</option>
                            <option value="Automatic" {{ old('transmission', $vehicle->transmission) == 'Automatic' ? 'selected' : '' }}>Automatic</option>
                            <option value="Manual"    {{ old('transmission', $vehicle->transmission) == 'Manual' ? 'selected' : '' }}>Manual</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4 mb-2">
                        <label for="fuel_type">Fuel Type</label>
                        <select name="fuel_type" id="fuel_type" class="form-control form-control-sm">
                            <option value="">Select</option>
                            <option value="Petrol"   {{ old('fuel_type', $vehicle->fuel_type) == 'Petrol' ? 'selected' : '' }}>Petrol</option>
                            <option value="Diesel"   {{ old('fuel_type', $vehicle->fuel_type) == 'Diesel' ? 'selected' : '' }}>Diesel</option>
                            <option value="Hybrid"   {{ old('fuel_type', $vehicle->fuel_type) == 'Hybrid' ? 'selected' : '' }}>Hybrid</option>
                            <option value="Electric" {{ old('fuel_type', $vehicle->fuel_type) == 'Electric' ? 'selected' : '' }}>Electric</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4 mb-2">
                        <label for="drive_type">Drive Type</label>
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
                        <label for="seats">Seats</label>
                        <input type="number" name="seats" id="seats" class="form-control form-control-sm"
                               value="{{ old('seats', $vehicle->seats) }}">
                    </div>
                    <div class="form-group col-md-4 mb-2">
                        <label for="mileage">Mileage in (Km)</label>
                        <input type="number" name="mileage" id="mileage" class="form-control form-control-sm"
                               value="{{ old('mileage', $vehicle->mileage) }}">
                    </div>
                    <div class="form-group col-md-4 mb-2">
                        <label for="engine">Engine</label>
                        <input type="text" name="engine" id="engine" class="form-control form-control-sm"
                               value="{{ old('engine', $vehicle->engine) }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Images -->
        <div class="container card card-white bg-white my-3">
            <div class="card-header py-2 text-center">
                <h3 class="mb-0">Images</h3>
            </div>
            <div class="card-body p-3">
                <div class="form-group mb-3">
                    <label>Main Photo</label><br>
                    @if ($vehicle->main_image_url)
                        <img src="{{ asset($vehicle->main_image_url) }}" width="100" class="mb-2">
                    @endif
                    <input type="file" name="main_image" class="form-control form-control-sm">
                </div>

                <div class="form-group mb-2">
                    <label>Additional Photos</label>
                    <div id="image-container">
                        {{-- Existing images --}}
                        @foreach($vehicle->images as $img)
                            <div class="d-inline-block position-relative mr-2 mb-2 existing-image" data-id="{{ $img->id }}">
                                <img src="{{ asset($img->url) }}" width="90" class="img-thumbnail border">
                                <button type="button" class="btn btn-sm btn-danger position-absolute remove-image-btn"
                                        style="top:2px; right:2px; padding:0 6px;">×</button>
                                <input type="hidden" name="existing_images[]" value="{{ $img->id }}">
                            </div>
                        @endforeach

                        {{-- Default first new image input (with remove button) --}}
                        <div class="d-flex align-items-center mt-2 image-input-group">
                            <input type="file" name="images[]" class="form-control form-control-sm">
                            <button type="button" class="btn btn-sm btn-outline-danger ml-2 remove-input-btn">×</button>
                        </div>
                    </div>

                    <button type="button" class="btn btn-sm btn-outline-success mt-2" id="addImageBtn">Add Image</button>
                </div>
            </div>
        </div>

        <!-- Pricing -->
        <div class="container card bg-white my-3">
            <div class="card-header py-2 text-center">
                <h3 class="mb-0">Rental Pricing</h3>
            </div>
            <div class="card-body p-3">
                <div class="row">
                    <div class="form-group col-md-4 mb-2">
                        <label>Daily</label>
                        <input type="number" name="rental_price_day" class="form-control form-control-sm"
                               value="{{ old('rental_price_day', $vehicle->rental_price_day) }}">
                    </div>
                    <div class="form-group col-md-4 mb-2">
                        <label>Weekly</label>
                        <input type="number" name="rental_price_week" class="form-control form-control-sm"
                               value="{{ old('rental_price_week', $vehicle->rental_price_week) }}">
                    </div>
                    <div class="form-group col-md-4 mb-2">
                        <label>Monthly</label>
                        <input type="number" name="rental_price_month" class="form-control form-control-sm"
                               value="{{ old('rental_price_month', $vehicle->rental_price_month) }}">
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-6 mb-2">
                        <label>Booking Lead Time (days)</label>
                        <input type="number" name="booking_lead_days" class="form-control form-control-sm"
                               value="{{ old('booking_lead_days', $vehicle->booking_lead_days) }}">
                    </div>
                    <div class="form-group col-md-6 mb-2">
                        <label>Status</label>
                        <select name="status" class="form-control form-control-sm">
                            @foreach (['available', 'rented', 'maintenance', 'sold'] as $status)
                                <option value="{{ $status }}"
                                    {{ old('status', $vehicle->status) == $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vehicle Features -->
        <div class="container card card-white bg-white my-3">
            <div class="card-header py-2 text-center">
                <h3 class="mb-0">Vehicle Features</h3>
            </div>
            <div class="card-body p-3">
                <div class="row" id="features-container">
                    @php $features = old('features', $vehicle->features ?? []); @endphp
                    @if ($features && count($features) > 0)
                        @foreach ($features as $feature)
                            <div class="col-md-6 mb-2 feature-item">
                                <div class="d-flex">
                                    <input type="text" name="features[]" class="form-control form-control-sm mr-2"
                                           value="{{ $feature }}" placeholder="Enter a feature">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-feature-btn">×</button>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="col-md-6 mb-2 feature-item">
                            <div class="d-flex">
                                <input type="text" name="features[]" class="form-control form-control-sm mr-2"
                                       placeholder="Enter a feature">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-feature-btn">×</button>
                            </div>
                        </div>
                    @endif
                </div>

                <button type="button" class="btn btn-sm btn-outline-success mt-2" id="addFeatureBtn">Add Feature</button>
            </div>
        </div>

        <!-- Sale Fields -->
        <div class="form-group mb-2 container">
            <label class="d-block">Is For Sale?</label>

            <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="forSaleToggle" name="is_for_sale"
                       value="1" {{ old('is_for_sale', $vehicle->is_for_sale) == 1 ? 'checked' : '' }}>
                <label class="custom-control-label" for="forSaleToggle">For Sale</label>
            </div>

            <div id="sale-fields" class="row mt-2"
                 style="display: {{ old('is_for_sale', $vehicle->is_for_sale) == 1 ? 'flex' : 'none' }};">
                <div class="form-group col-md-6 mb-2">
                    <label>Purchase Price</label>
                    <input type="number" name="purchase_price" class="form-control form-control-sm"
                           value="{{ old('purchase_price', $vehicle->purchase_price) }}">
                </div>
                <div class="form-group col-md-6 mb-2">
                    <label>Deposit Amount</label>
                    <input type="number" name="deposit_amount" class="form-control form-control-sm"
                           value="{{ old('deposit_amount', $vehicle->deposit_amount) }}">
                </div>
            </div>

            <!-- Submit -->
            <div class="card-footer py-2 d-flex justify-content-end">
                <a href="{{ route('vehicles.index') }}" class="btn btn-sm btn-secondary mr-1">Cancel</a>
                <button type="submit" class="btn btn-sm btn-dark mr-2"><i class="fas fa-save"></i> Update</button>
            </div>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Alerts
            @if (session('success'))
                Swal.fire({ icon: 'success', title: 'Success!', text: @json(session('success')), timer: 2500, showConfirmButton: false });
            @endif
            @if (session('error'))
                Swal.fire({ icon: 'error', title: 'Error!', text: @json(session('error')), timer: 3000, showConfirmButton: true });
            @endif

            // Sale fields toggle
            const saleFields = document.getElementById("sale-fields");
            const saleToggle = document.getElementById("forSaleToggle");
            const toggleSaleFields = () => saleFields.style.display = saleToggle.checked ? "flex" : "none";
            if (saleToggle) saleToggle.addEventListener("change", toggleSaleFields);

            // Dynamic features
            const addFeatureBtn = document.getElementById("addFeatureBtn");
            const featuresContainer = document.getElementById("features-container");
            function addFeatureInput(value = "") {
                const colDiv = document.createElement("div");
                colDiv.className = "col-md-6 mb-2 feature-item";
                colDiv.innerHTML = `
                    <div class="d-flex">
                        <input type="text" name="features[]" class="form-control form-control-sm mr-2" placeholder="Enter a feature" value="${value}">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-feature-btn">×</button>
                    </div>`;
                featuresContainer.appendChild(colDiv);
                colDiv.querySelector(".remove-feature-btn").addEventListener("click", () => colDiv.remove());
            }
            if (addFeatureBtn) addFeatureBtn.addEventListener("click", () => addFeatureInput());
            document.querySelectorAll(".remove-feature-btn").forEach(btn => btn.addEventListener("click", () => btn.closest(".feature-item").remove()));

            // Remove existing images
            document.querySelectorAll(".remove-image-btn").forEach(btn => {
                btn.addEventListener("click", function() {
                    const parent = btn.closest(".existing-image");
                    const id = parent.dataset.id;

                    const removedInput = document.createElement('input');
                    removedInput.type = 'hidden';
                    removedInput.name = 'removed_images[]';
                    removedInput.value = id;
                    parent.closest('form').appendChild(removedInput);

                    parent.remove();
                });
            });

            // Add/remove new image inputs
            const addImageBtn = document.getElementById("addImageBtn");
            const imageContainer = document.getElementById("image-container");
            if (addImageBtn && imageContainer) {
                addImageBtn.addEventListener("click", () => {
                    const wrapper = document.createElement("div");
                    wrapper.className = "d-flex align-items-center mt-2 image-input-group";
                    wrapper.innerHTML = `
                        <input type="file" name="images[]" class="form-control form-control-sm">
                        <button type="button" class="btn btn-sm btn-outline-danger ml-2 remove-input-btn">×</button>`;
                    imageContainer.appendChild(wrapper);
                    wrapper.querySelector(".remove-input-btn").addEventListener("click", () => wrapper.remove());
                });
            }
            document.querySelectorAll(".remove-input-btn").forEach(btn => {
                btn.addEventListener("click", () => btn.closest(".image-input-group").remove());
            });
        });
    </script>
@stop
