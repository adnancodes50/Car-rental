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
    <div class="modal fade" id="multiStepBookingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
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
                            <div class="col-12 col-md-4 mb-3">
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
                            <div class="col-12 col-md-4 mb-3">
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
                            <div class="col-12 col-md-4 mb-3">
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
                        <label class="form-label fw-semibold">Select Start Date</label>
                        <div class="position-relative w-40">
                            <input type="text" id="rentalStartDate" class="form-control ps-5"
                                placeholder="Click to select a start date" readonly data-lead="{{ $bookingLeadDays }}"
                                data-blocked='@json($bookedRanges)'>
                            <span class="position-absolute top-50 start-0 translate-middle-y ps-3">
                                <i class="bi bi-calendar-event"></i>
                            </span>
                        </div>
                    </div>

                    <!-- Quantity & Extra Days -->
                    <div class="row g-2">
                        <!-- Quantity -->
                        <div class="col-12 col-md-6 mb-3 d-none" id="quantitySection">
                            <label for="rentalQuantity" class="form-label fw-semibold" id="quantityLabel"></label>
                            <select id="rentalQuantity" class="form-select rounded-3"></select>
                        </div>

                        <!-- Extra Days -->
                        {{-- <div class="col-12 col-md-6 mb-3 d-none" id="extraDaysSection">
                            <label for="extraDaysInput" class="form-label fw-semibold">Extra day(s)</label>
                            <input type="number" min="0" step="1" value="0" class="form-control"
                                id="extraDaysInput" inputmode="numeric">
                            <div class="form-text" id="extraDaysHelp">Add additional day(s) on top of the selected
                                duration.</div>
                        </div> --}}
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
                        <div class="row g-2" id="locationRow">
                            <div class="col-12 col-md-6">
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
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold" for="stockQuantitySelect">Stock Quantity</label>
                                <select class="form-select" id="stockQuantitySelect">
                                    <option value="" selected>Select quantity</option>
                                </select>
                                <div class="form-text" id="locationAvailabilityHint">{{ $initialHint }}</div>
                            </div>
                        </div>
                    @endif

                    <!-- Total Price -->
                    <div class="alert alert-info fw-bold d-none mt-3" id="totalPrice"></div>

                    <!-- Rental Period -->
                    <div class="alert alert-secondary fw-bold d-none mt-2" id="rentalPeriod"></div>
                </div>

                <div class="modal-footer bg-light border-top py-3">
                    <button type="button" id="continueFromStep1" class="btn btn-dark rounded-3 w-100 py-2">
                        Continue to Details
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 2: Customer Details Modal -->
    <div class="modal fade" id="customerStep" tabindex="-1" aria-hidden="true">
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
                                pattern="^[a-zA-Z0-9\s\-\.,#()+]+$"
                                title="Enter a valid phone number with country code, e.g. +27 123 456 7890">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Your Address</label>
                            <input type="text" id="bookingCustomerCountry" name="country"
                                class="form-control rounded-3" placeholder="Start typing your address..."
                                autocomplete="street-address" required pattern="^[a-zA-Z0-9\s\-\.,#()]+$"
                                title="Enter a valid address with letters, numbers, spaces, hyphens, commas, periods, #, and parentheses">
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 py-3">
                    <div class="w-100 d-flex justify-content-between gap-2">
                        <button type="button" class="btn btn-md btn-outline-secondary rounded-3"
                            id="customerBackToStep1">Back</button>
                        <button type="button" id="goToSummary" class="btn btn-md  btn-dark rounded-3 ">
                            Review Booking
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 3: Booking Summary -->
    <div class="modal fade" id="summaryStep" tabindex="-1" aria-hidden="true">
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
                        <!-- Detailed breakdown will be inserted here -->
                        <div id="summaryPriceBreakdown">
                            <div class="d-flex justify-content-between small mb-1">
                                <span>{{ $bookableLabel }} rental</span>
                                <span id="summaryVehicleTotal">R0.00</span>
                            </div>
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

                <div class="modal-footer py-3">
                    <div class="w-100 d-flex justify-content-between gap-2">
                        <button type="button" class="btn btn-md btn-outline-secondary rounded-3 "
                            id="summaryBackToCustomer">Back</button>
                        <button type="button" id="openPayment" class="btn btn-dark rounded-3 btn-md">
                            Continue to Payment
                        </button>
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
    <div class="modal-dialog modal-md modal-dialog-centered">
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

            <div class="modal-footer py-3">
                <div class="w-100 d-flex justify-content-between gap-2">
                    <button type="button" class="btn btn-outline-secondary rounded-3 btn-md"
                        id="paymentBackToSummary">Back</button>
                    <div class="flex-fill"></div> <!-- Empty spacer for alignment -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stripe Payment Modal -->
