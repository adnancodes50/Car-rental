@extends('adminlte::page')

@section('title', 'Company Settings')

@section('content_header')
    <h1 class="font-weight-bold text-dark">⚙️ Company Settings</h1>
@stop

@section('content')
<div class="container-fluid d-flex justify-content-center">
  <div class="card shadow-lg w-100">
    <div class="card-body p-4">

      {{-- Error Messages --}}
      @if ($errors->any())
        <div class="alert alert-danger mb-4">
          <ul class="mb-0">
            @foreach ($errors->all() as $err)
              <li>{{ $err }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form action="{{ route('company-setting.update') }}" method="POST" enctype="multipart/form-data" id="companyForm">
        @csrf

        {{-- Project Name --}}
        <div class="form-group mb-4">
          <label class="font-weight-bold">Project Name <span class="text-danger">*</span></label>
          <input
            type="text"
            name="project_name"
            class="form-control form-control-lg"
            placeholder="Enter your company or project name"
            value="{{ old('project_name', optional($detail)->project_name) }}"
            required
          >
        </div>

        {{-- Logo Upload --}}
        <div class="form-group mb-4">
          <label class="font-weight-bold">Company Logo</label>
          <div class="custom-file mb-3">
            <input
              type="file"
              class="custom-file-input"
              id="logoInput"
              name="logo"
              accept="image/*"
            >
            <label class="custom-file-label" for="logoInput">Choose a logo (PNG, JPG, WEBP, SVG)</label>
          </div>

          {{-- Logo Preview --}}
          <div id="logoPreviewContainer" class="text-center">
            @if(optional($detail)->logo_url)
              <img
                id="logoPreview"
                src="{{ $detail->logo_url }}"
                class="rounded shadow-sm border"
                style="height: 100px; max-width: 200px; object-fit: contain;"
                alt="Company Logo"
              >
            @else
              <img
                id="logoPreview"
                src="https://via.placeholder.com/150x100?text=Logo+Preview"
                class="rounded border bg-light"
                style="height: 100px; max-width: 200px; object-fit: contain;"
                alt="Company Logo"
              >
            @endif
          </div>
        </div>

        {{-- Save Button --}}
        <div class="d-flex justify-content-end">
          <button type="submit" class="btn btn-dark btn-lg px-4">
            <i class="fas fa-save mr-2"></i> Save Settings
          </button>
        </div>
      </form>

    </div>
  </div>
</div>
@stop

@section('js')
{{-- ✅ Include SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function () {

  // ✅ SweetAlert for success message from session
  @if (session('success'))
    Swal.fire({
      title: '✅ Success!',
      text: '{{ session('success') }}',
      icon: 'success',
      confirmButtonColor: '#343a40',
      confirmButtonText: 'OK'
    });
  @endif

  // ✅ File input + live preview
  $('#logoInput').on('change', function (e) {
    const fileName = e.target.files[0]?.name || 'Choose a logo (PNG, JPG, WEBP, SVG)';
    $(this).next('.custom-file-label').html(fileName);

    const reader = new FileReader();
    reader.onload = function (event) {
      $('#logoPreview').attr('src', event.target.result);
    };
    if (e.target.files[0]) {
      reader.readAsDataURL(e.target.files[0]);
    }
  });
});
</script>
@stop
