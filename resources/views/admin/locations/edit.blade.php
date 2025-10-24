@extends('adminlte::page')

@section('title', 'Edit Location')

@section('content_header')
  <h1 class="container text-bold">Edit Location</h1>
@stop

@section('content')
<div class="container-fluid">
  <div class="card">
    <div class="card-body">
      <form action="{{ route('locations.update', $location) }}" method="POST">
        @csrf @method('PUT')

        {{-- Location Name with Google Maps Autocomplete --}}
        <div class="form-group">
          <label>Name <span class="text-danger">*</span></label>
          <input
            type="text"
            id="location_name"
            name="name"
            class="form-control"
            value="{{ old('name', $location->name) }}"
            required
            placeholder="Start typing a South African city, e.g. Cape Town"
            autocomplete="off"
          >
          @error('name') <span class="text-danger small">{{ $message }}</span> @enderror


        </div>

        <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude', $location->latitude) }}">
        <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude', $location->longitude) }}">

        {{-- Map --}}
        <div id="map" style="height: 340px; width: 100%; margin-bottom: 15px;" class="rounded border"></div>

        {{-- Email --}}
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" class="form-control" value="{{ old('email', $location->email) }}">
          @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        {{-- Phone --}}
        <div class="form-group">
          <label>Phone</label>
          <input type="text" name="phone" class="form-control" value="{{ old('phone', $location->phone) }}" placeholder="+277117909863">
          @error('phone') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        {{-- Status --}}
        <div class="form-group">
          <label>Status <span class="text-danger">*</span></label>
          <select name="status" class="form-control" required>
            <option value="active"   {{ old('status', $location->status) === 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ old('status', $location->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
          </select>
          @error('status') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <div class="d-flex justify-content-end">
          <a href="{{ route('locations.index') }}" class="btn btn-secondary mr-2">Cancel</a>
          <button class="btn btn-dark"><i class="fas fa-save mr-1"></i> Update</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- tiny style for the suggestion chips --}}
<style>
  .btn-xs { padding: .15rem .45rem; font-size: .75rem; line-height: 1; border-radius: .2rem; margin-right:.35rem; margin-bottom:.35rem; }
  .gap-2 { gap: .5rem; }
</style>
@stop

@section('js')
<script>
let map, marker, autocomplete;

// Google callback must be global
window.initMap = function () {
  // Use saved lat/lng if available, otherwise SA center-ish
  const existingLat = parseFloat("{{ old('latitude', $location->latitude) ?? '' }}");
  const existingLng = parseFloat("{{ old('longitude', $location->longitude) ?? '' }}");
  const hasExisting = !Number.isNaN(existingLat) && !Number.isNaN(existingLng);

  const defaultPos = hasExisting
      ? { lat: existingLat, lng: existingLng }
      : { lat: -30.5595, lng: 22.9375 }; // South Africa center-ish

  const startZoom = hasExisting ? 11 : 5;

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

  // Places Autocomplete on the input, restricted to South Africa
  const input = document.getElementById("location_name");
  autocomplete = new google.maps.places.Autocomplete(input, {
    // types: ['(cities)'], // enable if you want cities only
    componentRestrictions: { country: 'ZA' }
  });

  // When a place is selected from the dropdown
  autocomplete.addListener("place_changed", () => {
    const place = autocomplete.getPlace();
    if (!place.geometry || !place.geometry.location) {
      console.warn("No geometry for:", place);
      return;
    }
    const loc = place.geometry.location;
    moveMapAndMarker(loc.lat(), loc.lng(), 11);
    setLatLngFields(loc.lat(), loc.lng());
  });

  // If the user drags the marker, update hidden fields
  marker.addListener("dragend", () => {
    const pos = marker.getPosition();
    setLatLngFields(pos.lat(), pos.lng());
  });

  // Quick suggestions
  document.querySelectorAll('.sa-suggestion').forEach(btn => {
    btn.addEventListener('click', () => {
      const lat = parseFloat(btn.dataset.lat);
      const lng = parseFloat(btn.dataset.lng);
      const name = btn.dataset.name;
      document.getElementById('location_name').value = name;
      moveMapAndMarker(lat, lng, 11);
      setLatLngFields(lat, lng);
    });
  });
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

{{-- Load the Maps JS with Places + callback (uses your configured key) --}}
<script
  src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps.key') }}&libraries=places&callback=initMap"
  defer
></script>
@endsection