<div class="modal fade" id="bookingStripeModal" tabindex="-1" aria-hidden="true">
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
                        <div class="col-12 col-md-6">
                            <div id="booking-card-expiry" class="form-control"></div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div id="booking-card-cvc" class="form-control"></div>
                        </div>
                    </div>
                    <div id="booking-card-errors" class="text-danger mt-2"></div>
                </div>
            </div>
            <div class="modal-footer py-3">
                <div class="w-100 d-flex justify-content-between gap-2">
                    <button type="button" class="btn btn-outline-secondary rounded-3 flex-fill"
                        id="stripeBackToPayment">Back</button>
                    <button type="button" id="bookingStripePayButton" class="btn btn-dark rounded-3 flex-fill">
                        Pay with Stripe
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Thank You Modal -->
<div class="modal fade" id="bookingThankYou" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
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
                        <div class="col-12 col-md-6">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-box-seam mt-1"></i>
                                <div>
                                    <div class="small text-muted">{{ $bookableLabel }}</div>
                                    <div class="fw-semibold" id="tyVehicleName">N/A</div>
                                    <div class="text-muted small" id="tyVehicleSub"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-person mt-1"></i>
                                <div>
                                    <div class="small text-muted">Primary renter</div>
                                    <div class="fw-semibold" id="tyCustomerName">N/A</div>
                                    <div class="text-muted small" id="tyCustomerContact">N/A</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-calendar-event mt-1"></i>
                                <div>
                                    <div class="small text-muted">Rental period</div>
                                    <div class="fw-semibold" id="tyPeriod"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
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

            <div class="modal-footer border-0 pt-0 px-4 px-md-5 pb-4">
                <div class="w-100 d-flex flex-wrap gap-2 justify-content-between">
                    <a href="/" class="btn btn-outline-secondary rounded-3 flex-fill" id="tyContinueVehicles">
                        Continue to Categories
                    </a>

                    <a href="https://api.whatsapp.com/send?phone=27673285525&text=Hi%20Wayne%2C%20I%27m%20contacting%20your%20from%20your%20Rent2Recover%20website"
                        class="btn btn-success fw-bold rounded-3 d-flex align-items-center justify-content-center gap-2 flex-fill"
                        target="_blank" id="tyWhatsappBtn" rel="noopener">
                        <i class="bi bi-whatsapp fs-5"></i>Chat with Us
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* ===========================================
       RESPONSIVE MODAL STYLES - MOBILE FIRST
       =========================================== */
    .modal {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    .modal-dialog {
        margin: 0.5rem;
        max-width: calc(100% - 1rem);
    }

    .modal-content {
        border: none;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        display: flex;
        flex-direction: column;
        max-height: 95vh;
    }

    .modal-header {
        flex-shrink: 0;
        border-bottom: 1px solid #dee2e6;
        padding: 1rem 1.25rem;
    }

    .modal-body {
        flex-grow: 1;
        overflow-y: auto;
        padding: 1.25rem;
    }

    .modal-footer {
        flex-shrink: 0;
        border-top: 1px solid #dee2e6;
        padding: 1rem 1.25rem;
        background: #f8f9fa;
    }

    /* Mobile footer buttons - always visible and properly spaced */
    .modal-footer .btn {
        min-height: 44px;
        font-weight: 600;
    }

    .modal-footer .flex-fill {
        flex: 1;
        min-width: 0;
    }

    /* Small devices (phones, 576px and down) */
    @media (max-width: 576px) {
        .modal-dialog {
            margin: 0;
            max-width: 100%;
            min-height: 100vh;
        }

        .modal-content {
            border-radius: 0;
            min-height: 100vh;
            max-height: 100vh;
        }

        .modal-header {
            padding: 1rem;
            position: sticky;
            top: 0;
            background: white;
            z-index: 10;
        }

        .modal-body {
            padding: 1rem;
            overflow-y: auto;
        }

        .modal-footer {
            padding: 1rem;
            position: sticky;
            bottom: 0;
            background: white;
            border-top: 1px solid #dee2e6;
            z-index: 10;
        }

        /* Ensure buttons are properly sized on mobile */
        .btn {
            padding: 12px 16px;
            font-size: 16px;
            min-height: 44px;
        }

        /* Form elements full width on mobile */
        .form-control,
        .form-select {
            font-size: 16px;
            padding: 12px 16px;
            min-height: 44px;
        }

        /* Better spacing for mobile */
        .row.g-3 {
            margin: -0.5rem;
        }

        .row.g-3>[class*="col-"] {
            padding: 0.5rem;
        }

        /* Stack columns vertically on mobile */
        .row>[class*="col-"] {
            margin-bottom: 0.75rem;
        }
    }

    /* Medium devices (tablets, 768px and up) */
    @media (min-width: 768px) {
        .modal-dialog {
            margin: 1.75rem auto;
            max-width: 90%;
        }

        .modal-content {
            border-radius: 0.5rem;
            min-height: auto;
            max-height: 90vh;
        }

        .modal-header {
            padding: 1rem 1.5rem;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            padding: 1rem 1.5rem;
        }
    }

    /* Large devices (desktops, 992px and up) */
    @media (min-width: 992px) {
        .modal-dialog.modal-lg {
            max-width: 800px;
        }

        .modal-dialog.modal-md {
            max-width: 600px;
        }
    }

    /* ===========================================
       PAYMENT METHOD SELECTION
       =========================================== */
    #bookingPayment .booking-pay-option {
        min-height: 140px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        border: 1px solid #dee2e6;
        border-radius: .75rem;
        padding: 20px;
        text-align: left;
        transition: box-shadow .15s ease, transform .15s ease, border-color .15s ease;
        cursor: pointer;
    }

    #bookingPayment .booking-pay-option:hover {
        transform: translateY(-2px);
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .08);
    }

    #bookingPayment .btn-check:checked+.booking-pay-option {
        border-color: #0d6efd;
        box-shadow: 0 0 0 .25rem rgba(13, 110, 253, .2);
    }

    @media (max-width: 576px) {
        #bookingPayment .booking-pay-option {
            min-height: 120px;
            padding: 15px;
            flex-direction: column;
            text-align: center;
            gap: 8px;
        }

        #bookingPayment .booking-pay-text .fw-bold {
            font-size: 1rem;
        }
    }

    /* ===========================================
       CALENDAR STYLES - FULLY RESPONSIVE
       =========================================== */
    #dateSection {
        width: 100%;
        margin-bottom: 1rem;
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
        min-height: 48px;
    }

    #rentalStartDate:focus {
        border-color: #CF9B4D;
        box-shadow: 0 0 0 0.2rem rgba(207, 155, 77, 0.25);
        outline: none;
    }

    #dateSection .position-relative {
        width: 100%;
        margin-bottom: 1rem;
    }

    #dateSection .position-absolute {
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        z-index: 1;
        color: #6c757d;
    }

    /* Flatpickr Calendar Responsive Styles */
    .flatpickr-calendar {
        z-index: 99999 !important;
    }

    .flatpickr-wrapper {
        width: 100% !important;
    }

    /* Mobile calendar styles */
    @media (max-width: 768px) {
        #rentalStartDate {
            padding: 14px 16px 14px 45px;
            font-size: 16px;
        }

        #dateSection .position-absolute {
            left: 16px;
        }

        .flatpickr-calendar {
            width: 90% !important;
            max-width: 400px !important;
            position: fixed !important;
            top: 50% !important;
            left: 50% !important;
            transform: translate(-50%, -50%) !important;
        }
    }

    /* Desktop calendar styles - fixed for larger screens */
    @media (min-width: 769px) {
        .flatpickr-calendar {
            width: 320px !important;
            position: absolute !important;
            top: 100% !important;
            left: 0 !important;
            transform: none !important;
        }

        .flatpickr-calendar.open {
            top: 100% !important;
            left: 0 !important;
        }

        .flatpickr-calendar.arrowTop:before,
        .flatpickr-calendar.arrowTop:after {
            display: none !important;
        }
    }

    /* ===========================================
       OPTION CARDS
       =========================================== */
    .option-card {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
        margin-bottom: 0.75rem;
    }

    .option-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .option-card.active {
        border-color: #CF9B4D !important;
        background-color: #fff9f0 !important;
        box-shadow: 0 4px 12px rgba(207, 155, 77, 0.2);
    }

    @media (max-width: 576px) {
        .option-card {
            margin-bottom: 0.5rem;
            padding: 1rem !important;
        }
    }

    /* ===========================================
       SUMMARY PRICING BREAKDOWN
       =========================================== */
    #summaryPriceBreakdown .price-line {
        display: flex;
        justify-content: between;
        align-items: center;
        padding: 0.25rem 0;
        border-bottom: 1px solid #f8f9fa;
    }

    #summaryPriceBreakdown .price-line:last-child {
        border-bottom: none;
    }

    #summaryPriceBreakdown .price-details {
        font-size: 0.875rem;
        color: #6c757d;
    }

    #summaryPriceBreakdown .price-amount {
        font-weight: 600;
        color: #000;
    }

    /* ===========================================
       UTILITY CLASSES
       =========================================== */
    .text-break-word {
        word-wrap: break-word;
        word-break: break-word;
    }

    /* Loading State */
    .btn-loading {
        position: relative;
        color: transparent !important;
    }

    .btn-loading::after {
        content: '';
        position: absolute;
        width: 20px;
        height: 20px;
        top: 50%;
        left: 50%;
        margin-left: -10px;
        margin-top: -10px;
        border: 2px solid #ffffff;
        border-radius: 50%;
        border-right-color: transparent;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }
