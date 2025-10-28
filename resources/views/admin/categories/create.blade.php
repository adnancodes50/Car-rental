@extends('adminlte::page')

@section('title', 'Create Category')

@section('content_header')
<h1>Create Category</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('categories.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label>Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                @error('name') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label>Image</label>
                <input type="file" name="image_file" class="form-control-file">
                @error('image') <span class="text-danger">{{ $message }}</span> @enderror
                @error('image_file') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label>Status <span class="text-danger">*</span></label>
                <select name="status" class="form-control" required>
                    <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('status') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            {{-- Pricing & Sale Fields --}}
            <div class="form-group">
                <label>Daily Price</label>
                <input type="number" step="0.01" name="daily_price" class="form-control" value="{{ old('daily_price') }}">
                @error('daily_price') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label>Weekly Price</label>
                <input type="number" step="0.01" name="weekly_price" class="form-control" value="{{ old('weekly_price') }}">
                @error('weekly_price') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label>Monthly Price</label>
                <input type="number" step="0.01" name="monthly_price" class="form-control" value="{{ old('monthly_price') }}">
                @error('monthly_price') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="form-group form-check">
                <input type="checkbox" class="form-check-input" id="is_for_sale" name="is_for_sale" {{ old('is_for_sale') ? 'checked' : '' }}>
                <label class="form-check-label" for="is_for_sale">Is For Sale</label>
            </div>

            <div class="form-group sale-field {{ old('is_for_sale') ? '' : 'd-none' }}">
                <label>Deposit Price</label>
                <input type="number" step="0.01" name="deposit_price" class="form-control" value="{{ old('deposit_price') }}" {{ old('is_for_sale') ? '' : 'disabled' }}>
                @error('deposit_price') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="form-group sale-field {{ old('is_for_sale') ? '' : 'd-none' }}">
                <label>Total Amount</label>
                <input type="number" step="0.01" name="total_amount" class="form-control" value="{{ old('total_amount') }}" {{ old('is_for_sale') ? '' : 'disabled' }}>
                @error('total_amount') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="form-group d-flex justify-content-between mt-4">
                <a href="{{ route('categories.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-dark">Save Category</button>
            </div>
        </form>
    </div>
</div>
@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const saleCheckbox = document.getElementById('is_for_sale');
        const saleFields = document.querySelectorAll('.sale-field');

        const toggleSaleFields = (isChecked) => {
            saleFields.forEach((field) => {
                const input = field.querySelector('input');
                field.classList.toggle('d-none', !isChecked);

                if (input) {
                    input.disabled = !isChecked;

                    if (!isChecked) {
                        input.value = '';
                    }
                }
            });
        };

        toggleSaleFields(saleCheckbox.checked);

        saleCheckbox.addEventListener('change', function () {
            toggleSaleFields(this.checked);
        });
    });
</script>
@endpush
@stop
