@php
    $isEquipmentBooking = isset($equipment);
    $bookable = $isEquipmentBooking ? $equipment : $vehicle ?? null;
    $bookableLabel = $isEquipmentBooking ? 'Equipment' : 'Vehicle';
    $bookableName = $bookable->name ?? '';
    $bookableDescription = $bookable->description ?? '';
    $bookableModel = $isEquipmentBooking ? $equipment->category->name ?? '' : $vehicle->model ?? '';
    $pricingDay = $isEquipmentBooking ? ($equipment->daily_price ?: null) : $vehicle->rental_price_day ?? null;
    $pricingWeek = $isEquipmentBooking ? ($equipment->weekly_price ?: null) : $vehicle->rental_price_week ?? null;
    $pricingMonth = $isEquipmentBooking ? ($equipment->monthly_price ?: null) : $vehicle->rental_price_month ?? null;
    $bookingLeadDays = $isEquipmentBooking
        ? (int) ($equipment->category->booking_lead_days ?? 0)
        : (int) ($vehicle->booking_lead_days ?? 0);
    $bookableImage = $isEquipmentBooking
        ? asset('storage/' . ($equipment->image ?? ''))
        : $vehicle->mainImage() ?? null;
    $categoryId = $isEquipmentBooking ? $equipment->category_id ?? null : $vehicle->category_id ?? null;
    $locationId = $isEquipmentBooking
        ? optional($equipment->stocks->first())->location_id
        : $vehicle->location_id ?? null;

    $bookedRanges = collect($bookedRanges ?? [])
        ->map(function ($range) {
            return [
                'from' => $range['from'] ?? null,
                'to' => $range['to'] ?? null,
            ];
        })
        ->filter(fn($range) => $range['from'] && $range['to'])
        ->values()
        ->toArray();

    $locationOptions = collect($locationOptions ?? []);
    $locationBookings = collect($locationBookings ?? []);
    $locationFullyBooked = collect($locationFullyBooked ?? [])
        ->mapWithKeys(function ($ranges, $locationKey) {
            $items = collect($ranges)
                ->map(function ($range) {
                    return [
                        'from' => $range['from'] ?? null,
                        'to' => $range['to'] ?? null,
                    ];
                })
                ->filter(fn($range) => $range['from'] && $range['to'])
                ->values();

            return [(string) $locationKey => $items];
        })
        ->toArray();

    if ($isEquipmentBooking && isset($equipment)) {
        if ($locationOptions->isEmpty()) {
            $locationOptions = ($equipment->stocks ?? collect())->map(function ($stock) {
                return [
                    'id' => $stock->location?->id,
                    'name' => $stock->location?->name ?? 'Location',
                    'stock' => (int) ($stock->stock ?? 0),
                ];
            });
        }
    } elseif (isset($vehicle)) {
        if ($locationOptions->isEmpty()) {
            $locationOptions = collect([
                [
                    'id' => $locationId ?? optional($vehicle->branch ?? null)->id,
                    'name' => optional($vehicle->branch ?? null)->name ?? ($vehicle->location ?? 'Primary Location'),
                    'stock' => null,
                ],
            ]);
        }
    }

    $locationOptions = $locationOptions
        ->filter(fn($loc) => !empty($loc['id']) || !empty($loc['name']))
        ->unique(fn($loc) => $loc['id'] ?? $loc['name'])
        ->values();

    if (!$locationId && $locationOptions->count() === 1) {
        $locationId = $locationOptions->first()['id'] ?? null;
    }

    $locationInventory = $locationOptions
        ->filter(fn($loc) => !empty($loc['id']))
        ->mapWithKeys(function ($loc) {
            return [
                (string) $loc['id'] => [
                    'stock' => isset($loc['stock']) ? (int) $loc['stock'] : null,
                    'name' => $loc['name'] ?? 'Location',
                ],
            ];
        })
        ->toArray();

    $locationBookingMap = $locationBookings
        ->mapWithKeys(function ($bookings, $locationKey) {
            if (is_null($locationKey)) {
                return [];
            }

            $items = collect($bookings)
                ->map(function ($booking) {
                    return [
                        'from' => $booking['from'] ?? null,
                        'to' => $booking['to'] ?? null,
                        'units' => (int) ($booking['units'] ?? 1),
                    ];
                })
                ->filter(fn($item) => $item['from'] && $item['to'])
                ->values();

            return [(string) $locationKey => $items];
        })
        ->toArray();

    $showLocationSelect = $locationOptions->contains(function ($loc) {
        return !empty($loc['id']);
    });

    $continueBrowseLabel = $isEquipmentBooking ? 'Equipment' : 'Vehicles';
    $continueBrowseUrl = $isEquipmentBooking ?: url('/');
@endphp

