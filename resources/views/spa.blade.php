@extends('layouts.frontend')

@section('title', 'Home')
@include('frontend.partials.navbar')

<div id="app">
    <div>
        @php
            $heroBg = $settings && $settings->hero_image_path ? asset($settings->hero_image_path) : asset('images/bg.jpg');
        @endphp

        <section class="hero d-flex align-items-center text-center text-white position-relative" id="home-section"
            style="min-height: 100vh; overflow: hidden;">

            <div class="position-absolute top-0 start-0 w-100 h-100"
                style="background: url('{{ $heroBg }}') center center/cover no-repeat;
                    filter: blur(0px);
                    transform: scale(1.1);
                    z-index: 0;">
            </div>

            <div class="position-absolute top-0 start-0 w-100 h-100"
                style="background-color: rgba(0,0,0,0.5); z-index: 1;"></div>

            <div class="container mt-5 position-relative" style="z-index: 2;">
                <h1 class="display-3 fw-bold mb-4"
                    style="text-shadow: 0 4px 12px rgba(0,0,0,0.8), 0 2px 6px rgba(0,0,0,0.8);">
                    Built for where <br>
                    <span class="" style=" color: #CF9B4D; text-shadow: 0 3px 8px rgba(146, 116, 27, 0.2);">
                        adventure was born
                    </span>
                </h1>

                <p class="lead text-light mb-5 mx-auto" style="max-width: 700px;">
                    Meticulously restored vintage Land Rovers...
                </p>

                <div class="d-flex justify-content-center mb-5">
                    <a href="{{ url('/#vehicles-section') }}" class="btn btn-lg fw-bold px-4 py-3"
                        style="background-color: #CF9B4D">
                        Explore Vehicles <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                </div>

                <div class="row justify-content-center g-4 px-3 px-lg-5">
                    <div class="col-12 col-md-4">
                        <div class="d-flex flex-column align-items-center text-center">
                            <i class="bi bi-shield-lock display-4 mb-3" style="color:#CF9B4D;"></i>
                            <h3 class="h5 fw-semibold mb-2">Authentic Restoration</h3>
                            <p class="text-light">Every Land Rover restored to original specifications with modern reliability.</p>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="d-flex flex-column align-items-center text-center">
                            <i class="bi bi-compass display-4 mb-3" style="color:#CF9B4D;"></i>
                            <h3 class="h5 fw-semibold mb-2">Adventure Ready</h3>
                            <p class="text-light">Equipped for multi-day expeditions with premium overland accessories.</p>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="d-flex flex-column align-items-center text-center">
                            <i class="bi bi-arrow-right-circle display-4 mb-3" style="color:#CF9B4D;"></i>
                            <h3 class="h5 fw-semibold mb-2">Own or Rent</h3>
                            <p class="text-light">Choose your perfect Land Rover for adventure rentals or purchase.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Fleet Section -->
    <section class="bg-light py-5" id="vehicles-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold text-dark mb-3">Our Fleet</h2>
                <p class="lead text-muted mx-auto" style="max-width: 700px;">
                    Each Land Rover in our collection has been meticulously restored and prepared for your next adventure.
                </p>
            </div>

            <div class="row g-4 align-items-stretch">
                @forelse($vehicles as $vehicle)
                    <div class="col-md-6 col-lg-4">
                        <div class="card vehicle-card shadow-sm border-0 h-100 rounded-4 overflow-hidden">
                            <div class="position-relative">
                                <img src="{{ $vehicle->mainImage() }}" class="card-img-top" alt="{{ $vehicle->model }}">
                                <span class="badge bg-success position-absolute top-0 end-0 m-3 px-3 py-2">
                                    {{ ucfirst($vehicle->status) }}
                                </span>
                                @if ($vehicle->model)
                                    <span class="position-absolute bottom-0 start-0 m-3 px-3 py-1 bg-dark text-white small rounded">
                                        {{ $vehicle->year }} {{ $vehicle->model }}
                                    </span>
                                @endif
                            </div>

                            <div class="card-body d-flex flex-column mt-2">
                                <h5 class="card-title fw-bold">{{ $vehicle->name }}</h5>
                                <p class="card-text text-muted">{{ Str::limit($vehicle->description, 100) }}</p>

                                <div class="row small text-muted mb-3">
                                    @if ($vehicle->seats)
                                        <div class="col-8 mb-2"><i class="bi bi-people-fill me-1"></i>{{ $vehicle->seats }} seats</div>
                                    @endif
                                    @if ($vehicle->fuel_type)
                                        <div class="col-4 mb-2"><i class="bi bi-fuel-pump-fill me-1"></i>{{ $vehicle->fuel_type }}</div>
                                    @endif
                                    @if ($vehicle->location)
                                        <div class="col-8 mb-2"><i class="bi bi-geo-alt-fill me-1"></i>{{ $vehicle->location }}</div>
                                    @endif
                                    @if ($vehicle->status)
                                        <div class="col-4 mb-2"><i class="bi bi-gear-fill me-1"></i>{{ $vehicle->transmission }}</div>
                                    @endif
                                </div>

                                <hr class="mt-auto">

                                <div class="mb-3 d-flex justify-content-between">
                                    @if ($vehicle->rental_price_week)
                                        <div>
                                            <div class="text-muted small">From</div>
                                            <p class="h5 text-dark mb-0"><strong>R{{ number_format($vehicle->rental_price_week) }}/week</strong></p>
                                        </div>
                                    @endif
                                    @if ($vehicle->purchase_price)
                                        <div class="text-end">
                                            <div class="text-muted small">Purchase from</div>
                                            <p class="h5 text-dark mb-0">R{{ number_format($vehicle->purchase_price) }}</p>
                                        </div>
                                    @endif
                                </div>

                                <a href="{{ route('fleet.view', $vehicle->id) }}" class="btn btn-dark w-100 d-flex align-items-center py-3 justify-content-center mt-auto">
                                    <i class="bi bi-eye me-2"></i> View Details
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <p class="text-center text-muted">No vehicles available at the moment.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</div>

{{-- @section('css') --}}
<style>
    /* Card layout */
    .vehicle-card {
        display: flex;
        flex-direction: column;
        height: 100%;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .vehicle-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }

    .vehicle-card img {
        height: 220px;
        object-fit: cover;
        width: 100%;
    }

    /* Keep consistent description height */
    .vehicle-card .card-text {
        min-height: 70px;
        max-height: 70px;
        overflow: hidden;
    }

    .vehicle-card .card-body {
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }

    /* Keep HR line and button aligned */
    .vehicle-card hr {
        margin-top: auto;
    }

    .vehicle-card .mt-auto {
        margin-top: auto !important;
    }

    .row.g-4 {
        align-items: stretch;
    }
</style>

@include('frontend.partials.footer')

@section('content')
@endsection
