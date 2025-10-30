@extends('layouts.frontend')

@section('title', $equipment->name)

@section('content')
    @include('frontend.partials.navbar')

    <div class="container py-4 py-lg-5 mt-5">

        <a href="{{ url()->previous() }}" class="text-muted mb-4 d-inline-flex align-items-center">
            <i class="bi bi-arrow-left me-2"></i> Back
        </a>

        <div class="row g-4 g-lg-5 align-items-stretch">
            <!-- Image Section -->
            <div class="col-12 col-md-6 d-flex">
                <div class="card shadow-lg mb-3 border-3 rounded-4 flex-fill"
                    style="border-color: #679767; min-height: 430px; overflow: hidden;">
                    <img src="{{ asset('storage/' . $equipment->image) }}" alt="{{ $equipment->name }}"
                        class="card-img-top img-fluid w-100 h-100" style="object-fit: cover; object-position: center;">
                </div>
            </div>


            <!-- Equipment Details -->
            <div class="col-12 col-md-6 d-flex">
                <div class="card border-0 shadow-sm p-4 rounded-4 flex-fill d-flex flex-column" style="min-height: 430px;">
                    <!-- Equipment name on left | Category on right -->
                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
                        <h2 class="h4 fw-bold mb-0">{{ $equipment->name }}</h2>
                        <h2 class="text-muted small mb-0">
                            Category: <span class="fw-semibold text-dark">{{ $equipment->category->name ?? 'N/A' }}</span>
                        </h2>
                    </div>

                    @php
                        $rawDescription = $equipment->description ?? '';
                        $plainDescription = trim(strip_tags($rawDescription));
                        $length = $plainDescription === ''
                            ? 0
                            : (function_exists('mb_strlen') ? mb_strlen($plainDescription) : strlen($plainDescription));
                        $hasLongDescription = $length > 160;
                        $description = $plainDescription !== '' ? $rawDescription : 'No description available.';
                    @endphp

                    <div class="equipment-description-wrapper mb-3">
                        <p class="text-muted small mb-0 equipment-description {{ $hasLongDescription ? '' : 'expanded' }}">
                            {{ $description }}
                        </p>

                        @if ($hasLongDescription)
                            <button type="button"
                                class="btn btn-link p-0 text-decoration-none small fw-semibold equipment-description-toggle align-self-start">
                                See more
                            </button>
                        @endif
                    </div>

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
                    <div class="d-flex flex-wrap gap-3 mt-1">
                        <a href="#" class="btn flex-grow-1 py-3 fw-semibold"
                            style="background-color: #679767; color: black;">
                            <i class="bi bi-calendar-check me-2"></i> Book {{ $equipment->name }}
                        </a>

                        @if ($equipment->category && $equipment->category->is_for_sale)
                            @php $isSoldBtn = ($equipment->status ?? null) === 'sold'; @endphp

                            <a href="javascript:void(0)"
                                class="btn flex-grow-1 py-3 fw-semibold purchase-trigger {{ ($equipment->status ?? null) === 'sold' ? 'disabled' : '' }}"
                                data-bs-toggle="modal" data-bs-target="#purchaseModal"
                                aria-disabled="{{ ($equipment->status ?? null) === 'sold' ? 'true' : 'false' }}"
                                style="background-color:white;color:black;border:2px solid #679767;transition:all .3s;{{ ($equipment->status ?? null) === 'sold' ? 'pointer-events:none;opacity:.55;' : '' }}"
                                onmouseover="this.style.backgroundColor='#679767';this.style.color='white';"
                                onmouseout="this.style.backgroundColor='white';this.style.color='black';">
                                <i class="bi bi-bag-check me-2"></i> Purchase {{ $equipment->name }}
                            </a>
                        @endif
                    </div>

                    <!-- Stock Info -->
                    @if ($equipment->stocks && $equipment->stocks->count() > 0)
                        <div class="mt-4">
                            <h6 class="fw-bold mb-3">Stock Availability</h6>
                            <div class="row row-cols-1 row-cols-sm-2 g-2 text-muted small">
                                @foreach ($equipment->stocks as $stock)
                                    <div class="col d-flex align-items-center">
                                        <i class="bi bi-geo-alt-fill me-2 text-dark"></i>
                                        <span>{{ $stock->location->name ?? 'Unknown Location' }} — {{ $stock->stock }} in
                                            stock</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <style>
        .equipment-description-wrapper {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: .25rem;
        }

        .equipment-description-toggle {
            color: #679767;
            font-size: .85rem;
        }

        .equipment-description-toggle:hover {
            color: #4f7b50;
            text-decoration: underline;
        }

        .equipment-description {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            transition: all .25s ease;
        }

        .equipment-description.expanded {
            -webkit-line-clamp: unset;
            max-height: none;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.equipment-description-toggle').forEach(function (button) {
                button.addEventListener('click', function () {
                    const wrapper = button.closest('.equipment-description-wrapper');
                    const description = wrapper ? wrapper.querySelector('.equipment-description') : null;
                    if (!description) return;

                    const expanded = description.classList.toggle('expanded');
                    button.textContent = expanded ? 'See less' : 'See more';
                    button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                });
            });
        });
    </script>

    @include('models.purchase', ['item' => $equipment, 'type' => 'equipment'])
@endsection
