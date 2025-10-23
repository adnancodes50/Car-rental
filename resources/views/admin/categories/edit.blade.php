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

            <div class="form-group">
                <label>Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $category->name) }}" required>
                @error('name') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label>Short Description</label>
                <textarea name="short_description" class="form-control">{{ old('short_description', $category->short_description) }}</textarea>
                @error('short_description') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label>Icon / Image</label>
                <input type="text" name="image" class="form-control mb-2" value="{{ old('image', $category->image) }}">
                @if($category->image && !str_contains($category->image, 'fa-'))
                    <img src="{{ asset('storage/'.$category->image) }}" width="100" class="mb-2">
                @endif
                <input type="file" name="image_file" class="form-control-file">
                <small class="text-muted">Upload new image to replace existing OR use FontAwesome class.</small>
                @error('image') <span class="text-danger">{{ $message }}</span> @enderror
                @error('image_file') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label>Status <span class="text-danger">*</span></label>
                <select name="status" class="form-control" required>
                    <option value="active" {{ (old('status', $category->status) === 'active') ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ (old('status', $category->status) === 'inactive') ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('status') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <button type="submit" class="btn btn-dark">Update Category</button>
        </form>
    </div>
</div>
@stop
