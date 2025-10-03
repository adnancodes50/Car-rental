    @php
        $countries = [
            'Afghanistan',
            'Albania',
            'Algeria',
            'Andorra',
            'Angola',
            'Antigua and Barbuda',
            'Argentina',
            'Armenia',
            'Australia',
            'Austria',
            'Azerbaijan',
            'Bahamas',
            'Bahrain',
            'Bangladesh',
            'Barbados',
            'Belarus',
            'Belgium',
            'Belize',
            'Benin',
            'Bhutan',
            'Bolivia',
            'Bosnia and Herzegovina',
            'Botswana',
            'Brazil',
            'Brunei',
            'Bulgaria',
            'Burkina Faso',
            'Burundi',
            'Cabo Verde',
            'Cambodia',
            'Cameroon',
            'Canada',
            'Central African Republic',
            'Chad',
            'Chile',
            'China',
            'Colombia',
            'Comoros',
            'Congo (Congo-Brazzaville)',
            'Costa Rica',
            'Croatia',
            'Cuba',
            'Cyprus',
            'Czechia',
            'Democratic Republic of the Congo',
            'Denmark',
            'Djibouti',
            'Dominica',
            'Dominican Republic',
            'Ecuador',
            'Egypt',
            'El Salvador',
            'Equatorial Guinea',
            'Eritrea',
            'Estonia',
            'Eswatini',
            'Ethiopia',
            'Fiji',
            'Finland',
            'France',
            'Gabon',
            'Gambia',
            'Georgia',
            'Germany',
            'Ghana',
            'Greece',
            'Grenada',
            'Guatemala',
            'Guinea',
            'Guinea-Bissau',
            'Guyana',
            'Haiti',
            'Holy See',
            'Honduras',
            'Hungary',
            'Iceland',
            'India',
            'Indonesia',
            'Iran',
            'Iraq',
            'Ireland',
            'Israel',
            'Italy',
            'Jamaica',
            'Japan',
            'Jordan',
            'Kazakhstan',
            'Kenya',
            'Kiribati',
            'Kuwait',
            'Kyrgyzstan',
            'Laos',
            'Latvia',
            'Lebanon',
            'Lesotho',
            'Liberia',
            'Libya',
            'Liechtenstein',
            'Lithuania',
            'Luxembourg',
            'Madagascar',
            'Malawi',
            'Malaysia',
            'Maldives',
            'Mali',
            'Malta',
            'Marshall Islands',
            'Mauritania',
            'Mauritius',
            'Mexico',
            'Micronesia',
            'Moldova',
            'Monaco',
            'Mongolia',
            'Montenegro',
            'Morocco',
            'Mozambique',
            'Myanmar',
            'Namibia',
            'Nauru',
            'Nepal',
            'Netherlands',
            'New Zealand',
            'Nicaragua',
            'Niger',
            'Nigeria',
            'North Korea',
            'North Macedonia',
            'Norway',
            'Oman',
            'Pakistan',
            'Palau',
            'Palestine State',
            'Panama',
            'Papua New Guinea',
            'Paraguay',
            'Peru',
            'Philippines',
            'Poland',
            'Portugal',
            'Qatar',
            'Romania',
            'Russia',
            'Rwanda',
            'Saint Kitts and Nevis',
            'Saint Lucia',
            'Saint Vincent and the Grenadines',
            'Samoa',
            'San Marino',
            'Sao Tome and Principe',
            'Saudi Arabia',
            'Senegal',
            'Serbia',
            'Seychelles',
            'Sierra Leone',
            'Singapore',
            'Slovakia',
            'Slovenia',
            'Solomon Islands',
            'Somalia',
            'South Africa',
            'South Korea',
            'South Sudan',
            'Spain',
            'Sri Lanka',
            'Sudan',
            'Suriname',
            'Sweden',
            'Switzerland',
            'Syria',
            'Tajikistan',
            'Tanzania',
            'Thailand',
            'Timor-Leste',
            'Togo',
            'Tonga',
            'Trinidad and Tobago',
            'Tunisia',
            'Turkey',
            'Turkmenistan',
            'Tuvalu',
            'Uganda',
            'Ukraine',
            'United Arab Emirates',
            'United Kingdom',
            'United States',
            'Uruguay',
            'Uzbekistan',
            'Vanuatu',
            'Venezuela',
            'Vietnam',
            'Yemen',
            'Zambia',
            'Zimbabwe',
        ];

        // Remove South Africa from the array
        $countries = array_diff($countries, ['South Africa']);

        // Sort the remaining countries alphabetically
        sort($countries);

        // Add South Africa at the beginning
        array_unshift($countries, 'South Africa');
    @endphp

    {{-- Booking FORM (only data collection - we won't submit to go to payment) --}}
    <form id="bookingForm" method="POST" action="{{ route('bookings.store') }}">
        @csrf
        <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
        <input type="hidden" name="rental_unit" id="inputRentalUnit">
        <input type="hidden" name="rental_quantity" id="inputRentalQuantity">
        <input type="hidden" name="rental_start_date" id="inputRentalStartDate">
        <input type="hidden" name="extra_days" id="inputExtraDays" value="0">
        <input type="hidden" name="total_price" id="inputTotalPrice">
        <input type="hidden" name="booking_id" id="bookingId">

        <!-- Step 1: Multi-Step Booking Modal -->
        <div class="modal fade " id="multiStepBookingModal" tabindex="-1" aria-hidden="true"
            style="height: 90vh; margin-top: 4rem;">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content rounded-4 shadow-lg">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold"><i class="bi bi-calendar-check me-2"></i> Book
                            {{ $vehicle->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <h5 class="mb-3 text-center">Select Rental Duration</h5>
                        <div class="row text-center g-3 text-muted">
                            @if ($vehicle->rental_price_day)
                                <div class="col-md-4">
                                    <div class="option-card p-3 border rounded-4 bg-light h-100" data-type="day"
                                        data-price="{{ $vehicle->rental_price_day }}">
                                        <i class="bi bi-clock display-6" style="color: #CF9B4D"></i>
                                        <h6 class="mt-2">Daily Rental</h6>
                                        <p class="small text-muted mb-1">Perfect for short trips</p>
                                        <div class="text-dark">R{{ number_format($vehicle->rental_price_day) }}/day
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if ($vehicle->rental_price_week)
                                <div class="col-md-4">
                                    <div class="option-card p-3 border rounded-4 h-100" data-type="week"
                                        data-price="{{ $vehicle->rental_price_week }}">
                                        <i class="bi bi-calendar-event display-6" style="color: #CF9B4D"></i>
                                        <h6 class="mt-2">Weekly Rental</h6>
                                        <p class="small text-muted mb-1">Great for 1-4 weeks</p>
                                        <div class="text-dark">R{{ number_format($vehicle->rental_price_week) }}/week
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if ($vehicle->rental_price_month)
                                <div class="col-md-4">
                                    <div class="option-card p-3 border rounded-4 h-100" data-type="month"
                                        data-price="{{ $vehicle->rental_price_month }}">
                                        <i class="bi bi-box display-6" style="color: #CF9B4D"></i>
                                        <h6 class="mt-2">Monthly Rental</h6>
                                        <p class="small text-muted mb-1">Best for long stays</p>
                                        <div class="text-dark">
                                            R{{ number_format($vehicle->rental_price_month) }}/month
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <hr class="my-4">

                        <div id="dateSection" class="mb-3 d-none">
                            <div class="position-relative">
                                <input type="text" id="rentalStartDate" class="form-control ps-5"
                                    placeholder="Select a start date" readonly>
                                <span class="position-absolute top-50 start-0 translate-middle-y ps-3">
                                    <i class="bi bi-calendar-event"></i>
                                </span>
                            </div>
                        </div>

                        <!-- Quantity -->
                        <div class="mb-3 d-none" id="quantitySection">
                            <label for="rentalQuantity" class="form-label" id="quantityLabel"></label>
                            <select id="rentalQuantity" class="form-select rounded-3"></select>
                        </div>

                        <!-- Total Price -->
                        <div class="alert alert-info fw-bold d-none" id="totalPrice"></div>
                        <!-- Rental Period -->
                        <div class="alert alert-secondary fw-bold d-none" id="rentalPeriod"></div>
                    </div>
                    <div class="modal-footer d-block">
                        <button type="button" id="continueFromStep1" class="btn btn-dark rounded-3 w-100">
                            Continue to Add-Ons
                        </button>
                    </div>

                </div>
            </div>
        </div>
        <!-- Step 2: Add-Ons Modal -->
        <div class="modal fade mt-5" id="addonsStep" tabindex="-1" aria-hidden="true"
            style="height: 90vh; margin-top: 4rem;">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content rounded-4 shadow-lg">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold"><i class="bi bi-box-seam"></i> Select Add-Ons</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @foreach ($addOns as $addOn)
                            @php
                                $blockedRanges = $addonFullyBooked[$addOn->id] ?? [];
                                $availableToday = max((int) ($addOn->available_today ?? $addOn->qty_total), 0);
                                $totalStock = (int) $addOn->qty_total;
                                $availabilityClass = $availableToday > 0 ? 'bg-success' : 'bg-danger';
                                $availabilityText =
                                    $availableToday > 0 ? $availableToday . ' available today' : 'Fully booked today';
                                $blockedPreview = array_slice($blockedRanges, 0, 3);
                                $blockedTextParts = [];
                                foreach ($blockedPreview as $range) {
                                    if (!empty($range['from']) && !empty($range['to'])) {
                                        $blockedTextParts[] =
                                            $range['from'] === $range['to']
                                                ? $range['from']
                                                : $range['from'] . ' to ' . $range['to'];
                                    }
                                }
                                $blockedText = implode(', ', $blockedTextParts);
                                $remainingBlocked = max(count($blockedRanges) - count($blockedPreview), 0);
                            @endphp
                            <div class="addon-card border rounded p-3 mb-3 shadow-sm" data-id="{{ $addOn->id }}"
                                data-name="{{ e($addOn->name) }}" data-total="{{ $totalStock }}"
                                data-available="{{ $availableToday }}" data-blocked='@json($blockedRanges)'
                                style="cursor:pointer;">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="me-3">
                                        <img src="{{ asset($addOn->image_url) }}" alt="{{ $addOn->name }}"
                                            class="rounded border" style="width:60px; height:60px; object-fit:cover;">
                                    </div>
                                    <div class="flex-grow-1 pe-3">
                                        <h5 class="fw-semibold mb-1">{{ $addOn->name }}</h5>
                                        <p class="text-muted mb-1">{{ $addOn->description }}</p>
                                        <hr class="text-black">
                                        <p class="small text-bold mb-0">
                                            R{{ number_format($addOn->price_day, 2) }}/day /
                                            R{{ number_format($addOn->price_week, 2) }}/week /
                                            R{{ number_format($addOn->price_month, 2) }}/month
                                        </p>
                                    </div>
                                    <div class="text-end">
                                        <span
                                            class="badge availability-badge {{ $availabilityClass }} mb-2">{{ $availabilityText }}</span>
                                        <small class="text-muted d-block">Total stock: {{ $totalStock }}</small>
                                    </div>
                                </div>

                                <div class="addon-details mt-3 d-none border-top pt-3">
                                    @if ($blockedText)
                                        <div class="alert alert-warning small py-2 px-3 addon-unavailable-dates mb-3">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                            <span class="blocked-text">{{ $blockedText }}</span>
                                            @if ($remainingBlocked > 0)
                                                <span class="text-muted">+ {{ $remainingBlocked }} more
                                                    period{{ $remainingBlocked > 1 ? 's' : '' }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <div
                                            class="alert alert-warning small py-2 px-3 addon-unavailable-dates mb-3 d-none">
                                        </div>
                                    @endif

                                    <div class="row g-2 mb-2">
                                        <div class="col-sm-4">
                                            <div class="card text-center shadow-sm h-100 addon-type-card"
                                                data-type="day" data-price="{{ $addOn->price_day }}">
                                                <div
                                                    class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                                                    <!-- Icon (center & top) -->
                                                    <div class="addon-type-icon mb-2">
                                                        <i class="bi bi-clock display-6" style="color: #CF9B4D"></i>
                                                        <!-- Or: <i class="bi bi-sun"></i> -->
                                                    </div>

                                                    <h6 class="card-title mb-1">Daily</h6>
                                                    <p class="card-text fw-bold text-primary mb-0">
                                                        R{{ number_format($addOn->price_day, 2) }}
                                                    </p>
                                                </div>

                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="card text-center shadow-sm h-100 addon-type-card"
                                                data-type="week" data-price="{{ $addOn->price_week }}">
                                                <div class="card-body">

                                                    <div class="addon-type-icon mb-2">
                                                        <i class="bi bi-calendar-event display-6"
                                                            style="color: #CF9B4D"></i>
                                                        <!-- Or: <i class="bi bi-sun"></i> -->
                                                    </div>
                                                    <h6 class="card-title">Weekly</h6>
                                                    <p class="card-text fw-bold text-primary">
                                                        R{{ number_format($addOn->price_week, 2) }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="card text-center shadow-sm h-100 addon-type-card"
                                                data-type="month" data-price="{{ $addOn->price_month }}">
                                                <div class="card-body">

                                                    <div class="addon-type-icon mb-2">
                                                        <i class="bi bi-box display-6" style="color: #CF9B4D"></i>
                                                        <!-- Or: <i class="bi bi-sun"></i> -->
                                                    </div>


                                                    <h6 class="card-title">Monthly</h6>
                                                    <p class="card-text fw-bold text-primary">
                                                        R{{ number_format($addOn->price_month, 2) }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-2 align-items-end">
                                        <div class="col-sm-7">
                                            <label class="form-label small mb-1">Quantity <span class="text-danger">*</span></label>
                                            <select class="form-select form-select-sm addon-qty"
                                                data-id="{{ $addOn->id }}"
                                                @if ($totalStock <= 0) disabled @endif required>
                                                @for ($i = 0; $i <= $addOn->qty_total; $i++)
                                                    <option value="{{ $i }}">{{ $i }}
                                                    </option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div class="col-sm-5 mt-3 mt-sm-0 text-sm-end">
                                            {{-- <span class="form-label small mb-1 d-block">Live Price</span> --}}
                                            <div class="fw-bold text-primary" id="addon-price-{{ $addOn->id }}">
                                                R0.00
                                            </div>
                                        </div>
                                    </div>

                                    <input type="hidden" name="add_ons[{{ $addOn->id }}][type]"
                                        id="addon-type-{{ $addOn->id }}">
                                    <input type="hidden" name="add_ons[{{ $addOn->id }}][quantity]"
                                        id="addon-quantity-{{ $addOn->id }}">
                                    <input type="hidden" name="add_ons[{{ $addOn->id }}][start_date]"
                                        id="addon-start-{{ $addOn->id }}">
                                    <input type="hidden" name="add_ons[{{ $addOn->id }}][end_date]"
                                        id="addon-end-{{ $addOn->id }}">
                                    <input type="hidden" name="add_ons[{{ $addOn->id }}][extra_days]"
                                        id="addon-extra-{{ $addOn->id }}" value="0">
                                    <input type="hidden" name="add_ons[{{ $addOn->id }}][total]"
                                        id="addon-total-{{ $addOn->id }}">
                                    <input type="hidden" name="add_ons[{{ $addOn->id }}][days]"
                                        id="addon-days-{{ $addOn->id }}" value="0">

                                    <div class="small text-muted mt-2" id="addon-period-{{ $addOn->id }}">
                                    </div>

                                    <div class="addon-live-summary d-none">
                                        <div class="alert alert-info border-0 rounded-3 py-3 px-3 mb-3">
                                            <div class="fw-semibold mb-1">
                                                <span class="als-line-1-label">Days</span>:
                                                <span class="als-qty">0</span>
                                                Ãƒâ€” <span class="als-unit">R0.00</span>
                                            </div>
                                            <div class="fw-semibold">
                                                Total Cost: <span class="als-total">R0.00</span>
                                            </div>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="addon-date-pill bg-light border rounded-3 p-3 h-100">
                                                    <div class="text-muted fw-semibold mb-1">Start Date</div>
                                                    <div class="als-start">Ã¢â‚¬â€</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="addon-date-pill bg-light border rounded-3 p-3 h-100">
                                                    <div class="text-muted fw-semibold mb-1">End Date</div>
                                                    <div class="als-end">Ã¢â‚¬â€</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="modal-footer">
                        <div class="d-flex justify-content-between w-100">
                            <button type="button" class="btn btn-outline-secondary" id="backToStep1">
                                Back
                            </button>
                            <button type="button" class="btn btn-dark rounded-3" id="addonsToCustomer">
                                Continue to Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Step 3: Customer Details Modal -->
        <div class="modal fade" id="customerStep" tabindex="-1" aria-hidden="true" style="margin-bottom: 10rem">
            <div class="modal-dialog modal-md modal-dialog-centered">
                <div class="modal-content rounded-4 shadow">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold"><i class="bi bi-person-circle me-2"></i>Enter Your Details
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>




                    <!-- Body -->
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
                                <label class="form-label">Country</label>
                                <select name="country" class="form-select rounded-3" required>
                                    <option value="" disabled selected>Select your country</option>
                                    @foreach ($countries as $country)
                                        <option value="{{ $country }}">{{ $country }}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="modal-footer border-0 d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary rounded-3" id="customerBackToAddons">
                            Back
                        </button>
                        <button type="button" id="goToSummary" class="btn btn-dark rounded-3 px-4">
                            Review Booking
                        </button>
                    </div>

                </div>
            </div>
        </div>


        <!-- Step 4: Booking Summary -->
        <div class="modal fade mt-5" id="summaryStep" tabindex="-1" aria-hidden="true"
            style="height: 90vh; margin-top: 4rem;">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content rounded-4 shadow-lg">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold"><i class="bi bi-clipboard-check me-2"></i> Booking Summary
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <h4 class="fw-bold mb-3 text-center">Review Your Booking</h4>
                        <p class="text-center text-muted">Please review your booking details before proceeding to
                            payment</p>

                        <div class="border rounded p-3 mb-3 bg-light">
                            <h6 class="fw-semibold">Vehicle</h6>
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ $vehicle->mainImage() }}" class="rounded"
                                    style="width:80px; height:80px; object-fit:cover;">
                                <div>
                                    <p class="fw-bold mb-1">{{ $vehicle->name }}</p>
                                    <p class="text-muted small">{{ $vehicle->description ?? '' }}</p>
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
                            </div>
                        </div>

                        <div class="border rounded p-3 mb-3 bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-semibold mb-0">Price Breakdown</h6>
                            </div>
                            <div class="d-flex justify-content-between small mb-1">
                                <span>Vehicle rental</span>
                                <span id="summaryVehicleTotal">R0.00</span>
                            </div>
                            <div class="d-flex justify-content-between small mb-2">
                                <span>Add-ons</span>
                                <span id="summaryAddonTotal">R0.00</span>
                            </div>
                            <div class="d-flex justify-content-between fw-semibold border-top pt-2">
                                <span>Grand total</span>
                                <span class="text-success" id="summaryGrandTotal">R0.00</span>
                            </div>
                        </div>

                        <div class="border rounded p-3 mb-3 bg-light">
                            <h6 class="fw-semibold">Add-Ons</h6>
                            <div id="summaryAddOnList" class="small text-muted">No add-ons selected.</div>
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
                                    <p class="small text-muted mb-1">Country</p>
                                    <p class="fw-bold" id="summaryCustomerCountry"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="d-flex justify-content-between w-100">
                            <button type="button" class="btn btn-outline-secondary" id="summaryBackToCustomer">Back</button>

                            {{-- IMPORTANT: do not submit here. We will create booking via AJAX and then open payment
                                    --}}
                            <button type="button" id="openPayment" class="btn btn-dark rounded-3">
                                Continue to Payment
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </form> {{-- CLOSE booking form here --}}



    @php
        use App\Models\SystemSetting;
        use App\Models\StripeSetting;
        use Illuminate\Support\Facades\Cache;

        if (app()->environment('local')) {
            $settings =
                SystemSetting::first() ?:
                new SystemSetting([
                    'stripe_enabled' => false,
                    'payfast_enabled' => false,
                ]);
        } else {
            $settings = Cache::remember('payments.settings', 60, function () {
                return SystemSetting::first() ?:
                    new SystemSetting([
                        'stripe_enabled' => false,
                        'payfast_enabled' => false,
                    ]);
            });
        }

        $stripeConfig = StripeSetting::first();

        // Count enabled payment methods
        $enabledCount = ($settings->stripe_enabled ? 1 : 0) + ($settings->payfast_enabled ? 1 : 0);
    @endphp

    <div class="modal fade" id="bookingPayment" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 shadow">

                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-credit-card-fill me-2"></i> Select Payment Method
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- Body -->
                <div class="modal-body">
                    <div class="row g-3 align-items-stretch justify-content-center">

                        @if ($settings->stripe_enabled)
                            <!-- Stripe -->
                            <div class="col-12 {{ $enabledCount === 2 ? 'col-md-6' : 'col-md-12' }}">
                                <input type="radio" name="booking_payment_method" id="bookingStripe"
                                    value="stripe" class="btn-check" autocomplete="off" required>
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
                            <!-- PayFast -->
                            <div class="col-12 {{ $enabledCount === 2 ? 'col-md-6' : 'col-md-12' }}">
                                <input type="radio" name="booking_payment_method" id="bookingPayfast"
                                    value="payfast" class="btn-check" autocomplete="off" required>
                                <label for="bookingPayfast" class="card btn w-100 booking-pay-option p-3 flex-column">
                                    <div class="text-center mb-2">
                                        <img src="{{ asset('images/payfast.png') }}" class="rounded-3"
                                            alt="PayFast" style="width: 80px;">
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
                                <div class="alert alert-warning text-center mb-0">
                                    No payment methods are currently available.
                                </div>
                            </div>
                        @endif

                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" id="paymentBackToSummary">
                        Back
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- Step 5b: Stripe Card -->
    <div class="modal fade" id="bookingStripeModal" tabindex="-1" aria-hidden="true"
        style="margin-top: 4rem; height:90vh;">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content rounded-4 shadow">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-credit-card-fill me-2"></i> Stripe Payment
                    </h5>
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
                    <button type="button" class="btn btn-outline-secondary me-auto" id="stripeBackToPayment">
                        Back
                    </button>
                    <button type="button" id="bookingStripePayButton" class="btn btn-dark">
                        Pay with Stripe
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 6: Thank You -->
    <div class="modal fade" id="bookingThankYou" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content rounded-4 shadow border-0">
                <div class="modal-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-success-subtle rounded-circle d-inline-flex align-items-center justify-content-center"
                            style="width:48px;height:48px;">
                            <i class="bi bi-check-lg text-success fs-4"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-1 text-success">Payment received</h4>
                            <p class="text-muted mb-0">Thanks! We'll contact you soon with next steps.</p>
                        </div>
                    </div>

                    <!-- Amount & Payment Info -->
                    <div class="border rounded-3 p-3 mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Amount paid</span>
                            <span class="fw-semibold" id="tyAmount">R0.00</span>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span>Payment method</span>
                            <span id="tyMethod">Ã¢â‚¬â€</span>
                        </div>
                    </div>

                    <!-- Customer Info -->
                    <div class="border rounded-3 p-3 mb-3 bg-light">
                        <div class="small text-muted mb-1">Booked by</div>
                        <div class="fw-semibold" id="tyCustomerName">Ã¢â‚¬â€</div>
                        <div class="small text-muted" id="tyCustomerContact">Ã¢â‚¬â€</div>
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div class="modal-footer border-0 pt-0 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-3" id="tyGoHome"
                        onclick="window.location.href='{{ url('/') }}'">
                        Go to Home
                    </button>

                    <a href="https://wa.me/27612345678?text=Hi! I just completed my booking (Reference: %23) and need assistance."
                        class="btn btn-success fw-bold rounded-3 d-flex align-items-center gap-2" target="_blank"
                        id="whatsappButton">
                        <i class="bi bi-whatsapp fs-5"></i>
                        Chat with Us
                    </a>
                </div>
            </div>
        </div>
    </div>




    <style>
        /* Scope to the booking modal by ID to avoid clashes */
        #bookingPayment .booking-pay-option {
            min-height: 160px;
            /* tweak height to taste */
            display: flex;
            align-items: center;
            justify-content: center;
            /* center icon + text as a block */
            gap: 12px;
            /* space between icon and text */
            border: 1px solid #dee2e6;
            border-radius: .75rem;
            padding: 20px;
            text-align: left;
            transition: box-shadow .15s ease, transform .15s ease, border-color .15s ease;
        }

        .addon-details .row.g-2.mb-2 {
            display: none !important;
        }

        .addon-live-summary,
        [id^="addon-period-"] {
            display: none !important;
        }

        [id^="addon-price-"] {
            margin-top: 0.75rem;
            display: inline-block;
        }

        [id^="addon-price-"]::before {
            content: "Live price";
            display: block;
            font-size: 0.875rem;
            color: #6c757d;
            margin-bottom: 0.25rem;
        }

        .addon-card.addon-selected {
            border-color: var(--bs-primary);
            box-shadow: 0 .5rem 1.25rem rgba(13, 110, 253, .20);
            background-color: rgba(13, 110, 253, .05);
        }

        .addon-card.addon-selected .availability-badge {
            background-color: var(--bs-primary) !important;
        }

        #bookingPayment .booking-pay-option:hover {
            transform: translateY(-2px);
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .08);
        }

        /* Selected state when radio is checked */
        #bookingPayment .btn-check:checked+.booking-pay-option {
            border-color: #0d6efd;
            box-shadow: 0 0 0 .25rem rgba(13, 110, 253, .2);
        }

        .addon-type-card.addon-selected {
            border: 2px solid var(--bs-primary) !important;
            box-shadow: 0 .5rem 1rem rgba(13, 110, 253, .15) !important;
        }

        /* Icon container */
        #bookingPayment .booking-icon-wrap {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            flex: 0 0 56px;
        }

        /* Selected plan card = light warning look */
        .addon-type-card.active {
            background: var(--bs-warning-bg-subtle, #fff3cd) !important;
            border: 1.5px solid var(--bs-warning, #ffc107) !important;
            box-shadow: 0 .35rem .8rem rgba(255, 193, 7, .18);
        }

        /* Live summary styling (screenshot look) */
        .addon-live-summary .alert-info {
            background: #def7ff;
        }

        .addon-date-pill {
            background: #e9eaed;
        }


        #bookingPayment .booking-icon-wrap i,
        #bookingPayment .booking-icon-wrap img {
            width: 28px;
            height: 28px;
            object-fit: contain;
        }

        /* Text block next to icon */
        #bookingPayment .booking-pay-text {
            display: flex;
            flex-direction: column;
        }

        .modal {
            --bs-backdrop-bg: #000;
            /* backdrop color */
            --bs-backdrop-opacity: .18;
            /* 0 = none, .5 = default; try .12–.22 */
        }

        .modal-backdrop.show {
            backdrop-filter: blur(2px);
            /* optional: soft blur behind */
        }

        .addon-details .small.text-muted.mt-2[id^="addon-period-"] {
            display: none !important;
        }



        /* Make it a single row (50/50) on md+, stack on xs for usability */
        @media (min-width: 768px) {
            #bookingPayment .col-md-6 {
                display: flex;
            }

            #bookingPayment .booking-pay-option {
                width: 100%;
            }
        }
    </style>
    <script src="https://js.stripe.com/v3/"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- ADD-ONS + SUMMARY (keeps SweetAlerts + overlap checks) -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  /* =========================
     GLOBAL MODAL STACKING
     ========================= */
  const Z_BASE = 1055; // Bootstrap modal z-index baseline
  const Z_STEP = 20;   // Step per stacked modal

  function visibleModals() {
    return Array.from(document.querySelectorAll('.modal.show'));
  }

  function ensureSingleBackdrop() {
    const backs = Array.from(document.querySelectorAll('.modal-backdrop'));
    // Keep only the last (topmost) backdrop if multiple exist
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
    setTimeout(() => { ensureSingleBackdrop(); restack(); }, 0);
  });
  document.addEventListener('shown.bs.modal', () => setTimeout(() => { ensureSingleBackdrop(); restack(); }, 0));
  document.addEventListener('hidden.bs.modal', () => setTimeout(() => { ensureSingleBackdrop(); restack(); }, 0));

  // Helper: get or create Bootstrap modal instance
  const getModalInstance = (id) => {
    const el = document.getElementById(id);
    return el ? bootstrap.Modal.getOrCreateInstance(el) : null;
  };

  // Safe modal swap
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
      if (!fromModal) { showNext(); return; }
      const onHidden = () => { fromEl.removeEventListener('hidden.bs.modal', onHidden); showNext(); };
      fromEl.addEventListener('hidden.bs.modal', onHidden, { once: true });
      fromModal.hide();
    } else {
      showNext();
    }
  };

  // Optional generic swap triggers
  document.addEventListener('click', (e) => {
    const swapper = e.target.closest('.modal-swap[data-target]');
    if (!swapper) return;
    e.preventDefault();
    const nextSel = swapper.getAttribute('data-target');
    const current = e.target.closest('.modal');
    const showNext = () => {
      const next = document.querySelector(nextSel);
      if (next) (bootstrap.Modal.getInstance(next) || new bootstrap.Modal(next)).show();
    };
    if (current && current.classList.contains('show')) {
      current.addEventListener('hidden.bs.modal', function onH() {
        current.removeEventListener('hidden.bs.modal', onH);
        showNext();
      }, { once: true });
      (bootstrap.Modal.getInstance(current) || new bootstrap.Modal(current)).hide();
    } else {
      showNext();
    }
  });

  /* =========================
     STEP-1 HARD LOCK (Fix)
     ========================= */
  const bookingForm = document.getElementById('bookingForm');
  const step1Modal  = document.getElementById('multiStepBookingModal');
  const startDateInput = document.getElementById('rentalStartDate');

  // We never submit the form implicitly. All submits are via AJAX later.
  if (bookingForm) {
    bookingForm.addEventListener('submit', (e) => {
      // Prevent ANY default submit from Step-1 or other accidental triggers
      e.preventDefault();
    });

    // Block Enter while Step-1 is open (date inputs often trigger submit on Enter)
    bookingForm.addEventListener('keydown', (e) => {
      const step1Open = step1Modal?.classList.contains('show');
      if (step1Open && e.key === 'Enter') {
        e.preventDefault();
        e.stopPropagation();
      }
    });
  }

  // Date input: stop bubbling that can cause submit/advance
  if (startDateInput) {
    ['change','input','keydown','keypress'].forEach(evt => {
      startDateInput.addEventListener(evt, (e) => {
        const step1Open = step1Modal?.classList.contains('show');
        if (!step1Open) return;

        // Never allow Enter to submit in date field
        if ((evt === 'keydown' || evt === 'keypress') && e.key === 'Enter') {
          e.preventDefault();
          e.stopPropagation();
        } else {
          // Some datepickers bubble change/close → stop it
          e.stopPropagation();
        }
      });
    });
  }

  // Only this button can advance Step-1 → Step-2
  const step1NextBtn = document.getElementById('continueFromStep1');
  if (step1NextBtn) {
    step1NextBtn.addEventListener('click', (event) => {
      event.preventDefault();

      // Minimal validation to ensure user actually selected plan/date
      const unit = document.getElementById('inputRentalUnit')?.value;
      const qty  = parseInt(document.getElementById('inputRentalQuantity')?.value || '0', 10);
      const start= document.getElementById('inputRentalStartDate')?.value;

      if (!unit || !qty || !start) {
        if (window.Swal?.fire) {
          Swal.fire({ icon:'error', title:'Missing info', text:'Please select duration, quantity, and start date.' });
        }
        return;
      }

      swapModal('multiStepBookingModal', 'addonsStep');
    });
  }

  // Back & forward controls between modals
  const addonsBackBtn  = document.getElementById('backToStep1');
  const addonsNextBtn  = document.getElementById('addonsToCustomer');
  const customerBackBtn= document.getElementById('customerBackToAddons');
  const summaryBackBtn = document.getElementById('summaryBackToCustomer');
  const paymentBackBtn = document.getElementById('paymentBackToSummary');
  const stripeBackBtn  = document.getElementById('stripeBackToPayment');

  addonsBackBtn?.addEventListener('click', (e) => { e.preventDefault(); swapModal('addonsStep','multiStepBookingModal'); });
  addonsNextBtn?.addEventListener('click', (e) => { e.preventDefault(); swapModal('addonsStep','customerStep'); });
  customerBackBtn?.addEventListener('click', (e) => { e.preventDefault(); swapModal('customerStep','addonsStep'); });
  summaryBackBtn?.addEventListener('click', (e) => { e.preventDefault(); swapModal('summaryStep','customerStep'); });
  paymentBackBtn?.addEventListener('click', (e) => { e.preventDefault(); resetPaymentSelection(); swapModal('bookingPayment','summaryStep'); });
  stripeBackBtn?.addEventListener('click',  (e) => { e.preventDefault(); resetPaymentSelection(); swapModal('bookingStripeModal','bookingPayment'); });

  /* =========================
     DATE / RENTAL HELPERS
     ========================= */
  const DAY_MS = 86400000;
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
    return dt ? dt.toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' }) : ymd;
  };
  const addDays = (date, amount) => {
    const t = new Date(date.getFullYear(), date.getMonth(), date.getDate());
    t.setDate(t.getDate() + amount);
    return t;
  };
  const diffDaysIncl = (a, b) => (!a || !b) ? 0 : Math.floor((fromYMD(b) - fromYMD(a)) / DAY_MS) + 1;
  const unitDays = (u) => (u === 'week' ? 7 : u === 'month' ? 30 : 1);
  const snapEndForUnit = (start, unit) => {
    if (!start) return null;
    const base = new Date(start.getFullYear(), start.getMonth(), start.getDate());
    if (unit === 'day')  return base;
    if (unit === 'week') return addDays(base, 6);
    if (unit === 'month')return addDays(base, 29);
    return base;
  };
  const money = (v) => `R${Number(v || 0).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2})}`;

  function clearSelect(sel) { while (sel.options.length) sel.remove(0); }
  function fillSelect(sel, from, to, value) {
    clearSelect(sel);
    for (let i = from; i <= to; i++) {
      const o = document.createElement('option'); o.value = String(i); o.textContent = String(i);
      sel.appendChild(o);
    }
    sel.value = String(value);
  }
  function ensureOpt(sel, v) {
    const vs = String(v);
    for (const o of sel.options) if (o.value === vs) return;
    const o = document.createElement('option'); o.value = vs; o.textContent = vs; sel.appendChild(o);
  }

  function getRentalContext() {
    const unitEl  = document.getElementById('inputRentalUnit');
    const qtyEl   = document.getElementById('inputRentalQuantity');
    const startEl = document.getElementById('inputRentalStartDate');
    const extraEl = document.getElementById('inputExtraDays');

    const unit = (unitEl?.value || '').toLowerCase();
    const quantity = parseInt(qtyEl?.value || '0', 10);
    const extraDays = Math.max(0, parseInt(extraEl?.value || '0', 10));
    const startYMD = startEl?.value || '';
    const startDate = startYMD ? fromYMD(startYMD) : null;

    let totalDays = 0;
    if (quantity > 0) {
      if (unit === 'week')   totalDays = (quantity * 7)  + extraDays;
      else if (unit === 'month') totalDays = (quantity * 30) + extraDays;
      else totalDays = quantity + extraDays;
    }
    const endDate = (startDate && totalDays > 0) ? addDays(startDate, totalDays - 1) : null;

    return {
      unit,
      quantity,
      extraDays,
      startDate,
      endDate,
      startYMD: startDate ? toYMD(startDate) : '',
      endYMD: endDate ? toYMD(endDate) : '',
      totalDays: totalDays > 0 ? totalDays : 0
    };
  }

  /* =========================
     SWEETALERT HELPER
     ========================= */
  const alertDebounce = new Map();
  function notify(key, { icon = 'warning', title = 'Notice', text = '' }) {
    const now = Date.now(), last = alertDebounce.get(key) || 0;
    if (now - last < 600) return;
    alertDebounce.set(key, now);
    if (window.Swal?.fire) Swal.fire({ icon, title, text, confirmButtonText:'OK' });
    else alert(`${title}\n\n${text}`);
  }

  /* =========================
     ADD-ONS LOGIC (unchanged)
     ========================= */
  const inRange = (d, r) => {
    const s = fromYMD(r.from), e = fromYMD(r.to);
    if (!s || !e) return false;
    const t = new Date(d.getFullYear(), d.getMonth(), d.getDate()).getTime();
    return (t >= s.getTime() && t <= e.getTime());
  };
  const nextAvailableFrom = (startDate, ranges) => {
    let cur = new Date(startDate.getFullYear(), startDate.getMonth(), startDate.getDate());
    for (let i = 0; i < 365; i++) {
      const bad = ranges.some(r => inRange(cur, r));
      if (!bad) return cur;
      cur = addDays(cur, 1);
    }
    return null;
  };
  function hasOverlap(startYMD, endYMD, blockedRanges) {
    const s = fromYMD(startYMD), e = fromYMD(endYMD);
    if (!s || !e) return false;
    for (let d = new Date(s.getFullYear(), s.getMonth(), s.getDate()); d <= e; d = addDays(d, 1)) {
      if (blockedRanges.some(r => inRange(d, r))) return true;
    }
    return false;
  }
  function listOverlappingRanges(startYMD, endYMD, blockedRanges) {
    const s = fromYMD(startYMD), e = fromYMD(endYMD), res = [];
    if (!s || !e) return res;
    blockedRanges.forEach(r => {
      const rs = fromYMD(r.from), re = fromYMD(r.to);
      if (!rs || !re) return;
      const start = new Date(Math.max(rs.getTime(), s.getTime()));
      const end   = new Date(Math.min(re.getTime(), e.getTime()));
      if (start <= end) res.push(`${niceDate(toYMD(start))} – ${niceDate(toYMD(end))}`);
    });
    return res;
  }
  function warnIfPlannedRangeOverlaps({ unit, startDate, extraDays = 0, addonId, blockedRanges, availableQty }) {
    if (!startDate) return false;
    const snapEnd = snapEndForUnit(startDate, unit);
    const endDate = addDays(snapEnd, (unit === 'day' ? 0 : extraDays));
    const s = toYMD(startDate), e = toYMD(endDate);
    if (hasOverlap(s, e, blockedRanges)) {
      const where = listOverlappingRanges(s, e, blockedRanges);
      const blockedTxt = where.length ? `Blocked on: ${where.join(', ')}` : 'Some dates are unavailable.';
      notify(`overlap-plan-${addonId}`, { icon:'error', title:'Dates unavailable', text:`${blockedTxt}\nAvailable quantity for this add-on: ${availableQty}.` });
      return true;
    }
    return false;
  }
  function setAddonHiddenDisabled(detailsEl, disabled) {
    detailsEl.querySelectorAll('input[type="hidden"]').forEach(h => h.disabled = !!disabled);
  }
  function computeAddonsTotal() {
    const selections = Object.values(window.bookingAddonSelections || {});
    if (selections.length) {
      const tot = selections.reduce((sum, item) => {
        const v = parseFloat(item?.total || 0);
        return sum + (isNaN(v) ? 0 : v);
      }, 0);
      return Math.round(tot * 100) / 100;
    }
    let addonTotal = 0;
    document.querySelectorAll('.addon-card').forEach(card => {
      const id = card.dataset.id;
      const totalH = card.querySelector(`#addon-total-${id}`);
      if (!totalH || totalH.disabled) return;
      const val = parseFloat(totalH.value || '0');
      if (!isNaN(val) && val > 0) addonTotal += val;
    });
    return Math.round(addonTotal * 100) / 100;
  }
  function updateSummaryAddonTotals() {
    const total = computeAddonsTotal();
    const el = document.getElementById('summaryAddonTotal');
    if (el) el.textContent = money(total);
    return total;
  }
  function enableSelectedAddonHiddenFields() {
    document.querySelectorAll('.addon-details').forEach(details => {
      const totalH = details.querySelector('input[id^="addon-total-"]');
      const hiddens = details.querySelectorAll('input[type="hidden"]');
      const total = parseFloat(totalH?.value || '0');
      hiddens.forEach(h => { h.disabled = !(total > 0); });
    });
  }
  window.computeAddonsTotal = computeAddonsTotal;
  window.enableSelectedAddonHiddenFields = enableSelectedAddonHiddenFields;

  const addonSelections = {};
  window.bookingAddonSelections = addonSelections;

  // Add-on cards init (same behavior as your code; trimmed comments)
  document.querySelectorAll('.addon-card').forEach(card => {
    const addonId = card.dataset.id;
    const totalStock = parseInt(card.dataset.total || '0', 10);
    const availToday = parseInt(card.dataset.available || '0', 10);

    const details = card.querySelector('.addon-details');
    const qtySel = details.querySelector('.addon-qty');
    const planCards = details.querySelectorAll('.addon-type-card');
    const badgeEl = card.querySelector('.availability-badge');

    // Hidden fields
    const typeH  = details.querySelector(`#addon-type-${addonId}`);
    const qtyH   = details.querySelector(`#addon-quantity-${addonId}`);
    const startH = details.querySelector(`#addon-start-${addonId}`);
    const endH   = details.querySelector(`#addon-end-${addonId}`);
    const extraH = details.querySelector(`#addon-extra-${addonId}`);
    const totalH = details.querySelector(`#addon-total-${addonId}`);
    const daysH  = details.querySelector(`#addon-days-${addonId}`);

    const priceEl  = details.querySelector(`#addon-price-${addonId}`);
    const periodEl = details.querySelector(`#addon-period-${addonId}`);

    const live = details.querySelector('.addon-live-summary');
    const alsQty = live ? live.querySelector('.als-qty') : null;
    const alsUnit = live ? live.querySelector('.als-unit') : null;
    const alsTotal = live ? live.querySelector('.als-total') : null;
    const alsStart = live ? live.querySelector('.als-start') : null;
    const alsEnd   = live ? live.querySelector('.als-end') : null;
    const alsLbl   = live ? live.querySelector('.als-line-1-label') : null;

    let blockedRanges = [];
    try { blockedRanges = JSON.parse(card.dataset.blocked || '[]') || []; } catch { blockedRanges = []; }
    blockedRanges = blockedRanges.filter(r => r && r.from && r.to);

    setAddonHiddenDisabled(details, true);
    let currentCtx = null;

    const rangeOverlapsBlocked = (startYMD, endYMD) => hasOverlap(startYMD, endYMD, blockedRanges);

    function setBadge(text, ok) {
      if (!badgeEl) return;
      badgeEl.textContent = text;
      badgeEl.classList.remove('bg-success', 'bg-danger');
      badgeEl.classList.add(ok ? 'bg-success' : 'bg-danger');
    }

    function clampQtyTo(avail, startYMD = null, endYMD = null) {
      const prev = parseInt(qtySel.value || '0', 10);
      const newMax = Math.max(0, avail);
      const newVal = Math.min(prev, newMax);

      fillSelect(qtySel, 0, newMax, newVal);
      qtySel.disabled = (newMax <= 0);

      if (startYMD && endYMD) {
        const where = listOverlappingRanges(startYMD, endYMD, blockedRanges);
        const blockedTxt = where.length ? `Blocked on: ${where.join(', ')}` : null;

        if (newMax <= 0) {
          notify(`noqty-${addonId}-${startYMD}-${endYMD}`, {
            icon:'warning', title:'Out of stock',
            text:`${blockedTxt ? blockedTxt + '\n' : ''}Available quantity for these dates: 0.`
          });
        } else if (prev > newVal) {
          notify(`clamped-${addonId}-${startYMD}-${endYMD}`, {
            icon:'warning', title:'Limited availability',
            text:`${blockedTxt ? blockedTxt + '\n' : ''}Only ${newMax} unit${newMax > 1 ? 's' : ''} available.`
          });
        }
      }
    }

    function updateAvailabilityForDates(startYMD, endYMD) {
      if (startYMD && endYMD) {
        if (rangeOverlapsBlocked(startYMD, endYMD)) {
          setBadge('Fully booked for these dates', false);
          clampQtyTo(0, startYMD, endYMD);
          qtyH.value = '0';
          const where = listOverlappingRanges(startYMD, endYMD, blockedRanges);
          const blockedTxt = where.length ? `Blocked on: ${where.join(', ')}` : 'Some dates are unavailable.';
          notify(`overlap-${addonId}-${startYMD}-${endYMD}`, { icon:'error', title:'Dates unavailable', text:`${blockedTxt}\nAvailable quantity for this add-on: 0.` });
          return 0;
        } else {
          setBadge(`${totalStock} available for these dates`, true);
          clampQtyTo(totalStock, startYMD, endYMD);
          return totalStock;
        }
      } else {
        setBadge('Select rental Quantity first', false);
        clampQtyTo(Math.max(0, availToday));
        return availToday;
      }
    }

    function ensureActivePlan(ctx) {
      let active = [...planCards].find(c => c.classList.contains('active'));
      const desired = typeH.value || ctx?.unit;
      if (!active && desired) active = [...planCards].find(c => c.dataset.type === desired);
      if (!active) active = planCards[0];
      if (active) {
        planCards.forEach(c => c.classList.remove('active'));
        active.classList.add('active');
        typeH.value = active.dataset.type;
      }
      return active;
    }

    function updatePeriodDisplay(ctx) {
      if (!periodEl) return;
      if (!ctx || !ctx.startYMD || !ctx.endYMD) periodEl.textContent = 'Select your rental dates first.';
      else {
        const days = ctx.totalDays || 0;
        periodEl.textContent = `${niceDate(ctx.startYMD)} -> ${niceDate(ctx.endYMD)} (${days} day${days === 1 ? '' : 's'})`;
      }
    }

    function setPrice(amount) { if (priceEl) priceEl.textContent = money(amount); }

    function removeSelection(clearPeriod) {
      delete addonSelections[addonId];
      totalH.value = '';
      qtyH.value = '0';
      if (qtySel) qtySel.value = '0';
      if (clearPeriod) {
        startH.value = ''; endH.value = ''; daysH.value = '0';
      }
      setAddonHiddenDisabled(details, true);
      setPrice(0);
      card.classList.remove('addon-selected');
      if (live) {
        if (alsQty) alsQty.textContent = '0';
        if (alsUnit) alsUnit.textContent = money(0);
        if (alsTotal) alsTotal.textContent = money(0);
        if (alsStart) alsStart.textContent = '-';
        if (alsEnd)   alsEnd.textContent   = '-';
      }
      if (clearPeriod && periodEl) periodEl.textContent = 'Select your rental dates first.';
      const startY = startH.value || null;
      const endY   = endH.value || null;
      updateAvailabilityForDates(startY, endY);
    }

    function computeAddonUnits(unit, totalDays) {
      if (!totalDays || totalDays <= 0) return 0;
      if (unit === 'week')  return Math.max(1, Math.ceil(totalDays / 7));
      if (unit === 'month') return Math.max(1, Math.ceil(totalDays / 30));
      return Math.max(1, totalDays);
    }

    function updateLiveSummary(unit, ctx, unitPrice, units, quantity, total) {
      if (!live) return;
      const labelMap = { day:'Days', week:'Weeks', month:'Months' };
      const qtyVal = unit === 'day' ? ctx.totalDays : units;
      if (alsLbl)   alsLbl.textContent  = labelMap[unit] || 'Units';
      if (alsQty)   alsQty.textContent  = String(qtyVal);
      if (alsUnit)  alsUnit.textContent = money(unitPrice);
      if (alsTotal) alsTotal.textContent= money(total);
      if (alsStart) alsStart.textContent= ctx.startYMD ? niceDate(ctx.startYMD) : '-';
      if (alsEnd)   alsEnd.textContent  = ctx.endYMD   ? niceDate(ctx.endYMD)   : '-';
      live.classList.remove('d-none');
    }

    function updateTotal() {
      const ctx = currentCtx;
      const activePlan = ensureActivePlan(ctx);
      const unit = (activePlan?.dataset.type || typeH.value || 'day').toLowerCase();
      const unitPrice = parseFloat(activePlan?.dataset.price || '0');
      const quantity  = parseInt(qtySel.value || '0', 10);

      if (!ctx || !ctx.startYMD || !ctx.endYMD || !quantity) {
        removeSelection(true);
        updateSummaryAddonTotals();
        return;
      }

      const units = computeAddonUnits(unit, ctx.totalDays);
      const totalPerAddon = unitPrice * units;
      const total = Number((totalPerAddon * quantity).toFixed(2));

      startH.value = ctx.startYMD;
      endH.value   = ctx.endYMD;
      daysH.value  = String(ctx.totalDays);
      extraH.value = '0';
      totalH.value = total.toFixed(2);
      qtyH.value   = String(quantity);
      setAddonHiddenDisabled(details, false);
      setPrice(total);
      card.classList.add('addon-selected');

      addonSelections[addonId] = {
        id: addonId, name: card.dataset.name || '', type: unit, qty: quantity, unitPrice,
        perUnitTotal: Number((totalPerAddon).toFixed(2)), total,
        start: ctx.startYMD, end: ctx.endYMD, days: ctx.totalDays,
        extraDays: 0, remainderDays: 0, fullUnits: units
      };

      if (badgeEl && quantity > 0) {
        const label = `Selected ${quantity} unit${quantity !== 1 ? 's' : ''}${totalStock ? ` (of ${totalStock})` : ''}`;
        setBadge(label, true);
      }

      updateSummaryAddonTotals();
      updateLiveSummary(unit, ctx, unitPrice, units, quantity, total);
    }

    function applyContext(ctx) {
      currentCtx = (ctx && ctx.totalDays > 0 && ctx.startYMD && ctx.endYMD) ? ctx : null;
      if (!currentCtx) { startH.value = ''; endH.value = ''; daysH.value = '0'; extraH.value = '0'; }
      ensureActivePlan(currentCtx);
      updatePeriodDisplay(currentCtx);
      const available = updateAvailabilityForDates(currentCtx?.startYMD || null, currentCtx?.endYMD || null);
      let currentQty = parseInt(qtySel.value || '0', 10);
      if (available > 0 && (!currentQty || currentQty <= 0)) { qtySel.value = '1'; currentQty = 1; }
      else if (available <= 0) { qtySel.value = '0'; currentQty = 0; }
      else if (currentQty > available) { qtySel.value = String(available); currentQty = available; }
      qtyH.value = String(currentQty);
      updateTotal();
    }

    // Toggle details on card click (but ignore clicks inside details)
    card.addEventListener('click', (e) => {
      if (e.target.closest('.addon-details')) return;
      details.classList.toggle('d-none');
      if (!details.classList.contains('d-none')) applyContext(currentCtx || getRentalContext());
    });

    planCards.forEach(pc => {
      pc.addEventListener('click', (e) => {
        e.stopPropagation();
        planCards.forEach(c => c.classList.remove('active'));
        pc.classList.add('active');
        typeH.value = pc.dataset.type;
        if (!qtySel.value || qtySel.value === '0') { qtySel.value = '1'; qtyH.value = '1'; }
        updateTotal();
      });
    });

    qtySel.addEventListener('change', () => { qtyH.value = qtySel.value || '0'; updateTotal(); });

    // expose for global refresh
    card.__applyRentalContext = applyContext;
  });

  function refreshAllAddons() {
    const ctx = getRentalContext();
    document.querySelectorAll('.addon-card').forEach(card => {
      const applyCtx = card.__applyRentalContext;
      if (typeof applyCtx === 'function') applyCtx(ctx);
    });
    updateSummaryAddonTotals();
  }

  ['inputRentalUnit', 'inputRentalQuantity', 'inputExtraDays', 'inputRentalStartDate'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('change', refreshAllAddons);
  });
  document.addEventListener('rental:updated', refreshAllAddons);
  refreshAllAddons();

  /* =========================
     BOOKING + PAYMENT FLOW
     ========================= */
  const bookingIdField         = document.getElementById('bookingId');
  const openPaymentBtn         = document.getElementById('openPayment');
  const bookingPaymentModalEl  = document.getElementById('bookingPayment');
  const bookingStripeModalEl   = document.getElementById('bookingStripeModal');
  const bookingStripePayButton = document.getElementById('bookingStripePayButton');
  const bookingThankYouModalEl = document.getElementById('bookingThankYou');
  const tyGoHomeNowBtn         = document.getElementById('tyGoHomeNow');
  const bookingCardErrorsEl    = document.getElementById('booking-card-errors');

  const paymentMethodInputs = Array.from(document.querySelectorAll('input[name="booking_payment_method"]'));
  const resetPaymentSelection = () => {
    paymentMethodInputs.forEach((input) => { input.checked = false; input.removeAttribute('checked'); });
  };
  if (bookingPaymentModalEl) bookingPaymentModalEl.addEventListener('hidden.bs.modal', resetPaymentSelection);

  let currentBookingReference = null;

  const stripePublicKey = "{{ $stripeConfig->stripe_key ?? '' }}";
  let stripeInstance = null, stripeElements = null, stripeCardNumber = null, stripeCardExpiry = null, stripeCardCvc = null;

  const showPaymentLoader = (message = 'Processing payment...') => {
    if (window.Swal) {
      Swal.fire({ title: message, text: 'Please wait while we confirm your payment.', allowOutsideClick:false, allowEscapeKey:false, showConfirmButton:false, didOpen:() => Swal.showLoading() });
    }
  };
  const hidePaymentLoader = () => { if (window.Swal && Swal.isVisible()) Swal.close(); };

  const computeGrandTotal = () => {
    const vehicleTotal = parseFloat(document.getElementById('inputTotalPrice')?.value || '0');
    const addonTotal   = window.computeAddonsTotal ? window.computeAddonsTotal() : 0;
    return Math.round((vehicleTotal + addonTotal) * 100) / 100;
  };

  const populateThankYouModal = (methodLabel) => {
    if (!bookingThankYouModalEl) return;
    const tyVehicleNameEl = document.getElementById('tyVehicleName');
    if (tyVehicleNameEl) tyVehicleNameEl.textContent = "{{ addslashes($vehicle->name) }}";

    const periodText = document.getElementById('summaryPeriod')?.textContent?.trim() || '-';
    const tyPeriodEl = document.getElementById('tyPeriod'); if (tyPeriodEl) tyPeriodEl.textContent = periodText;

    const reference = currentBookingReference || (bookingIdField?.value ? `#${bookingIdField.value}` : '-');
    const tyReferenceEl = document.getElementById('tyReference'); if (tyReferenceEl) tyReferenceEl.textContent = reference;

    const tyAmountEl = document.getElementById('tyAmount'); if (tyAmountEl) tyAmountEl.textContent = money(computeGrandTotal());

    const tyMethodEl = document.getElementById('tyMethod'); if (tyMethodEl) tyMethodEl.textContent = methodLabel;

    const tyCustomerNameEl = document.getElementById('tyCustomerName'); if (tyCustomerNameEl) tyCustomerNameEl.textContent = bookingForm?.name?.value || '-';

    const contactParts = [];
    if (bookingForm?.email?.value) contactParts.push(bookingForm.email.value);
    if (bookingForm?.phone?.value) contactParts.push(bookingForm.phone.value);
    const tyCustomerContactEl = document.getElementById('tyCustomerContact');
    if (tyCustomerContactEl) tyCustomerContactEl.textContent = contactParts.join(' - ') || '-';
  };

  if (tyGoHomeNowBtn) tyGoHomeNowBtn.addEventListener('click', () => { window.location.href = "{{ url('/') }}"; });
  if (bookingThankYouModalEl) bookingThankYouModalEl.addEventListener('hidden.bs.modal', () => { window.location.href = "{{ url('/') }}"; });

  if (openPaymentBtn) {
    openPaymentBtn.addEventListener('click', async () => {
      if (typeof window.enableSelectedAddonHiddenFields === 'function') window.enableSelectedAddonHiddenFields();

      if (!bookingIdField?.value && bookingForm) {
        const formData = new FormData(bookingForm);
        try {
          const res = await fetch(bookingForm.action, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' }
          });
          const text = await res.text();
          let data; try { data = JSON.parse(text); } catch { data = { success:false, message:text }; }
          if (!res.ok || !data?.success) {
            await Swal.fire({ icon:'error', title:'Booking not created', text: data?.message || 'Failed to create booking.' });
            return;
          }
          bookingIdField.value = data.booking_id || data.id || '';
          currentBookingReference = data.reference || null;
          if (!bookingIdField.value) {
            await Swal.fire({ icon:'error', title:'Missing booking ID', text:'Booking was created but no identifier was returned.' });
            return;
          }
        } catch (error) {
          console.error(error);
          await Swal.fire({ icon:'error', title:'Network error', text:'Unable to create booking, please try again.' });
          return;
        }
      }

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
        await Swal.fire({ icon:'error', title:'Booking missing', text:'Please create the booking first.' });
        if (bookingPaymentModalEl) new bootstrap.Modal(bookingPaymentModalEl).show();
        e.target.checked = false;
        return;
      }

      try {
        showPaymentLoader('Redirecting to PayFast...');
        const res = await fetch(`/payfast/booking/init/${encodeURIComponent(bookingId)}`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
          body: JSON.stringify({ booking_id: bookingId })
        });
        const data = await res.json();
        if (!res.ok || !data?.success) throw new Error(data?.message || 'Failed to prepare PayFast checkout.');

        const form = document.createElement('form');
        form.method = 'POST'; form.action = data.action; form.style.display = 'none';
        Object.entries(data.fields || {}).forEach(([key, value]) => {
          const input = document.createElement('input');
          input.type = 'hidden'; input.name = key; input.value = value;
          form.appendChild(input);
        });
        document.body.appendChild(form);
        form.submit();
      } catch (err) {
        console.error(err);
        await Swal.fire({ icon:'error', title:'PayFast error', text: err.message || 'Could not redirect to PayFast.' });
        if (bookingPaymentModalEl) new bootstrap.Modal(bookingPaymentModalEl).show();
        e.target.checked = false;
      } finally {
        hidePaymentLoader();
      }
    }
  });

  // Stripe mount
  if (typeof Stripe !== 'undefined' && stripePublicKey) {
    stripeInstance = Stripe(stripePublicKey);
    stripeElements = stripeInstance.elements();
    const stripeStyle = { base: { fontSize:'16px', color:'#32325d', '::placeholder':{ color:'#a0aec0' } } };
    stripeCardNumber = stripeElements.create('cardNumber', { style: stripeStyle });
    stripeCardExpiry = stripeElements.create('cardExpiry', { style: stripeStyle });
    stripeCardCvc    = stripeElements.create('cardCvc',    { style: stripeStyle });

    const cardNumberMount = document.getElementById('booking-card-number');
    const cardExpiryMount = document.getElementById('booking-card-expiry');
    const cardCvcMount    = document.getElementById('booking-card-cvc');

    if (cardNumberMount) stripeCardNumber.mount(cardNumberMount);
    if (cardExpiryMount) stripeCardExpiry.mount(cardExpiryMount);
    if (cardCvcMount)    stripeCardCvc.mount(cardCvcMount);
  } else if (stripePublicKey) {
    console.warn('Stripe.js not loaded or public key missing.');
  }

  if (bookingStripePayButton && !stripeInstance) bookingStripePayButton.disabled = true;

  if (bookingStripePayButton && stripeInstance) {
    bookingStripePayButton.addEventListener('click', async function() {
      if (!bookingIdField?.value) {
        Swal.fire({ icon:'error', title:'Booking missing', text:'Please create the booking first.' });
        return;
      }
      if (!stripeCardNumber || !stripeCardExpiry || !stripeCardCvc) {
        Swal.fire({ icon:'error', title:'Stripe unavailable', text:'Payment form is not ready yet.' });
        return;
      }

      if (bookingCardErrorsEl) bookingCardErrorsEl.textContent = '';

      const button = this;
      const originalText = button.textContent;
      button.disabled = true; button.textContent = 'Processing...';
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
          headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
          body: JSON.stringify({ payment_method_id: paymentMethod.id, amount: parseFloat(button.dataset.amount || '0') })
        });

        const text = await res.text();
        let data; try { data = JSON.parse(text); } catch { data = { success:false, message:text }; }

        hidePaymentLoader();

        if (!res.ok || !data) {
          await Swal.fire({ icon:'error', title:'Payment failed', text: data?.message || 'Server error while processing payment.' });
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
            await Swal.fire({ icon:'error', title:'Authentication failed', text: result.error.message || 'Unable to confirm your card.' });
          } else {
            bootstrap.Modal.getInstance(bookingStripeModalEl)?.hide();
            populateThankYouModal('Stripe');
            if (bookingThankYouModalEl) new bootstrap.Modal(bookingThankYouModalEl).show();
          }
        } else {
          await Swal.fire({ icon:'error', title:'Payment failed', text: data.message || 'Unable to charge your card.' });
        }
      } catch (error) {
        console.error(error);
        hidePaymentLoader();
        await Swal.fire({ icon:'error', title:'Network error', text: error.message || 'Unable to reach the payment server.' });
      } finally {
        hidePaymentLoader();
        button.disabled = false; button.textContent = originalText;
      }
    });
  }

  /* =========================
     SUMMARY (same logic)
     ========================= */
  const goToSummaryBtn = document.getElementById('goToSummary');
  if (goToSummaryBtn) {
    goToSummaryBtn.addEventListener('click', function() {
      const form = document.getElementById('bookingForm');
      const name = form.querySelector('[name="name"]');
      const email= form.querySelector('[name="email"]');
      const phone= form.querySelector('[name="phone"]');
      const country = form.querySelector('[name="country"]');
      const emailValue = (email.value || '').trim();
      const phoneValue = (phone.value || '').trim();
      email.value = emailValue; phone.value = phoneValue;

      const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
      const phonePattern = /^\+?[0-9]{1,4}(?:[\s-]?[0-9]{2,4}){2,4}$/;

      if (!name.value.trim() || !emailValue || !phoneValue || !country.value) {
        notify('cust-missing', { icon:'error', title:'Missing Information', text:'Please fill all required customer details.' });
        return;
      }
      if (!emailPattern.test(emailValue)) {
        notify('cust-invalid', { icon:'error', title:'Invalid Email', text:'Enter a valid email address, e.g. you@example.com.' });
        email.focus(); return;
      }
      if (!phonePattern.test(phoneValue)) {
        notify('cust-invalid', { icon:'error', title:'Invalid Phone Number', text:'Use digits with optional spaces or dashes, e.g. +27 123 456 7890.' });
        phone.focus(); return;
      }

      const unitH  = document.getElementById('inputRentalUnit');
      const startH = document.getElementById('inputRentalStartDate');
      const extraH = document.getElementById('inputExtraDays');
      const totalH = document.getElementById('inputTotalPrice');

      const typeLabel = ({ day:'Daily', week:'Weekly', month:'Monthly' })[unitH.value] || (unitH.value || '—');
      document.getElementById('summaryType').textContent = typeLabel;

      let vehiclePeriod = '';
      if (startH && startH.value) {
        vehiclePeriod = niceDate(startH.value);
        if (extraH && (unitH.value === 'week' || unitH.value === 'month')) {
          vehiclePeriod += ` + ${extraH.value || 0} extra day(s)`;
        }
      }
      document.getElementById('summaryPeriod').textContent = vehiclePeriod || '—';
      document.getElementById('summaryVehicleTotal').textContent = money(totalH ? totalH.value : 0);

      // add-ons summary
      let addonTotal = 0, listHtml = '';
      document.querySelectorAll('.addon-card').forEach(card => {
        const id = card.dataset.id;
        const nm = card.dataset.name || 'Add-on';
        const totalHid = card.querySelector(`#addon-total-${id}`);
        const qtyHid   = card.querySelector(`#addon-quantity-${id}`);
        const typeHid  = card.querySelector(`#addon-type-${id}`);
        const startHid = card.querySelector(`#addon-start-${id}`);
        const endHid   = card.querySelector(`#addon-end-${id}`);
        const daysHid  = card.querySelector(`#addon-days-${id}`);
        if (totalHid && totalHid.disabled) return;

        const total = parseFloat(totalHid?.value || '0');
        if (total > 0) {
          addonTotal += total;
          listHtml += `
            <div class="d-flex justify-content-between align-items-start mb-1">
              <div>
                <div class="fw-semibold">${nm}</div>
                <div class="text-muted small">${(typeHid?.value||'-').toUpperCase()} • Qty ${qtyHid?.value||1} • ${niceDate(startHid?.value||'')} – ${niceDate(endHid?.value||'')} (${daysHid?.value||0} days)</div>
              </div>
              <div class="fw-semibold">${money(total)}</div>
            </div>`;
        }
      });
      if (!listHtml) listHtml = '<span class="text-muted">No add-ons selected.</span>';
      document.getElementById('summaryAddOnList').innerHTML = listHtml;
      document.getElementById('summaryAddonTotal').textContent = money(addonTotal);

      const vehicleTotal = parseFloat(totalH?.value || '0');
      document.getElementById('summaryGrandTotal').textContent = money(vehicleTotal + addonTotal);

      document.getElementById('summaryCustomerName').textContent = name.value;
      document.getElementById('summaryCustomerEmail').textContent = email.value;
      document.getElementById('summaryCustomerPhone').textContent = phone.value;
      document.getElementById('summaryCustomerCountry').textContent = country.value;

      const custEl = document.getElementById('customerStep');
      const sumEl  = document.getElementById('summaryStep');
      (bootstrap.Modal.getInstance(custEl) || new bootstrap.Modal(custEl)).hide();
      (bootstrap.Modal.getInstance(sumEl)  || new bootstrap.Modal(sumEl)).show();
    });
  }

  // Before any real submit from server side (we still block default submits),
  // make sure selected add-ons fields are enabled so they're included in FormData.
  const formEl = document.getElementById('bookingForm');
  if (formEl) {
    formEl.addEventListener('submit', () => { enableSelectedAddonHiddenFields(); });
  }
});
</script>
