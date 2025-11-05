@extends('layouts.frontend')

@section('title', $category->name)
@include('frontend.partials.navbar')

<div id="app">
    @php
        $heroBg = $settings && $settings->hero_image_path ? asset($settings->hero_image_path) : asset('images/bg.jpg');
    @endphp

    <!-- Hero Section -->
    <section class="hero d-flex align-items-center text-center text-white position-relative"
        style="min-height: 50vh; overflow: hidden;">
        <div class="position-absolute top-0 start-0 w-100 h-100"
            style="background: url('{{ $heroBg }}') center center/cover no-repeat; filter: blur(0px); transform: scale(1.1); z-index: 0;">
        </div>
        <div class="position-absolute top-0 start-0 w-100 h-100" style="background-color: rgba(0,0,0,0.5); z-index: 1;">
        </div>
        <div class="container position-relative" style="z-index: 2;">
            <h1 class="display-3 fw-bold">{{ $category->name }}</h1>
            <p class="lead text-light">{{ $category->short_description }}</p>
        </div>
    </section>

<!-- Equipment Section -->
<section class="bg-light py-5" id="equipment-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold text-dark mb-3">{{ $category->name }} Equipment</h2>
            <p class="lead text-muted mx-auto" style="max-width: 700px;">
                All equipment under the "{{ $category->name }}" category ready for your next adventure.
            </p>
        </div>

        <div class="row g-4 align-items-stretch">
            @php
                // Filter out equipment with all stock = 0 or no stock
                $filteredEquipments = $equipments->filter(function ($equipment) {
                    return $equipment->stocks->sum('stock') > 0;
                });
            @endphp

            @forelse($filteredEquipments as $equipment)
                <div class="col-md-6 col-lg-4">
                    <div class="card vehicle-card shadow-sm border-0 h-100 rounded-4 overflow-hidden d-flex flex-column">
                        <div class="position-relative">
                            @if ($equipment->image)
                                <img src="{{ asset('storage/' . $equipment->image) }}" class="card-img-top"
                                    alt="{{ $equipment->name }}" style="height: 220px; object-fit: cover;">
                            @else
                                <img src="https://via.placeholder.com/400x300?text=Equipment+Image"
                                    class="card-img-top" alt="{{ $equipment->name }}"
                                    style="height: 220px; object-fit: cover;">
                            @endif

                            @if ($equipment->status)
                                <span class="badge bg-success position-absolute top-0 end-0 m-3 px-3 py-2">
                                    {{ ucfirst($equipment->status) }}
                                </span>
                            @endif
                        </div>

                        <div class="card-body d-flex flex-column mt-2">
                            <h5 class="card-title fw-bold">{{ $equipment->name }}</h5>

                            {{-- Description --}}
                            <p class="card-text text-muted"
                                style="min-height: 60px; max-height: 60px; overflow: hidden;">
                                {{ Str::limit($equipment->description, 100) }}
                            </p>

                            {{-- Stock & Location --}}
                            <div class="row small text-muted mb-3"
                                style="min-height: 50px; max-height: 50px; overflow-y: auto;">
                                @foreach ($equipment->stocks as $stock)
                                    @if ($stock->stock > 0)
                                        <div class="col-6 mb-2">
                                            <i class="bi bi-geo-alt-fill me-1"></i>
                                            {{ $stock->location->name ?? 'Unknown' }}: {{ $stock->stock }}
                                        </div>
                                    @endif
                                @endforeach
                            </div>

                            <hr class="mt-auto">

                            <a href="{{ route('equipment.view', $equipment->id) }}"
                                class="btn btn-dark w-100 d-flex align-items-center py-3 justify-content-center mt-auto">
                                <i class="bi bi-eye me-2"></i> View Details
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <p class="text-center text-muted">No equipment available in this category.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>




</div>

@include('frontend.partials.footer')
