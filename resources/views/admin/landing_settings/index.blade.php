@extends('adminlte::page')

@section('title', 'Landing Page Settings')

@section('content_header')
<h1 class="container text-bold">Landing Page Management</h1>
@stop

@section('content')
<div class="container-fluid">

    {{-- Hero Background Image --}}
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">Hero Background Image</h3>
        </div>
        <div class="card-body">
            <form id="landingForm" action="{{ route('admin.landing-settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Current Hero Image --}}
                <div class="form-group">
                    <label>Current Background Image</label>
                    <div class="position-relative w-100 mb-3" style="height: 250px;">
                        @php
                            $preview = $settings && $settings->hero_image_path
                                ? asset($settings->hero_image_path)
                                : asset('images/bg.jpg');
                        @endphp
                        <img id="hero_preview" src="{{ $preview }}" class="w-100 h-100 object-fit-cover rounded" alt="Hero">
                    </div>
                </div>

                {{-- Upload --}}
                <div class="form-group">
                    <label>Upload New Image</label>
                    <input accept="image/*" type="file" name="hero_image" id="hero_image" class="form-control">
                </div>

                {{-- Contact Buttons --}}
                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="card-title">Contact Buttons</h3>
                    </div>
                    <div class="card-body">

                        {{-- Email --}}
                        <div class="row">
                            <div class="col-md-6">
                                <label>Email Button Text</label>
                                <input type="text" name="email_btn_text" class="form-control"
                                    value="{{ old('email_btn_text', $settings->email_btn_text ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label>Email Link</label>
                                <input type="text" name="email_link" class="form-control"
                                    value="{{ old('email_link', $settings->email_link ?? '') }}">
                            </div>
                        </div>

                        {{-- Phone --}}
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label>Phone Button Text</label>
                                <input type="text" name="phone_btn_text" class="form-control"
                                    value="{{ old('phone_btn_text', $settings->phone_btn_text ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label>Phone Link</label>
                                <input type="text" name="phone_link" class="form-control" placeholder = "+277117909863"
                                    value="{{ old('phone_link', $settings->phone_link ?? '') }}">
                            </div>
                        </div>

                        {{-- WhatsApp --}}
                        {{-- <div class="row mt-3">
                            <div class="col-md-6">
                                <label>WhatsApp Button Text</label>
                                <input type="text" name="whatsapp_btn_text" class="form-control"
                                    value="{{ old('whatsapp_btn_text', $settings->whatsapp_btn_text ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label>WhatsApp Link</label>
                                <input type="text" name="whatsapp_link" class="form-control" placeholder = "+270017909863"
                                    value="{{ old('whatsapp_link', $settings->whatsapp_link ?? '') }}">
                            </div>
                        </div> --}}

                    </div>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-dark btn-md">
                        <i class="fas fa-save me-1"></i> Save Settings
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .object-fit-cover { object-fit: cover; }
    .error { color: #dc3545; font-size: 0.9em; margin-top: 4px; display: block; }
</style>
@stop

@section('js')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/additional-methods.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // ===== Preview Hero Image =====
    const heroInput = document.getElementById('hero_image');
    const heroPreview = document.getElementById('hero_preview');

    if (heroInput && heroPreview) {
        heroInput.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function (ev) {
                heroPreview.src = ev.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    // ===== jQuery Validation (inline errors only) =====
    if (window.jQuery && $.validator && !$.validator.methods.filesize) {
       $.validator.addMethod("filesize", function (value, element, filesize) {
    if (!element.files || !element.files.length) {
        return true;
    }
    return element.files[0].size <= 5242880; // 5 MB
}, "File must be smaller than 5 MB.");

    }
$("#landingForm").validate({
    rules: {
        email_btn_text: { required: true, maxlength: 255 },
        email_link: { required: true, email: true },

        phone_btn_text: { required: true, maxlength: 255 },
        phone_link: {
            required: true,
            pattern: /^\+\d{8,15}$/  // ✅ Must start with + and contain 8–15 digits
        },

        whatsapp_btn_text: { required: true, maxlength: 255 },
        whatsapp_link: {
            required: true,
            pattern: /^\+\d{8,15}$/  // ✅ Same rule for WhatsApp: + followed by digits
        },

        hero_image: {
            accept: "jpg,jpeg,png,gif",
            filesize: 5242880 // 5 MB max
        }
    },
    messages: {
        email_btn_text: { required: "Email button text is required" },
        email_link: { required: "Email is required", email: "Enter a valid email address" },

        phone_btn_text: { required: "Phone button text is required" },
        phone_link: {
            required: "Phone number is required",
            pattern: "Phone number must start with + and contain only digits (e.g., +1234567890)"
        },

        whatsapp_btn_text: { required: "WhatsApp button text is required" },
        whatsapp_link: {
            required: "WhatsApp number is required",
            pattern: "WhatsApp number must start with + and contain only digits (e.g., +1234567890)"
        },

        hero_image: {
            accept: "Only image formats allowed (jpg, jpeg, png, gif)",
            filesize: "File must be smaller than 5 MB"
        }
    },
    errorPlacement: function(error, element) {
        error.insertAfter(element);
    },
    submitHandler: function(form) {
        form.submit();
    }
});


    // ===== SweetAlert ONLY for backend messages =====
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: @json(session('success')),
            timer: 2500,
            showConfirmButton: false
        });
    @endif

    @if($errors->any())
        let errorMessages = @json($errors->all());
        Swal.fire({
            icon: 'error',
            title: 'Oops! Something went wrong',
            html: errorMessages.map(msg => `<p>${msg}</p>`).join(''),
            confirmButtonText: 'OK',
        });
    @endif
});
</script>
@stop
