@extends('adminlte::page')

@section('title', 'Vehicles Management')

@section('content_header')
    <h1 class="text-bold container">Stripe Configuration</h1>
@stop

@section('content')
    <div class="container-fluid mt-4">

        {{-- Stripe Payment Settings Form --}}
        <div class="card shadow-sm">
            <div class="card-body">
                <form action="{{ route('stripe.update') }}" method="POST">
                    @csrf

                    <!-- Publishable Key -->
                    <div class="mb-3">
                        <label class="form-label">Publishable Key</label>
                        <input type="text" name="stripe_key" class="form-control w-100"
                               value="{{ old('stripe_key', $stripe->stripe_key ?? '') }}">
                    </div>

                    <!-- Secret Key -->
                    <div class="mb-3">
                        <label class="form-label">Secret Key</label>
                        <input type="text" name="stripe_secret" class="form-control w-100"
                               value="{{ old('stripe_secret', $stripe->stripe_secret ?? '') }}">
                    </div>

                    <!-- Mode Selection -->
                    <div class="mb-3">
                        <label class="form-label">Mode</label>
                        <select name="stripe_mode" class="form-control">
                            <option value="sandbox" {{ ($stripe->stripe_mode ?? 'sandbox') === 'sandbox' ? 'selected' : '' }}>Sandbox</option>
                            <option value="live" {{ ($stripe->stripe_mode ?? '') === 'live' ? 'selected' : '' }}>Live</option>
                        </select>
                    </div>

                    <!-- Enable Toggle -->
                    <div class="form-check form-switch mb-4">
                        <input type="hidden" name="stripe_enabled" value="0">
                        <input type="checkbox" name="stripe_enabled" value="1" class="form-check-input"
                               id="stripeToggle" {{ ($stripe->stripe_enabled ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="stripeToggle">
                            Enable Stripe
                        </label>
                    </div>

                    <!-- Footer -->
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-dark">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if(session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '{{ session('success') }}',
                confirmButtonColor: '#000',
            });
        </script>
    @endif

    @if($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                html: `{!! implode('<br>', $errors->all()) !!}`,
                confirmButtonColor: '#000',
            });
        </script>
    @endif
@stop
