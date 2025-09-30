@extends('adminlte::page')

@section('title', 'System Setting')

@section('content')
    <h1 class="container fw-bold mt-0">System Setting</h1>

    <div class="container mt-3">
        <form action="{{ route('systemsetting.update') }}" method="POST">
            @csrf

            {{-- Stripe Settings --}}
            <div class="card shadow-sm my-3">
                <div class="card-header  text-black text-center py-2">
                    <h5 class="mb-0">Stripe Settings</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Publishable Key</label>
                        <input type="text" name="stripe_key" class="form-control"
                            value="{{ old('stripe_key', $setting->stripe_key ?? '') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Secret Key</label>
                        <input type="text" name="stripe_secret" class="form-control"
                            value="{{ old('stripe_secret', $setting->stripe_secret ?? '') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mode</label>
                        <select name="stripe_mode" class="form-control">
                            <option value="sandbox"
                                {{ ($setting->stripe_mode ?? 'sandbox') === 'sandbox' ? 'selected' : '' }}>Sandbox</option>
                            <option value="live" {{ ($setting->stripe_mode ?? '') === 'live' ? 'selected' : '' }}>Live
                            </option>
                        </select>
                    </div>

                    <div class="form-check form-switch">
                        <input type="hidden" name="stripe_enabled" value="0">
                        <input type="checkbox" name="stripe_enabled" value="1" class="form-check-input"
                            id="stripeToggle" {{ $setting->stripe_enabled ?? false ? 'checked' : '' }}>
                        <label class="form-check-label" for="stripeToggle">Enable Stripe</label>
                    </div>
                </div>
            </div>

          {{-- PayFast Settings --}}
<div class="card shadow-sm my-3">
    <div class="card-header text-black text-center py-2">
        <h5 class="mb-0">PayFast Settings</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Merchant ID</label>
                <input type="text" name="payfast_merchant_id" class="form-control"
                       value="{{ old('payfast_merchant_id', $setting->payfast_merchant_id ?? '') }}">
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Merchant Key</label>
                <input type="text" name="payfast_merchant_key" class="form-control"
                       value="{{ old('payfast_merchant_key', $setting->payfast_merchant_key ?? '') }}">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Passphrase</label>
                <input type="text" name="payfast_passphrase" class="form-control"
                       value="{{ old('payfast_passphrase', $setting->payfast_passphrase ?? '') }}">
            </div>

            <div class="mb-3 col-md-6">
            <label class="form-label">PayFast Live URL</label>
            <input type="url" name="payfast_live_url" class="form-control"
                   placeholder="https://www.payfast.co.za/eng/process"
                   value="{{ old('payfast_live_url', $setting->payfast_live_url ?? '') }}">
        </div>
        </div>
{{--
        <div class="mb-3">
            <label class="form-label">PayFast Live URL</label>
            <input type="url" name="payfast_live_url" class="form-control"
                   placeholder="https://www.payfast.co.za/eng/process"
                   value="{{ old('payfast_live_url', $setting->payfast_live_url ?? '') }}">
        </div> --}}


         <div class="col-md-12 mb-3">
                <label class="form-label">Mode</label>
                <select name="payfast_test_mode" class="form-control">
                    <option value="1" {{ $setting->payfast_test_mode ?? true ? 'selected' : '' }}>Sandbox (Test)</option>
                    <option value="0" {{ isset($setting) && !$setting->payfast_test_mode ? 'selected' : '' }}>Live</option>
                </select>
            </div>

        <div class="form-check form-switch">
            <input type="hidden" name="payfast_enabled" value="0">
            <input type="checkbox" name="payfast_enabled" value="1" class="form-check-input"
                   id="payfastToggle" {{ $setting->payfast_enabled ?? false ? 'checked' : '' }}>
            <label class="form-check-label" for="payfastToggle">Enable PayFast</label>
        </div>
    </div>
</div>

            {{-- SMTP Settings --}}
<div class="card shadow-sm my-3">
    <div class="card-header text-black text-center py-2">
        <h5 class="mb-0">SMTP / Email Settings</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">SMTP Username</label>
                <input type="text" name="mail_username" class="form-control"
                       value="{{ old('mail_username', $setting->mail_username ?? '') }}">
            </div>

     <div class="col-md-6 mb-3">
    <label class="form-label">SMTP Password</label>
    <div class="input-group">
        <input type="password" name="mail_password" id="mailPassword" class="form-control"
               value="{{ old('mail_password', $setting->mail_password ?? '') }}">
        <span class="input-group-text" id="togglePassword" style="cursor: pointer;">
            <i class="fas fa-eye"></i>
        </span>
    </div>
</div>


        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">SMTP Host</label>
                <input type="text" name="mail_host" class="form-control"
                       value="{{ old('mail_host', $setting->mail_host ?? '') }}">
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">SMTP Port</label>
                <input type="number" name="mail_port" class="form-control"
                       value="{{ old('mail_port', $setting->mail_port ?? '') }}">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Encryption</label>
                <select name="mail_encryption" class="form-control">
                    <option value="ssl" {{ ($setting->mail_encryption ?? 'ssl') === 'ssl' ? 'selected' : '' }}>SSL</option>
                    <option value="tls" {{ ($setting->mail_encryption ?? '') === 'tls' ? 'selected' : '' }}>TLS</option>
                    <option value="none" {{ ($setting->mail_encryption ?? '') === 'none' ? 'selected' : '' }}>None</option>
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">From Address</label>
                <input type="email" name="mail_from_address" class="form-control"
                       value="{{ old('mail_from_address', $setting->mail_from_address ?? '') }}">
            </div>
        </div>


        <div class="row">
           <div class="mb-3 col-md-6">
            <label class="form-label">From Name</label>
            <input type="text" name="mail_from_name" class="form-control"
                   value="{{ old('mail_from_name', $setting->mail_from_name ?? '') }}">
        </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Owner Address(Optional)</label>
                <input type="email" name="mail_owner_address" class="form-control"
                       value="{{ old('mail_owner_address', $setting->mail_owner_address ?? '') }}">
            </div>
        </div>



        <div class="form-check form-switch">
            <input type="hidden" name="mail_enabled" value="0">
            <input type="checkbox" name="mail_enabled" value="1" class="form-check-input"
                   id="mailToggle" {{ $setting->mail_enabled ?? false ? 'checked' : '' }}>
            <label class="form-check-label" for="mailToggle">Enable Email</label>
        </div>
    </div>
</div>


            {{-- Save button --}}
            <div class="d-flex justify-content-end mb-3 ">
                <button type="submit" class="btn btn-dark mb-3">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
@stop


@section('js')
<script>
    document.getElementById('togglePassword').addEventListener('click', function () {
        const input = document.getElementById('mailPassword');
        const icon = this.querySelector('i');
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        } else {
            input.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: '{{ session('success') }}',
            confirmButtonText: 'OK'
        });
    @endif
</script>


@endsection
