@extends('adminlte::page')

@section('title', 'Landing Page Settings')

@section('content_header')
    <h1>Landing Page Management</h1>
@stop

@section('content')
<div class="container-fluid">

    {{-- Hero Background Image --}}
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">Hero Background Image</h3>




        </div>
        <div class="card-body">
            <form action="{{ route('admin.landing-settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Current Hero Image --}}
                <div class="form-group">
                    <label>Current Background Image</label>
                    <div class="position-relative w-100 mb-3" style="height: 250px;">

    @php
        $preview = $settings && $settings->hero_image_path
            ? Storage::url($settings->hero_image_path)
            : asset('images/bg.jpg');
    @endphp


                        <img id="hero_preview" src="{{ $preview }}" class="w-100 h-100 object-fit-cover rounded" alt="Hero">

                        {{-- <div class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-center align-items-center bg-dark bg-opacity-50 text-center text-white rounded">
                            <h2 class="mb-1" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.8);">Built for where</h2>
                            <span class="fw-bold text-warning" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.8);">adventure was born</span>
                        </div> --}}
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
                                @error('email_btn_text')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label>Email Link</label>
                                <input type="text" name="email_link" class="form-control"
                                    value="{{ old('email_link', $settings->email_link ?? '') }}">
                                @error('email_link')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        {{-- Phone --}}
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label>Phone Button Text</label>
                                <input type="text" name="phone_btn_text" class="form-control"
                                    value="{{ old('phone_btn_text', $settings->phone_btn_text ?? '') }}">
                                @error('phone_btn_text')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label>Phone Link</label>
                                <input type="text" name="phone_link" class="form-control"
                                    value="{{ old('phone_link', $settings->phone_link ?? '') }}">
                                @error('phone_link')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        {{-- WhatsApp --}}
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label>WhatsApp Button Text</label>
                                <input type="text" name="whatsapp_btn_text" class="form-control"
                                    value="{{ old('whatsapp_btn_text', $settings->whatsapp_btn_text ?? '') }}">
                                @error('whatsapp_btn_text')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label>WhatsApp Link</label>
                                <input type="text" name="whatsapp_link" class="form-control"
                                    value="{{ old('whatsapp_link', $settings->whatsapp_link ?? '') }}">
                                @error('whatsapp_link')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                    </div>
                </div>

                <button type="submit" class="btn btn-success mt-3">
                    <i class="fas fa-save"></i> Save Settings
                </button>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.object-fit-cover { object-fit: cover; }
.position-relative { position: relative; }
.position-absolute { position: absolute; }
.top-0 { top: 0; }
.start-0 { left: 0; }
.w-100 { width: 100%; }
.h-100 { height: 100%; }
.bg-opacity-50 { background-color: rgba(0,0,0,0.5); }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ===== Preview Hero Image =====
    const heroInput = document.getElementById('hero_image');
    const heroPreview = document.getElementById('hero_preview');

    if (heroInput && heroPreview) {
        heroInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(ev) {
                heroPreview.src = ev.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    // ===== SweetAlert Success Message =====
    @if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: @json(session('success')),
        timer: 2500,
        showConfirmButton: false
    });
    @endif

    // ===== SweetAlert Error Message =====
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

