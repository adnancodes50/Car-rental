@extends('adminlte::page')

@section('title', 'Add Vehicle')

@section('content_header')
@stop

@section('content')
    <h1 class="container fw-bold mt-0">Add New Vehicle</h1>

    <form id="vehicleForm" action="{{ route('vehicles.store') }}" method="POST" enctype="multipart/form-data">
        @csrf


        <!-- Basic Info -->
        <div class="container card card-white my-3">
            <div class="card-header py-2 text-center">
                <h3 class="mb-0">Basic Information</h3>
            </div>

            <div class="card-body p-3">
                <div class="row">
                    <div class="form-group col-md-4 mb-2">
                        <label for="name" class="mb-1">Vehicle Name *</label>
                        <input type="text" name="name" id="name" class="form-control form-control-sm"
                            value="{{ old('name') }}">
                        <span class="text-danger small error-message" id="name-error"></span>
                    </div>
                    <div class="form-group col-md-4 mb-2">
                        <label for="model" class="mb-1">Model</label>
                        <input type="text" name="model" id="model" class="form-control form-control-sm"
                            value="{{ old('model') }}">
                        <span class="text-danger small error-message" id="model-error"></span>
                    </div>
                    <div class="form-group col-md-4 mb-2">
                        <label for="year" class="mb-1">Year</label>
                        <input type="number" name="year" id="year" class="form-control form-control-sm"
                            value="{{ old('year') }}">
                        <span class="text-danger small error-message" id="year-error"></span>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-4 mb-2">
                        <label for="type" class="mb-1">Type</label>
                        <input type="text" name="type" id="type" class="form-control form-control-sm"
                            value="{{ old('type') }}">
                        <span class="text-danger small error-message" id="type-error"></span>
                    </div>
                    <div class="form-group col-md-4 mb-2">
                        <label for="location" class="mb-1">Location</label>
                        <input type="text" name="location" id="location" class="form-control form-control-sm"
                            value="{{ old('location') }}">
                        <span class="text-danger small error-message" id="location-error"></span>
                    </div>
                    <div class="form-group col-md-4 mb-2">
                        <label for="description" class="mb-1">Description</label>
                        <textarea name="description" id="description" rows="1" class="form-control form-control-sm">{{ old('description') }}</textarea>
                        <span class="text-danger small error-message" id="description-error"></span>
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
                        <span class="text-danger small error-message" id="transmission-error"></span>
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
                        <span class="text-danger small error-message" id="fuel_type-error"></span>
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
                            <option value="AWD" {{ old('drive_type') == 'AWD' ? 'selected' : '' }}>AWD (All-Wheel
                                Drive)</option>
                            <option value="4WD" {{ old('drive_type') == '4WD' ? 'selected' : '' }}>4WD (Four-Wheel
                                Drive)</option>
                        </select>
                        <span class="text-danger small error-message" id="drive_type-error"></span>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-4 mb-2">
                        <label for="seats" class="mb-1">Seats</label>
                        <input type="number" name="seats" id="seats" class="form-control form-control-sm"
                            value="{{ old('seats') }}">
                        <span class="text-danger small error-message" id="seats-error"></span>
                    </div>
                    <div class="form-group col-md-4 mb-2">
                        <label for="mileage" class="mb-1">Mileage in (Km)</label>
                        <input type="number" name="mileage" id="mileage" class="form-control form-control-sm"
                            value="{{ old('mileage') }}">
                        <span class="text-danger small error-message" id="mileage-error"></span>
                    </div>
                    <div class="form-group col-md-4 mb-2">
                        <label for="engine" class="mb-1">Engine</label>
                        <input type="text" name="engine" id="engine" class="form-control form-control-sm"
                            value="{{ old('engine') }}">
                        <span class="text-danger small error-message" id="engine-error"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Images -->
        <div class="container card card-white bg-white my-3">
            <div class="card-header py-2 text-center">
                <h3 class="mb-2">Images</h3>
            </div>
            <div class="card-body p-3">
                <!-- Main Photo -->
                <!-- Main Photo -->
                <div class="form-group mb-3">
                    <label for="main_image" class="mb-1">Main Photo</label>
                    <div id="mainPreviewContainer" class="mb-2" style="display:none;">
                        <img id="mainPreview" src="#" alt="Preview" class="img-fluid rounded border"
                            style="max-height:150px;">
                    </div>
                    <input type="file" name="main_image" id="main_image" class="form-control form-control-sm">
                    <span class="text-danger small error-message" id="main_image-error"></span>
                </div>

                <!-- Additional Photos -->
                <div class="form-group mb-2">
                    <label class="mb-1">Additional Photos</label>
                    <div id="image-container">
                        <div class="image-input-wrapper mb-2">
                            <div class="preview mb-1" style="display:none;">
                                <img src="#" class="img-fluid rounded border" style="max-height:120px;">
                            </div>
                            <input type="file" name="images[]" class="form-control form-control-sm image-input">
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-success mt-1" id="addImageBtn">
                        <i class="fas fa-plus"></i> Add Image
                    </button>
                    <span class="text-danger small error-message d-block" id="images-error"></span>
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
                        <label for="rental_price_day" class="mb-1">Daily</label>
                        <input type="number" name="rental_price_day" id="rental_price_day"
                            class="form-control form-control-sm" value="{{ old('rental_price_day') }}">
                        <span class="text-danger small error-message" id="rental_price_day-error"></span>
                    </div>
                    <div class="form-group col-md-4 mb-2">
                        <label for="rental_price_week" class="mb-1">Weekly</label>
                        <input type="number" name="rental_price_week" id="rental_price_week"
                            class="form-control form-control-sm" value="{{ old('rental_price_week') }}">
                        <span class="text-danger small error-message" id="rental_price_week-error"></span>
                    </div>
                    <div class="form-group col-md-4 mb-2">
                        <label for="rental_price_month" class="mb-1">Monthly</label>
                        <input type="number" name="rental_price_month" id="rental_price_month"
                            class="form-control form-control-sm" value="{{ old('rental_price_month') }}">
                        <span class="text-danger small error-message" id="rental_price_month-error"></span>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-6 mb-2">
                        <label for="booking_lead_days" class="mb-1">Booking Lead Time (days)</label>
                        <input type="number" name="booking_lead_days" id="booking_lead_days"
                            class="form-control form-control-sm" value="{{ old('booking_lead_days', 0) }}">
                        <span class="text-danger small error-message" id="booking_lead_days-error"></span>
                    </div>
                    <div class="form-group col-md-6 mb-2">
                        <label for="status" class="mb-1">Status *</label>
                        <select name="status" id="status" class="form-control form-control-sm">
                            <option value="">Select Status</option>
                            <option value="available" {{ old('status') === 'available' ? 'selected' : '' }}>Available
                            </option>
                            <option value="rented" {{ old('status') === 'rented' ? 'selected' : '' }}>Rented</option>
                            <option value="maintenance" {{ old('status') === 'maintenance' ? 'selected' : '' }}>
                                Maintenance</option>
                            <option value="sold" {{ old('status') === 'sold' ? 'selected' : '' }}>Sold</option>
                        </select>
                        <span class="text-danger small error-message" id="status-error"></span>
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
                <div id="features-container" class="row">
                    @if (old('features'))
                        @foreach (old('features') as $feature)
                            <div class="col-md-6 feature-item mb-2">
                                <div class="input-group input-group-sm">
                                    <input type="text" name="features[]" class="form-control feature-input"
                                        placeholder="Enter a feature" value="{{ $feature }}">
                                    <button type="button"
                                        class="btn btn-sm btn-outline-danger remove-feature-btn ml-1">×</button>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="col-md-6 feature-item mb-2">
                            <div class="input-group input-group-sm">
                                <input type="text" name="features[]" class="form-control feature-input"
                                    placeholder="Enter a feature">
                                <button type="button"
                                    class="btn btn-sm btn-outline-danger remove-feature-btn ml-1">×</button>
                            </div>
                        </div>
                    @endif
                </div>
                <button type="button" class="btn btn-sm btn-outline-success mt-2" id="addFeatureBtn">Add
                    Feature</button>
                <span class="text-danger small error-message d-block" id="features-error"></span>
            </div>
        </div>

        <!-- For Sale Toggle -->
        <div class="form-group container">
            <label class="mb-1 d-block">Is For Sale?</label>

            <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="forSaleToggle" name="is_for_sale"
                    value="1" {{ old('is_for_sale', 0) == 1 ? 'checked' : '' }}>
                <label class="custom-control-label" for="forSaleToggle">For Sale</label>
            </div>

            <div id="sale-fields" class="row mt-2" style="display: {{ old('is_for_sale') ? 'flex' : 'none' }};">
                <div class="form-group col-md-6 mb-2">
                    <label for="purchase_price" class="mb-1">Purchase Price</label>
                    <input type="number" name="purchase_price" id="purchase_price" class="form-control form-control-sm"
                        value="{{ old('purchase_price') }}">
                    <span class="text-danger small error-message" id="purchase_price-error"></span>
                </div>
                <div class="form-group col-md-6 mb-2">
                    <label for="deposit_amount" class="mb-1">Deposit Amount</label>
                    <input type="number" name="deposit_amount" id="deposit_amount" class="form-control form-control-sm"
                        value="{{ old('deposit_amount') }}">
                    <span class="text-danger small error-message" id="deposit_amount-error"></span>
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

        .error-message {
            display: block;
            margin-top: 2px;
        }

        .is-invalid {
            border-color: #dc3545 !important;
        }

        .is-valid {
            border-color: #28a745 !important;
        }

        .text-danger {
            font-size: 0.875em;
        }

        .form-group {
            margin-bottom: 0.5rem;
        }
    </style>
