@extends('adminlte::page')

@section('title', 'Create Location')

@section('content_header')
  <h1 class="container text-bold">Create Location</h1>
@stop

@section('content')
<div class="container-fluid">
  <div class="card">
    <div class="card-body">
      <form action="{{ route('locations.store') }}" method="POST">
        @csrf

        <div class="form-group">
          <label>Name <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
          @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" class="form-control" value="{{ old('email') }}">
          @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
          <label>Phone</label>
          <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="+277117909863">
          @error('phone') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
          <label>Status <span class="text-danger">*</span></label>
          <select name="status" class="form-control" required>
            <option value="active"   {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
          </select>
          @error('status') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <div class="d-flex justify-content-end">
          <a href="{{ route('locations.index') }}" class="btn btn-secondary mr-2">Cancel</a>
          <button class="btn btn-dark"><i class="fas fa-save mr-1"></i> Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
@stop
