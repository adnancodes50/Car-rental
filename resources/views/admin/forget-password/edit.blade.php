@extends('adminlte::page')

@section('title', 'Forget Password Setting')

@section('content_header')
    <h1>Change Your Password</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('forget-password.update') }}" method="POST" id="passwordForm">
            @csrf

            {{-- Current Password --}}
            <div class="form-group position-relative">
                <label>Current Password <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="password" name="current_password" class="form-control password-input" required>
                    <div class="input-group-append">
                        <span class="input-group-text bg-light toggle-password" style="cursor:pointer;">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>
                @error('current_password') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            {{-- New Password --}}
            <div class="form-group position-relative">
                <label>New Password <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="password" name="new_password" class="form-control password-input" required>
                    <div class="input-group-append">
                        <span class="input-group-text bg-light toggle-password" style="cursor:pointer;">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>
                @error('new_password') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            {{-- Confirm New Password --}}
            <div class="form-group position-relative">
                <label>Confirm New Password <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="password" name="new_password_confirmation" class="form-control password-input" required>
                    <div class="input-group-append">
                        <span class="input-group-text bg-light toggle-password" style="cursor:pointer;">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>
            </div>

            {{-- ✅ Right-aligned button --}}
            <div class="text-right mt-3">
                <button type="submit" class="btn btn-dark">
                    <i class="fas fa-save mr-1"></i> Update Password
                </button>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
{{-- ✅ Include required libraries --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/additional-methods.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- ✅ SweetAlert messages --}}
@if (session('success'))
<script>
Swal.fire({
    icon: 'success',
    title: 'Success',
    text: '{{ session('success') }}',
    confirmButtonColor: '#343a40',
});
</script>
@endif

@if (session('error'))
<script>
Swal.fire({
    icon: 'error',
    title: 'Error',
    text: '{{ session('error') }}',
    confirmButtonColor: '#343a40',
});
</script>
@endif

{{-- 👁️ Toggle password visibility --}}
<script>
document.querySelectorAll('.toggle-password').forEach(toggle => {
    toggle.addEventListener('click', function() {
        const input = this.closest('.input-group').querySelector('.password-input');
        const icon = this.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
});
</script>

{{-- ✅ jQuery Validation for password form --}}
<script>
$(function() {
    $('#passwordForm').validate({
        rules: {
            current_password: {
                required: true,
                minlength: 6
            },
            new_password: {
                required: true,
                minlength: 8
            },
            new_password_confirmation: {
                required: true,
                equalTo: "input[name='new_password']"
            }
        },
        messages: {
            current_password: {
                required: "Please enter your current password.",
                minlength: "Password must be at least 6 characters long."
            },
            new_password: {
                required: "Please enter a new password.",
                minlength: "New password must be at least 8 characters long."
            },
            new_password_confirmation: {
                required: "Please confirm your new password.",
                equalTo: "Passwords do not match."
            }
        },
        errorElement: 'span',
        errorClass: 'text-danger',
        errorPlacement: function(error, element) {
            const group = element.closest('.input-group');
            if (group.length) {
                error.insertAfter(group);
            } else {
                error.insertAfter(element);
            }
        },
        highlight: function(element) {
            $(element).addClass('is-invalid');
        },
        unhighlight: function(element) {
            $(element).removeClass('is-invalid');
        },
        submitHandler: function(form) {
            form.submit(); // Proceed with normal Laravel submission
        }
    });
});
</script>
@stop