{{-- Booking FORM --}}
<form id="bookingForm" method="POST" action="{{ route('bookings.store') }}">
    @csrf

    @if ($isEquipmentBooking)
        <input type="hidden" name="equipment_id" value="{{ $equipment->id }}">
    @else
        <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
    @endif
    <input type="hidden" name="category_id" value="{{ $categoryId ?? '' }}">
    <input type="hidden" name="location_id" id="inputLocationId" value="{{ $locationId ?? '' }}">
    <input type="hidden" name="stock_quantity" id="inputStockQuantity" value="1">
    <input type="hidden" name="rental_unit" id="inputRentalUnit">
    <input type="hidden" name="rental_quantity" id="inputRentalQuantity">
    <input type="hidden" name="rental_start_date" id="inputRentalStartDate">
    <input type="hidden" name="extra_days" id="inputExtraDays" value="0">
    <input type="hidden" name="total_price" id="inputTotalPrice">
    <input type="hidden" name="booking_id" id="bookingId">

    <!-- Step 1: Multi-Step Booking Modal -->
    <div class="modal fade" id="multiStepBookingModal" tabindex="-1" aria-hidden="true"
        style="height: 90vh; margin-top: 4rem;">
        <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered modal-fullscreen-sm-down">
            <div class="modal-content rounded-4 shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-calendar-check me-2"></i> Book {{ $bookableName }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h5 class="mb-3 text-center">Select Rental Duration</h5>
                    <div class="row text-center g-3 text-muted">
                        @if ($pricingDay)
                            <div class="col-md-4">
                                <div class="option-card p-3 border rounded-4 bg-light h-100" data-type="day"
                                    data-price="{{ $pricingDay }}">
                                    <i class="bi bi-clock display-6" style="color: #CF9B4D"></i>
                                    <h6 class="mt-2">Daily Rental</h6>
                                    <p class="small text-muted mb-1">Perfect for short trips</p>
                                    <div class="text-dark">R{{ number_format($pricingDay) }}/day</div>
                                </div>
                            </div>
                        @endif

                        @if ($pricingWeek)
                            <div class="col-md-4">
                                <div class="option-card p-3 border rounded-4 h-100" data-type="week"
                                    data-price="{{ $pricingWeek }}">
                                    <i class="bi bi-calendar-event display-6" style="color: #CF9B4D"></i>
                                    <h6 class="mt-2">Weekly Rental</h6>
                                    <p class="small text-muted mb-1">Great for 1-4 weeks</p>
                                    <div class="text-dark">R{{ number_format($pricingWeek) }}/week</div>
                                </div>
                            </div>
                        @endif

                        @if ($pricingMonth)
                            <div class="col-md-4">
                                <div class="option-card p-3 border rounded-4 h-100" data-type="month"
                                    data-price="{{ $pricingMonth }}">
                                    <i class="bi bi-box display-6" style="color: #CF9B4D"></i>
                                    <h6 class="mt-2">Monthly Rental</h6>
                                    <p class="small text-muted mb-1">Best for long stays</p>
                                    <div class="text-dark">R{{ number_format($pricingMonth) }}/month</div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Date Section -->
                    <div id="dateSection" class="mb-3 mt-3 d-none">
                        <div class="position-relative w-100">
                            <input type="text" id="rentalStartDate" class="form-control ps-5 w-100"
                                placeholder="Select a start date" readonly data-lead="{{ $bookingLeadDays }}"
                                data-blocked='@json($bookedRanges)'>
                            <span class="position-absolute top-50 start-0 translate-middle-y ps-3">
                                <i class="bi bi-calendar-event"></i>
                            </span>
                        </div>
                    </div>

                    <!-- Quantity & Extra Days -->
                    <div class="row">
                        <!-- Quantity -->
                        <div class="col-md-6 mb-3 d-none" id="quantitySection">
                            <label for="rentalQuantity" class="form-label" id="quantityLabel"></label>
                            <select id="rentalQuantity" class="form-select rounded-3"></select>
                        </div>

                        <!-- Extra Days -->
                        <div class="col-md-6 mb-3 d-none" id="extraDaysSection">
                            <label for="extraDaysInput" class="form-label">Extra day(s)</label>
                            <input type="number" min="0" step="1" value="0" class="form-control"
                                id="extraDaysInput" inputmode="numeric">
                            <div class="form-text" id="extraDaysHelp">Add additional day(s) on top of the selected
                                duration.</div>
                        </div>
                    </div>

                    @if ($showLocationSelect)
                        @php
                            $selectedLocation = $locationId ? $locationOptions->firstWhere('id', $locationId) : null;
                            $initialHint = $selectedLocation
                                ? (($selectedLocation['stock'] ?? null) !== null
                                    ? $selectedLocation['stock'] . ' in stock'
                                    : 'Location selected')
                                : 'Select a location to view availability.';
                        @endphp
                        <div class="row g-3" id="locationRow">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="bookingLocationSelect">Select
                                    Location</label>
                                <select class="form-select" id="bookingLocationSelect"
                                    {{ $locationOptions->count() === 1 ? 'data-single="true"' : '' }}>
                                    @if (!$locationId)
                                        <option value="" selected disabled>Select a location</option>
                                    @endif
                                    @foreach ($locationOptions as $loc)
                                        <option value="{{ $loc['id'] ?? '' }}"
                                            data-base-stock="{{ $loc['stock'] ?? '' }}"
                                            data-display-name="{{ $loc['name'] ?? 'Location' }}"
                                            {{ (string) ($locationId ?? '') === (string) ($loc['id'] ?? '') ? 'selected' : '' }}>
                                            {{ $loc['name'] ?? 'Location' }}
                                            @if (!is_null($loc['stock']))
                                                ({{ $loc['stock'] }} in stock)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="stockQuantitySelect">Stock Quantity</label>
                                <select class="form-select" id="stockQuantitySelect">
                                    <option value="" selected>Select quantity</option>
                                </select>
                                <div class="form-text" id="locationAvailabilityHint">{{ $initialHint }}</div>
                            </div>
                        </div>
                    @endif

                    <!-- Total Price -->
                    <div class="alert alert-info fw-bold d-none" id="totalPrice"></div>

                    <!-- Rental Period -->
                    <div class="alert alert-secondary fw-bold d-none" id="rentalPeriod"></div>
                </div>

                <div class="modal-footer d-block">
                    <button type="button" id="continueFromStep1" class="btn btn-dark rounded-3 w-100">Continue to
                        Details</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 2: Customer Details Modal -->
    <div class="modal fade" id="customerStep" tabindex="-1" aria-hidden="true" style="margin-bottom: 10rem">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content rounded-4 shadow">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-person-circle me-2"></i>Enter Your Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body px-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control rounded-3" name="name"
                                placeholder="John Doe" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control rounded-3" name="email"
                                placeholder="you@example.com" inputmode="email" autocomplete="email" required
                                pattern="^([^\s@]+)@([^\s@]+)\.[^\s@]{2,}$"
                                title="Enter a valid email address, e.g. you@example.com">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" class="form-control rounded-3" name="phone"
                                placeholder="+27 123 456 7890" inputmode="tel" required
                                pattern="^\+?[0-9]{1,4}(?:[\s-]?[0-9]{2,4}){2,4}$"
                                title="Use digits, optional spaces or dashes, e.g. +27 123 456 7890">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <input type="text" id="bookingCustomerCountry" name="country"
                                class="form-control rounded-3" placeholder="Start typing your address..."
                                autocomplete="street-address" required>
                            <small class="text-muted">Use the suggestions to pick your full address.</small>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-3"
                        id="customerBackToStep1">Back</button>
                    <button type="button" id="goToSummary" class="btn btn-dark rounded-3 px-4">Review
                        Booking</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 3: Booking Summary -->
    <div class="modal fade mt-5" id="summaryStep" tabindex="-1" aria-hidden="true"
        style="height: 90vh; margin-top: 4rem;">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content rounded-4 shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-clipboard-check me-2"></i> Booking Summary</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h4 class="fw-bold mb-3 text-center">Review Your Booking</h4>
                    <p class="text-center text-muted">Please review your booking details before proceeding to payment
                    </p>

                    <div class="border rounded p-3 mb-3 bg-light">
                        <h6 class="fw-semibold">{{ $bookableLabel }}</h6>
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $bookableImage }}" class="rounded"
                                style="width:80px; height:80px; object-fit:cover;">
                            <div>
                                <p class="fw-bold mb-1">{{ $bookableName }}</p>
                                <p class="text-muted small">{{ $bookableDescription ?? '' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded p-3 mb-3 bg-light">
                        <h6 class="fw-semibold">Rental Details</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="text-muted small mb-1">Rental Type</p>
                                <p class="fw-bold" id="summaryType"></p>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted small mb-1">Period</p>
                                <p class="fw-bold" id="summaryPeriod"></p>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted small mb-1">Location</p>
                                <p class="fw-bold" id="summaryLocation"></p>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted small mb-1">Quantity</p>
                                <p class="fw-bold" id="summaryUnits">1 unit</p>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded p-3 mb-3 bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-semibold mb-0">Price Breakdown</h6>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span>{{ $bookableLabel }} rental</span>
                            <span id="summaryVehicleTotal">R0.00</span>
                        </div>
                        <div class="d-flex justify-content-between fw-semibold border-top pt-2">
                            <span>Grand total</span>
                            <span class="text-success" id="summaryGrandTotal">R0.00</span>
                        </div>
                    </div>

                    <div class="border rounded p-3 mb-3 bg-light">
                        <h6 class="fw-semibold">Customer Details</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="small text-muted mb-1">Name</p>
                                <p class="fw-bold" id="summaryCustomerName"></p>
                            </div>
                            <div class="col-md-6">
                                <p class="small text-muted mb-1">Email</p>
                                <p class="fw-bold" id="summaryCustomerEmail"></p>
                            </div>
                            <div class="col-md-6">
                                <p class="small text-muted mb-1">Phone</p>
                                <p class="fw-bold" id="summaryCustomerPhone"></p>
                            </div>
                            <div class="col-md-6">
                                <p class="small text-muted mb-1">Address</p>
                                <p class="fw-bold" id="summaryCustomerCountry"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <div class="d-flex justify-content-between w-100">
                        <button type="button" class="btn btn-outline-secondary"
                            id="summaryBackToCustomer">Back</button>
                        <button type="button" id="openPayment" class="btn btn-dark rounded-3">Continue to
                            Payment</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@php
    use App\Models\SystemSetting;
    use App\Models\StripeSetting;
    use Illuminate\Support\Facades\Cache;

    if (app()->environment('local')) {
        $settings =
            SystemSetting::first() ?: new SystemSetting(['stripe_enabled' => false, 'payfast_enabled' => false]);
    } else {
        $settings = Cache::remember('payments.settings', 60, function () {
            return SystemSetting::first() ?: new SystemSetting(['stripe_enabled' => false, 'payfast_enabled' => false]);
        });
    }

    $stripeConfig = StripeSetting::first();
    $enabledCount = ($settings->stripe_enabled ? 1 : 0) + ($settings->payfast_enabled ? 1 : 0);
@endphp

<!-- Payment Method Selection -->
<div class="modal fade" id="bookingPayment" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-credit-card-fill me-2"></i> Select Payment Method</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="row g-3 align-items-stretch justify-content-center">
                    @if ($settings->stripe_enabled)
                        <div class="col-12 {{ $enabledCount === 2 ? 'col-md-6' : 'col-md-12' }}">
                            <input type="radio" name="booking_payment_method" id="bookingStripe" value="stripe"
                                class="btn-check" autocomplete="off" required>
                            <label for="bookingStripe" class="card btn w-100 booking-pay-option p-3 flex-column">
                                <div class="text-center mb-2">
                                    <img src="{{ asset('images/stripe.png') }}" class="rounded-3" alt="Stripe"
                                        style="width: 80px;">
                                </div>
                                <div class="booking-pay-text text-center">
                                    <div class="fw-bold">Stripe (Card)</div>
                                    <small class="text-muted">Visa .Mastercard. Amex</small>
                                </div>
                            </label>
                        </div>
                    @endif

                    @if ($settings->payfast_enabled)
                        <div class="col-12 {{ $enabledCount === 2 ? 'col-md-6' : 'col-md-12' }}">
                            <input type="radio" name="booking_payment_method" id="bookingPayfast" value="payfast"
                                class="btn-check" autocomplete="off" required>
                            <label for="bookingPayfast" class="card btn w-100 booking-pay-option p-3 flex-column">
                                <div class="text-center mb-2">
                                    <img src="{{ asset('images/payfast.png') }}" class="rounded-3" alt="PayFast"
                                        style="width: 80px;">
                                </div>
                                <div class="booking-pay-text text-center">
                                    <div class="fw-bold">PayFast</div>
                                    <small class="text-muted">South Africa payments</small>
                                </div>
                            </label>
                        </div>
                    @endif

                    @if ($enabledCount === 0)
                        <div class="col-12">
                            <div class="alert alert-warning text-center mb-0">No payment methods are currently
                                available.</div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-outline-secondary" id="paymentBackToSummary">Back</button>
            </div>
        </div>
    </div>
</div>

<!-- Stripe Payment Modal -->
<div class="modal fade" id="bookingStripeModal" tabindex="-1" aria-hidden="true"
    style="margin-top: 4rem; height:90vh;">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content rounded-4 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-credit-card-fill me-2"></i> Stripe Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="booking-card-element" class="mt-3">
                    <div id="booking-card-number" class="form-control mb-3"></div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div id="booking-card-expiry" class="form-control"></div>
                        </div>
                        <div class="col-md-6">
                            <div id="booking-card-cvc" class="form-control"></div>
                        </div>
                    </div>
                    <div id="booking-card-errors" class="text-danger mt-2"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary me-auto"
                    id="stripeBackToPayment">Back</button>
                <button type="button" id="bookingStripePayButton" class="btn btn-dark">Pay with Stripe</button>
            </div>
        </div>
    </div>
</div>

<!-- Thank You Modal -->
<div class="modal fade" id="bookingThankYou" tabindex="-1" aria-hidden="true">
    <div class="container modal-dialog modal-fullscreen-md-down custom-modal-dialog " style="margin-top: 7rem">
        <div class="modal-content rounded-4 shadow border-0">
            <div class="modal-body p-4 p-md-5">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10"
                        style="width:56px;height:56px;">
                        <i class="bi bi-check-lg text-success fs-3"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1">Payment Successful!</h4>
                        <div class="text-muted small">Your booking deposit has been processed successfully.</div>
                    </div>
                </div>

                <div class="border border-success-subtle rounded-3 p-3 p-md-4 mb-4 bg-success bg-opacity-10">
                    <div class="fw-semibold mb-3">
                        Booking Reference: <span class="text-success" id="tyReference">N/A</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-box-seam mt-1"></i>
                                <div>
                                    <div class="small text-muted">{{ $bookableLabel }}</div>
                                    <div class="fw-semibold" id="tyVehicleName">N/A</div>
                                    <div class="text-muted small" id="tyVehicleSub"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-person mt-1"></i>
                                <div>
                                    <div class="small text-muted">Primary renter</div>
                                    <div class="fw-semibold" id="tyCustomerName">N/A</div>
                                    <div class="text-muted small" id="tyCustomerContact">N/A</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-calendar-event mt-1"></i>
                                <div>
                                    <div class="small text-muted">Rental period</div>
                                    <div class="fw-semibold" id="tyPeriod"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-credit-card mt-1"></i>
                                <div>
                                    <div class="small text-muted">Deposit paid</div>
                                    <div class="fw-semibold">
                                        <span id="tyAmount">R0.00</span>
                                        <span class="text-muted small" id="tyMethod"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-3 p-3 p-md-4 border bg-light">
                    <div class="fw-semibold text-center mb-2">What happens next?</div>
                    <div class="text-muted small text-center">
                        Your booking is now <strong>under offer</strong> and pending confirmation.
                        We'll be in touch shortly to finalize the details and arrange vehicle handover.
                        Please keep your booking reference safe for future correspondence.
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 pt-0 px-4 px-md-5 pb-4 d-flex flex-wrap gap-2 justify-content-between">
                <a href="/" class="btn btn-outline-secondary rounded-3" id="tyContinueVehicles">
                    Continue to Categories
                </a>

                <a href="https://api.whatsapp.com/send?phone=27673285525&text=Hi%20Wayne%2C%20I%27m%20contacting%20your%20from%20your%20Rent2Recover%20website"
                    class="btn btn-success fw-bold rounded-3 d-flex align-items-center gap-2" target="_blank"
                    id="tyWhatsappBtn" rel="noopener">
                    <i class="bi bi-whatsapp fs-5"></i>Chat with Us
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    #bookingThankYou .custom-modal-dialog {
        max-width: none;
        width: calc(100vw - 2rem);
        margin: 1rem auto;
    }

    @media (min-width: 1200px) {
        #bookingThankYou .custom-modal-dialog {
            width: calc(100vw - 4rem);
            margin: 2rem auto;
        }
    }

    #bookingPayment .booking-pay-option {
        min-height: 160px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        border: 1px solid #dee2e6;
        border-radius: .75rem;
        padding: 20px;
        text-align: left;
        transition: box-shadow .15s ease, transform .15s ease, border-color .15s ease;
    }

    #bookingPayment .booking-pay-option:hover {
        transform: translateY(-2px);
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .08);
    }

    #bookingPayment .btn-check:checked+.booking-pay-option {
        border-color: #0d6efd;
        box-shadow: 0 0 0 .25rem rgba(13, 110, 253, .2);
    }

    #stockQuantitySelect:disabled {
        background-color: #f8f9fa;
        opacity: 0.6;
    }

    #locationAvailabilityHint.text-danger {
        font-weight: 600;
    }

    #summaryUnits {
        color: #198754;
        font-weight: 600;
    }

    @media (min-width: 768px) {
        #bookingPayment .col-md-6 {
            display: flex;
        }

        #bookingPayment .booking-pay-option {
            width: 100%;
        }
    }

    /* Make Google Places dropdown visible above all modals */
    .pac-container {
        z-index: 9999 !important;
    }

    /* FULL WIDTH CALENDAR STYLES */
    #dateSection {
        width: 100%;
    }

    #rentalStartDate {
        width: 100% !important;
        display: block;
        padding: 12px 16px 12px 45px;
        font-size: 16px;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        background-color: white;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    #rentalStartDate:focus {
        border-color: #CF9B4D;
        box-shadow: 0 0 0 0.2rem rgba(207, 155, 77, 0.25);
        outline: none;
    }

    #dateSection .position-relative {
        width: 100%;
    }

    #dateSection .position-absolute {
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        z-index: 1;
        color: #6c757d;
    }

    /* Ensure calendar opens above modals and has proper width */
    .flatpickr-calendar {
        z-index: 99999 !important;
        width: 100% !important;
        max-width: 42% !important;
    }

    .flatpickr-wrapper {
        width: 100% !important;
    }

    /* Mobile responsiveness */
    @media (max-width: 768px) {
        #rentalStartDate {
            padding: 14px 16px 14px 45px;
            font-size: 16px;
        }

        #dateSection .position-absolute {
            left: 16px;
        }
    }

    /* Make the calendar input container full width */
    #dateSection .form-control {
        width: 100% !important;
    }

    /* Remove any max-width constraints */
    #multiStepBookingModal .modal-dialog {
        max-width: 800px;
    }

    #multiStepBookingModal .modal-body {
        padding: 20px;
    }

    /* Ensure the date section takes full width */
    #dateSection .row {
        width: 100%;
        margin: 0;
    }

    #dateSection .col-12 {
        padding: 0;
    }

    /* Make option cards more interactive */
    .option-card {
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .option-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .option-card.active {
        border-color: #CF9B4D !important;
        background-color: #fff9f0 !important;
    }
</style>

<script src="https://js.stripe.com/v3/"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- GOOGLE PLACES AUTOCOMPLETE CALLBACK (GLOBAL, BEFORE MAPS SCRIPT) --}}
<script>
    window.initCustomerAutocomplete = function() {
        var input = document.getElementById('bookingCustomerCountry');
        if (!input) {
            return;
        }

        if (!window.google || !google.maps || !google.maps.places) {
            console.warn('Google Places library not available.');
            return;
        }

        var autocomplete = new google.maps.places.Autocomplete(input, {
            types: ['geocode'],
            fields: ['formatted_address', 'geometry', 'address_components']
        });

        autocomplete.addListener('place_changed', function() {
            var place = autocomplete.getPlace();
            if (place && place.formatted_address) {
                input.value = place.formatted_address;
            }
        });

        window.bookingAddressAutocomplete = autocomplete;
    };
