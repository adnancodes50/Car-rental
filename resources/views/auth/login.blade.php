@extends('adminlte::auth.login')

@section('title', 'Custom Login')

{{-- Dark background for whole page --}}
@section('classes_body', 'login-page py-5 bg-black')

@section('auth_body')
    <div class="card shadow-sm p-4 rounded-3 bg-white" style="max-width: 400px; margin: auto;">
        {{-- <h3 class="text-center mb-4 fw-bold">Welcome Back</h3> --}}

        <form action="{{ route('login') }}" method="post">
            @csrf

            {{-- Email field --}}
            <div class="form-group mb-3">
                <label for="email" class="form-label fw-semibold">Email</label>
                <input type="email" name="email" id="email"
                       class="form-control border rounded px-3 py-2"
                       placeholder="Enter your email" required autofocus>
            </div>

            {{-- Password field --}}
            <div class="form-group mb-3">
                <label for="password" class="form-label fw-semibold">Password</label>
                <input type="password" name="password" id="password"
                       class="form-control border rounded px-3 py-2"
                       placeholder="Enter your password" required>
            </div>

            {{-- Remember me --}}
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="form-check">
                    <input type="checkbox" id="remember" name="remember" class="form-check-input">
                    <label for="remember" class="form-check-label">Remember me</label>
                </div>
                {{-- Forgot password link (optional, uncomment if needed) --}}
                {{-- <a href="{{ route('password.request') }}" class="small text-decoration-none">Forgot password?</a> --}}
            </div>

            {{-- Login button (full width) --}}
            <button type="submit" class="btn btn-dark w-100 py-2 fw-bold rounded">
                Log In
            </button>
        </form>
    </div>
@endsection

{{-- Remove default footer --}}
@section('auth_footer')
@endsection
