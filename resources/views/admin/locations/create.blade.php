@extends('adminlte::page')

@section('title', 'Create Location')

@section('content_header')
  <h1 class="container text-bold">Create Location</h1>
@stop

@section('content')
<div class="container-fluid">
  <div class="card">
    <div class="card-body">
      <form action="{{ route('locations.store') }}" method="POST" id="locationForm">
        @csrf

        {{-- Location Name --}}
        <div class="form-group">
          <label>Location Name <span class="text-danger">*</span></label>
          <input
            type="text"
            id="location_name"
            name="name"
            class="form-control"
            value="{{ old('name') }}"
            required
            placeholder="Start typing a South African city, e.g. Cape Town"
            autocomplete="off"
          >
          @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        {{-- Hidden lat/lng --}}
        <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude') }}">
        <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude') }}">

        {{-- Map --}}
        <div id="map" style="height: 340px; width: 100%; margin-bottom: 15px;" class="rounded border"></div>

        {{-- Email --}}
        <div class="form-group">
          <label>Email</label>
          <input
            type="text"
            name="email"
            id="email"
            class="form-control"
            value="{{ old('email') }}"
            placeholder="example@gmail.com">
          @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        {{-- Phone --}}
        <div class="form-group">
          <label>Phone</label>
          <input
            type="text"
            name="phone"
            id="phone"
            class="form-control"
            value="{{ old('phone') }}"
            placeholder="+2771 179 0986">
          @error('phone') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        {{-- Status --}}
        <div class="form-group">
          <label>Status <span class="text-danger">*</span></label>
          <select name="status" class="form-control" required>
            <option value="">-- Select Status --</option>
            <option value="active"   {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
          </select>
          @error('status') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        {{-- Buttons --}}
        <div class="d-flex justify-content-between">
          <a href="{{ route('locations.index') }}" class="btn btn-secondary">
              <i class="fas fa-arrow-left mr-1"></i> Cancel
          </a>
          <button class="btn btn-dark" type="submit">
              <i class="fas fa-save mr-1"></i> Save Location
          </button>
        </div>

      </form>
    </div>
  </div>
</div>

{{-- tiny style --}}
<style>
  .error { color: #e3342f; font-size: 0.875rem; }
</style>
@stop

@section('js')
{{-- ✅ jQuery + jQuery Validation --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>

<script>
$(function () {
  const form = $("#locationForm");

  // ✅ Custom phone validation rule
  $.validator.addMethod("validPhone", function (value, element) {
    return this.optional(element) || /^\+[0-9 ]{7,15}$/.test(value);
  }, "Phone must start with '+' and contain only digits or spaces (7–15 numbers).");

  // ✅ Initialize validation
  form.validate({
    onkeyup: false,
    onclick: false,
    onfocusout: function (element) { this.element(element); },
    rules: {
      name: { required: true, minlength: 2 },
      email: { email: true },
      phone: { validPhone: true },
      status: { required: true }
    },
    messages: {
      name: {
        required: "Location name is required.",
        minlength: "Name must be at least 2 characters."
      },
      email: {
        email: "Please enter a valid email address."
      },
      phone: {
        validPhone: "Please enter a valid phone number (e.g. +27711234567)."
      },
      status: {
        required: "Please select a status."
      }
    },
    errorElement: "span",
    errorClass: "error",
    highlight: function (element) {
      $(element).addClass("is-invalid");
    },
    unhighlight: function (element) {
      $(element).removeClass("is-invalid");
    }
  });

  // ✅ Prevent submit if invalid
  form.on("submit", function (e) {
    if (!form.valid()) {
      e.preventDefault();
    }
  });
});
</script>

{{-- ✅ Google Maps --}}
<script
  src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps.key') }}&libraries=places&callback=initMap"
  defer
></script>

<script>
let map, marker, autocomplete;
window.initMap = function () {
  const defaultPos = { lat: -30.5595, lng: 22.9375 };
  const startZoom  = 5;

  map = new google.maps.Map(document.getElementById("map"), {
    center: defaultPos,
    zoom: startZoom,
    streetViewControl: false,
    mapTypeControl: false,
  });

  marker = new google.maps.Marker({
    map,
    position: defaultPos,
    draggable: true
  });

  const input = document.getElementById("location_name");
  autocomplete = new google.maps.places.Autocomplete(input, {
    componentRestrictions: { country: 'ZA' }
  });

  autocomplete.addListener("place_changed", () => {
    const place = autocomplete.getPlace();
    if (!place.geometry || !place.geometry.location) return;
    const loc = place.geometry.location;
    moveMapAndMarker(loc.lat(), loc.lng(), 11);
    setLatLngFields(loc.lat(), loc.lng());
  });

  marker.addListener("dragend", () => {
    const pos = marker.getPosition();
    setLatLngFields(pos.lat(), pos.lng());
  });

  const oldLat = parseFloat("{{ old('latitude') ?? '' }}");
  const oldLng = parseFloat("{{ old('longitude') ?? '' }}");
  if (!Number.isNaN(oldLat) && !Number.isNaN(oldLng)) {
    moveMapAndMarker(oldLat, oldLng, 11);
    setLatLngFields(oldLat, oldLng);
  }
};

function moveMapAndMarker(lat, lng, zoom = 10) {
  const pos = { lat: lat, lng: lng };
  map.setCenter(pos);
  map.setZoom(zoom);
  marker.setPosition(pos);
}

function setLatLngFields(lat, lng) {
  document.getElementById("latitude").value  = lat;
  document.getElementById("longitude").value = lng;
}
</script>
@endsection
