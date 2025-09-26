@extends('adminlte::page')

@section('title', 'PayFast Settings')

@section('content_header')
    <h1 class="text-bold container">PayFast Settings</h1>
@stop

@section('content')
<div class="container-fluid mt-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('payfast.update') }}" method="POST">
                @csrf

                <!-- Merchant ID -->
                <div class="mb-3">
                    <label class="form-label">Merchant ID</label>
                    <input type="text" name="merchant_id" class="form-control"
                           value="{{ old('merchant_id', $payfast->merchant_id ?? '') }}">
                </div>

                <!-- Merchant Key -->
                <div class="mb-3">
                    <label class="form-label">Merchant Key</label>
                    <input type="text" name="merchant_key" class="form-control"
                           value="{{ old('merchant_key', $payfast->merchant_key ?? '') }}">
                </div>

                <!-- Passphrase -->
                <div class="mb-3">
                    <label class="form-label">Passphrase (optional)</label>
                    <input type="text" name="passphrase" class="form-control"
                           value="{{ old('passphrase', $payfast->passphrase ?? '') }}">
                </div>

                <!-- Mode -->
                <div class="mb-3">
                    <label class="form-label">Mode</label>
                    <select name="test_mode" class="form-control">
                        <option value="1" {{ ($payfast->test_mode ?? true) ? 'selected' : '' }}>Sandbox (Test)</option>
                        <option value="0" {{ isset($payfast) && !$payfast->test_mode ? 'selected' : '' }}>Live</option>
                    </select>
                </div>

                <!-- Enable Toggle -->
                <div class="form-check form-switch mb-4">
                    <input type="hidden" name="enabled" value="0">
                    <input type="checkbox" name="enabled" value="1"
                           class="form-check-input"
                           id="payfastToggle"
                           {{ ($payfast->enabled ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="payfastToggle">
                        Enable PayFast
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