@stop

@section('js')
    <!-- Load jQuery first with error handling -->
    <script>
        if (typeof jQuery === 'undefined') {
            document.write('<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"><\/script>');
        }
    </script>

    <!-- Load jQuery Validate -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/additional-methods.min.js"></script>

    <!-- SweetAlert -->
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
document.addEventListener("DOMContentLoaded", function () {
    // ===== Toggle Sale fields =====
    const toggle = document.getElementById('forSaleToggle');
    const saleFields = document.getElementById('sale-fields');
    if (toggle) {
        saleFields.style.display = toggle.checked ? 'flex' : 'none';
        toggle.addEventListener('change', function () {
            saleFields.style.display = this.checked ? 'flex' : 'none';
        });
    }

    // ===== Image Preview Function =====
    function previewImage(input, previewContainer) {
        const file = input.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function (e) {
            previewContainer.innerHTML = `<img src="${e.target.result}"
                alt="Preview"
                style="width:100px; height:100px; object-fit:cover; border:1px solid #ccc; border-radius:6px; margin-bottom:6px;">`;
        };
        reader.readAsDataURL(file);
    }

    // ===== Main Image Preview =====
    const mainImageInput = document.getElementById("main_image");
    if (mainImageInput) {
        const mainPreview = document.createElement("div");
        mainPreview.id = "main-image-preview";
        mainPreview.className = "mb-2";
        mainImageInput.parentNode.insertBefore(mainPreview, mainImageInput);
        mainImageInput.addEventListener("change", function () {
            previewImage(this, mainPreview);
        });
    }

    // ===== Add multiple image inputs with preview & remove =====
    const addImageBtn = document.getElementById("addImageBtn");
    const imageContainer = document.getElementById("image-container");

    if (addImageBtn && imageContainer) {
        addImageBtn.addEventListener("click", function () {
            const wrapper = document.createElement("div");
            wrapper.className = "d-flex flex-column mb-2";

            const previewDiv = document.createElement("div");
            previewDiv.className = "image-preview mb-1";

            const inputWrapper = document.createElement("div");
            inputWrapper.className = "d-flex align-items-center";

            const newInput = document.createElement("input");
            newInput.type = "file";
            newInput.name = "images[]";
            newInput.className = "form-control form-control-sm me-2 flex-grow-1 image-input";

            const removeBtn = document.createElement("button");
            removeBtn.type = "button";
            removeBtn.className = "btn btn-outline-danger btn-sm";
            removeBtn.innerHTML = '<i class="fas fa-times"></i>';

            // Remove input
            removeBtn.addEventListener("click", function () {
                wrapper.remove();
            });

            // Image preview event
            newInput.addEventListener("change", function () {
                previewImage(this, previewDiv);
            });

            inputWrapper.appendChild(newInput);
            inputWrapper.appendChild(removeBtn);
            wrapper.appendChild(previewDiv);
            wrapper.appendChild(inputWrapper);
            imageContainer.appendChild(wrapper);
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
                <input type="text" name="features[]" class="form-control feature-input" placeholder="Enter a feature" value="${value}">
                <button type="button" class="btn btn-sm btn-outline-danger remove-feature-btn ml-1">×</button>
            </div>
        `;
        featuresContainer.appendChild(colDiv);

        // remove
        colDiv.querySelector(".remove-feature-btn").addEventListener("click", function () {
            colDiv.remove();
        });
    }

    if (addFeatureBtn) {
        addFeatureBtn.addEventListener("click", function () {
            addFeatureInput();
        });
    }

    // attach remove to any preloaded features
    featuresContainer.querySelectorAll(".remove-feature-btn").forEach(btn => {
        btn.addEventListener("click", function () {
            btn.closest(".feature-item").remove();
        });
    });

    // Initialize validation after a short delay to ensure jQuery is loaded
    setTimeout(initializeValidation, 100);
});

// ===== jQuery Validation =====
function initializeValidation() {
    if (typeof jQuery === 'undefined' || typeof jQuery.fn.validate === 'undefined') {
        console.error('jQuery or jQuery Validate not loaded');
        return;
    }

    // Custom method: file size (2MB max)
    jQuery.validator.addMethod("filesize", function (value, element, param) {
        if (this.optional(element)) return true;
        if (element.files && element.files[0]) {
            return element.files[0].size <= param;
        }
        return true;
    }, "File size must be less than 2MB");

    const $form = jQuery("#vehicleForm");

    $form.validate({
        ignore: [],
        errorClass: "is-invalid",
        validClass: "is-valid",
        errorElement: "span",
        errorPlacement: function (error, element) {
            error.addClass('text-danger small error-message');

            if (element.attr("name") === "images[]") {
                error.appendTo("#images-error");
            } else if (element.attr("name") === "features[]") {
                error.appendTo("#features-error");
            } else {
                error.insertAfter(element);
            }
        },
        rules: {
            name: { required: true, maxlength: 255 },
            model: { required: true, maxlength: 255 },
            year: { required: true, digits: true, minlength: 4, maxlength: 4 },
            type: { required: true, maxlength: 255 },
            location: { required: true, maxlength: 255 },
            description: { required: true },
            transmission: { required: true },
            fuel_type: { required: true },
            drive_type: { required: true },
            seats: { required: true, digits: true, min: 1 },
            mileage: { required: true, digits: true, min: 0 },
            engine: { required: true },
            main_image: { required: true, accept: "jpg,jpeg,png,gif,webp", filesize: 2 * 1024 * 1024 },
            "images[]": { required: true, accept: "jpg,jpeg,png,gif,webp", filesize: 2 * 1024 * 1024 },
            rental_price_day: { required: true, number: true, min: 0 },
            rental_price_week: { required: true, number: true, min: 0 },
            rental_price_month: { required: true, number: true, min: 0 },
            booking_lead_days: { required: true, digits: true, min: 0 },
            "features[]": { required: true, maxlength: 255 },
            status: { required: true },
            purchase_price: {
                required: function () { return jQuery("#forSaleToggle").is(":checked"); },
                number: true, min: 0
            },
            deposit_amount: {
                required: function () { return jQuery("#forSaleToggle").is(":checked"); },
                number: true, min: 0
            }
        },
        messages: {
            name: { required: "Vehicle name is required" },
            model: { required: "Model is required" },
            year: { required: "Year is required", digits: "Enter valid year" },
            type: { required: "Type is required" },
            location: { required: "Location is required" },
            description: { required: "Description is required" },
            transmission: { required: "Transmission is required" },
            fuel_type: { required: "Fuel type is required" },
            drive_type: { required: "Drive type is required" },
            seats: { required: "Seats is required" },
            mileage: { required: "Mileage is required" },
            engine: { required: "Engine is required" },
            main_image: { required: "Main image is required", accept: "Only image formats allowed" },
            "images[]": { required: "At least one additional image required", accept: "Only image formats allowed" },
            rental_price_day: { required: "Daily price required" },
            rental_price_week: { required: "Weekly price required" },
            rental_price_month: { required: "Monthly price required" },
            booking_lead_days: { required: "Booking lead days required" },
            "features[]": { required: "At least one feature is required" },
            status: { required: "Status is required" },
            purchase_price: { required: "Purchase price is required when For Sale" },
            deposit_amount: { required: "Deposit is required when For Sale" }
        },
        highlight: function (element) {
            jQuery(element).addClass('is-invalid').removeClass('is-valid');
        },
        unhighlight: function (element) {
            jQuery(element).removeClass('is-invalid').addClass('is-valid');
        },
        submitHandler: function (form) {
            const submitBtn = jQuery(form).find('button[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            form.submit();
        },
        invalidHandler: function (event, validator) {
            if (validator.errorList.length > 0) {
                const firstError = validator.errorList[0].element;
                jQuery('html, body').animate({
                    scrollTop: jQuery(firstError).offset().top - 100
                }, 500);
            }
        }
    });

    // Real-time validation
    jQuery(document).on('blur change', 'input, select, textarea', function () {
        jQuery(this).valid();
    });
}
</script>

@stop
