@extends('layouts.frontend')

@section('title', $equipment->name)

@section('content')
    @include('frontend.partials.navbar')

    <div class="container py-4 py-lg-5 mt-5">

        <a href="{{ url()->previous() }}" class="text-muted mb-4 d-inline-flex align-items-center">
            <i class="bi bi-arrow-left me-2"></i> Back
        </a>

        <div class="row g-4 g-lg-5">
            <!-- Image Section -->
            <div class="col-12 col-md-6">
                <div class="card border-0 shadow-sm mb-3">
                    <img src="{{ asset('storage/' . $equipment->image) }}" class="card-img-top rounded img-fluid"
                        alt="{{ $equipment->name }}" style="object-fit: cover; max-height: 380px;">
                </div>
            </div>

            <!-- Equipment Details -->
            <div class="col-12 col-md-6">
                <div class="card border-0 shadow-sm p-4 rounded-4">
                    <h2 class="h4">{{ $equipment->name }}</h2>
                    <p class="text-muted small mb-2">Category: {{ $equipment->category->name ?? 'N/A' }}</p>

                    <p class="text-muted small mb-3">{{ $equipment->description ?? 'No description available.' }}</p>

                    <div class="row text-center g-2 mb-4">
                        @if ($equipment->category && $equipment->category->daily_price)
                            <div class="col-6 col-lg-4">
                                <div class="border rounded p-2">
                                    <div class="fw-semibold">
                                        R{{ number_format($equipment->category->daily_price) }}/day
                                    </div>
                                    <small class="text-muted">Daily</small>
                                </div>
                            </div>
                        @endif

                        @if ($equipment->category && $equipment->category->weekly_price)
                            <div class="col-6 col-lg-4">
                                <div class="border rounded p-2">
                                    <div class="fw-semibold">
                                        R{{ number_format($equipment->category->weekly_price) }}/week
                                    </div>
                                    <small class="text-muted">Weekly</small>
                                </div>
                            </div>
                        @endif

                        @if ($equipment->category && $equipment->category->monthly_price)
                            <div class="col-6 col-lg-4">
                                <div class="border rounded p-2">
                                    <div class="fw-semibold">
                                        R{{ number_format($equipment->category->monthly_price) }}/month
                                    </div>
                                    <small class="text-muted">Monthly</small>
                                </div>
                            </div>
                        @endif
                    </div>




                    <!-- Action Buttons -->
                    <div class="d-flex flex-wrap gap-3 mt-3">
                        <a href="#" class="btn btn-dark flex-grow-1 py-3 fw-semibold">
                            <i class="bi bi-calendar-check me-2"></i> Book Equipment
                        </a>

                        @if ($equipment->category && $equipment->category->is_for_sale)
                            <a href="#" class="btn btn-outline-dark flex-grow-1 py-3 fw-semibold">
                                <i class="bi bi-bag-check me-2"></i> Purchase Equipment
                            </a>
                        @endif
                    </div>


                    <!-- Stock Info -->
                    @if ($equipment->stocks && $equipment->stocks->count() > 0)
                        <div class="mt-3">
                            <h6 class="fw-bold">Stock Availability</h6>
                            <ul class="list-unstyled small text-muted">
                                @foreach ($equipment->stocks as $stock)
                                    <li>
                                        <i class="bi bi-geo-alt-fill me-1"></i>
                                        {{ $stock->location->name ?? 'Unknown Location' }} — {{ $stock->stock }} in stock
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
@endsection
