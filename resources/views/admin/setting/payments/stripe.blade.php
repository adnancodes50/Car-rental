@extends('adminlte::page')

@section('title', 'Stripe Settings')

@section('content_header')
    <h1 class="text-bold container">Stripe Settings</h1>
@stop

@section('content')
    <div class="container-fluid mt-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <form action="{{ route('settings.payments.stripe.update') }}" method="POST">
                    @csrf







                    <!-- Publishable Key -->
                    <div class="mb-3">
                        <label class="form-label">Publishable Key</label>
                        <input type="text" name="stripe_key" class="form-control w-100"
                            value="{{ old('stripe_key', config('payments.stripe.key')) }}">
                    </div>

                    <!-- Secret Key -->
                    <div class="mb-3">
                        <label class="form-label">Secret Key</label>
                        <input type="text" name="stripe_secret" class="form-control w-100"
                            value="{{ old('stripe_secret', config('payments.stripe.secret')) }}">
                    </div>


                     <!-- Mode Selection -->
                    <div class="mb-3">
                        <label class="form-label">Mode</label>
                        <select name="stripe_mode" class="form-control">
                            <option value="sandbox" {{ config('payments.stripe.mode') === 'sandbox' ? 'selected' : '' }}>
                                Sandbox
                            </option>
                            <option value="live" {{ config('payments.stripe.mode') === 'live' ? 'selected' : '' }}>
                                Live
                            </option>
                        </select>
                    </div>

                    <!-- Enable Toggle -->
                    <div class="form-check form-switch mb-4">
                        <input type="hidden" name="stripe_enabled" value="false">
                        <input type="checkbox" name="stripe_enabled" value="true" class="form-check-input"
                            id="stripeToggle" {{ config('payments.stripe.enabled') ? 'checked' : '' }}>
                        <label class="form-check-label" for="stripeToggle">
                            Enable Stripe
                        </label>
                    </div>

                    <!-- Footer -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('settings.payments.edit') }}" class="btn btn-outline-secondary">
                            Back
                        </a>
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
    <!-- Include SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if (session('status'))
        <script>
            Swal.fire({
                title: "Success!",
                text: "{{ session('status') }}",
                icon: "success",
                confirmButtonText: "OK"
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            Swal.fire({
                title: "Error!",
                html: "{!! implode('<br>', $errors->all()) !!}",
                icon: "error",
                confirmButtonText: "OK"
            });
        </script>
    @endif
@endsection