</style>
<script src="https://js.stripe.com/v3/"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- GOOGLE PLACES AUTOCOMPLETE CALLBACK --}}
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
                        if (window.latestLocationAvailability !== null && window.latestLocationAvailability <=
                            0) {
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

                let currentUnitMax = 6; // Fixed for daily
                let suppressRentalEvent = false;
                let isUpdatingStep1 = false;

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
                    let max = 6; // Daily: ALWAYS 1-6 days
                    let label = 'How many day(s)?';
                    currentUnitMax = max;

                    if (u === 'week') {
                        max = 4; // Weekly: ALWAYS 1-4 weeks
                        label = 'How many week(s)?';
                        currentUnitMax = max;
                    }
                    if (u === 'month') {
                        max = 12; // Monthly: ALWAYS 1-12 months
                        label = 'How many month(s)?';
                        currentUnitMax = max;
                    }

                    qtyLabel.textContent = label;
                    // FIXED: Always show the fixed range, never limit by stock for rental duration
                    fillSelect(qtySelect, 1, max, 1);
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
                            hidTotal.dispatchEvent(new Event('change', {
                                bubbles: true
                            }));
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
                            hidUnit.dispatchEvent(new Event('change', {
                                bubbles: true
                            }));
                        }

                        // show date + qty sections and prepare qty
                        dateSection?.classList.remove('d-none');
                        qtySection?.classList.remove('d-none');
                        configureQtyForUnit(u);

                        // sync qty hidden
                        if (hidQty && qtySelect) {
                            hidQty.value = qtySelect.value;
                            hidQty.dispatchEvent(new Event('change', {
                                bubbles: true
                            }));
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
                            hidQty.dispatchEvent(new Event('change', {
                                bubbles: true
                            }));
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

                    const leadDays = parseInt(inp.getAttribute('data-lead') || (window.bookingLeadDays ?? '0'),
                        10) || 0;

                    let blockedRanges = [];
                    try {
                        const raw = inp.getAttribute('data-blocked') || JSON.stringify(window
                            .vehicleBlockedRanges || []);
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
                            static: true,
                            monthSelectorType: 'static',
                            // Mobile-friendly options
                            disableMobile: false,
                            time_24hr: true,
                            // Responsive positioning
                            position: 'auto',
                            onReady: function(selectedDates, dateStr, instance) {
                                const calendar = instance.calendarContainer;
                                const input = instance._input;

                                if (calendar) {
                                    calendar.style.zIndex = '99999';
                                    // Make calendar responsive
                                    calendar.style.width = '100%';
                                    calendar.style.maxWidth = '100%';
                                }

                                // Ensure input takes full width
                                if (input) {
                                    input.style.width = '100%';
                                }
                            },
                            onOpen: function(selectedDates, dateStr, instance) {
                                const calendar = instance.calendarContainer;
                                if (calendar) {
                                    calendar.style.zIndex = '99999';
                                    calendar.style.width = '100%';
                                    calendar.style.maxWidth = '100%';

                                    // Mobile-specific adjustments
                                    if (window.innerWidth < 768) {
                                        calendar.style.position = 'fixed';
                                        calendar.style.top = '50%';
                                        calendar.style.left = '50%';
                                        calendar.style.transform = 'translate(-50%, -50%)';
                                        calendar.style.width = '90%';
                                        calendar.style.maxWidth = '400px';
                                    }
                                }
                            },
                            onChange: function(selectedDates, dateStr) {
                                if (hidStart) {
                                    hidStart.value = dateStr || '';
                                    hidStart.dispatchEvent(new Event('change', {
                                        bubbles: true
                                    }));
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
                                inp.style.boxSizing = 'border-box';
                            }
                        }, 100);
                    } else {
                        // Fallback to native date input for mobile
                        try {
                            inp.removeAttribute('readonly');
                            inp.setAttribute('type', 'date');
                            inp.style.width = '100%';
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
                                hidStart.dispatchEvent(new Event('change', {
                                    bubbles: true
                                }));
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
                        hidExtra.dispatchEvent(new Event('change', {
                            bubbles: true
                        }));
                        if (extraDaysHelp) {
                            extraDaysHelp.textContent = limit === 6 ? '1 to 6 days.' : '1 to 29 days.';
                        }
                    } else {
                        extraDaysSection.classList.add('d-none');
                        extraDaysInput.value = '0';
                        hidExtra.value = '0';
                        hidExtra.dispatchEvent(new Event('change', {
                            bubbles: true
                        }));
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
                        hidExtra.dispatchEvent(new Event('change', {
                            bubbles: true
                        }));
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

                function notify(key, {
                    icon = 'warning',
                    title = 'Notice',
                    text = ''
                }) {
                    const now = Date.now(),
                        last = alertDebounce.get(key) || 0;
                    if (now - last < 600) return;

                    const step1ModalEl = document.getElementById('multiStepBookingModal');
                    const step1Visible = step1ModalEl?.classList.contains('show');
                    const suppressForStep1 = typeof key === 'string' && /^overlap|^noqty|^clamped/.test(key);
                    if (step1Visible && suppressForStep1) return;

                    alertDebounce.set(key, now);
                    if (window.Swal?.fire) Swal.fire({
                        icon,
                        title,
                        text,
                        confirmButtonText: 'OK'
                    });
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

                const paymentMethodInputs = Array.from(document.querySelectorAll(
                    'input[name="booking_payment_method"]'));
                const resetPaymentSelection = () => {
                    paymentMethodInputs.forEach((input) => {
                        input.checked = false;
                        input.removeAttribute('checked');
                    });
                };
                if (bookingPaymentModalEl) bookingPaymentModalEl.addEventListener('hidden.bs.modal',
                    resetPaymentSelection);

                let currentBookingReference = null;

                const stripePublicKey = "{{ $stripeConfig->stripe_key ?? '' }}";
                let stripeInstance = null,
                    stripeElements = null,
                    stripeCardNumber = null,
                    stripeCardExpiry = null,
                    stripeCardCvc = null;

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

                // Remove the old populateThankYouModal and showThankYou functions since we're redirecting to confirmation page

                if (openPaymentBtn) {
                    const openPaymentDefaultLabel = (openPaymentBtn.textContent || '').trim() || 'Continue to Payment';
                    openPaymentBtn.dataset.originalLabel = openPaymentDefaultLabel;

                    const setOpenPaymentLoading = (isLoading) => {
                        if (isLoading) {
                            openPaymentBtn.disabled = true;
                            openPaymentBtn.textContent = 'Preparing booking...';
                        } else {
                            openPaymentBtn.disabled = false;
                            openPaymentBtn.textContent = openPaymentBtn.dataset.originalLabel ||
                                openPaymentDefaultLabel;
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
                                        'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name="csrf-token"]')?.getAttribute(
                                            'content') || ''
                                    }
                                });

                                const text = await res.text();
                                let data;
                                try {
                                    data = JSON.parse(text);
                                } catch {
                                    data = {
                                        success: false,
                                        message: text
                                    };
                                }

                                if (!res.ok || !data?.success) {
                                    await Swal.fire({
                                        icon: 'error',
                                        title: 'Booking not created',
                                        text: data?.message || 'Failed to create booking.'
                                    });
                                    return;
                                }

                                bookingIdField.value = data.booking_id || data.id || '';
                                currentBookingReference = data.reference || null;
                                if (!bookingIdField.value) {
                                    await Swal.fire({
                                        icon: 'error',
                                        title: 'Missing booking ID',
                                        text: 'Booking was created but no identifier was returned.'
                                    });
                                    return;
                                }
                            } catch (error) {
                                console.error(error);
                                await Swal.fire({
                                    icon: 'error',
                                    title: 'Network error',
                                    text: 'Unable to create booking, please try again.'
                                });
                                return;
                            } finally {
                                bookingCreationInFlight = false;
                                setOpenPaymentLoading(false);
                            }
                        }

                        if (!bookingIdField?.value) return;

                        const summaryModal = bootstrap.Modal.getInstance(document.getElementById(
                            'summaryStep'));
                        summaryModal?.hide();

                        if (bookingPaymentModalEl) new bootstrap.Modal(bookingPaymentModalEl).show();

                        const stripeRadio = document.getElementById('bookingStripe');
                        if (stripeRadio?.checked && bookingStripePayButton) bookingStripePayButton.dataset
                            .amount = String(computeGrandTotal());
                    });
                }

     document.addEventListener('change', async (e) => {
    if (!(e.target && e.target.name === 'booking_payment_method')) return;

    const method = e.target.value;
    const paymentModalInstance = bookingPaymentModalEl ? bootstrap.Modal.getInstance(
        bookingPaymentModalEl) : null;
    paymentModalInstance?.hide();

    const grandTotal = computeGrandTotal();

    if (method === 'stripe') {
        if (bookingStripePayButton) bookingStripePayButton.dataset.amount = String(
            grandTotal);
        if (bookingStripeModalEl) new bootstrap.Modal(bookingStripeModalEl).show();
        return;
    }

    if (method === 'payfast') {
        const bookingId = bookingIdField?.value;
        if (!bookingId) {
            await Swal.fire({
                icon: 'error',
                title: 'Booking missing',
                text: 'Please create the booking first.'
            });
            if (bookingPaymentModalEl) new bootstrap.Modal(bookingPaymentModalEl).show();
            e.target.checked = false;
            return;
        }

        try {
            showPaymentLoader('Redirecting to PayFast...');

            // Add debugging
            console.log('Initiating PayFast for booking:', bookingId);

            // Make the API call to get PayFast checkout data
            const res = await fetch(
                `/payfast/booking/init/${encodeURIComponent(bookingId)}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector(
                            'meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({
                        booking_id: bookingId,
                        return_url: window.location.origin + '/booking/confirmation/' + bookingId
                    })
                });

            console.log('PayFast init response status:', res.status);

            const text = await res.text();
            console.log('PayFast init response text:', text);

            let data;
            try {
                data = JSON.parse(text);
            } catch {
                data = {
                    success: false,
                    message: text
                };
            }

            if (!res.ok || !data?.success) {
                console.error('PayFast init failed:', data);
                throw new Error(data?.message ||
                    'Failed to prepare PayFast checkout.');
            }

            console.log('PayFast init successful, redirecting to:', data.action);
            console.log('Return URL will be:', data.fields?.return_url);

            // Create and submit form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = data.action;
            form.style.display = 'none';

            // Add all fields from response
            Object.entries(data.fields || {}).forEach(([key, value]) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                form.appendChild(input);
            });

            // Ensure return_url is set (backup)
            if (!data.fields?.return_url) {
                const returnInput = document.createElement('input');
                returnInput.type = 'hidden';
                returnInput.name = 'return_url';
                returnInput.value = window.location.origin + '/booking/confirmation/' + bookingId;
                form.appendChild(returnInput);
            }

            // Ensure cancel_url is set
            if (!data.fields?.cancel_url) {
                const cancelInput = document.createElement('input');
                cancelInput.type = 'hidden';
                cancelInput.name = 'cancel_url';
                cancelInput.value = window.location.origin + '/';
                form.appendChild(cancelInput);
            }

            document.body.appendChild(form);
            form.submit();
            return; // Important: Stop further execution

        } catch (err) {
            console.error('PayFast error details:', err);
            await Swal.fire({
                icon: 'error',
                title: 'PayFast error',
                text: err.message ||
                    'Could not redirect to PayFast. Please try again.'
            });
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
                            base: {
                                fontSize: '16px',
                                color: '#32325d',
                                '::placeholder': {
                                    color: '#a0aec0'
                                }
                            }
                        };

                        stripeCardNumber = stripeElements.create('cardNumber', {
                            style: stripeStyle
                        });
                        stripeCardExpiry = stripeElements.create('cardExpiry', {
                            style: stripeStyle
                        });
                        stripeCardCvc = stripeElements.create('cardCvc', {
                            style: stripeStyle
                        });

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
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Booking missing',
                                    text: 'Please create the booking first.'
                                });
                                return;
                            }

                            if (!stripeCardNumber || !stripeCardExpiry || !stripeCardCvc) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Stripe unavailable',
                                    text: 'Payment form is not ready yet.'
                                });
                                return;
                            }

                            if (bookingCardErrorsEl) bookingCardErrorsEl.textContent = '';

                            const button = this;
                            const originalText = button.textContent;
                            button.disabled = true;
                            button.textContent = 'Processing...';
                            showPaymentLoader();

                            try {
                                const {
                                    paymentMethod,
                                    error
                                } = await stripeInstance.createPaymentMethod({
                                    type: 'card',
                                    card: stripeCardNumber,
                                    billing_details: {
                                        name: bookingForm?.name?.value || '',
                                        email: bookingForm?.email?.value || ''
                                    }
                                });

                                if (error) {
                                    if (bookingCardErrorsEl) bookingCardErrorsEl.textContent = error
                                        .message ||
                                        'Payment method error.';
                                    hidePaymentLoader();
                                    return;
                                }

                                const res = await fetch(
                                    `/bookings/${encodeURIComponent(bookingIdField.value)}/pay-with-stripe`, {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector(
                                                'meta[name="csrf-token"]')?.content || ''
                                        },
                                        body: JSON.stringify({
                                            payment_method_id: paymentMethod.id,
                                            amount: parseFloat(button.dataset.amount || '0')
                                        })
                                    });

                                const text = await res.text();
                                let data;
                                try {
                                    data = JSON.parse(text);
                                } catch {
                                    data = {
                                        success: false,
                                        message: text
                                    };
                                }

                                hidePaymentLoader();

                                if (!res.ok || !data) {
                                    await Swal.fire({
                                        icon: 'error',
                                        title: 'Payment failed',
                                        text: data?.message ||
                                            'Server error while processing payment.'
                                    });
                                    return;
                                }

                                if (data.success) {
                                    bootstrap.Modal.getInstance(bookingStripeModalEl)?.hide();

                                    // Redirect to confirmation page instead of showing thank you modal
                                    if (data.redirect_url) {
                                        window.location.href = data.redirect_url;
                                    } else {
                                        window.location.href = '/booking/confirmation/' + bookingIdField
                                            .value;
                                    }
                                    return;
                                }

                                if (data.requires_action && data.payment_intent_client_secret) {
                                    const result = await stripeInstance.confirmCardPayment(data
                                        .payment_intent_client_secret);
                                    if (result.error) {
                                        await Swal.fire({
                                            icon: 'error',
                                            title: 'Authentication failed',
                                            text: result.error.message ||
                                                'Unable to confirm your card.'
                                        });
                                    } else {
                                        bootstrap.Modal.getInstance(bookingStripeModalEl)?.hide();

                                        // Redirect to confirmation page
                                        window.location.href = '/booking/confirmation/' + bookingIdField
                                            .value;
                                    }
                                } else {
                                    await Swal.fire({
                                        icon: 'error',
                                        title: 'Payment failed',
                                        text: data.message || 'Unable to charge your card.'
                                    });
                                }
                            } catch (error) {
                                console.error(error);
                                hidePaymentLoader();
                                await Swal.fire({
                                    icon: 'error',
                                    title: 'Network error',
                                    text: error.message || 'Unable to reach the payment server.'
                                });
                            } finally {
                                hidePaymentLoader();
                                button.disabled = false;
                                button.textContent = originalText;
                            }
                        });
                    }

                    /* =========================
                       SUMMARY WITH DETAILED PRICING
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

                            // Updated patterns to match backend validation
                            const emailPattern = /^([^\s@]+)@([^\s@]+)\.[^\s@]{2,}$/;
                            const phonePattern =
                                /^[a-zA-Z0-9\s\-\.,#()+]+$/; // Matches backend pattern exactly

                            if (!name.value.trim() || !emailValue || !phoneValue || !country.value) {
                                notify('cust-missing', {
                                    icon: 'error',
                                    title: 'Missing Information',
                                    text: 'Please fill all required customer details.'
                                });
                                return;
                            }

                            if (!emailPattern.test(emailValue)) {
                                notify('cust-invalid', {
                                    icon: 'error',
                                    title: 'Invalid Email',
                                    text: 'Enter a valid email address, e.g. you@example.com.'
                                });
                                email.focus();
                                return;
                            }

                            if (!phonePattern.test(phoneValue)) {
                                notify('cust-invalid', {
                                    icon: 'error',
                                    title: 'Invalid Phone Number',
                                    text: 'Enter a valid phone number with country code, e.g. +27 123 456 7890. Only letters, numbers, spaces, hyphens, commas, periods, #, parentheses, and + are allowed.'
                                });
                                phone.focus();
                                return;
                            }

                            // Get all the necessary values
                            const unitH = document.getElementById('inputRentalUnit');
                            const startH = document.getElementById('inputRentalStartDate');
                            const extraH = document.getElementById('inputExtraDays');
                            const totalH = document.getElementById('inputTotalPrice');
                            const stockQty = document.getElementById('inputStockQuantity')?.value || '1';
                            const rentalQty = document.getElementById('inputRentalQuantity')?.value || '1';

                            const typeLabel = ({
                                day: 'Daily',
                                week: 'Weekly',
                                month: 'Monthly'
                            })[unitH.value] || (unitH.value || 'N/A');
                            document.getElementById('summaryType').textContent = typeLabel;

                            // Calculate period with start and end dates
                            let vehiclePeriod = '';
                            if (startH && startH.value) {
                                const startY = startH.value;
                                const unit = unitH.value;
                                const qty = parseInt(rentalQty);
                                const extra = parseInt(extraH?.value || '0');
                                const startDt = fromYMD(startY);

                                if (startDt) {
                                    const baseDays = qty * unitDays(unit);
                                    const days = baseDays + (unit === 'day' ? 0 : extra);
                                    const endDt = addDays(startDt, Math.max(0, days - 1));
                                    const endY = toYMD(endDt);

                                    vehiclePeriod = `${niceDate(startY)} to ${niceDate(endY)}`;

                                    if (extra > 0) {
                                        vehiclePeriod +=
                                            ` (${days} days total = ${baseDays} base + ${extra} extra)`;
                                    } else {
                                        vehiclePeriod += ` (${days} days)`;
                                    }
                                }
                            }

                            document.getElementById('summaryPeriod').textContent = vehiclePeriod || 'N/A';
                            document.getElementById('summaryUnits').textContent =
                                `${stockQty} unit${stockQty !== '1' ? 's' : ''}`;

                            const vehicleTotal = parseFloat(totalH?.value || '0');
                            document.getElementById('summaryGrandTotal').textContent = money(vehicleTotal);

                            // Generate and display detailed price breakdown
                            const breakdownHTML = generatePriceBreakdown();
                            const summaryBreakdownEl = document.getElementById('summaryPriceBreakdown');
                            if (summaryBreakdownEl) {
                                summaryBreakdownEl.innerHTML = breakdownHTML;
                            }

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

                    // Add the generatePriceBreakdown function
                    function generatePriceBreakdown() {
                        const unit = document.getElementById('inputRentalUnit')?.value;
                        const qty = parseInt(document.getElementById('inputRentalQuantity')?.value || '0', 10);
                        const extra = parseInt(document.getElementById('inputExtraDays')?.value || '0', 10);
                        const stockQty = parseInt(document.getElementById('inputStockQuantity')?.value || '1', 10);
                        const pricePer = priceForActiveUnit();

                        if (!unit || !qty || !pricePer) return '';

                        let baseDays = 0;
                        let unitLabel = '';
                        let dailyRate = 0;
                        let basePrice = 0;

                        switch (unit) {
                            case 'day':
                                baseDays = qty;
                                unitLabel = 'day(s)';
                                dailyRate = pricePer;
                                basePrice = pricePer * qty;
                                break;
                            case 'week':
                                baseDays = qty * 7;
                                unitLabel = 'week(s)';
                                dailyRate = pricePer / 7;
                                basePrice = pricePer * qty;
                                break;
                            case 'month':
                                baseDays = qty * 30;
                                unitLabel = 'month(s)';
                                dailyRate = pricePer / 30;
                                basePrice = pricePer * qty;
                                break;
                        }

                        const extraDaysPrice = dailyRate * extra;
                        const totalBasePrice = (basePrice + extraDaysPrice) * stockQty;

                        let html = `
        <div class="d-flex justify-content-between small mb-2">
            <span>
                ${stockQty} unit${stockQty > 1 ? 's' : ''} × ${qty} ${unit}${qty > 1 ? 's' : ''}
                @ R${pricePer.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}/${unit}
            </span>
            <span class="fw-semibold">R${(basePrice * stockQty).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
        </div>`;

                        if (extra > 0) {
                            html += `
        <div class="d-flex justify-content-between small mb-2">
            <span>
                ${stockQty} unit${stockQty > 1 ? 's' : ''} × ${extra} extra day${extra > 1 ? 's' : ''}
                @ R${dailyRate.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}/day
            </span>
            <span class="fw-semibold">R${(extraDaysPrice * stockQty).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
        </div>`;
                        }

                        // Add subtotal if multiple items
                        if (stockQty > 1 || extra > 0) {
                            html += `
        <div class="d-flex justify-content-between small mb-2 border-top pt-2">
            <span class="fw-semibold">Subtotal</span>
            <span class="fw-semibold">R${totalBasePrice.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
        </div>`;
                        }

                        return html;
                    }

                    // Make sure priceForActiveUnit function is available
                    function priceForActiveUnit() {
                        const a = document.querySelector('.option-card.active');
                        const p = parseFloat(a?.getAttribute('data-price') || '0');
                        return isNaN(p) ? 0 : p;
                    }

                    /* =========================
                       REMOVE OLD THANK YOU MODAL HANDLING
                       ========================= */
                    // Remove or comment out the old thank you modal event listeners
                    if (bookingThankYouModalEl) {
                        // Keep this if you still want to use the modal for something else,
                        // but remove the redirect on hidden since we're using confirmation page
                        // bookingThankYouModalEl.addEventListener('hidden.bs.modal', () => {
                        //     window.location.href = "{{ url('/') }}";
                        // });
                    }

                    /* =========================
                       CLEAR BOOKING SESSION ON PAGE LOAD (for confirmation page)
                       ========================= */
                    window.addEventListener('load', function() {
                        // Only clear if we're not on the confirmation page
                        if (!window.location.pathname.includes('/booking/confirmation/')) {
                            fetch('/clear-booking-session', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                        ?.getAttribute('content') || '',
                                    'Content-Type': 'application/json'
                                },
                                credentials: 'same-origin'
                            }).catch(err => console.log('Session cleanup failed:', err));
                        }
                    });

                    /* =========================
                       INITIALIZATION
                       ========================= */
                    initCalendar(); updateExtraDaysVisibility(); updateLocationAvailability(); updateStep1Paint(); populateStockQuantitySelect(
                        null);
                });
</script>
