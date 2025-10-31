@php
    $item = $equipment ?? $vehicle ?? null;
    $category = $item?->category ?? null;
    $stocks = collect($item?->stocks ?? [])->filter(function ($stock) {
        return $stock && $stock->location;
    })->values();

    $unitOptions = [
        'day' => [
            'label' => 'Daily Rental',
            'description' => 'Perfect for short trips',
            'icon' => 'bi-clock',
            'price' => $category->daily_price ?? $item->daily_price ?? $item->rental_price_day ?? null,
            'suffix' => '/day',
        ],
        'week' => [
            'label' => 'Weekly Rental',
            'description' => 'Great for 1-4 weeks',
            'icon' => 'bi-calendar-event',
            'price' => $category->weekly_price ?? $item->weekly_price ?? $item->rental_price_week ?? null,
            'suffix' => '/week',
        ],
        'month' => [
            'label' => 'Monthly Rental',
            'description' => 'Best for long stays',
            'icon' => 'bi-box',
            'price' => $category->monthly_price ?? $item->monthly_price ?? $item->rental_price_month ?? null,
            'suffix' => '/month',
        ],
    ];

    $unitOptions = array_filter($unitOptions, static function ($option) {
        return !is_null($option['price']) && $option['price'] > 0;
    });

    $defaultUnit = array_key_first($unitOptions);
    $categoryId = $category?->id ?? $item?->category_id ?? null;
    $today = now()->toDateString();
@endphp

