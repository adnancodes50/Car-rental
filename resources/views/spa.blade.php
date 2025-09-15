@extends('layouts.frontend')

@section('title', 'Home')
@include('frontend.partials.navbar')
{{-- Main SPA mount point --}}
<div id="app">
    {{-- @yield('content') --}}


    @php
        $heroBg = $settings && $settings->hero_image_path
            ? Storage::url($settings->hero_image_path)
            : asset('images/bg.jpg');
    @endphp


    <section class="hero d-flex mt-5 align-items-center text-center text-white"
        style="background: url('{{ $heroBg }}') center center/cover no-repeat; min-height: 100vh;">
        <div class="container mt-5">
            <h1 class="display-3 fw-bold mb-4">
                Built for where <br>
                <span class="text-warning">adventure was born</span>
            </h1>

            <p class="lead text-light mb-5 mx-auto" style="max-width: 700px;">
                Meticulously restored vintage Land Rovers...
            </p>

            <div class="d-flex justify-content-center mb-5">
                <a href="#vehicles" class="btn btn-warning btn-lg fw-bold px-4 py-3">
                    Explore Vehicles <i class="bi bi-arrow-right ms-2"></i>
                </a>
            </div>

            <!-- Features ... -->
            <div class="row justify-content-center g-4 px-3 px-lg-5">
                <div class="col-12 col-md-4">
                    <div class="d-flex flex-column align-items-center text-center">
                        <i class="bi bi-triangle text-warning display-4 mb-3"></i>
                        <h3 class="h5 fw-semibold mb-2">Authentic Restoration</h3>
                        <p class="text-light">Every Land Rover restored to original specifications with modern
                            reliability</p>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="d-flex flex-column align-items-center text-center">
                        <i class="bi bi-compass text-warning display-4 mb-3"></i>
                        <h3 class="h5 fw-semibold mb-2">Adventure Ready</h3>
                        <p class="text-light">Equipped for multi-day expeditions with premium overland accessories</p>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="d-flex flex-column align-items-center text-center">
                        <i class="bi bi-arrow-right-circle text-warning display-4 mb-3"></i>
                        <h3 class="h5 fw-semibold mb-2">Own or Rent</h3>
                        <p class="text-light">Choose your perfect Land Rover for adventure rentals or purchase</p>
                    </div>
                </div>
            </div>

        </div>
    </section>


    <!-- Features -->


    <section class="bg-light mt-5 py-5">
        <div class="container">
            <!-- Header -->
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold text-dark mb-3">Our Fleet</h2>
                <p class="lead text-muted mx-auto" style="max-width: 700px;">
                    Each Land Rover in our collection has been meticulously restored and prepared for your next
                    adventure
                </p>
            </div>

            <!-- Fleet Grid -->
            <div class="row g-4">
                @forelse($vehicles as $vehicle)
                    <div class="col-md-6 col-lg-4">
                        <div class="card shadow-sm border-0 h-100 rounded-4 overflow-hidden">

                            <!-- Vehicle Image with overlays -->
                            <div class="position-relative">
                                <img src="{{ $vehicle->mainImage() }}" class="card-img-top" alt="{{ $vehicle->model }}"
                                    style="height: 220px; object-fit: cover;">

                                <!-- Status Badge -->
                                <span class="badge bg-success position-absolute top-0 end-0 m-3 px-3 py-2">
                                    {{ ucfirst($vehicle->status) }}
                                </span>

                                <!-- Model Tag -->
                                @if($vehicle->model)
                                    <span
                                        class="position-absolute bottom-0 start-0 m-3 px-3 py-1 bg-dark text-white small rounded">
                                        {{ $vehicle->year }} {{ $vehicle->model }}
                                    </span>
                                @endif
                            </div>

                            <!-- Card Body -->
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title fw-bold">{{ $vehicle->name }}</h5>
                                <p class="card-text text-muted">
                                    {{ Str::limit($vehicle->description, 100) }}
                                </p>

                                <!-- Specs -->
                                <div class="d-flex flex-wrap small text-muted mb-3">
                                    @if($vehicle->seats)
                                        <div class="me-3 mb-2"><i class="bi bi-people-fill me-1"></i>{{ $vehicle->seats }} seats
                                        </div>
                                    @endif
                                    @if($vehicle->fuel_type)
                                        <div class="me-3 mb-2"><i
                                                class="bi bi-fuel-pump-fill me-1"></i>{{ $vehicle->fuel_type }}</div>
                                    @endif
                                    @if($vehicle->location)
                                        <div class="me-3 mb-2"><i class="bi bi-geo-alt-fill me-1"></i>{{ $vehicle->location }}
                                        </div>
                                    @endif
                                </div>

                                <!-- Pricing -->
                                <div class="mb-3">
                                    @if($vehicle->rental_price_week)
                                        <div class="text-muted small">From</div>
                                        <p class="mb-1"><strong>R{{ number_format($vehicle->rental_price_week) }}/week</strong>
                                        </p>
                                    @endif

                                    @if($vehicle->purchase_price)
                                        <div class="text-muted small">Purchase from</div>
                                        <p class="fw-bold h5 text-dark mb-0">
                                            R{{ number_format($vehicle->purchase_price) }}
                                        </p>
                                    @endif
                                </div>

                                <!-- Button -->
                                <div class="mt-auto">
                                    <a href="{{ route('fleet.view', $vehicle->id) }}"
                                        class="btn btn-dark w-100 d-flex align-items-center justify-content-center">
                                        <i class="bi bi-eye me-2"></i> View Details
                                    </a>


                                </div>
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


@include('frontend.partials.footer')

@section('content')


@endsection