</script>

{{-- LOAD GOOGLE MAPS (PLACES) WITH CALLBACK --}}
<script
    src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps.key') }}&libraries=places&callback=initCustomerAutocomplete"
    async defer></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        /* =========================
           GLOBAL MODAL STACKING
           ========================= */
        const Z_BASE = 1055;
        const Z_STEP = 20;

        function visibleModals() {
            return Array.from(document.querySelectorAll('.modal.show'));
        }

        function ensureSingleBackdrop() {
            const backs = Array.from(document.querySelectorAll('.modal-backdrop'));
            if (backs.length > 1) backs.slice(0, -1).forEach(b => b.remove());

            const anyVisible = !!document.querySelector('.modal.show');
            if (!anyVisible) {
                backs.forEach(b => b.remove());
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('padding-right');
            } else {
                document.body.classList.add('modal-open');
            }
        }

        function restack() {
            const open = visibleModals();
            open.forEach((m, i) => m.style.zIndex = String(Z_BASE + (i * Z_STEP)));
            const top = open[open.length - 1];
            const backdrop = document.querySelector('.modal-backdrop');
            if (top && backdrop) {
                const topZ = parseInt(getComputedStyle(top).zIndex || Z_BASE, 10);
                backdrop.style.zIndex = String(topZ - 10);
            }
        }

        document.addEventListener('show.bs.modal', ev => {
            const openCount = visibleModals().length;
            ev.target.style.zIndex = String(Z_BASE + (openCount * Z_STEP));
            setTimeout(() => {
                ensureSingleBackdrop();
                restack();
            }, 0);
        });

        document.addEventListener('shown.bs.modal', () => setTimeout(() => {
            ensureSingleBackdrop();
            restack();
        }, 0));

        document.addEventListener('hidden.bs.modal', () => setTimeout(() => {
            ensureSingleBackdrop();
            restack();
        }, 0));

        const getModalInstance = (id) => {
            const el = document.getElementById(id);
            return el ? bootstrap.Modal.getOrCreateInstance(el) : null;
        };

        const swapModal = (fromId, toId) => {
            const toEl = document.getElementById(toId);
            if (!toEl) return;

            const showNext = () => {
                const target = getModalInstance(toId);
                target?.show();
            };

            const fromEl = document.getElementById(fromId);
            if (fromEl && fromEl.classList.contains('show')) {
                const fromModal = getModalInstance(fromId);
                if (!fromModal) {
                    showNext();
                    return;
                }
                const onHidden = () => {
                    fromEl.removeEventListener('hidden.bs.modal', onHidden);
                    showNext();
                };
                fromEl.addEventListener('hidden.bs.modal', onHidden, {
                    once: true
                });
                fromModal.hide();
            } else {
                showNext();
            }
        };

        /* =========================
           STEP-1 HARD LOCK (Fix)
           ========================= */
        const bookingForm = document.getElementById('bookingForm');
        const isEquipmentBooking = {{ $isEquipmentBooking ? 'true' : 'false' }};
        const showLocationSelect = {{ $showLocationSelect ? 'true' : 'false' }};
        const locationRow = document.getElementById('locationRow');
        const locationSelect = document.getElementById('bookingLocationSelect');
        const locationHint = document.getElementById('locationAvailabilityHint');
        const stockSelect = document.getElementById('stockQuantitySelect');
        const hiddenLocationId = document.getElementById('inputLocationId');
        const hiddenStockQuantity = document.getElementById('inputStockQuantity');
        const step1Modal = document.getElementById('multiStepBookingModal');
        const startDateInput = document.getElementById('rentalStartDate');
        let startDatePicker = null;
        let bookingCreationInFlight = false;

        const setDefaultStockSelection = () => {
            if (!stockSelect) return;
            const optionOne = stockSelect.querySelector('option[value="1"]');
            if (optionOne) {
                optionOne.selected = true;
                stockSelect.value = '1';
                if (hiddenStockQuantity) hiddenStockQuantity.value = '1';
                return;
            }
            if (stockSelect.options.length > 0) {
                stockSelect.selectedIndex = 0;
                if (hiddenStockQuantity) hiddenStockQuantity.value = stockSelect.value || '';
                return;
            }
            if (hiddenStockQuantity) hiddenStockQuantity.value = '';
        };

        if (bookingForm) {
            bookingForm.addEventListener('submit', (e) => {
                e.preventDefault();
            });
            bookingForm.addEventListener('keydown', (e) => {
                const step1Open = step1Modal?.classList.contains('show');
                if (step1Open && e.key === 'Enter') {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
        }

        if (startDateInput) {
            ['change', 'input', 'keydown', 'keypress'].forEach(evt => {
                startDateInput.addEventListener(evt, (e) => {
                    const step1Open = step1Modal?.classList.contains('show');
                    if (!step1Open) return;
                    if ((evt === 'keydown' || evt === 'keypress') && e.key === 'Enter') {
                        e.preventDefault();
                        e.stopPropagation();
                    } else {
                        e.stopPropagation();
                    }
                });
            });
        }

        // Only this button can advance Step-1 -> Step-2
        const step1NextBtn = document.getElementById('continueFromStep1');
        if (step1NextBtn) {
            step1NextBtn.addEventListener('click', (event) => {
                event.preventDefault();
                const unit = document.getElementById('inputRentalUnit')?.value;
                const qty = parseInt(document.getElementById('inputRentalQuantity')?.value || '0', 10);
                const start = document.getElementById('inputRentalStartDate')?.value;

                if (!unit || !qty || !start) {
                    if (window.Swal?.fire) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Missing info',
                            text: 'Please select duration, quantity, and start date.'
                        });
                    }
                    return;
                }

                // Check location selection
                if (showLocationSelect && locationSelect && !locationSelect.value) {
                    if (window.Swal?.fire) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Select location',
                            text: 'Please choose a location before continuing.'
                        });
                    }
                    return;
                }

                // Check stock availability
                if (window.latestLocationAvailability !== null && window.latestLocationAvailability <= 0) {
                    if (window.Swal?.fire) {
                        Swal.fire({
                            icon: 'error',
                            title: 'No availability',
                            text: 'Selected location has no availability for the chosen dates.'
                        });
                    }
                    return;
                }

                swapModal('multiStepBookingModal', 'customerStep');
            });
        }

        // Back & forward controls between modals
        const customerBackBtn = document.getElementById('customerBackToStep1');
        const summaryBackBtn = document.getElementById('summaryBackToCustomer');
        const paymentBackBtn = document.getElementById('paymentBackToSummary');
        const stripeBackBtn = document.getElementById('stripeBackToPayment');

        customerBackBtn?.addEventListener('click', (e) => {
            e.preventDefault();
            swapModal('customerStep', 'multiStepBookingModal');
        });

        summaryBackBtn?.addEventListener('click', (e) => {
            e.preventDefault();
            swapModal('summaryStep', 'customerStep');
        });

        paymentBackBtn?.addEventListener('click', (e) => {
            e.preventDefault();
            resetPaymentSelection();
            swapModal('bookingPayment', 'summaryStep');
        });

        stripeBackBtn?.addEventListener('click', (e) => {
            e.preventDefault();
            resetPaymentSelection();
            swapModal('bookingStripeModal', 'bookingPayment');
        });

        /* =========================
           DATE / RENTAL HELPERS
           ========================= */
        const toYMD = (date) => {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const d = String(date.getDate()).padStart(2, '0');
            return `${y}-${m}-${d}`;
        };

        const fromYMD = (s) => {
            const [Y, M, D] = (s || '').split('-').map(Number);
            return (Y && M && D) ? new Date(Y, M - 1, D) : null;
        };

        const niceDate = (ymd) => {
            if (!ymd) return '';
            const dt = fromYMD(ymd);
            return dt ? dt.toLocaleDateString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            }) : ymd;
        };

        const addDays = (date, amount) => {
            const t = new Date(date.getFullYear(), date.getMonth(), date.getDate());
            t.setDate(t.getDate() + amount);
            return t;
        };

        const unitDays = (u) => (u === 'week' ? 7 : u === 'month' ? 30 : 1);
        const money = (v) =>
            `R${Number(v || 0).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2})}`;

        function clearSelect(sel) {
            while (sel.options.length) sel.remove(0);
        }

        function fillSelect(sel, from, to, value) {
            clearSelect(sel);
            for (let i = from; i <= to; i++) {
                const o = document.createElement('option');
                o.value = String(i);
                o.textContent = String(i);
                sel.appendChild(o);
            }
            sel.value = String(value);
        }

        /* =========================================================
           STEP-1: UI wiring (unit cards, quantity, price, period)
           ========================================================= */
        const unitCards = document.querySelectorAll('.option-card');
        const dateSection = document.getElementById('dateSection');
        const qtySection = document.getElementById('quantitySection');
        const qtySelect = document.getElementById('rentalQuantity');
        const qtyLabel = document.getElementById('quantityLabel');
        const totalBox = document.getElementById('totalPrice');
        const periodBox = document.getElementById('rentalPeriod');
        const extraDaysSection = document.getElementById('extraDaysSection');
        const extraDaysInput = document.getElementById('extraDaysInput');
        const extraDaysHelp = document.getElementById('extraDaysHelp');

        // Hidden inputs
        const hidUnit = document.getElementById('inputRentalUnit');
        const hidQty = document.getElementById('inputRentalQuantity');
        const hidStart = document.getElementById('inputRentalStartDate');
        const hidExtra = document.getElementById('inputExtraDays');
        const hidTotal = document.getElementById('inputTotalPrice');

        let currentUnitMax = 30;
        let suppressRentalEvent = false;
        let isUpdatingStep1 = false;

        const applyQuantityLimit = (limit) => {
            if (!qtySelect) return;
            const fallback = currentUnitMax;
            let parsedLimit = typeof limit === 'number' ? Math.floor(limit) : null;
            if (parsedLimit !== null && parsedLimit < 1) parsedLimit = 1;
            const targetMax = parsedLimit !== null && parsedLimit > 0 ? Math.min(fallback, parsedLimit) : fallback;
            const previousValue = parseInt(qtySelect.value || '1', 10) || 1;
            fillSelect(qtySelect, 1, targetMax, 1);
            const nextValue = Math.min(previousValue, targetMax);
            qtySelect.value = String(nextValue);
            if (hidQty) {
                hidQty.value = String(nextValue);
            }
            suppressRentalEvent = true;
            try {
                updateStep1Paint();
            } finally {
                suppressRentalEvent = false;
            }
        };

        window.updateQuantityLimit = (limit) => applyQuantityLimit(typeof limit === 'number' ? limit : null);

        function activeUnit() {
            const a = document.querySelector('.option-card.active');
            return a ? a.getAttribute('data-type') : '';
        }

        function priceForActiveUnit() {
            const a = document.querySelector('.option-card.active');
            const p = parseFloat(a?.getAttribute('data-price') || '0');
            return isNaN(p) ? 0 : p;
        }

        function configureQtyForUnit(u) {
            let max = 30;
            let label = 'How many day(s)?';
            currentUnitMax = max;

            if (u === 'week') {
                max = 4; // Changed from 12 to 4
                label = 'How many week(s)?';
                currentUnitMax = max;
            }
            if (u === 'month') {
                max = 12; // Keep 12 for months
                label = 'How many month(s)?';
                currentUnitMax = max;
            }

            qtyLabel.textContent = label;
            fillSelect(qtySelect, 1, max, 1);
            applyQuantityLimit(window.latestLocationAvailability ?? null);
        }

        // Compute + paint Step-1 price & period with extra days and stock quantity
        function updateStep1Paint() {
            if (isUpdatingStep1) return;
            isUpdatingStep1 = true;

            try {
                const unit = (hidUnit?.value || activeUnit() || '').toLowerCase();
                const qty = parseInt(hidQty?.value || qtySelect?.value || '0', 10) || 0;
                const startY = (hidStart?.value || '').trim();
                const startDt = startY ? fromYMD(startY) : null;
                const extra = parseInt(hidExtra?.value || '0', 10) || 0;
                const stockQuantity = parseInt(hiddenStockQuantity?.value || '1', 10);

                // Show sections progressively
                if (unit) dateSection?.classList.remove('d-none');

                // Guard
                if (!unit || !qty || !startDt) {
                    totalBox?.classList.add('d-none');
                    if (totalBox) totalBox.textContent = '';
                    periodBox?.classList.add('d-none');
                    if (periodBox) periodBox.textContent = '';
                    if (hidTotal) hidTotal.value = '';
                    if (showLocationSelect && locationRow) locationRow.classList.add('d-none');
                    if (stockSelect) {
                        stockSelect.disabled = false;
                        setDefaultStockSelection();
                    }
                    if (!suppressRentalEvent) {
                        document.dispatchEvent(new CustomEvent('rental:updated'));
                    }
                    return;
                }

                if (showLocationSelect && locationRow) locationRow.classList.remove('d-none');

                const baseDays = qty * unitDays(unit);
                const days = baseDays + (unit === 'day' ? 0 : extra);
                const endDt = addDays(startDt, Math.max(0, days - 1));
                const endY = toYMD(endDt);

                const pricePer = priceForActiveUnit();

                // Calculate base price for the rental period
                let basePrice = pricePer * qty;

                // Calculate extra days price (using daily rate)
                let extraDaysPrice = 0;
                if (extra > 0) {
                    let dailyRate = 0;
                    if (unit === 'week') {
                        dailyRate = pricePer / 7;
                    } else if (unit === 'month') {
                        dailyRate = pricePer / 30;
                    } else {
                        dailyRate = pricePer;
                    }
                    extraDaysPrice = dailyRate * extra;
                }

                // Total vehicle price (base + extra days) multiplied by stock quantity
                const vehicleTotal = Number((basePrice + extraDaysPrice) * stockQuantity).toFixed(2);

                if (totalBox) {
                    let priceBreakdown = `
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted">Base rental (${qty} ${unit}${qty>1?'s':''})</span>
                        <span class="fw-bold">${money(basePrice * stockQuantity)}</span>
                    </div>`;

                    if (extra > 0) {
                        priceBreakdown += `
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted">Extra days (${extra} day${extra>1?'s':''})</span>
                        <span class="fw-bold">${money(extraDaysPrice * stockQuantity)}</span>
                    </div>`;
                    }

                    priceBreakdown += `
                    <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-2">
                        <span class="fw-semibold">Total (${stockQuantity} unit${stockQuantity>1?'s':''})</span>
                        <span class="fw-bold text-success">${money(vehicleTotal)}</span>
                    </div>`;

                    totalBox.innerHTML = priceBreakdown;
                    totalBox.classList.remove('d-none');
                }

                if (periodBox) {
                    periodBox.innerHTML = `
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="small text-muted">Start date</div>
                        <div class="fw-semibold">${niceDate(startY)}</div>
                    </div>
                    <div class="text-end">
                        <div class="small text-muted">End date</div>
                        <div class="fw-semibold">${niceDate(endY)}</div>
                    </div>
                </div>
                <div class="mt-2 text-center small">
                    ${days} day${days===1?'':'s'}
                    ${extra > 0 ? `(${baseDays} base + ${extra} extra)` : ''}
                    × ${stockQuantity} unit${stockQuantity>1?'s':''}
                </div>`;
                    periodBox.classList.remove('d-none');
                }

                if (hidTotal) {
                    hidTotal.value = String(vehicleTotal);
                    hidTotal.dispatchEvent(new Event('change', { bubbles: true }));
                }

                // Inform listeners
                if (!suppressRentalEvent) {
                    document.dispatchEvent(new CustomEvent('rental:updated'));
                }
            } finally {
                isUpdatingStep1 = false;
            }
        }

        // Unit card selection with auto-open calendar
        unitCards.forEach(card => {
            card.addEventListener('click', () => {
                unitCards.forEach(c => c.classList.remove('bg-light', 'active'));
                card.classList.add('bg-light', 'active');

                const u = card.getAttribute('data-type') || '';
                if (hidUnit) {
                    hidUnit.value = u;
                    hidUnit.dispatchEvent(new Event('change', { bubbles: true }));
                }

                // show date + qty sections and prepare qty
                dateSection?.classList.remove('d-none');
                qtySection?.classList.remove('d-none');
                configureQtyForUnit(u);

                // sync qty hidden
                if (hidQty && qtySelect) {
                    hidQty.value = qtySelect.value;
                    hidQty.dispatchEvent(new Event('change', { bubbles: true }));
                }

                // AUTO-OPEN CALENDAR
                setTimeout(() => {
                    if (startDateInput && startDatePicker) {
                        startDateInput.focus();
                        // Force open the calendar
                        if (typeof startDatePicker.open === 'function') {
                            startDatePicker.open();
                        }
                    }
                }, 300);

                updateStep1Paint();
            });
        });

        // Qty select -> hidden + repaint
        if (qtySelect) {
            qtySelect.addEventListener('change', () => {
                if (hidQty) {
                    hidQty.value = qtySelect.value;
                    hidQty.dispatchEvent(new Event('change', { bubbles: true }));
                }
                updateStep1Paint();
            });
        }

        /* =========================================
           CALENDAR (lead days + booked ranges lock)
           ========================================= */
        const initCalendar = () => {
            const inp = startDateInput;
            if (!inp) return;

            const leadDays = parseInt(inp.getAttribute('data-lead') || (window.bookingLeadDays ?? '0'), 10) || 0;

            let blockedRanges = [];
            try {
                const raw = inp.getAttribute('data-blocked') || JSON.stringify(window.vehicleBlockedRanges || []);
                blockedRanges = (JSON.parse(raw) || []).filter(r => r && r.from && r.to);
            } catch {
                blockedRanges = [];
            }

            // Normalize "today" to local midnight
            const today = new Date();
            const todayLocal = new Date(today.getFullYear(), today.getMonth(), today.getDate());

            // Min selectable date = today + leadDays (local)
            const minDate = new Date(todayLocal);
            if (leadDays > 0) minDate.setDate(minDate.getDate() + leadDays);

            const getActiveLocationId = () => {
                if (locationSelect && locationSelect.value) return locationSelect.value;
                if (hiddenLocationId && hiddenLocationId.value) return hiddenLocationId.value;
                return null;
            };

            const isInRanges = (ranges, ymd) => {
                if (!Array.isArray(ranges)) return false;
                return ranges.some(range => {
                    if (!range?.from || !range?.to) return false;
                    return ymd >= range.from && ymd <= range.to;
                });
            };

            // All checks in LOCAL YYYY-MM-DD
            function isDisabled(dateObj) {
                const dLocal = new Date(dateObj.getFullYear(), dateObj.getMonth(), dateObj.getDate());
                const ymd = toYMD(dLocal);

                // Lead-days lock: disable [today .. today+leadDays-1]
                if (leadDays > 0) {
                    const leadStart = new Date(todayLocal);
                    const leadEnd = new Date(todayLocal);
                    leadEnd.setDate(leadEnd.getDate() + (leadDays - 1));
                    const y0 = toYMD(leadStart);
                    const y1 = toYMD(leadEnd);
                    if (ymd >= y0 && ymd <= y1) return true;
                }

                // Blocked ranges: inclusive [from .. to]
                if (isInRanges(blockedRanges, ymd)) {
                    return true;
                }

                // Location-specific fully booked dates
                const activeLocationId = getActiveLocationId();
                if (activeLocationId) {
                    const ranges = locationFullyBookedMap[String(activeLocationId)] || [];
                    if (isInRanges(ranges, ymd)) {
                        return true;
                    }
                }
                return false;
            }

            // Use flatpickr if available
            if (typeof flatpickr !== 'undefined') {
                startDatePicker = flatpickr(inp, {
                    minDate,
                    disable: [isDisabled],
                    dateFormat: 'Y-m-d',
                    clickOpens: true,
                    allowInput: false,
                    // Add these options for better UX and full width
                    static: true,
                    monthSelectorType: 'static',
                    // Ensure full width for calendar
                    onReady: function(selectedDates, dateStr, instance) {
                        const calendar = instance.calendarContainer;
                        const input = instance._input;

                        if (calendar) {
                            calendar.style.zIndex = '99999';
                            // Set calendar to full width
                            calendar.style.width = '100%';
                            calendar.style.maxWidth = '100%';
                        }

                        // Ensure input takes full width
                        if (input) {
                            input.style.width = '100%';
                            input.style.maxWidth = '100%';
                        }
                    },
                    onOpen: function(selectedDates, dateStr, instance) {
                        const calendar = instance.calendarContainer;
                        if (calendar) {
                            calendar.style.zIndex = '99999';
                            calendar.style.width = '100%';
                            calendar.style.maxWidth = '100%';

                            // Force recalculation of position for full width
                            setTimeout(() => {
                                instance.redraw();
                            }, 10);
                        }

                        // Close any other open flatpickr instances
                        document.querySelectorAll('.flatpickr-calendar').forEach(cal => {
                            if (cal !== calendar && cal.style.display !== 'none') {
                                cal.style.display = 'none';
                            }
                        });
                    },
                    onMonthChange: function(selectedDates, dateStr, instance) {
                        // Ensure calendar maintains full width on month change
                        const calendar = instance.calendarContainer;
                        if (calendar) {
                            calendar.style.width = '100%';
                            calendar.style.maxWidth = '100%';
                        }
                    },
                    onChange: function(selectedDates, dateStr) {
                        if (hidStart) {
                            hidStart.value = dateStr || '';
                            hidStart.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                        qtySection?.classList.remove('d-none');
                        updateStep1Paint();
                    }
                });

                // Add event listener to auto-open when input gets focus
                inp.addEventListener('focus', function() {
                    if (startDatePicker && typeof startDatePicker.open === 'function') {
                        startDatePicker.open();
                    }
                });

                // Additional width enforcement after initialization
                setTimeout(() => {
                    if (inp) {
                        inp.style.width = '100%';
                        inp.style.maxWidth = '100%';
                        inp.style.boxSizing = 'border-box';

                        // Also set parent elements to full width
                        const parentRelative = inp.closest('.position-relative');
                        if (parentRelative) {
                            parentRelative.style.width = '100%';
                        }

                        const dateSection = document.getElementById('dateSection');
                        if (dateSection) {
                            dateSection.style.width = '100%';
                        }
                    }
                }, 100);
            } else {
                // Fallback to native date input
                try {
                    inp.removeAttribute('readonly');
                    inp.setAttribute('type', 'date');
                    // Set full width for native date input
                    inp.style.width = '100%';
                    inp.style.maxWidth = '100%';
                } catch {}
                inp.addEventListener('input', () => {
                    const val = inp.value;
                    const picked = fromYMD(val);
                    if (!picked) return;
                    if (toYMD(picked) < toYMD(minDate) || isDisabled(picked)) {
                        if (window.Swal?.fire) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Date not available',
                                text: 'This date is unavailable.'
                            });
                        }
                        inp.value = '';
                        return;
                    }
                });
                inp.addEventListener('change', () => {
                    const dateStr = inp.value || '';
                    if (hidStart) {
                        hidStart.value = dateStr;
                        hidStart.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    qtySection?.classList.remove('d-none');
                    updateStep1Paint();
                });
            }
        };

        /* =========================
           LOCATION & STOCK MANAGEMENT
           ========================= */
        const locationInventory = @json($locationInventory);
        const locationBookingMap = @json($locationBookingMap);
        const locationFullyBookedMap = @json($locationFullyBooked);
        let latestLocationAvailability = null;
        window.latestLocationAvailability = null;

        const parseIntSafe = (value, fallback = 0) => {
            const parsed = parseInt(value ?? '', 10);
            return Number.isNaN(parsed) ? fallback : parsed;
        };

        const rangesOverlap = (startA, endA, startB, endB) => {
            if (!startA || !endA || !startB || !endB) return false;
            return !(endA < startB || endB < startA);
        };

        const ensureHiddenLocation = () => {
            if (hiddenLocationId && locationSelect && locationSelect.value) {
                hiddenLocationId.value = locationSelect.value;
            }
        };

        const baseStockForLocation = (locationId) => {
            if (!locationId) return null;
            const info = locationInventory[String(locationId)];
            if (info && info.stock !== null && info.stock !== undefined) {
                const parsed = parseInt(info.stock, 10);
                return Number.isNaN(parsed) ? null : parsed;
            }
            if (!locationSelect) return null;
            const opt = Array.from(locationSelect.options || []).find(o => o.value === String(locationId));
            if (!opt) return null;
            const attr = opt.dataset.baseStock;
            if (attr === undefined || attr === null || attr === '') return null;
            const parsed = parseInt(attr, 10);
            return Number.isNaN(parsed) ? null : parsed;
        };

        const computeRentalContext = () => {
            const unit = (document.getElementById('inputRentalUnit')?.value || '').toLowerCase();
            const quantity = parseIntSafe(document.getElementById('inputRentalQuantity')?.value);
            const startValue = document.getElementById('inputRentalStartDate')?.value || '';
            const extra = parseIntSafe(hidExtra?.value);
            const startDate = fromYMD(startValue);
            if (!unit || !quantity || !startDate) return null;

            let totalDays = 0;
            switch (unit) {
                case 'week':
                    totalDays = quantity * 7 + extra;
                    break;
                case 'month':
                    totalDays = quantity * 30 + extra;
                    break;
                default:
                    totalDays = quantity + (unit === 'day' ? 0 : extra);
                    break;
            }
            if (totalDays <= 0) return null;
            const endDate = addDays(startDate, totalDays - 1);
            return {
                unit,
                quantity,
                extra,
                start: toYMD(startDate),
                end: toYMD(endDate),
                totalDays,
            };
        };

        const availableUnitsForLocation = (locationId, context) => {
            const base = baseStockForLocation(locationId);
            if (base === null || base === undefined) return null;
            if (!context) return base;
            const bookings = locationBookingMap[String(locationId)] || [];
            let available = base;
            bookings.forEach(booking => {
                const units = parseIntSafe(booking.units, 1);
                if (rangesOverlap(context.start, context.end, booking.from, booking.to)) {
                    available -= units;
                }
            });
            return available < 0 ? 0 : available;
        };

        // Populate stock quantity select
        const populateStockQuantitySelect = (available) => {
            if (!stockSelect) return;

            stockSelect.innerHTML = '';

            if (available === null || available === undefined) {
                for (let i = 1; i <= 10; i++) {
                    const option = document.createElement('option');
                    option.value = i;
                    option.textContent = i;
                    stockSelect.appendChild(option);
                }
                stockSelect.disabled = false;
            } else if (available <= 0) {
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'No stock available';
                stockSelect.appendChild(option);
                stockSelect.disabled = true;
                if (hiddenStockQuantity) hiddenStockQuantity.value = '0';
            } else {
                for (let i = 1; i <= available; i++) {
                    const option = document.createElement('option');
                    option.value = i;
                    option.textContent = i;
                    stockSelect.appendChild(option);
                }
                stockSelect.disabled = false;
            }

            setDefaultStockSelection();
            if (stockSelect.disabled && available !== null && available <= 0 && hiddenStockQuantity) {
                hiddenStockQuantity.value = '0';
            }
        };

        // Handle stock quantity changes
        const handleStockQuantityChange = () => {
            if (!stockSelect || !hiddenStockQuantity) return;
            hiddenStockQuantity.value = stockSelect.value;
            updateStep1Paint();
        };

        const updateSummaryLocation = () => {
            const summaryLocationEl = document.getElementById('summaryLocation');
            if (!summaryLocationEl) return;
            let label = 'N/A';
            let stockInfo = '';
            if (locationSelect && locationSelect.value) {
                const selected = locationSelect.selectedOptions[0];
                if (selected) {
                    label = selected.dataset.displayName || selected.textContent.trim();
                    const stockQty = hiddenStockQuantity?.value || '1';
                    stockInfo = ` × ${stockQty} unit${stockQty !== '1' ? 's' : ''}`;
                }
            }
            summaryLocationEl.textContent = (label + stockInfo) || 'N/A';
        };

        const setLocationDisplay = (message, danger = false) => {
            if (locationHint) {
                locationHint.textContent = message;
                locationHint.classList.toggle('text-danger', !!danger);
            }
        };

        const updateLocationAvailability = () => {
            if (!locationSelect || !locationHint) return;
            ensureHiddenLocation();
            const locationId = locationSelect.value;
            if (!locationId) {
                setLocationDisplay('Select a location to view availability.', false);
                latestLocationAvailability = null;
                window.latestLocationAvailability = null;
                populateStockQuantitySelect(null);
                updateSummaryLocation();
                return;
            }
            const context = computeRentalContext();
            const base = baseStockForLocation(locationId);
            if (base === null || base === undefined) {
                setLocationDisplay('Availability varies for this location.', false);
                latestLocationAvailability = null;
                window.latestLocationAvailability = null;
                populateStockQuantitySelect(null);
                updateSummaryLocation();
                return;
            }
            if (!context) {
                const message = `${base} in stock`;
                setLocationDisplay(message, base <= 0);
                latestLocationAvailability = base;
                window.latestLocationAvailability = base;
                populateStockQuantitySelect(base);
                updateSummaryLocation();
                return;
            }
            const available = availableUnitsForLocation(locationId, context);
            latestLocationAvailability = available;
            window.latestLocationAvailability = available;
            populateStockQuantitySelect(available);
            if (available === null || available === undefined) {
                const message = `${base} in stock`;
                setLocationDisplay(message, base <= 0);
            } else if (available <= 0) {
                setLocationDisplay('Not available for the selected dates.', true);
            } else {
                setLocationDisplay(`${available} of ${base} available for the selected dates.`, false);
            }
            updateSummaryLocation();
        };

        const updateExtraDaysVisibility = () => {
            if (!extraDaysSection || !extraDaysInput || !hidExtra) return;
            const unit = (document.getElementById('inputRentalUnit')?.value || '').toLowerCase();
            if (unit === 'week' || unit === 'month') {
                const limit = unit === 'week' ? 6 : 29;
                extraDaysSection.classList.remove('d-none');
                extraDaysInput.setAttribute('max', String(limit));
                let value = parseIntSafe(extraDaysInput.value);
                if (value > limit) value = limit;
                extraDaysInput.value = String(value);
                hidExtra.value = String(value);
                hidExtra.dispatchEvent(new Event('change', { bubbles: true }));
                if (extraDaysHelp) {
                    extraDaysHelp.textContent = limit === 6 ? '1 to 6 days.' : '1 to 29 days.';
                }
            } else {
                extraDaysSection.classList.add('d-none');
                extraDaysInput.value = '0';
                hidExtra.value = '0';
                hidExtra.dispatchEvent(new Event('change', { bubbles: true }));
            }
        };

        if (hidUnit) {
            hidUnit.addEventListener('change', () => {
                updateExtraDaysVisibility();
                updateLocationAvailability();
            });
        }

        // Event Listeners for location and stock
        if (extraDaysInput) {
            extraDaysInput.addEventListener('input', () => {
                const max = parseIntSafe(extraDaysInput.getAttribute('max'), Infinity);
                let value = parseIntSafe(extraDaysInput.value);
                if (value < 0) value = 0;
                if (value > max) value = max;
                extraDaysInput.value = String(value);
                hidExtra.value = String(value);
                hidExtra.dispatchEvent(new Event('change', { bubbles: true }));
                updateLocationAvailability();
                updateStep1Paint();
            });
        }

        if (locationSelect) {
            locationSelect.addEventListener('change', () => {
                ensureHiddenLocation();
                updateLocationAvailability();
                updateStep1Paint();
                if (startDatePicker && typeof startDatePicker.redraw === 'function') {
                    startDatePicker.redraw();
                }
            });
        }

        if (stockSelect) {
            stockSelect.addEventListener('change', handleStockQuantityChange);
        }

        /* =========================
           SWEETALERT HELPER
           ========================= */
        const alertDebounce = new Map();

        function notify(key, { icon = 'warning', title = 'Notice', text = '' }) {
            const now = Date.now(), last = alertDebounce.get(key) || 0;
            if (now - last < 600) return;

            const step1ModalEl = document.getElementById('multiStepBookingModal');
            const step1Visible = step1ModalEl?.classList.contains('show');
            const suppressForStep1 = typeof key === 'string' && /^overlap|^noqty|^clamped/.test(key);
            if (step1Visible && suppressForStep1) return;

            alertDebounce.set(key, now);
            if (window.Swal?.fire) Swal.fire({ icon, title, text, confirmButtonText: 'OK' });
            else alert(`${title}\n\n${text}`);
        }

        /* =========================
           BOOKING + PAYMENT FLOW
           ========================= */
        const bookingIdField = document.getElementById('bookingId');
        const openPaymentBtn = document.getElementById('openPayment');
        const bookingPaymentModalEl = document.getElementById('bookingPayment');
        const bookingStripeModalEl = document.getElementById('bookingStripeModal');
        const bookingStripePayButton = document.getElementById('bookingStripePayButton');
        const bookingThankYouModalEl = document.getElementById('bookingThankYou');
        const bookingCardErrorsEl = document.getElementById('booking-card-errors');

        const paymentMethodInputs = Array.from(document.querySelectorAll('input[name="booking_payment_method"]'));
        const resetPaymentSelection = () => {
            paymentMethodInputs.forEach((input) => {
                input.checked = false;
                input.removeAttribute('checked');
            });
        };
        if (bookingPaymentModalEl) bookingPaymentModalEl.addEventListener('hidden.bs.modal', resetPaymentSelection);

        let currentBookingReference = null;

        const stripePublicKey = "{{ $stripeConfig->stripe_key ?? '' }}";
        let stripeInstance = null, stripeElements = null, stripeCardNumber = null, stripeCardExpiry = null, stripeCardCvc = null;

        const showPaymentLoader = (message = 'Processing payment...') => {
            if (window.Swal) {
                Swal.fire({
                    title: message,
                    text: 'Please wait while we confirm your payment.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => Swal.showLoading()
                });
            }
        };

        const hidePaymentLoader = () => {
            if (window.Swal && Swal.isVisible()) Swal.close();
        };

        const computeGrandTotal = () => {
            const vehicleTotal = parseFloat(document.getElementById('inputTotalPrice')?.value || '0');
            return Math.round(vehicleTotal * 100) / 100;
        };

        const populateThankYouModal = (methodLabel) => {
            if (!bookingThankYouModalEl) return;
            const tyVehicleNameEl = document.getElementById('tyVehicleName');
            if (tyVehicleNameEl) tyVehicleNameEl.textContent = "{{ addslashes($bookableName) }}";

            const periodText = document.getElementById('summaryPeriod')?.textContent?.trim() || '-';
            const tyPeriodEl = document.getElementById('tyPeriod');
            if (tyPeriodEl) tyPeriodEl.textContent = periodText;

            const reference = currentBookingReference || (bookingIdField?.value ? `#${bookingIdField.value}` : '-');
            const tyReferenceEl = document.getElementById('tyReference');
            if (tyReferenceEl) tyReferenceEl.textContent = reference;

            const tyAmountEl = document.getElementById('tyAmount');
            if (tyAmountEl) tyAmountEl.textContent = money(computeGrandTotal());

            const tyMethodEl = document.getElementById('tyMethod');
            if (tyMethodEl) tyMethodEl.textContent = methodLabel;

            const tyCustomerNameEl = document.getElementById('tyCustomerName');
            if (tyCustomerNameEl) tyCustomerNameEl.textContent = bookingForm?.name?.value || '-';

            const contactParts = [];
            if (bookingForm?.email?.value) contactParts.push(bookingForm.email.value);
            if (bookingForm?.phone?.value) contactParts.push(bookingForm.phone.value);
            const tyCustomerContactEl = document.getElementById('tyCustomerContact');
            if (tyCustomerContactEl) tyCustomerContactEl.textContent = contactParts.join(' - ') || '-';
        };

        if (bookingThankYouModalEl) bookingThankYouModalEl.addEventListener('hidden.bs.modal', () => {
            window.location.href = "{{ url('/') }}";
        });

        if (openPaymentBtn) {
            const openPaymentDefaultLabel = (openPaymentBtn.textContent || '').trim() || 'Continue to Payment';
            openPaymentBtn.dataset.originalLabel = openPaymentDefaultLabel;

            const setOpenPaymentLoading = (isLoading) => {
                if (isLoading) {
                    openPaymentBtn.disabled = true;
                    openPaymentBtn.textContent = 'Preparing booking...';
                } else {
                    openPaymentBtn.disabled = false;
                    openPaymentBtn.textContent = openPaymentBtn.dataset.originalLabel || openPaymentDefaultLabel;
                }
            };

            openPaymentBtn.addEventListener('click', async () => {
                if (bookingCreationInFlight) return;

                if (!bookingIdField?.value && bookingForm) {
                    bookingCreationInFlight = true;
                    setOpenPaymentLoading(true);

                    const formData = new FormData(bookingForm);
                    if (!bookingIdField?.value) formData.delete('booking_id');

                    try {
                        const res = await fetch(bookingForm.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            }
                        });

                        const text = await res.text();
                        let data;
                        try { data = JSON.parse(text); }
                        catch { data = { success: false, message: text }; }

                        if (!res.ok || !data?.success) {
                            await Swal.fire({ icon: 'error', title: 'Booking not created', text: data?.message || 'Failed to create booking.' });
                            return;
                        }

                        bookingIdField.value = data.booking_id || data.id || '';
                        currentBookingReference = data.reference || null;
                        if (!bookingIdField.value) {
                            await Swal.fire({ icon: 'error', title: 'Missing booking ID', text: 'Booking was created but no identifier was returned.' });
                            return;
                        }
                    } catch (error) {
                        console.error(error);
                        await Swal.fire({ icon: 'error', title: 'Network error', text: 'Unable to create booking, please try again.' });
                        return;
                    } finally {
                        bookingCreationInFlight = false;
                        setOpenPaymentLoading(false);
                    }
                }

                if (!bookingIdField?.value) return;

                const summaryModal = bootstrap.Modal.getInstance(document.getElementById('summaryStep'));
                summaryModal?.hide();

                if (bookingPaymentModalEl) new bootstrap.Modal(bookingPaymentModalEl).show();

                const stripeRadio = document.getElementById('bookingStripe');
                if (stripeRadio?.checked && bookingStripePayButton) bookingStripePayButton.dataset.amount = String(computeGrandTotal());
            });
        }

        document.addEventListener('change', async (e) => {
            if (!(e.target && e.target.name === 'booking_payment_method')) return;

            const method = e.target.value;
            const paymentModalInstance = bookingPaymentModalEl ? bootstrap.Modal.getInstance(bookingPaymentModalEl) : null;
            paymentModalInstance?.hide();

            const grandTotal = computeGrandTotal();

            if (method === 'stripe') {
                if (bookingStripePayButton) bookingStripePayButton.dataset.amount = String(grandTotal);
                if (bookingStripeModalEl) new bootstrap.Modal(bookingStripeModalEl).show();
                return;
            }

            if (method === 'payfast') {
                const bookingId = bookingIdField?.value;
                if (!bookingId) {
                    await Swal.fire({ icon: 'error', title: 'Booking missing', text: 'Please create the booking first.' });
                    if (bookingPaymentModalEl) new bootstrap.Modal(bookingPaymentModalEl).show();
                    e.target.checked = false;
                    return;
                }

                try {
                    showPaymentLoader('Redirecting to PayFast...');
                    const res = await fetch(`/payfast/booking/init/${encodeURIComponent(bookingId)}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify({ booking_id: bookingId })
                    });

                    const data = await res.json();
                    if (!res.ok || !data?.success) throw new Error(data?.message || 'Failed to prepare PayFast checkout.');

                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = data.action;
                    form.style.display = 'none';
                    Object.entries(data.fields || {}).forEach(([key, value]) => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = key;
                        input.value = value;
                        form.appendChild(input);
                    });
                    document.body.appendChild(form);
                    form.submit();
                } catch (err) {
                    console.error(err);
                    await Swal.fire({ icon: 'error', title: 'PayFast error', text: err.message || 'Could not redirect to PayFast.' });
                    if (bookingPaymentModalEl) new bootstrap.Modal(bookingPaymentModalEl).show();
                    e.target.checked = false;
                } finally {
                    hidePaymentLoader();
                }
            }
        });

        // Stripe mount
        const stripePublicKeyJS = "{{ $stripeConfig->stripe_key ?? '' }}";
        if (typeof Stripe !== 'undefined' && stripePublicKeyJS) {
            stripeInstance = Stripe(stripePublicKeyJS);
            stripeElements = stripeInstance.elements();
            const stripeStyle = {
                base: { fontSize: '16px', color: '#32325d', '::placeholder': { color: '#a0aec0' } }
            };

            stripeCardNumber = stripeElements.create('cardNumber', { style: stripeStyle });
            stripeCardExpiry = stripeElements.create('cardExpiry', { style: stripeStyle });
            stripeCardCvc = stripeElements.create('cardCvc', { style: stripeStyle });

            const cardNumberMount = document.getElementById('booking-card-number');
            const cardExpiryMount = document.getElementById('booking-card-expiry');
            const cardCvcMount = document.getElementById('booking-card-cvc');

            if (cardNumberMount) stripeCardNumber.mount(cardNumberMount);
            if (cardExpiryMount) stripeCardExpiry.mount(cardExpiryMount);
            if (cardCvcMount) stripeCardCvc.mount(cardCvcMount);
        } else if (stripePublicKeyJS) {
            console.warn('Stripe.js not loaded or public key missing.');
        }

        if (bookingStripePayButton && !stripeInstance) bookingStripePayButton.disabled = true;

        if (bookingStripePayButton && stripeInstance) {
            bookingStripePayButton.addEventListener('click', async function() {
                if (!bookingIdField?.value) {
                    Swal.fire({ icon: 'error', title: 'Booking missing', text: 'Please create the booking first.' });
                    return;
                }

                if (!stripeCardNumber || !stripeCardExpiry || !stripeCardCvc) {
                    Swal.fire({ icon: 'error', title: 'Stripe unavailable', text: 'Payment form is not ready yet.' });
                    return;
                }

                if (bookingCardErrorsEl) bookingCardErrorsEl.textContent = '';

                const button = this;
                const originalText = button.textContent;
                button.disabled = true;
                button.textContent = 'Processing...';
                showPaymentLoader();

                try {
                    const { paymentMethod, error } = await stripeInstance.createPaymentMethod({
                        type: 'card',
                        card: stripeCardNumber,
                        billing_details: { name: bookingForm?.name?.value || '', email: bookingForm?.email?.value || '' }
                    });

                    if (error) {
                        if (bookingCardErrorsEl) bookingCardErrorsEl.textContent = error.message || 'Payment method error.';
                        hidePaymentLoader();
                        return;
                    }

                    const res = await fetch(`/bookings/${encodeURIComponent(bookingIdField.value)}/pay-with-stripe`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify({ payment_method_id: paymentMethod.id, amount: parseFloat(button.dataset.amount || '0') })
                    });

                    const text = await res.text();
                    let data;
                    try { data = JSON.parse(text); }
                    catch { data = { success: false, message: text }; }

                    hidePaymentLoader();

                    if (!res.ok || !data) {
                        await Swal.fire({ icon: 'error', title: 'Payment failed', text: data?.message || 'Server error while processing payment.' });
                        return;
                    }

                    if (data.success) {
                        bootstrap.Modal.getInstance(bookingStripeModalEl)?.hide();
                        populateThankYouModal('Stripe');
                        if (bookingThankYouModalEl) new bootstrap.Modal(bookingThankYouModalEl).show();
                        return;
                    }

                    if (data.requires_action && data.payment_intent_client_secret) {
                        const result = await stripeInstance.confirmCardPayment(data.payment_intent_client_secret);
                        if (result.error) {
                            await Swal.fire({ icon: 'error', title: 'Authentication failed', text: result.error.message || 'Unable to confirm your card.' });
                        } else {
                            bootstrap.Modal.getInstance(bookingStripeModalEl)?.hide();
                            populateThankYouModal('Stripe');
                            if (bookingThankYouModalEl) new bootstrap.Modal(bookingThankYouModalEl).show();
                        }
                    } else {
                        await Swal.fire({ icon: 'error', title: 'Payment failed', text: data.message || 'Unable to charge your card.' });
                    }
                } catch (error) {
                    console.error(error);
                    hidePaymentLoader();
                    await Swal.fire({ icon: 'error', title: 'Network error', text: error.message || 'Unable to reach the payment server.' });
                } finally {
                    hidePaymentLoader();
                    button.disabled = false;
                    button.textContent = originalText;
                }
            });
        }

        /* =========================
           SUMMARY
           ========================= */
        const goToSummaryBtn = document.getElementById('goToSummary');
        if (goToSummaryBtn) {
            goToSummaryBtn.addEventListener('click', function() {
                const form = document.getElementById('bookingForm');
                const name = form.querySelector('[name="name"]');
                const email = form.querySelector('[name="email"]');
                const phone = form.querySelector('[name="phone"]');
                const country = form.querySelector('[name="country"]');
                const emailValue = (email.value || '').trim();
                const phoneValue = (phone.value || '').trim();
                email.value = emailValue;
                phone.value = phoneValue;

                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
                const phonePattern = /^\+?[0-9]{1,4}(?:[\s-]?[0-9]{2,4}){2,4}$/;

                if (!name.value.trim() || !emailValue || !phoneValue || !country.value) {
                    notify('cust-missing', { icon: 'error', title: 'Missing Information', text: 'Please fill all required customer details.' });
                    return;
                }

                if (!emailPattern.test(emailValue)) {
                    notify('cust-invalid', { icon: 'error', title: 'Invalid Email', text: 'Enter a valid email address, e.g. you@example.com.' });
                    email.focus();
                    return;
                }

                if (!phonePattern.test(phoneValue)) {
                    notify('cust-invalid', { icon: 'error', title: 'Invalid Phone Number', text: 'Use digits with optional spaces or dashes, e.g. +27 123 456 7890.' });
                    phone.focus();
                    return;
                }

                const unitH = document.getElementById('inputRentalUnit');
                const startH = document.getElementById('inputRentalStartDate');
                const extraH = document.getElementById('inputExtraDays');
                const totalH = document.getElementById('inputTotalPrice');
                const stockQty = document.getElementById('inputStockQuantity')?.value || '1';

                const typeLabel = ({ day: 'Daily', week: 'Weekly', month: 'Monthly' })[unitH.value] || (unitH.value || 'N/A');
                document.getElementById('summaryType').textContent = typeLabel;

                // Calculate period with start and end dates
                let vehiclePeriod = '';
                if (startH && startH.value) {
                    const startY = startH.value;
                    const unit = unitH.value;
                    const qty = parseInt(document.getElementById('inputRentalQuantity').value);
                    const extra = parseInt(extraH?.value || '0');
                    const startDt = fromYMD(startY);

                    if (startDt) {
                        const baseDays = qty * unitDays(unit);
                        const days = baseDays + (unit === 'day' ? 0 : extra);
                        const endDt = addDays(startDt, Math.max(0, days - 1));
                        const endY = toYMD(endDt);

                        vehiclePeriod = `${niceDate(startY)} to ${niceDate(endY)}`;

                        if (extra > 0) {
                            vehiclePeriod += ` (${days} days total = ${baseDays} base + ${extra} extra)`;
                        } else {
                            vehiclePeriod += ` (${days} days)`;
                        }
                    }
                }

                document.getElementById('summaryPeriod').textContent = vehiclePeriod || 'N/A';
                document.getElementById('summaryVehicleTotal').textContent = money(totalH ? totalH.value : 0);
                document.getElementById('summaryUnits').textContent = `${stockQty} unit${stockQty !== '1' ? 's' : ''}`;

                const vehicleTotal = parseFloat(totalH?.value || '0');
                document.getElementById('summaryGrandTotal').textContent = money(vehicleTotal);

                document.getElementById('summaryCustomerName').textContent = name.value;
                document.getElementById('summaryCustomerEmail').textContent = email.value;
                document.getElementById('summaryCustomerPhone').textContent = phone.value;
                document.getElementById('summaryCustomerCountry').textContent = country.value;

                const custEl = document.getElementById('customerStep');
                const sumEl = document.getElementById('summaryStep');
                (bootstrap.Modal.getInstance(custEl) || new bootstrap.Modal(custEl)).hide();
                (bootstrap.Modal.getInstance(sumEl) || new bootstrap.Modal(sumEl)).show();
            });
        }

        /* =========================
           THANK YOU MODAL
           ========================= */
        window.populateThankYouModal = (methodLabel) => {
            if (!bookingThankYouModalEl) return;
            const tyVehicleNameEl = document.getElementById('tyVehicleName');
            if (tyVehicleNameEl) tyVehicleNameEl.textContent = "{{ addslashes($bookableName) }}";

            const tyVehicleSubEl = document.getElementById('tyVehicleSub');
            if (tyVehicleSubEl) tyVehicleSubEl.textContent = "{{ addslashes($bookableModel) }}";

            const periodText = document.getElementById('summaryPeriod')?.textContent?.trim() || 'N/A';
            const tyPeriodEl = document.getElementById('tyPeriod');
            if (tyPeriodEl) tyPeriodEl.textContent = periodText;

            const reference = currentBookingReference || (bookingIdField?.value ? `#${bookingIdField.value}` : 'N/A');
            const tyReferenceEl = document.getElementById('tyReference');
            if (tyReferenceEl) tyReferenceEl.textContent = reference;

            const tyAmountEl = document.getElementById('tyAmount');
            if (tyAmountEl) tyAmountEl.textContent = money(computeGrandTotal());

            const tyMethodEl = document.getElementById('tyMethod');
            if (tyMethodEl) tyMethodEl.textContent = methodLabel || 'N/A';

            const tyCustomerNameEl = document.getElementById('tyCustomerName');
            if (tyCustomerNameEl) tyCustomerNameEl.textContent = bookingForm?.name?.value?.trim() || 'N/A';

            const contactParts = [];
            if (bookingForm?.email?.value) contactParts.push(bookingForm.email.value.trim());
            if (bookingForm?.phone?.value) contactParts.push(bookingForm.phone.value.trim());
            const tyCustomerContactEl = document.getElementById('tyCustomerContact');
            if (tyCustomerContactEl) tyCustomerContactEl.textContent = contactParts.join(' | ') || 'N/A';

            const wa = document.getElementById('tyWhatsappBtn');
            if (wa) {
                const txt = `Hi! I just completed my booking (Reference: ${reference}) and need assistance.`;
                const url = new URL("https://wa.me/27612345678");
                url.searchParams.set('text', txt);
                wa.href = url.toString();
            }

            const cont = document.getElementById('tyContinueVehicles');
            if (cont) cont.onclick = () => {
                window.location.href = "{{ $continueBrowseUrl }}";
            };
        };

        window.showThankYou = (methodLabel) => {
            window.populateThankYouModal(methodLabel);
            const m = bootstrap.Modal.getOrCreateInstance(bookingThankYouModalEl);
            m.show();
        };

        /* =========================
           INITIALIZATION
           ========================= */
        initCalendar();
        updateExtraDaysVisibility();
        updateLocationAvailability();
        updateStep1Paint();
        populateStockQuantitySelect(null);
    });
</script>