@if ($item && $categoryId && count($unitOptions) > 0)
    <div class="modal fade" id="bookingModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
            <div class="modal-content rounded-4">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-calendar-check me-2"></i>Book {{ $item->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body pt-0">
                    {{-- <div class="booking-stepper mb-4">
                        <div class="booking-step active" data-step-index="0">
                            <span class="booking-step-number">1</span>
                            <span class="booking-step-label">Options</span>
                        </div>
                        <div class="booking-step" data-step-index="1">
                            <span class="booking-step-number">2</span>
                            <span class="booking-step-label">Your Details</span>
                        </div>
                        <div class="booking-step" data-step-index="2">
                            <span class="booking-step-number">3</span>
                            <span class="booking-step-label">Review</span>
                        </div>
                    </div> --}}

                    <form id="bookingForm" action="{{ route('bookings.store') }}" method="POST" novalidate>
                        @csrf
                        <input type="hidden" name="category_id" value="{{ $categoryId }}">
                        <input type="hidden" name="equipment_id" value="{{ $item->id }}">
                        <input type="hidden" name="rental_unit" id="bookingRentalUnit"
                            value="{{ $defaultUnit ?? '' }}">
                        <input type="hidden" name="total_price" id="bookingTotalInput" value="0">
                        <input type="hidden" name="equipment_stock_id" id="bookingStockIdInput" value="">

                        <div data-booking-step="0">
                            <div class="alert alert-danger d-none" id="bookingStep1Error"></div>

                            <h6 class="fw-bold mb-3">Select Rental Duration</h6>
                            <div class="row g-3 mb-4">
                                @foreach ($unitOptions as $unit => $option)
                                    <div class="col-12 col-md-4">
                                        <button type="button"
                                            class="booking-unit-card {{ $loop->first ? 'active' : '' }}"
                                            data-unit-card data-unit="{{ $unit }}"
                                            data-price="{{ $option['price'] }}"
                                            data-suffix="{{ $option['suffix'] }}">
                                            <span class="booking-unit-icon"><i
                                                    class="bi {{ $option['icon'] }}"></i></span>
                                            <span class="booking-unit-title">{{ $option['label'] }}</span>
                                            <span class="booking-unit-description text-muted small">
                                                {{ $option['description'] }}
                                            </span>
                                            <span class="booking-unit-price fw-semibold">
                                                R{{ number_format($option['price'], 2) }}{{ $option['suffix'] }}
                                            </span>
                                        </button>
                                    </div>
                                @endforeach
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-6">
                                    <label for="bookingStartDate" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="bookingStartDate"
                                        name="rental_start_date" value="{{ $today }}" min="{{ $today }}" required>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="bookingQuantity">
                                        <span data-quantity-label>Number of days</span>
                                    </label>
                                    <select id="bookingQuantity" name="rental_quantity" class="form-select" required>
                                        <option value="1" selected>1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-6">
                                    <label for="bookingLocation" class="form-label">Select Location</label>
                                    <select id="bookingLocation" name="location_id" class="form-select" required
                                        {{ $stocks->isEmpty() ? 'disabled' : '' }}>
                                        @if ($stocks->isEmpty())
                                            <option value="" selected>No locations available</option>
                                        @else
                                            @foreach ($stocks as $stock)
                                                <option value="{{ $stock->location_id }}"
                                                    data-available="{{ (int) $stock->stock }}"
                                                    data-stock-id="{{ $stock->id }}"
                                                    data-location-name="{{ $stock->location->name }}">
                                                    {{ $stock->location->name }} ({{ (int) $stock->stock }} in stock)
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @if ($stocks->isEmpty())
                                        <div class="form-text text-danger">No stock available for this item.</div>
                                    @endif
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="bookingStock" class="form-label">Units to Reserve</label>
                                    <select id="bookingStock" name="stock_quantity" class="form-select"
                                        {{ $stocks->isEmpty() ? 'disabled' : '' }}>
                                        <option value="1" selected>1 unit</option>
                                    </select>
                                    <div class="form-text">Controls how many units are reserved at the selected
                                        location.</div>
                                </div>
                            </div>

                            <div class="booking-summary-panel mt-4" id="bookingTotalsPanel">
                                <div class="booking-summary-row">
                                    <span class="text-muted small">Vehicle total</span>
                                    <strong id="bookingTotalDisplay">R0.00</strong>
                                </div>
                                <div class="booking-summary-row border-0 pt-3">
                                    <div>
                                        <div class="text-muted small">Start date</div>
                                        <strong id="bookingPeriodStart">-</strong>
                                    </div>
                                    <div class="text-end">
                                        <div class="text-muted small">End date</div>
                                        <strong id="bookingPeriodEnd">-</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="booking-step-actions mt-4 pt-3 border-top d-flex justify-content-end">
                                <button type="button" class="btn btn-dark" id="bookingStep1Next"
                                    {{ $stocks->isEmpty() ? 'disabled' : '' }}>
                                    Continue
                                </button>
                            </div>
                        </div>

                        <div class="d-none" data-booking-step="1">
                            <div class="alert alert-danger d-none" id="bookingStep2Error"></div>

                            <h6 class="fw-bold mb-3">Your Details</h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="bookingName" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="bookingName" name="name"
                                        placeholder="John Smith" required>
                                </div>
                                <div class="col-12">
                                    <label for="bookingEmail" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="bookingEmail" name="email"
                                        placeholder="you@example.com" required>
                                </div>
                                <div class="col-12">
                                    <label for="bookingPhone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="bookingPhone" name="phone"
                                        placeholder="+27 123 456 7890" required>
                                </div>
                                <div class="col-12">
                                    <label for="bookingCountry" class="form-label">Country (optional)</label>
                                    <input type="text" class="form-control" id="bookingCountry" name="country"
                                        placeholder="South Africa">
                                </div>
                            </div>

                            <div class="booking-step-actions mt-4 pt-3 border-top d-flex gap-2 justify-content-between">
                                <button type="button" class="btn btn-outline-secondary"
                                    id="bookingStep2Back">
                                    Back
                                </button>
                                <button type="button" class="btn btn-dark" id="bookingStep2Next">
                                    Review Booking
                                </button>
                            </div>
                        </div>

                        <div class="d-none" data-booking-step="2">
                            <h6 class="fw-bold mb-3">Review & Confirm</h6>

                            <div class="booking-review mb-4">
                                <h6 class="fw-semibold mb-2">Rental Details</h6>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Plan</span>
                                        <span data-summary="unit">-</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Rate</span>
                                        <span data-summary="rate">-</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Quantity</span>
                                        <span data-summary="quantity">-</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Start</span>
                                        <span data-summary="start">-</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>End</span>
                                        <span data-summary="end">-</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Location</span>
                                        <span data-summary="location">-</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Units reserved</span>
                                        <span data-summary="stock_qty">-</span>
                                    </li>
                                </ul>
                            </div>

                            <div class="booking-review mb-4">
                                <h6 class="fw-semibold mb-2">Contact</h6>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Name</span>
                                        <span data-summary="name">-</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Email</span>
                                        <span data-summary="email">-</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Phone</span>
                                        <span data-summary="phone">-</span>
                                    </li>
                                </ul>
                            </div>

                            <div class="booking-total-card mb-3">
                                <span class="text-muted small">Total price</span>
                                <span class="h5 mb-0" data-summary="total">R0.00</span>
                            </div>

                            <div class="alert d-none" id="bookingSubmissionAlert" role="alert"></div>

                            <div class="booking-step-actions mt-4 pt-3 border-top d-flex gap-2 justify-content-between">
                                <button type="button" class="btn btn-outline-secondary"
                                    id="bookingStep3Back">
                                    Back
                                </button>
                                <button type="submit" class="btn btn-dark" id="bookingSubmitButton">
                                    Confirm Booking
                                </button>
                            </div>
                        </div>
                    </form>
                </div>


            </div>
        </div>
    </div>

    <style>
        #bookingModal .booking-stepper {
            display: flex;
            gap: .75rem;
            justify-content: space-between;
        }

        #bookingModal .booking-step {
            flex: 1;
            padding: .75rem;
            border-radius: 1rem;
            background: #f1f3f2;
            text-align: center;
            transition: all .2s ease;
        }

        #bookingModal .booking-step.completed {
            background: #dce8dc;
            color: #2d6a4f;
        }

        #bookingModal .booking-step.active {
            background: #679767;
            color: #fff;
        }

        #bookingModal .booking-step-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .2);
            margin-bottom: .35rem;
            font-weight: 600;
        }

        #bookingModal .booking-step-label {
            display: block;
            font-size: .8rem;
            font-weight: 600;
        }

        #bookingModal .booking-unit-card {
            width: 100%;
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            background: #fff;
            padding: 1rem;
            text-align: left;
            display: flex;
            flex-direction: column;
            gap: .5rem;
            transition: all .2s ease;
        }

        #bookingModal .booking-unit-card:hover {
            border-color: #679767;
            transform: translateY(-2px);
        }

        #bookingModal .booking-unit-card.active {
            border-color: #679767;
            box-shadow: 0 12px 24px rgba(103, 151, 103, .15);
        }

        #bookingModal .booking-unit-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: #f5faf5;
            color: #679767;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        #bookingModal .booking-summary-panel {
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            padding: 1rem;
            background: #f8faf8;
        }

        #bookingModal .booking-summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #e5e7eb;
            padding-top: .75rem;
            margin-top: .75rem;
        }

        #bookingModal .booking-summary-row:first-child {
            border-top: none;
            margin-top: 0;
            padding-top: 0;
        }

        #bookingModal .booking-review .list-group-item {
            padding-left: 0;
            padding-right: 0;
            border: none;
        }

        #bookingModal .booking-total-card {
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f5faf5;
        }

        #bookingModal .form-text {
            font-size: .75rem;
        }
    </style>

    <script>
        (() => {
            if (window.__bookingModalInitialized) {
                return;
            }
            window.__bookingModalInitialized = true;

            const modalEl = document.getElementById('bookingModal');
            if (!modalEl) {
                return;
            }

            const form = modalEl.querySelector('#bookingForm');
            if (!form) {
                return;
            }

            const steps = Array.from(modalEl.querySelectorAll('[data-booking-step]'));
            const stepIndicators = Array.from(modalEl.querySelectorAll('.booking-step'));
            const stepErrors = {
                0: modalEl.querySelector('#bookingStep1Error'),
                1: modalEl.querySelector('#bookingStep2Error'),
            };

            const unitCards = Array.from(modalEl.querySelectorAll('[data-unit-card]'));
            const rentalUnitInput = form.querySelector('#bookingRentalUnit');
            const quantitySelect = form.querySelector('#bookingQuantity');
            const quantityLabel = modalEl.querySelector('[data-quantity-label]');
            const startDateInput = form.querySelector('#bookingStartDate');
            const locationSelect = form.querySelector('#bookingLocation');
            const stockSelect = form.querySelector('#bookingStock');
            const stockIdInput = form.querySelector('#bookingStockIdInput');
            const totalInput = form.querySelector('#bookingTotalInput');

            const totalDisplay = modalEl.querySelector('#bookingTotalDisplay');
            const periodStartDisplay = modalEl.querySelector('#bookingPeriodStart');
            const periodEndDisplay = modalEl.querySelector('#bookingPeriodEnd');

            const submissionAlert = modalEl.querySelector('#bookingSubmissionAlert');
            const submitButton = modalEl.querySelector('#bookingSubmitButton');

            const summaryFields = {
                unit: modalEl.querySelector('[data-summary="unit"]'),
                rate: modalEl.querySelector('[data-summary="rate"]'),
                quantity: modalEl.querySelector('[data-summary="quantity"]'),
                start: modalEl.querySelector('[data-summary="start"]'),
                end: modalEl.querySelector('[data-summary="end"]'),
                location: modalEl.querySelector('[data-summary="location"]'),
                stockQty: modalEl.querySelector('[data-summary="stock_qty"]'),
                total: modalEl.querySelector('[data-summary="total"]'),
                name: modalEl.querySelector('[data-summary="name"]'),
                email: modalEl.querySelector('[data-summary="email"]'),
                phone: modalEl.querySelector('[data-summary="phone"]'),
            };

            const fmtCurrency = new Intl.NumberFormat('en-ZA', {
                style: 'currency',
                currency: 'ZAR',
                minimumFractionDigits: 2,
            });

            const fmtDate = new Intl.DateTimeFormat('en-GB', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
            });

            let currentStep = 0;

            function setStep(index) {
                currentStep = index;
                steps.forEach((stepEl, idx) => {
                    stepEl.classList.toggle('d-none', idx !== index);
                });

                stepIndicators.forEach((indicator, idx) => {
                    indicator.classList.toggle('active', idx === index);
                    indicator.classList.toggle('completed', idx < index);
                });
            }

            function showStepError(index, message) {
                const target = stepErrors[index];
                if (!target) {
                    return;
                }
                if (!message) {
                    target.classList.add('d-none');
                    target.textContent = '';
                    return;
                }
                target.textContent = message;
                target.classList.remove('d-none');
            }

            function clearSubmissionAlert() {
                if (!submissionAlert) {
                    return;
                }
                submissionAlert.className = 'alert d-none';
                submissionAlert.textContent = '';
            }

            function showSubmissionAlert(type, message) {
                if (!submissionAlert) {
                    return;
                }
                submissionAlert.className = `alert alert-${type}`;
                submissionAlert.textContent = message;
            }

            function selectedUnitCard() {
                return unitCards.find((card) => card.classList.contains('active'));
            }

            function ensureSelectOptions(selectEl, count, formatter) {
                if (!selectEl) return;
                const currentValue = parseInt(selectEl.value || '1', 10) || 1;
                selectEl.innerHTML = '';
                for (let i = 1; i <= count; i += 1) {
                    const option = document.createElement('option');
                    option.value = String(i);
                    option.textContent = formatter(i);
                    if (i === Math.min(currentValue, count)) {
                        option.selected = true;
                    }
                    selectEl.appendChild(option);
                }
            }

            function updateQuantityOptions(unit) {
                if (!quantitySelect) {
                    return;
                }

                let max = 30;
                let label = 'Number of days';
                if (unit === 'week') {
                    max = 12;
                    label = 'Number of weeks';
                } else if (unit === 'month') {
                    max = 12;
                    label = 'Number of months';
                }

                if (quantityLabel) {
                    quantityLabel.textContent = label;
                }

                ensureSelectOptions(quantitySelect, max, (i) => `${i}`);
            }

            function updateStockSelect() {
                if (!locationSelect || !stockSelect || !stockIdInput) {
                    return;
                }

                if (locationSelect.disabled || locationSelect.options.length === 0) {
                    stockSelect.innerHTML = '<option value="0">Not available</option>';
                    stockSelect.disabled = true;
                    stockIdInput.value = '';
                    return;
                }

                const option = locationSelect.options[locationSelect.selectedIndex];
                const available = parseInt(option?.dataset.available || '0', 10) || 0;
                const stockId = option?.dataset.stockId || '';
                stockIdInput.value = stockId;

                stockSelect.innerHTML = '';

                if (available <= 0) {
                    const noOption = document.createElement('option');
                    noOption.value = '0';
                    noOption.textContent = 'Not available';
                    stockSelect.appendChild(noOption);
                    stockSelect.disabled = true;
                    return;
                }

                stockSelect.disabled = false;
                for (let i = 1; i <= Math.min(available, 20); i += 1) {
                    const newOption = document.createElement('option');
                    newOption.value = String(i);
                    newOption.textContent = `${i} unit${i === 1 ? '' : 's'}`;
                    if (i === 1) newOption.selected = true;
                    stockSelect.appendChild(newOption);
                }
            }

            function computeTotals() {
                const unitCard = selectedUnitCard();
                const unit = rentalUnitInput?.value || unitCard?.dataset.unit || '';
                const pricePerUnit = parseFloat(unitCard?.dataset.price || '0') || 0;
                const quantity = parseInt(quantitySelect?.value || '0', 10) || 0;
                const unitsReserved = parseInt(stockSelect?.value || '1', 10) || 1;

                const startValue = startDateInput?.value ? `${startDateInput.value}T00:00:00` : '';
                const startDate = startValue ? new Date(startValue) : null;

                const total = Math.round(pricePerUnit * quantity * unitsReserved * 100) / 100;

                let endDate = null;

                if (startDate) {
                    endDate = new Date(startDate);
                    if (unit === 'week') {
                        endDate.setDate(endDate.getDate() + (quantity * 7) - 1);
                    } else if (unit === 'month') {
                        endDate.setMonth(endDate.getMonth() + quantity);
                        endDate.setDate(endDate.getDate() - 1);
                    } else {
                        endDate.setDate(endDate.getDate() + quantity - 1);
                    }
                }

                return {
                    total,
                    endDate,
                    unit,
                    quantity,
                    unitsReserved,
                    pricePerUnit,
                };
            }

            function niceDate(ymd) {
                if (!ymd) return '-';
                const parsed = new Date(`${ymd}T00:00:00`);
                if (Number.isNaN(parsed.getTime())) return '-';
                return fmtDate.format(parsed);
            }

            function updateTotalsAndSummary() {
                const totals = computeTotals();
                if (totalDisplay) {
                    totalDisplay.textContent = fmtCurrency.format(totals.total);
                }
                if (totalInput) {
                    totalInput.value = totals.total.toFixed(2);
                }

                const startValue = startDateInput?.value || '';
                if (periodStartDisplay) {
                    periodStartDisplay.textContent = niceDate(startValue);
                }
                if (periodEndDisplay) {
                    const endYmd = totals.endDate
                        ? `${totals.endDate.getFullYear()}-${String(totals.endDate.getMonth() + 1).padStart(2, '0')}-${String(totals.endDate.getDate()).padStart(2, '0')}`
                        : '';
                    periodEndDisplay.textContent = niceDate(endYmd);
                }

                updateSummary(totals);
            }

            function updateSummary(totalsOverride) {
                const unitCard = selectedUnitCard();
                const totals = totalsOverride || computeTotals();

                if (summaryFields.unit) {
                    summaryFields.unit.textContent = unitCard
                        ? unitCard.querySelector('.booking-unit-title')?.textContent || '-'
                        : '-';
                }
                if (summaryFields.rate) {
                    const suffix = unitCard?.dataset.suffix || '';
                    summaryFields.rate.textContent = totals.pricePerUnit
                        ? `${fmtCurrency.format(totals.pricePerUnit)}${suffix}`
                        : '-';
                }
                if (summaryFields.quantity) {
                    const qty = parseInt(quantitySelect?.value || '0', 10) || 0;
                    const unit = totals.unit || 'unit';
                    summaryFields.quantity.textContent = qty
                        ? `${qty} ${unit}${qty === 1 ? '' : 's'}`
                        : '-';
                }
                if (summaryFields.start) {
                    summaryFields.start.textContent = niceDate(startDateInput?.value || '');
                }
                if (summaryFields.end) {
                    summaryFields.end.textContent = totals.endDate
                        ? fmtDate.format(totals.endDate)
                        : '-';
                }
                if (summaryFields.location && locationSelect) {
                    const option = locationSelect.options[locationSelect.selectedIndex];
                    summaryFields.location.textContent = option
                        ? option.dataset.locationName || option.textContent
                        : '-';
                }
                if (summaryFields.stockQty) {
                    const unitsReserved = totals.unitsReserved || 1;
                    summaryFields.stockQty.textContent = `${unitsReserved} unit${unitsReserved === 1 ? '' : 's'}`;
                }
                if (summaryFields.total) {
                    summaryFields.total.textContent = fmtCurrency.format(totals.total);
                }
                if (summaryFields.name) {
                    summaryFields.name.textContent = form.elements.name?.value?.trim() || '-';
                }
                if (summaryFields.email) {
                    summaryFields.email.textContent = form.elements.email?.value?.trim() || '-';
                }
                if (summaryFields.phone) {
                    summaryFields.phone.textContent = form.elements.phone?.value?.trim() || '-';
                }
            }

            function validateStep1() {
                showStepError(0, '');

                const unitCard = selectedUnitCard();
                if (!unitCard) {
                    showStepError(0, 'Please select a rental duration.');
                    return false;
                }

                if (!startDateInput?.value) {
                    showStepError(0, 'Please choose a start date.');
                    return false;
                }

                if (!locationSelect || locationSelect.options.length === 0 || locationSelect.disabled) {
                    showStepError(0, 'No locations are available for this item at the moment.');
                    return false;
                }

                const option = locationSelect.options[locationSelect.selectedIndex];
                const available = parseInt(option?.dataset.available || '0', 10) || 0;

                if (available <= 0) {
                    showStepError(0, 'Selected location currently has no stock available.');
                    return false;
                }

                return true;
            }

            function validateStep2() {
                showStepError(1, '');
                const requiredFields = ['name', 'email', 'phone'];
                for (const fieldName of requiredFields) {
                    const field = form.elements[fieldName];
                    if (!field || !field.value.trim()) {
                        showStepError(1, 'Please fill in all required contact fields.');
                        return false;
                    }
                }
                return true;
            }

            function resetFormState() {
                clearSubmissionAlert();
                showStepError(0, '');
                showStepError(1, '');

                form.reset();

                const firstCard = unitCards[0];
                unitCards.forEach((card) => card.classList.remove('active'));
                if (firstCard) {
                    firstCard.classList.add('active');
                    rentalUnitInput.value = firstCard.dataset.unit || '';
                }

                updateQuantityOptions(rentalUnitInput.value || firstCard?.dataset.unit || 'day');

                if (startDateInput && startDateInput.getAttribute('min')) {
                    startDateInput.value = startDateInput.getAttribute('min');
                }

                if (locationSelect && locationSelect.options.length) {
                    locationSelect.selectedIndex = 0;
                }

                updateStockSelect();
                updateTotalsAndSummary();
                setStep(0);

                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = 'Confirm Booking';
                }
            }

            unitCards.forEach((card) => {
                card.addEventListener('click', () => {
                    unitCards.forEach((c) => c.classList.remove('active'));
                    card.classList.add('active');
                    rentalUnitInput.value = card.dataset.unit || '';
                    updateQuantityOptions(card.dataset.unit || 'day');
                    updateTotalsAndSummary();
                });
            });

            if (quantitySelect) {
                quantitySelect.addEventListener('change', updateTotalsAndSummary);
            }

            if (startDateInput) {
                startDateInput.addEventListener('change', updateTotalsAndSummary);
            }

            if (locationSelect) {
                locationSelect.addEventListener('change', () => {
                    updateStockSelect();
                    updateTotalsAndSummary();
                });
            }

            if (stockSelect) {
                stockSelect.addEventListener('change', updateTotalsAndSummary);
            }

            ['name', 'email', 'phone', 'country'].forEach((fieldName) => {
                const field = form.elements[fieldName];
                if (field) {
                    field.addEventListener('input', updateSummary);
                }
            });

            const step1Next = modalEl.querySelector('#bookingStep1Next');
            if (step1Next) {
                step1Next.addEventListener('click', () => {
                    if (!validateStep1()) {
                        return;
                    }
                    updateSummary();
                    setStep(1);
                });
            }

            const step2Back = modalEl.querySelector('#bookingStep2Back');
            if (step2Back) {
                step2Back.addEventListener('click', () => setStep(0));
            }

            const step2Next = modalEl.querySelector('#bookingStep2Next');
            if (step2Next) {
                step2Next.addEventListener('click', () => {
                    if (!validateStep2()) {
                        return;
                    }
                    updateSummary();
                    setStep(2);
                });
            }

            const step3Back = modalEl.querySelector('#bookingStep3Back');
            if (step3Back) {
                step3Back.addEventListener('click', () => setStep(1));
            }

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                clearSubmissionAlert();

                if (!validateStep1() || !validateStep2()) {
                    setStep(!validateStep1() ? 0 : 1);
                    return;
                }

                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
                }

                try {
                    const formData = new FormData(form);
                    formData.set('extra_days', '0');
                    formData.set('rental_unit', rentalUnitInput.value || '');
                    formData.set('stock_quantity', stockSelect?.value || '1');

                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                        },
                        body: formData,
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        const message = data?.message || 'We could not complete your booking. Please try again.';
                        showSubmissionAlert('danger', message);
                        return;
                    }

                    showSubmissionAlert('success', 'Booking confirmed! We have sent a confirmation email.');
                    updateSummary();
                } catch (error) {
                    showSubmissionAlert('danger', 'Something went wrong. Please try again.');
                } finally {
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.innerHTML = 'Confirm Booking';
                    }
                }
            });

            modalEl.addEventListener('shown.bs.modal', resetFormState);
            modalEl.addEventListener('hidden.bs.modal', resetFormState);

            updateStockSelect();
            updateTotalsAndSummary();
        })();
    </script>
@else
    {{-- Booking modal unavailable because pricing or category data is missing --}}
@endif
