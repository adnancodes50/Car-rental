@extends('adminlte::page')

@section('title', 'Locations')

@section('content_header')
  <h1 class="container text-bold">Locations</h1>
@stop

@section('content')
<div class="container-fluid">

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="card">
    {{-- Header with button aligned to the right --}}
    <div class="card-header">
      <div class="d-flex w-100 align-items-center">
        <h3 class="card-title mb-0">All Locations</h3>
        <div class="ml-auto">
          <a href="{{ route('locations.create') }}" class="btn btn-dark btn-sm">
            <i class="fas fa-plus"></i> Add Location
          </a>
        </div>
      </div>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="locationsTable" class="table table-striped mb-0 w-100">
          <thead>
            <tr>
              <th style="width: 60px;">ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Status</th>
              <th style="width: 150px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($locations as $loc)
              <tr>
                <td>{{ $loc->id }}</td>
                <td>{{ $loc->name }}</td>
                <td>{{ $loc->email }}</td>
                <td>{{ $loc->phone }}</td>
                <td>
                  @if($loc->status === 'active')
                    <span class="badge badge-success">Active</span>
                  @else
                    <span class="badge badge-secondary">Inactive</span>
                  @endif
                </td>
                <td>
                  <a href="{{ route('locations.edit', $loc) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit"></i>
                  </a>
                  <form action="{{ route('locations.destroy', $loc) }}"
                        method="POST" class="d-inline"
                        onsubmit="return confirm('Delete this location?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center p-4">No locations yet.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@stop

