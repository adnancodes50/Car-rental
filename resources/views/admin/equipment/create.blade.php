@extends('adminlte::page')

@section('title', 'Create Equipment')

@section('content_header')
<h1>Create Equipment</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('equipment.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Row 1: Name + Stock --}}
            <div class="row">
                <div class="form-group col-md-6">
                    <label>Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="form-group col-md-6">
                    <label>Stock <span class="text-danger">*</span></label>
                    <input type="number" name="stock" class="form-control" value="{{ old('stock', 0) }}" min="0" required>
                    @error('stock') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Row 2: Category + Location --}}
            <div class="row">
                <div class="form-group col-md-6">
                    <label>Category <span class="text-danger">*</span></label>
                    <select name="category_id" class="form-control" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="form-group col-md-6">
                    <label>Location <span class="text-danger">*</span></label>
                    <select name="location_id" class="form-control" required>
                        <option value="">Select Location</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
                                {{ $location->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('location_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Row 3: Status --}}
            <div class="form-group">
                <label>Status <span class="text-danger">*</span></label>
                <select name="status" class="form-control" required>
                    <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('status') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            {{-- Row 4: Image (full-width with preview) --}}
            <div class="form-group">
                <label>Image</label>
                <input type="file" name="image_file" class="form-control-file" accept="image/*" onchange="previewImage(event)">
                @error('image_file') <span class="text-danger">{{ $message }}</span> @enderror
                <div class="mt-2">
                    <img id="imagePreview" src="#" alt="Preview Image" class="img-fluid" style="display:none; max-height:300px;">
                </div>
            </div>

            {{-- Buttons --}}
            <div class="form-group d-flex justify-content-between mt-4">
                <a href="{{ route('equipment.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-dark">Save Equipment</button>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
<script>
function previewImage(event) {
    const input = event.target;
    const preview = document.getElementById('imagePreview');
    if(input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.src = '#';
        preview.style.display = 'none';
    }
}
</script>
@stop
