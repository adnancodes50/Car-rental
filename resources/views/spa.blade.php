@extends('layouts.frontend')

@section('title', 'Home')
@include('frontend.partials.navbar')

<div id="app">
    @php
    $heroBg = $settings && $settings->hero_image_path
        ? asset($settings->hero_image_path)
        : asset('images/bg.jpg');
@endphp

<section class="hero-section position-relative d-flex align-items-center py-5" id="home-section"
    style="min-height: 100vh; background: url('{{ $heroBg }}') center center/cover no-repeat;">

    <!-- Overlay -->
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background-color: rgba(0,0,0,0.6); z-index: 0;"></div>

    <div class="container position-relative z-1">
        <div class="row align-items-center g-5">

            <!-- Left Column -->
            <div class="col-lg-7 text-white">
                <h1 class="display-3 fw-bold mb-4" style="text-shadow: 0 4px 12px rgba(0,0,0,0.8);">
                    Rent 2 Recover
                </h1>
<p class="lead mb-4 fw-normal" style="max-width: 600px;">
                    We specialise in a wide range of mobility equipment including electric wheelchairs,
                    scooters, hospital beds, bath lifts, oxygen concentrators, and much more.
                    <span class="text-light-50">
                        We also offer medical equipment for purchase with a guaranteed buyback option (T’s & C’s apply).
                    </span>
                </p>

                <a href="{{ url('/#category-section') }}" class="btn btn-lg fw-bold px-4 py-3" style="background-color:#679767; color:white;">
                    Rent Your Recovery Equipment Now <i class="bi bi-arrow-right ms-2"></i>
                </a>
            </div>

            <!-- Right Column (Image or Placeholder) -->
            {{-- <div class="col-lg-5 text-center d-none d-lg-block">
                <img src="{{ asset('images/wheelchair.png') }}" alt="Mobility Equipment"
                    class="img-fluid rounded-4 shadow-lg" style="max-height: 450px; object-fit: contain;">
            </div> --}}
        </div>
    </div>
</section>


    <!-- Categories Section -->
    <section class="py-5 bg-light" id="category-section" style="overflow: hidden;">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold text-dark mb-3">Categories</h2>
                <p class="lead text-muted mx-auto" style="max-width: 700px;">
                    Each Land Rover in our collection has been meticulously restored and prepared <br>
                    for your next adventure.
                </p>
            </div>

            <div class="row g-4 justify-content-center">
                @forelse($categories as $category)
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="category-card position-relative overflow-hidden rounded-4 shadow-sm">
                            @if ($category->image)
                                <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}"
                                    class="img-fluid w-100 h-100 category-bg">
                            @else
                                <img src="https://via.placeholder.com/600x400?text=Category+Image"
                                    alt="{{ $category->name }}" class="img-fluid w-100 h-100 category-bg">
                            @endif

                            <div class="category-overlay d-flex flex-column justify-content-end p-4">
                                <div class="text-start text-white">
                                    <h5 class="fw-bold mb-2">{{ $category->name }}</h5> <br>
                                    {{-- <h5 class=" mb-2">{{ $category->short_description }}</h5> --}}
                                    <p class="small mb-0">{{ Str::limit($category->short_description, 80) }}</p>
                                </div>
                            </div>

                            <a href="{{ route('category.show', $category->id) }}"
                                class="category-arrow position-absolute top-0 end-0 m-3 d-flex align-items-center justify-content-center rounded-circle bg-light text-dark">
                                <i class="bi bi-arrow-up-right fs-5"></i>
                            </a>

                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center text-muted">
                        <p>No categories available at the moment.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <style>
        .category-card {
            height: 420px;
            cursor: pointer;
            transition: all 0.4s ease;
        }

        .category-card:hover .category-bg {
            transform: scale(1.08);
        }

        .category-bg {
            height: 100%;
            width: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
            filter: brightness(70%);
        }

        .category-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.65), rgba(0, 0, 0, 0.1));
            border-radius: 1rem;
        }

        .category-arrow {
            width: 42px;
            height: 42px;
            background-color: #CF9B4D;
            /* Gold color */
            color: #fff;
            transition: all 0.3s ease;
        }

        .category-arrow:hover {
            background-color: #b8893d;
            /* Slightly darker gold */
            color: #fff;
        }
    </style>

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
