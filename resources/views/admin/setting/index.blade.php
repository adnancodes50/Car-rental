@extends('adminlte::page')

@section('title', 'Payment Methods Management')

@section('content_header')
<h1 class="text-bold container">Payment Methods Management</h1>
@stop

@section('content')
<div class="container-fluid mt-4">

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table align-middle text-center table-borderless table-hover">
                <thead class="bg-dark text-white">
                    <tr>
                        <th class="text-start">Payment Method</th>
                        <th class="text-start">Description</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Stripe -->
                    <tr class="align-middle">
                        <td class="text-start d-flex align-items-center">
                            <img src="{{ asset('images/stripe.png') }}" alt="Stripe" style="width: 60px; height:auto;" class="me-3">
                            <span class="fw-bold">Stripe</span>
                        </td>
                        <td class="text-start">Manage Stripe checkout settings. Supports Visa, Mastercard, Amex.</td>
                        <td>
    <a href="{{ route('settings.payments.stripe') }}" class="btn btn-outline-info btn-sm">
        <i class="fas fa-eye"></i> View
    </a>
</td>

                    </tr>

                    <!-- PayFast -->
                    <tr class="align-middle">
                        <td class="text-start d-flex align-items-center">
                            <img src="{{ asset('images/payfast.png') }}" alt="PayFast" style="width: 60px; height:auto;" class="me-3">
                            <span class="fw-bold">PayFast</span>
                        </td>
                        <td class="text-start">Manage PayFast checkout settings. Supports South Africa payments.</td>
                       <td>
    <a href="{{ route('settings.payments.payfast') }}" class="btn btn-outline-info btn-sm">
        <i class="fas fa-eye"></i> View
    </a>
</td>

                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>



@stop

@section('css')
<style>
    table.table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    @if(session('status'))
        Swal.fire({
            icon: 'success',
            title: 'Updated!',
            text: '{{ session('status') }}',
            timer: 2000,
            showConfirmButton: false
        });
    @endif
</script>
@stop
