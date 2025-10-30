@extends('adminlte::page')

@section('title', 'Edit Equipment')

@section('content_header')
    <h1>Edit Equipment</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('equipment.update', $equipment->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Name --}}
            <div class="form-group">
                <label>Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control"
                       value="{{ old('name', $equipment->name) }}" required>
                @error('name') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            {{-- Description --}}
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control">{{ old('description', $equipment->description) }}</textarea>
                @error('description') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            {{-- Category --}}
            <div class="form-group">
                <label>Category <span class="text-danger">*</span></label>
                <select name="category_id" class="form-control" required>
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}"
                            {{ old('category_id', $equipment->category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            {{-- Status --}}
            <div class="form-group">
                <label>Status <span class="text-danger">*</span></label>
                <select name="status" class="form-control" required>
                    <option value="active" {{ old('status', $equipment->status) === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $equipment->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('status') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            {{-- Image Upload --}}
            <div class="form-group">
                <label>Image</label>
                @if($equipment->image)
                    <div class="mb-2">
                        <img id="currentImage" src="{{ asset('storage/'.$equipment->image) }}"
                             class="img-thumbnail" style="max-height:200px;">
                    </div>
                @endif
                <input type="file" name="image_file" class="form-control-file" accept="image/*" onchange="previewImage(event)">
                @error('image_file') <span class="text-danger">{{ $message }}</span> @enderror
                <div class="mt-2">
                    <img id="imagePreview" src="#" alt="Preview Image" class="img-fluid"
                         style="display:none; max-height:300px;">
                </div>
            </div>

            <hr>

            {{-- Stock per Location --}}
            <h5 class="mb-3">Stock per Location</h5>
            <div class="form-row">
                @foreach($locations as $location)
                    @php
                        $existingStock = $equipment->stocks->firstWhere('location_id', $location->id);
                    @endphp
                    <div class="form-group col-md-6">
                        <label>{{ $location->name }}</label>
                        <input type="number"
                               name="stocks[{{ $location->id }}]"
                               class="form-control"
                               min="0"
                               value="{{ old('stocks.'.$location->id, $existingStock->stock ?? 0) }}">
                    </div>
                @endforeach
            </div>

            <hr>

            {{-- Buttons --}}
            <div class="form-group d-flex justify-content-between mt-4">
                <a href="{{ route('equipment.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Cancel
                </a>
                <button type="submit" class="btn btn-dark">
                    <i class="fas fa-save"></i> Update Equipment
                </button>
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

@if (session('success'))
<script>
Swal.fire({
    icon: 'success',
    title: 'Success',
    text: '{{ session('success') }}',
    confirmButtonColor: '#343a40',
});
</script>
@endif

@if (session('error'))
<script>
Swal.fire({
    icon: 'error',
    title: 'Error',
    text: '{{ session('error') }}',
    confirmButtonColor: '#343a40',
});
</script>
@endif
@stop
