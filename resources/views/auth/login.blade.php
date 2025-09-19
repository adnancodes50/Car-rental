@extends('adminlte::auth.login')

@section('title', 'Custom Login')

{{-- Dark background for whole page --}}
@section('classes_body', 'login-page py-5 bg-black' )

@section('auth_header')
    <h2 class="text-center" style="color: black;">Welcome Back!</h2>
@endsection


@section('auth_body')
    <form action="{{ route('login') }}" method="post">
        @csrf

        {{-- Email field --}}
        <div class="input-group mb-3">
            <input type="email" name="email" class="form-control" placeholder="Email" required>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-envelope"></span>
                </div>
            </div>
        </div>

        {{-- Password field --}}
        <div class="input-group mb-3">
            <input type="password" name="password" class="form-control" placeholder="Password" required>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-lock"></span>
                </div>
            </div>
        </div>

        {{-- Login button --}}
        <button type="submit" class="btn btn-black bg-black btn-block">Sign In</button>
    </form>
@endsection

{{-- Override footer to remove "Forgot password" and "Register" --}}
@section('auth_footer')
@endsection
