@extends('adminlte::page')

@section('title', 'PayFast Settings')

@section('content_header')
    <h1 class="text-bold container">PayFast Settings</h1>
@stop

@section('content')
<div class="container-fluid mt-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('settings.payments.payfast.update') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Merchant ID</label>
                    <input type="text" name="PAYFAST_MERCHANT_ID" class="form-control"
                           value="{{ old('PAYFAST_MERCHANT_ID', env('PAYFAST_MERCHANT_ID')) }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Merchant Key</label>
                    <input type="text" name="PAYFAST_MERCHANT_KEY" class="form-control"
                           value="{{ old('PAYFAST_MERCHANT_KEY', env('PAYFAST_MERCHANT_KEY')) }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Passphrase (optional)</label>
                    <input type="text" name="PAYFAST_PASSPHRASE" class="form-control"
                           value="{{ old('PAYFAST_PASSPHRASE', env('PAYFAST_PASSPHRASE')) }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Mode</label>
                    <select name="PAYFAST_TEST_MODE" class="form-control">
                        <option value="true" {{ env('PAYFAST_TEST_MODE') === 'true' ? 'selected' : '' }}>Sandbox (Test)</option>
                        <option value="false" {{ env('PAYFAST_TEST_MODE') === 'false' ? 'selected' : '' }}>Live</option>
                    </select>
                </div>

                <div class="form-check form-switch mb-4">
                    <input type="hidden" name="PAYFAST_ENABLED" value="false">
                    <input type="checkbox" name="PAYFAST_ENABLED" value="true"
                           class="form-check-input"
                           id="payfastToggle"
                           {{ config('payments.payfast.enabled') ? 'checked' : '' }}>
                    <label class="form-check-label" for="payfastToggle">
                        Enable PayFast
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
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(session('status'))
<script>
    Swal.fire({
        title: "Success!",
        text: "{{ session('status') }}",
        icon: "success",
        confirmButtonText: "OK"
    });
</script>
@endif

@if($errors->any())
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
