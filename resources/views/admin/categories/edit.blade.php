@extends('adminlte::page')

@section('title', 'Edit Category')

@section('content_header')
<h1>Edit Category</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('categories.update', $category->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Name --}}
            <div class="form-group">
                <label>Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $category->name) }}" required>
                @error('name') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            {{-- Image --}}
            <div class="form-group">
                <label>Image</label>
                @if($category->image)
                    <div class="mb-2">
                        <img src="{{ asset('storage/'.$category->image) }}" width="100" class="img-thumbnail">
                    </div>
                @endif
                <input type="file" name="image_file" class="form-control-file">
                @error('image') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            {{-- Status --}}
            <div class="form-group">
                <label>Status <span class="text-danger">*</span></label>
                <select name="status" class="form-control" required>
                    <option value="active" {{ (old('status', $category->status) === 'active') ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ (old('status', $category->status) === 'inactive') ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('status') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            {{-- Pricing --}}
            @php
                $daily_price = old('daily_price', $category->daily_price);
                $weekly_price = old('weekly_price', $category->weekly_price);
                $monthly_price = old('monthly_price', $category->monthly_price);
                $is_for_sale = old('is_for_sale', $category->is_for_sale);
                $deposit_price = old('deposit_price', $category->deposit_price);
                $total_amount = old('total_amount', $category->total_amount);
            @endphp

            <div class="form-group">
                <label>Daily Price</label>
                <input type="number" step="0.01" name="daily_price" class="form-control" value="{{ $daily_price }}">
                @error('daily_price') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label>Weekly Price</label>
                <input type="number" step="0.01" name="weekly_price" class="form-control" value="{{ $weekly_price }}">
                @error('weekly_price') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label>Monthly Price</label>
                <input type="number" step="0.01" name="monthly_price" class="form-control" value="{{ $monthly_price }}">
                @error('monthly_price') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            {{-- For Sale Checkbox --}}
            <div class="form-group form-check">
                <input type="checkbox" class="form-check-input" id="is_for_sale" name="is_for_sale" {{ $is_for_sale ? 'checked' : '' }}>
                <label class="form-check-label" for="is_for_sale">Is For Sale</label>
            </div>

            <div class="sale-fields">
                <div class="form-group">
                    <label>Deposit Price</label>
                    <input type="number" step="0.01" name="deposit_price" class="form-control" value="{{ $deposit_price }}">
                    @error('deposit_price') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Total Amount</label>
                    <input type="number" step="0.01" name="total_amount" class="form-control" value="{{ $total_amount }}">
                    @error('total_amount') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Buttons --}}
            <div class="form-group d-flex justify-content-between mt-4">
                <a href="{{ route('categories.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-dark">Update Category</button>
            </div>
        </form>
    </div>
</div>

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const saleCheckbox = document.getElementById('is_for_sale');
        const saleFields = document.querySelector('.sale-fields');

        const toggleSaleFields = (checked) => {
            saleFields.style.display = checked ? 'block' : 'none';
            if (!checked) {
                saleFields.querySelectorAll('input').forEach(i => i.value = '');
            }
        };

        toggleSaleFields(saleCheckbox.checked);
        saleCheckbox.addEventListener('change', function () {
            toggleSaleFields(this.checked);
        });
    });
</script>
@endpush
@stop
