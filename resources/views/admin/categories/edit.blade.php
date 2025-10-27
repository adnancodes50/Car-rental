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
                <label>Image</label>
                @if($category->image && !str_contains($category->image, 'fa-'))
                    <div class="mb-2">
                        <img src="{{ asset('storage/'.$category->image) }}" width="100" class="img-thumbnail">
                    </div>
                @endif
                <input type="file" name="image_file" class="form-control-file">
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

            <div class="form-group d-flex justify-content-between mt-4">
                <a href="{{ route('categories.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-dark">Update Category</button>
            </div>
        </form>
    </div>
</div>
@stop
