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

            <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered modal-fullscreen-sm-down">

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



                        {{-- <hr class=""> --}}



                        <div id="dateSection" class="mb-3 mt-3 d-none">

                            <div class="position-relative">

                                <input type="text" id="rentalStartDate" class="form-control ps-5"
                                    placeholder="Select a start date" readonly
                                    data-lead="{{ (int) ($vehicle->booking_lead_days ?? 0) }}"
                                    data-blocked='@json($bookedRanges ?? [])'>

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
                        <div id="addonsEmptyState" class="alert alert-info d-none" role="alert">
                            All add-ons are fully booked for your selected dates.
                        </div>

                        @foreach ($addOns as $addOn)
                            @php

                                $blockedRanges = $addonFullyBooked[$addOn->id] ?? [];

                                $availableToday = max((int) ($addOn->available_today ?? $addOn->qty_total), 0);

                                $totalStock = (int) $addOn->qty_total;

                                $availabilityCls = $availableToday > 0 ? 'bg-success' : 'bg-danger';

                                $availabilityTxt =
                                    $availableToday > 0 ? $availableToday . ' available today' : 'Fully booked today';

                                $blockedPreview = array_slice($blockedRanges, 0, 3);

                                $blockedParts = [];

                                foreach ($blockedPreview as $range) {
                                    if (!empty($range['from']) && !empty($range['to'])) {
                                        $blockedParts[] =
                                            $range['from'] === $range['to']
                                                ? $range['from']
                                                : $range['from'] . ' to ' . $range['to'];
                                    }
                                }

                                $blockedText = implode(', ', $blockedParts);

                                $remainingBlocked = max(count($blockedRanges) - count($blockedPreview), 0);

                            @endphp



                            <div class="addon-card border rounded p-3 mb-3 shadow-sm" data-id="{{ $addOn->id }}"
                                data-name="{{ e($addOn->name) }}" data-total="{{ $totalStock }}"
                                data-available="{{ $availableToday }}" data-blocked='@json($blockedRanges)'
                                data-booked='@json($addOn->daily_totals_map ?? [], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_NUMERIC_CHECK)' style="cursor:pointer;">



                                <div
                                    class="addon-card-header d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3 w-100">

                                    <!-- Bigger image -->

                                    <div class="flex-shrink-0 addon-thumb-wrap">

                                        <img src="{{ asset($addOn->image_url) }}" alt="{{ $addOn->name }}"
                                            class="addon-thumb rounded border">

                                    </div>



                                    <div class="flex-grow-1 w-100 pe-lg-2">

                                        <h5 class="fw-semibold mb-1">{{ $addOn->name }}</h5>



                                        <!-- 2-line clamp with toggle -->

                                        <p class="text-muted mb-1 addon-desc" id="desc-{{ $addOn->id }}">

                                            {{ $addOn->description }}

                                        </p>

                                        <button type="button" class="btn btn-link p-0 small addon-see-more d-none"
                                            data-target="#desc-{{ $addOn->id }}">

                                            See more

                                        </button>
                                        <p class="addon-price-summary fw-bold mb-0">
                                            R{{ number_format($addOn->price_day, 2) }}/day /
                                            R{{ number_format($addOn->price_week, 2) }}/week /
                                            R{{ number_format($addOn->price_month, 2) }}/month
                                        </p>
                                    </div>
                                    @php
                                        $bookedToday = max($totalStock - $availableToday, 0);
                                    @endphp
                                    <div class="addon-card-stats text-start text-lg-end w-100 w-lg-auto">
                                        <span class="badge availability-badge {{ $availabilityCls }} mb-2">
                                            {{ $availabilityTxt }}
                                        </span>
                                        <small class="text-muted d-block addon-availability-text"
                                            data-available-today="{{ $availableToday }}"></small>
                                    </div>
                                </div>
                                <div class="addon-details mt-3 d-none border-top pt-3">
                                    @php
                                        $blockedSummary = '';
                                        if ($blockedText) {
                                            $blockedSummary = 'Already booked for: ' . $blockedText;
                                            if ($remainingBlocked > 0) {
                                                $blockedSummary .=
                                                    ' + ' .
                                                    $remainingBlocked .
                                                    ' more period' .
                                                    ($remainingBlocked > 1 ? 's' : '');
                                            }
                                        }
                                    @endphp

                                    {{-- <div class="alert alert-warning small py-2 px-3 addon-unavailable-dates mb-3 {{ $blockedSummary ? '' : 'd-none' }}"
                                        data-default-message="{{ e($blockedSummary) }}">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                        <span class="blocked-text">{{ $blockedSummary }}</span>
                                    </div> --}}
                                    <div class="row g-2 mb-2">

                                        <div class="col-sm-4">

                                            <div class="card text-center shadow-sm h-100 addon-type-card"
                                                data-type="day" data-price="{{ $addOn->price_day }}">

                                                <div
                                                    class="card-body d-flex flex-column align-items-center justify-content-center text-center">

                                                    <div class="addon-type-icon mb-2">

                                                        <i class="bi bi-clock display-6" style="color: #CF9B4D"></i>

                                                    </div>

                                                    <h6 class="card-title mb-1">Daily</h6>

                                                    <p class="card-text fw-bold text-primary mb-0">

                                                        R{{ number_format($addOn->price_day, 2) }}</p>

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

                                            <label class="form-label small mb-1">Quantity <span
                                                    class="text-danger">*</span></label>

                                            <select class="form-select form-select-sm addon-qty"
                                                data-id="{{ $addOn->id }}"
                                                @if ($totalStock <= 0) disabled @endif required>

                                                @for ($i = 0; $i <= $addOn->qty_total; $i++)
                                                    <option value="{{ $i }}">{{ $i }}</option>
                                                @endfor

                                            </select>

                                        </div>

                                        <div class="col-sm-5 mt-3 mt-sm-0 text-sm-end">

                                            <div class="fw-bold text-primary" id="addon-price-{{ $addOn->id }}">

                                                R0.00</div>

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



                                    <div class="small text-muted mt-2" id="addon-period-{{ $addOn->id }}"></div>



                                    <div class="addon-live-summary d-none">

                                        <div class="alert alert-info border-0 rounded-3 py-3 px-3 mb-3">

                                            <div class="fw-semibold mb-1">

                                                <span class="als-line-1-label">Days</span>:

                                                <span class="als-qty">0</span>

                                                x <span class="als-unit">R0.00</span>

                                            </div>

                                            <div class="fw-semibold">

                                                Total Cost: <span class="als-total">R0.00</span>

                                            </div>

                                        </div>



                                        <div class="row g-3">

                                            <div class="col-md-6">

                                                <div class="addon-date-pill bg-light border rounded-3 p-3 h-100">

                                                    <div class="text-muted fw-semibold mb-1">Start Date</div>

                                                    <div class="als-start">N/A</div>

                                                </div>

                                            </div>

                                            <div class="col-md-6">

                                                <div class="addon-date-pill bg-light border rounded-3 p-3 h-100">

                                                    <div class="text-muted fw-semibold mb-1">End Date</div>

                                                    <div class="als-end">N/A</div>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </div> <!-- /.addon-details -->

                            </div> <!-- /.addon-card -->
                        @endforeach

                    </div>



                    <div class="modal-footer">

                        <div class="d-flex justify-content-between w-100">

                            <button type="button" class="btn btn-outline-secondary" id="backToStep1">Back</button>

                            <button type="button" class="btn btn-dark rounded-3" id="addonsToCustomer">Continue to

                                Details</button>

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

                            <button type="button" class="btn btn-outline-secondary"
                                id="summaryBackToCustomer">Back</button>



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

    <!-- Step 6: Thank You -->

    <div class="modal fade" id="bookingThankYou" tabindex="-1" aria-hidden="true">

        <div class="modal-dialog modal-fullscreen-sm-down custom-modal-dialog">

            <div class="modal-content rounded-4 shadow border-0">

                <div class="modal-body p-4 p-md-5">



                    <!-- Title / success icon -->

                    <div class="d-flex align-items-center gap-3 mb-3">

                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10"
                            style="width:56px;height:56px;">

                            <i class="bi bi-check-lg text-success fs-3"></i>

                        </div>

                        <div>

                            <h4 class="fw-bold mb-1">Payment Successful!</h4>

                            <div class="text-muted small">

                                Your booking deposit has been processed successfully.

                            </div>

                        </div>

                    </div>



                    <!-- Reference + summary panel -->

                    <div class="border border-success-subtle rounded-3 p-3 p-md-4 mb-4 bg-success bg-opacity-10">

                        <div class="fw-semibold mb-3">

                            Booking Reference:

                            <span class="text-success" id="tyReference">N/A</span>

                        </div>



                        <div class="row g-3">

                            <div class="col-md-6">

                                <div class="d-flex align-items-start gap-2">

                                    <i class="bi bi-box-seam mt-1"></i>

                                    <div>

                                        <div class="small text-muted">Vehicle</div>

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



                    <!-- What happens next -->

                    <div class="rounded-3 p-3 p-md-4 border bg-light">

                        <div class="fw-semibold text-center mb-2">What happens next?</div>

                        <div class="text-muted small text-center">

                            Your booking is now <strong>under offer</strong> and pending confirmation.

                            We'll be in touch shortly to finalize the details and arrange vehicle handover.

                            Please keep your booking reference safe for future correspondence.

                        </div>

                    </div>



                </div>



                <!-- Footer buttons -->

                <div
                    class="modal-footer border-0 pt-0 px-4 px-md-5 pb-4 d-flex flex-wrap gap-2 justify-content-between">

                    <a href="/" class="btn btn-outline-secondary rounded-3" id="tyContinueVehicles">

                        Continue to Vehicles

                    </a>



                    <a href="https://wa.link/8bgpe5"
                        class="btn btn-success fw-bold rounded-3 d-flex align-items-center gap-2" target="_blank"
                        id="tyWhatsappBtn" rel="noopener">

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





        .custom-modal-dialog {

            max-width: 80% !important;
            /* Set modal width */

            height: 80vh !important;
            /* Set modal height */

            margin-top: 90px !important;
            /* Margin from top */

        }



        .custom-modal-dialog .modal-content {

            height: 100% !important;

            overflow-y: auto;
            /* Scroll if needed */

            border-radius: 12px;

        }





        .addon-card.addon-selected {

            border-color: var(--bs-primary);

            box-shadow: 0 .5rem 1.25rem rgba(13, 110, 253, .20);

            background-color: rgba(13, 110, 253, .05);

        }



        /* Bigger thumbnail in Add-Ons modal */

        .addon-thumb {

            width: 110px;

            height: 110px;

            object-fit: cover;

        }

        #addonsStep .addon-card-header {
            width: 100%;
        }

        #addonsStep .addon-thumb-wrap {
            width: 120px;
        }

        #addonsStep .addon-card-stats .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 160px;
        }

        #addonsStep .addon-price-summary {
            font-weight: 600;
            font-size: 1rem;
            margin-top: 4px;
            margin-bottom: 0;
            white-space: nowrap;
        }

        @media (max-width: 991.98px) {
            #addonsStep .addon-thumb-wrap {
                width: 100%;
                margin-bottom: 1rem;
            }

            #addonsStep .addon-thumb {
                width: 100%;
                height: auto;
                max-height: 240px;
            }

            #addonsStep .addon-card-stats {
                width: 100% !important;
                text-align: left !important;
            }

            #addonsStep .addon-card-stats .badge {
                width: 100%;
            }
        }

        @media (max-width: 767.98px) {
            #addonsStep .addon-price-summary {
                font-size: 0.95rem;
            }
        }

        @media (max-width: 575.98px) {
            #addonsStep .addon-price-summary {
                white-space: normal;
                line-height: 1.4;
            }

            #addonsStep .addon-card-stats .badge {
                min-width: 0;
            }
        }



        /* Two-line clamp by default */

        .addon-desc {

            display: -webkit-box;

            -webkit-line-clamp: 2;

            -webkit-box-orient: vertical;

            overflow: hidden;

        }



        /* Expanded state removes clamp */

        .addon-desc.expanded {

            -webkit-line-clamp: unset;

            max-height: none;

        }



        .addon-see-more {

            text-decoration: none;

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

            /* 0 = none, .5 = default; try .12-.22 */

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

    <!-- ADD-ONS + SUMMARY (keeps SweetAlerts + overlap checks) -->

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            /* =========================

               GLOBAL MODAL STACKING

               ========================= */

            const Z_BASE = 1055; // Bootstrap modal z-index baseline

            const Z_STEP = 20; // Step per stacked modal



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



            // Optional generic swap triggers

            document.addEventListener('click', (e) => {

                const swapper = e.target.closest('.modal-swap[data-target]');

                if (!swapper) return;

                e.preventDefault();

                const nextSel = swapper.getAttribute('data-target');

                const current = e.target.closest('.modal');

                const showNext = () => {

                    const next = document.querySelector(nextSel);

                    if (next)(bootstrap.Modal.getInstance(next) || new bootstrap.Modal(next)).show();

                };

                if (current && current.classList.contains('show')) {

                    current.addEventListener('hidden.bs.modal', function onH() {

                        current.removeEventListener('hidden.bs.modal', onH);

                        showNext();

                    }, {

                        once: true

                    });

                    (bootstrap.Modal.getInstance(current) || new bootstrap.Modal(current)).hide();

                } else {

                    showNext();

                }

            });



            /* =========================

               STEP-1 HARD LOCK (Fix)

               ========================= */

            const bookingForm = document.getElementById('bookingForm');

            const step1Modal = document.getElementById('multiStepBookingModal');

            const startDateInput = document.getElementById('rentalStartDate');

            let bookingCreationInFlight = false;



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
                    refreshAllAddons();
                    swapModal('multiStepBookingModal', 'addonsStep');
                });
            }


            // Back & forward controls between modals

            const addonsBackBtn = document.getElementById('backToStep1');

            const addonsNextBtn = document.getElementById('addonsToCustomer');

            const customerBackBtn = document.getElementById('customerBackToAddons');

            const summaryBackBtn = document.getElementById('summaryBackToCustomer');

            const paymentBackBtn = document.getElementById('paymentBackToSummary');

            const stripeBackBtn = document.getElementById('stripeBackToPayment');



            addonsBackBtn?.addEventListener('click', (e) => {

                e.preventDefault();

                swapModal('addonsStep', 'multiStepBookingModal');

            });

            addonsNextBtn?.addEventListener('click', (e) => {

                e.preventDefault();

                swapModal('addonsStep', 'customerStep');

            });

            customerBackBtn?.addEventListener('click', (e) => {

                e.preventDefault();

                swapModal('customerStep', 'addonsStep');

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

            const snapEndForUnit = (start, unit) => {

                if (!start) return null;

                const base = new Date(start.getFullYear(), start.getMonth(), start.getDate());

                if (unit === 'day') return base;

                if (unit === 'week') return addDays(base, 6);

                if (unit === 'month') return addDays(base, 29);

                return base;

            };

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



            // Hidden inputs used by your existing flow:

            const hidUnit = document.getElementById('inputRentalUnit');

            const hidQty = document.getElementById('inputRentalQuantity');

            const hidStart = document.getElementById('inputRentalStartDate');

            const hidExtra = document.getElementById('inputExtraDays'); // optional

            const hidTotal = document.getElementById('inputTotalPrice');



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

                // You can tweak ranges here if needed

                let max = 30;

                let label = 'How many day(s)?';

                if (u === 'week') {

                    max = 12;

                    label = 'How many week(s)?';

                }

                if (u === 'month') {

                    max = 12;

                    label = 'How many month(s)?';

                }

                qtyLabel.textContent = label;

                fillSelect(qtySelect, 1, max, 1);

            }



            // Compute + paint Step-1 price & period

            function updateStep1Paint() {

                const unit = (hidUnit?.value || activeUnit() || '').toLowerCase();

                const qty = parseInt(hidQty?.value || qtySelect?.value || '0', 10) || 0;

                const startY = (hidStart?.value || '').trim();

                const startDt = startY ? fromYMD(startY) : null;

                const extra = parseInt(hidExtra?.value || '0', 10) || 0;



                // Show sections progressively

                if (unit) dateSection?.classList.remove('d-none');



                // Guard

                if (!unit || !qty || !startDt) {

                    totalBox?.classList.add('d-none');

                    if (totalBox) totalBox.textContent = '';

                    periodBox?.classList.add('d-none');

                    if (periodBox) periodBox.textContent = '';

                    if (hidTotal) hidTotal.value = '';

                    return;

                }



                const baseDays = qty * unitDays(unit);

                const days = baseDays + (unit === 'day' ? 0 : extra);

                const endDt = addDays(startDt, Math.max(0, days - 1));

                const endY = toYMD(endDt);



                const pricePer = priceForActiveUnit();

                const vehicleTotal = Number((pricePer * qty).toFixed(2)); // price is per unit (day/week/month)



                // START: replace this whole block inside updateStep1Paint()



                if (totalBox) {

                    totalBox.innerHTML = `

    <div class="d-flex justify-content-between align-items-center">

      <span class="small text-muted">Vehicle total (${qty} ${unit}${qty>1?'s':''})</span>

      <span class="fw-bold">${money(vehicleTotal)}</span>

    </div>`;

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

    <div class="mt-2 text-center small">${days} day${days===1?'':'s'}</div>`;

                    periodBox.classList.remove('d-none');

                }



                if (hidTotal) {

                    hidTotal.value = String(vehicleTotal);

                    hidTotal.dispatchEvent(new Event('change', {

                        bubbles: true

                    }));

                }



                // END: replacement



                // Inform add-ons & summary listeners

                document.dispatchEvent(new CustomEvent('rental:updated'));

            }



            // Unit card selection

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

            /* =========================================

               CALENDAR (lead days + booked ranges lock)

               ========================================= */

            (function initCalendar() {

                const inp = startDateInput;

                if (!inp) return;



                // Prefer data-* if present; otherwise allow global fallbacks

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



                // All checks in LOCAL YYYY-MM-DD (avoid toISOString())

                function isDisabled(dateObj) {

                    // Normalize the candidate date to local midnight

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

                    for (const r of blockedRanges) {

                        if (!r?.from || !r?.to) continue;

                        if (ymd >= r.from && ymd <= r.to) return true;

                    }

                    return false;

                }



                // Use flatpickr if available; otherwise basic fallback with native <input type="date">

                if (typeof flatpickr !== 'undefined') {

                    flatpickr(inp, {

                        minDate, // Date object is fine

                        disable: [isDisabled], // our local-ymd predicate

                        dateFormat: 'Y-m-d',

                        clickOpens: true,

                        allowInput: false,

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

                } else {

                    // Fallback to native date input

                    try {

                        inp.removeAttribute('readonly');

                        inp.setAttribute('type', 'date');

                    } catch {}

                    // Enforce minDate and disabling via simple check

                    inp.addEventListener('input', () => {

                        const val = inp.value;

                        const picked = fromYMD(val);

                        if (!picked) return;

                        // min date guard

                        if (toYMD(picked) < toYMD(minDate) || isDisabled(picked)) {

                            // show a friendly alert if SweetAlert is present

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

            })();



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

                const suppressForStep1 = typeof key === 'string' &&

                    /^overlap|^noqty|^clamped/.test(key);

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

               ADD-ONS LOGIC (unchanged)

               ========================= */

            const inRange = (d, r) => {

                const s = fromYMD(r.from),

                    e = fromYMD(r.to);

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

                const s = fromYMD(startYMD),

                    e = fromYMD(endYMD);

                if (!s || !e) return false;

                for (let d = new Date(s.getFullYear(), s.getMonth(), s.getDate()); d <= e; d = addDays(d, 1)) {

                    if (blockedRanges.some(r => inRange(d, r))) return true;

                }

                return false;

            }



            function listOverlappingRanges(startYMD, endYMD, blockedRanges) {

                const s = fromYMD(startYMD),

                    e = fromYMD(endYMD),

                    res = [];

                if (!s || !e) return res;

                blockedRanges.forEach(r => {

                    const rs = fromYMD(r.from),

                        re = fromYMD(r.to);

                    if (!rs || !re) return;

                    const start = new Date(Math.max(rs.getTime(), s.getTime()));

                    const end = new Date(Math.min(re.getTime(), e.getTime()));

                    if (start <= end) res.push(

                        `${niceDate(toYMD(start))} - ${niceDate(toYMD(end))}`);

                });

                return res;

            }



            function warnIfPlannedRangeOverlaps({

                unit,

                startDate,

                extraDays = 0,

                addonId,

                blockedRanges,

                availableQty

            }) {

                if (!startDate) return false;

                const snapEnd = snapEndForUnit(startDate, unit);

                const endDate = addDays(snapEnd, (unit === 'day' ? 0 : extraDays));

                const s = toYMD(startDate),

                    e = toYMD(endDate);

                if (hasOverlap(s, e, blockedRanges)) {

                    const where = listOverlappingRanges(s, e, blockedRanges);



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

                    hiddens.forEach(h => {

                        h.disabled = !(total > 0);

                    });

                });

            }

            window.computeAddonsTotal = computeAddonsTotal;

            window.enableSelectedAddonHiddenFields = enableSelectedAddonHiddenFields;



            const addonSelections = {};
            window.bookingAddonSelections = addonSelections;

            const addonsModalEl = document.getElementById('addonsStep');
            const addonsEmptyStateEl = document.getElementById('addonsEmptyState');

            function hasVisibleAddons() {
                if (!addonsModalEl) return false;
                return !!addonsModalEl.querySelector('.addon-card:not(.d-none)');
            }

            function hasVisibleAddons() {
                if (!addonsModalEl) return false;
                const visibleCards = addonsModalEl.querySelectorAll('.addon-card:not(.d-none)');
                return visibleCards.length > 0;
            }

            function updateAddonsEmptyState() {
                if (!addonsEmptyStateEl) return;
                const hasVisible = hasVisibleAddons();
                addonsEmptyStateEl.classList.toggle('d-none', hasVisible);
            }
            window.updateAddonsEmptyState = updateAddonsEmptyState;

            updateAddonsEmptyState();

            // Add-on cards (unchanged logic)
            document.querySelectorAll('#addonsStep .addon-card').forEach(card => {
                const desc = card.querySelector('.addon-desc');

                const btn = card.querySelector('.addon-see-more');

                if (!desc || !btn) return;



                const needsToggle = () => desc.scrollHeight > desc.clientHeight + 1;

                if (needsToggle()) btn.classList.remove('d-none');



                btn.addEventListener('click', (e) => {

                    e.stopPropagation();

                    const expanded = desc.classList.toggle('expanded');

                    btn.textContent = expanded ? 'See less' : 'See more';

                });

                card.addEventListener('click', (e) => {

                    if (e.target.closest('.addon-details')) return;

                    setTimeout(() => {

                        if (desc.classList.contains('expanded')) return;

                        if (needsToggle()) btn.classList.remove('d-none');

                        else btn.classList.add('d-none');

                    }, 0);

                });

            });



            // Add-on init (same behavior)

            document.querySelectorAll('.addon-card').forEach(card => {

                const addonId = card.dataset.id;

                const totalStock = parseInt(card.dataset.total || '0', 10);

                const availToday = parseInt(card.dataset.available || '0', 10);



                const details = card.querySelector('.addon-details');
                const qtySel = details.querySelector('.addon-qty');
                const planCards = details.querySelectorAll('.addon-type-card');
                const badgeEl = card.querySelector('.availability-badge');
                const availabilityTextEl = card.querySelector('.addon-availability-text');
                const availabilityDefaultText = availabilityTextEl?.textContent?.trim() || '';
                const blockedAlert = details.querySelector('.addon-unavailable-dates');
                const blockedTextEl = blockedAlert ? blockedAlert.querySelector('.blocked-text') : null;
                const blockedDefaultMessage = blockedAlert?.dataset.defaultMessage || (blockedTextEl
                    ?.textContent?.trim() || '');


                // Hidden fields

                const typeH = details.querySelector(`#addon-type-${addonId}`);

                const qtyH = details.querySelector(`#addon-quantity-${addonId}`);

                const startH = details.querySelector(`#addon-start-${addonId}`);

                const endH = details.querySelector(`#addon-end-${addonId}`);

                const extraH = details.querySelector(`#addon-extra-${addonId}`);

                const totalH = details.querySelector(`#addon-total-${addonId}`);

                const daysH = details.querySelector(`#addon-days-${addonId}`);



                const priceEl = details.querySelector(`#addon-price-${addonId}`);

                const periodEl = details.querySelector(`#addon-period-${addonId}`);



                const live = details.querySelector('.addon-live-summary');

                const alsQty = live ? live.querySelector('.als-qty') : null;

                const alsUnit = live ? live.querySelector('.als-unit') : null;

                const alsTotal = live ? live.querySelector('.als-total') : null;

                const alsStart = live ? live.querySelector('.als-start') : null;

                const alsEnd = live ? live.querySelector('.als-end') : null;

                const alsLbl = live ? live.querySelector('.als-line-1-label') : null;



                let blockedRanges = [];

                try {

                    blockedRanges = JSON.parse(card.dataset.blocked || '[]') || [];

                } catch {

                    blockedRanges = [];

                }

                blockedRanges = blockedRanges.filter(r => r && r.from && r.to);

                let bookedMap = {};

                try {

                    bookedMap = JSON.parse(card.dataset.booked || '{}') || {};

                } catch {

                    bookedMap = {};

                }



                setAddonHiddenDisabled(details, true);

                setBlockedMessage(blockedDefaultMessage);

               const hideCard = () => {
    card.style.display = 'none';
    card.classList.add('d-none');
    updateAddonsEmptyState();
};

const showCard = () => {
    card.style.display = '';
    card.classList.remove('d-none');
    updateAddonsEmptyState();
};

                let currentCtx = null;





                const rangeOverlapsBlocked = (startYMD, endYMD) => hasOverlap(startYMD, endYMD,

                    blockedRanges);



                function setBadge(text, ok) {



                    if (!badgeEl) return;



                    badgeEl.textContent = text;



                    badgeEl.classList.remove('bg-success', 'bg-danger');



                    badgeEl.classList.add(ok ? 'bg-success' : 'bg-danger');



                }



                function setAvailabilityText(text) {



                    if (!availabilityTextEl) return;



                    availabilityTextEl.textContent = text;



                }



                function setBlockedMessage(message) {



                    if (!blockedAlert || !blockedTextEl) return;



                    if (message) {



                        blockedAlert.classList.remove('d-none');



                        blockedTextEl.textContent = message;



                    } else {



                        blockedTextEl.textContent = '';



                        blockedAlert.classList.add('d-none');



                    }



                }



                function computeRangeAvailability(startYMD, endYMD) {



                    if (!startYMD || !endYMD) return totalStock;



                    const startDate = fromYMD(startYMD);

                    const endDate = fromYMD(endYMD);

                    if (!startDate || !endDate) return totalStock;

                    let minAvailable = totalStock;

                    let hasReservationsInRange = false;

                    for (let d = new Date(startDate); d <= endDate; d = addDays(d, 1)) {

                        const ymd = toYMD(d);

                        const reserved = parseInt(bookedMap?.[ymd] ?? 0, 10);

                        if (!isNaN(reserved) && reserved > 0) hasReservationsInRange = true;

                        const available = totalStock - (isNaN(reserved) ? 0 : reserved);

                        if (available < minAvailable) minAvailable = available;

                    }

                    if (!hasReservationsInRange) return totalStock;

                    return Math.max(0, minAvailable);

                }



                function clampQtyTo(avail, startYMD = null, endYMD = null, suppressAlerts = false) {



                    const prev = parseInt(qtySel.value || '0', 10);



                    const newMax = Math.max(0, avail);



                    const newVal = Math.min(prev, newMax);







                    fillSelect(qtySel, 0, newMax, newVal);



                    qtySel.disabled = (newMax <= 0);







                    if (startYMD && endYMD) {



                        const where = listOverlappingRanges(startYMD, endYMD, blockedRanges);



                        const blockedTxt = where.length ? `Already booked for: ${where.join(', ')}` : null;







                        if (!suppressAlerts && newMax <= 0) {



                            notify(`noqty-${addonId}-${startYMD}-${endYMD}`, {



                                icon: 'warning',



                                title: 'Out of stock',



                                text: `${blockedTxt ? blockedTxt + '\n' : ''}Available quantity for these dates: 0.`



                            });



                        } else if (!suppressAlerts && prev > newVal) {



                            notify(`clamped-${addonId}-${startYMD}-${endYMD}`, {



                                icon: 'warning',



                                title: 'Limited availability',



                                text: `${blockedTxt ? blockedTxt + '\n' : ''}Only ${newMax} unit${newMax > 1 ? 's' : ''} available.`



                            });



                        }



                    }







                    return newMax;



                }







                function updateAvailabilityForDates(startYMD, endYMD, suppressAlerts = false) {
                    if (startYMD && endYMD) {
                        if (rangeOverlapsBlocked(startYMD, endYMD)) {
                            // Completely hide the card when dates are blocked
                            hideCard();
                            return 0;
                        }

                        const rangeAvailability = computeRangeAvailability(startYMD, endYMD);
                        const clamped = clampQtyTo(rangeAvailability, startYMD, endYMD, suppressAlerts);

                        if (clamped <= 0) {
                            // Completely hide the card when no availability
                            hideCard();
                            return 0;
                        }

                        // Show the card and update availability info
                        setBadge(`${clamped} available for these dates (of ${totalStock})`, clamped > 0);
                        setBlockedMessage(blockedDefaultMessage);
                        setAvailabilityText(
                            `Available for selected dates (${formatRangeLabel(startYMD, endYMD)}): ${clamped}`
                        );
                        showCard();

                        return clamped;

                    } else {
                        const clampedToday = clampQtyTo(availToday, null, null, suppressAlerts);
                        if (clampedToday <= 0) {
                            hideCard();
                        } else {
                            setBadge('Select rental dates to check availability', false);
                            setBlockedMessage(blockedDefaultMessage);
                            setAvailabilityText(availabilityDefaultText);
                            showCard();
                        }
                        return clampedToday;
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

                    if (!ctx || !ctx.startYMD || !ctx.endYMD) periodEl.textContent =

                        'Select your rental dates first.';

                    else {

                        const days = ctx.totalDays || 0;

                        periodEl.textContent =

                            `${niceDate(ctx.startYMD)} -> ${niceDate(ctx.endYMD)} (${days} day${days === 1 ? '' : 's'})`;

                    }

                }



                function setPrice(amount) {

                    if (priceEl) priceEl.textContent = money(amount);

                }



                function removeSelection(clearPeriod) {

                    delete addonSelections[addonId];

                    totalH.value = '';

                    qtyH.value = '0';

                    if (qtySel) qtySel.value = '0';

                    if (clearPeriod) {

                        startH.value = '';

                        endH.value = '';

                        daysH.value = '0';

                    }

                    setAddonHiddenDisabled(details, true);

                    setPrice(0);

                    card.classList.remove('addon-selected');

                    const resetAvailabilityText = availabilityDefaultText || '';

                    setAvailabilityText(resetAvailabilityText);

                    setBlockedMessage(blockedDefaultMessage);

                    if (live) {

                        if (alsQty) alsQty.textContent = '0';

                        if (alsUnit) alsUnit.textContent = money(0);

                        if (alsTotal) alsTotal.textContent = money(0);

                        if (alsStart) alsStart.textContent = '-';

                        if (alsEnd) alsEnd.textContent = '-';

                    }

                    if (clearPeriod && periodEl) periodEl.textContent = 'Select your rental dates first.';

                    const startY = startH.value || null;

                    const endY = endH.value || null;

                    updateAvailabilityForDates(startY, endY, true);

                }



                function computeAddonUnits(unit, totalDays) {

                    if (!totalDays || totalDays <= 0) return 0;

                    if (unit === 'week') return Math.max(1, Math.ceil(totalDays / 7));

                    if (unit === 'month') return Math.max(1, Math.ceil(totalDays / 30));

                    return Math.max(1, totalDays);

                }



                function updateLiveSummary(unit, ctx, unitPrice, units, quantity, total) {

                    if (!live) return;

                    const labelMap = {

                        day: 'Days',

                        week: 'Weeks',

                        month: 'Months'

                    };

                    const qtyVal = unit === 'day' ? ctx.totalDays : units;

                    if (alsLbl) alsLbl.textContent = labelMap[unit] || 'Units';

                    if (alsQty) alsQty.textContent = String(qtyVal);

                    if (alsUnit) alsUnit.textContent = money(unitPrice);

                    if (alsTotal) alsTotal.textContent = money(total);

                    if (alsStart) alsStart.textContent = ctx.startYMD ? niceDate(ctx.startYMD) : '-';

                    if (alsEnd) alsEnd.textContent = ctx.endYMD ? niceDate(ctx.endYMD) : '-';

                    live.classList.remove('d-none');

                }



                function getRentalContext() {

                    const unitEl = document.getElementById('inputRentalUnit');

                    const qtyEl = document.getElementById('inputRentalQuantity');

                    const startEl = document.getElementById('inputRentalStartDate');

                    const extraEl = document.getElementById('inputExtraDays');



                    const unit = (unitEl?.value || '').toLowerCase();

                    const quantity = parseInt(qtyEl?.value || '0', 10);

                    const extraDays = Math.max(0, parseInt(extraEl?.value || '0', 10));

                    const startYMD = startEl?.value || '';

                    const startDate = startYMD ? fromYMD(startYMD) : null;



                    let totalDays = 0;

                    if (quantity > 0) {

                        if (unit === 'week') totalDays = (quantity * 7) + extraDays;

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



                function updateTotal() {

                    const ctx = currentCtx;

                    const activePlan = ensureActivePlan(ctx);

                    const unit = (activePlan?.dataset.type || typeH.value || 'day').toLowerCase();

                    const unitPrice = parseFloat(activePlan?.dataset.price || '0');

                    const quantity = parseInt(qtySel.value || '0', 10);



                    if (!ctx || !ctx.startYMD || !ctx.endYMD || !quantity) {

                        removeSelection(true);

                        updateSummaryAddonTotals();

                        return;

                    }



                    const units = computeAddonUnits(unit, ctx.totalDays);

                    const totalPerAddon = unitPrice * units;

                    const total = Number((totalPerAddon * quantity).toFixed(2));



                    startH.value = ctx.startYMD;

                    endH.value = ctx.endYMD;

                    daysH.value = String(ctx.totalDays);

                    extraH.value = '0';

                    totalH.value = total.toFixed(2);

                    qtyH.value = String(quantity);

                    setAddonHiddenDisabled(details, false);

                    setPrice(total);

                    card.classList.add('addon-selected');



                    addonSelections[addonId] = {

                        id: addonId,

                        name: card.dataset.name || '',

                        type: unit,

                        qty: quantity,

                        unitPrice,

                        perUnitTotal: Number((totalPerAddon).toFixed(2)),

                        total,

                        start: ctx.startYMD,

                        end: ctx.endYMD,

                        days: ctx.totalDays,

                        extraDays: 0,

                        remainderDays: 0,

                        fullUnits: units

                    };



                    if (badgeEl && quantity > 0) {

                        const label =

                            `Selected ${quantity} unit${quantity !== 1 ? 's' : ''}${totalStock ? ` (of ${totalStock})` : ''}`;

                        setBadge(label, true);

                    }

                    updateSummaryAddonTotals();

                    updateLiveSummary(unit, ctx, unitPrice, units, quantity, total);

                }



                function applyContext(ctx, options = {}) {

                    const suppressAlerts = !!options.suppressAlerts;

                    currentCtx = (ctx && ctx.totalDays > 0 && ctx.startYMD && ctx.endYMD) ? ctx : null;

                    if (!currentCtx) {

                        startH.value = '';

                        endH.value = '';

                        daysH.value = '0';

                        extraH.value = '0';

                    }

                    ensureActivePlan(currentCtx);

                    updatePeriodDisplay(currentCtx);

                    const available = updateAvailabilityForDates(currentCtx?.startYMD || null, currentCtx

                        ?.endYMD || null, suppressAlerts);

                    let currentQty = parseInt(qtySel.value || '0', 10);

                    if (available <= 0) {

                        qtySel.value = '0';

                        currentQty = 0;

                    } else if (currentQty > available) {

                        qtySel.value = String(available);

                        currentQty = available;

                    }
                    qtyH.value = String(currentQty);
                    updateTotal();

                    if (currentCtx && available <= 0) {
                        if (details) details.classList.add('d-none');
                        card.classList.add('d-none');
                    } else {
                        card.classList.remove('d-none');
                    }
                    updateAddonsEmptyState();
                }

                // Toggle details on card click (but ignore clicks inside details)
                card.addEventListener('click', (e) => {

                    if (e.target.closest('.addon-details')) return;

                    details.classList.toggle('d-none');

                    if (!details.classList.contains('d-none')) applyContext(getRentalContext());

                });

                planCards.forEach(pc => {

                    pc.addEventListener('click', (e) => {

                        e.stopPropagation();

                        planCards.forEach(c => c.classList.remove('active'));

                        pc.classList.add('active');

                        typeH.value = pc.dataset.type;

                        qtyH.value = qtySel.value || '0';

                        updateTotal();

                    });

                });

                qtySel.addEventListener('change', () => {

                    qtyH.value = qtySel.value || '0';

                    updateTotal();

                });

                card.__applyRentalContext = (ctx, opts) => applyContext(ctx, opts);

            });



            function refreshAllAddons() {

                // Uses current hidden inputs to compute ctx

                const unitEl = document.getElementById('inputRentalUnit');

                const qtyEl = document.getElementById('inputRentalQuantity');

                const startEl = document.getElementById('inputRentalStartDate');

                const extraEl = document.getElementById('inputExtraDays');



                const unit = (unitEl?.value || '').toLowerCase();

                const quantity = parseInt(qtyEl?.value || '0', 10);

                const extraDays = Math.max(0, parseInt(extraEl?.value || '0', 10));

                const startYMD = startEl?.value || '';

                const startDate = startYMD ? fromYMD(startYMD) : null;



                let totalDays = 0;

                if (quantity > 0) {

                    if (unit === 'week') totalDays = (quantity * 7) + extraDays;

                    else if (unit === 'month') totalDays = (quantity * 30) + extraDays;

                    else totalDays = quantity + extraDays;

                }

                const endDate = (startDate && totalDays > 0) ? addDays(startDate, totalDays - 1) : null;



                const ctx = {

                    unit,

                    quantity,

                    extraDays,

                    startDate,

                    endDate,

                    startYMD: startDate ? toYMD(startDate) : '',

                    endYMD: endDate ? toYMD(endDate) : '',

                    totalDays: totalDays > 0 ? totalDays : 0

                };



                document.querySelectorAll('.addon-card').forEach(card => {
                    const applyCtx = card.__applyRentalContext;
                    if (typeof applyCtx === 'function') applyCtx(ctx, {
                        suppressAlerts: true
                    });
                });
                updateAddonsEmptyState();
                updateSummaryAddonTotals();
            }


            ['inputRentalUnit', 'inputRentalQuantity', 'inputExtraDays', 'inputRentalStartDate'].forEach(id => {

                const el = document.getElementById(id);

                if (el) el.addEventListener('change', () => {

                    updateStep1Paint();

                    refreshAllAddons();

                });

            });

            document.addEventListener('rental:updated', () => {

                refreshAllAddons();

            });

            // Initial paint

            updateStep1Paint();



            /* =========================

               BOOKING + PAYMENT FLOW

               ========================= */

            const bookingIdField = document.getElementById('bookingId');

            const openPaymentBtn = document.getElementById('openPayment');

            const bookingPaymentModalEl = document.getElementById('bookingPayment');

            const bookingStripeModalEl = document.getElementById('bookingStripeModal');

            const bookingStripePayButton = document.getElementById('bookingStripePayButton');

            const bookingThankYouModalEl = document.getElementById('bookingThankYou');

            const tyGoHomeNowBtn = document.getElementById('tyGoHomeNow');

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

                const addonTotal = window.computeAddonsTotal ? window.computeAddonsTotal() : 0;

                return Math.round((vehicleTotal + addonTotal) * 100) / 100;

            };



            const populateThankYouModal = (methodLabel) => {

                if (!bookingThankYouModalEl) return;

                const tyVehicleNameEl = document.getElementById('tyVehicleName');

                if (tyVehicleNameEl) tyVehicleNameEl.textContent = "{{ addslashes($vehicle->name) }}";



                const periodText = document.getElementById('summaryPeriod')?.textContent?.trim() || '-';

                const tyPeriodEl = document.getElementById('tyPeriod');

                if (tyPeriodEl) tyPeriodEl.textContent = periodText;



                const reference = currentBookingReference || (bookingIdField?.value ?

                    `#${bookingIdField.value}` : '-');

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



            if (tyGoHomeNowBtn) tyGoHomeNowBtn.addEventListener('click', () => {

                window.location.href = "{{ url('/') }}";

            });

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

                        openPaymentBtn.textContent = openPaymentBtn.dataset.originalLabel ||

                            openPaymentDefaultLabel;

                    }

                };



                openPaymentBtn.addEventListener('click', async () => {

                    if (typeof window.enableSelectedAddonHiddenFields === 'function') window

                        .enableSelectedAddonHiddenFields();



                    if (bookingCreationInFlight) {

                        return;

                    }



                    if (!bookingIdField?.value && bookingForm) {

                        bookingCreationInFlight = true;

                        setOpenPaymentLoading(true);



                        const formData = new FormData(bookingForm);

                        if (!bookingIdField?.value) {

                            formData.delete('booking_id');

                        }

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



                    if (!bookingIdField?.value) {

                        return;

                    }



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

                        const res = await fetch(

                            `/payfast/booking/init/${encodeURIComponent(bookingId)}`, {

                                method: 'POST',

                                headers: {

                                    'Content-Type': 'application/json',

                                    'X-CSRF-TOKEN': document.querySelector(

                                        'meta[name="csrf-token"]')?.content || ''

                                },

                                body: JSON.stringify({

                                    booking_id: bookingId

                                })

                            });

                        const data = await res.json();

                        if (!res.ok || !data?.success) throw new Error(data?.message ||

                            'Failed to prepare PayFast checkout.');



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

                        await Swal.fire({

                            icon: 'error',

                            title: 'PayFast error',

                            text: err.message || 'Could not redirect to PayFast.'

                        });

                        if (bookingPaymentModalEl) new bootstrap.Modal(bookingPaymentModalEl).show();

                        e.target.checked = false;

                    } finally {

                        hidePaymentLoader();

                    }

                }

            });



            // Stripe mount

            const stripePublicKeyJS =

                "{{ $stripeConfig->stripe_key ?? '' }}"; // (local shadow to avoid accidental edits)

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

                            if (bookingCardErrorsEl) bookingCardErrorsEl.textContent = error.message ||

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

                                text: data?.message || 'Server error while processing payment.'

                            });

                            return;

                        }



                        if (data.success) {

                            bootstrap.Modal.getInstance(bookingStripeModalEl)?.hide();

                            populateThankYouModal('Stripe');

                            if (bookingThankYouModalEl) new bootstrap.Modal(bookingThankYouModalEl)

                                .show();

                            return;

                        }



                        if (data.requires_action && data.payment_intent_client_secret) {

                            const result = await stripeInstance.confirmCardPayment(data

                                .payment_intent_client_secret);

                            if (result.error) {

                                await Swal.fire({

                                    icon: 'error',

                                    title: 'Authentication failed',

                                    text: result.error.message || 'Unable to confirm your card.'

                                });

                            } else {

                                bootstrap.Modal.getInstance(bookingStripeModalEl)?.hide();

                                populateThankYouModal('Stripe');

                                if (bookingThankYouModalEl) new bootstrap.Modal(bookingThankYouModalEl)

                                    .show();

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

               SUMMARY (same logic)

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

                            text: 'Use digits with optional spaces or dashes, e.g. +27 123 456 7890.'

                        });

                        phone.focus();

                        return;

                    }



                    const unitH = document.getElementById('inputRentalUnit');

                    const startH = document.getElementById('inputRentalStartDate');

                    const extraH = document.getElementById('inputExtraDays');

                    const totalH = document.getElementById('inputTotalPrice');



                    const typeLabel = ({

                        day: 'Daily',

                        week: 'Weekly',

                        month: 'Monthly'

                    })[unitH.value] || (unitH.value || 'N/A');

                    document.getElementById('summaryType').textContent = typeLabel;



                    let vehiclePeriod = '';

                    if (startH && startH.value) {

                        vehiclePeriod = niceDate(startH.value);

                        if (extraH && (unitH.value === 'week' || unitH.value === 'month')) {

                            vehiclePeriod += ` + ${extraH.value || 0} extra day(s)`;

                        }

                    }

                    document.getElementById('summaryPeriod').textContent = vehiclePeriod || 'N/A';

                    document.getElementById('summaryVehicleTotal').textContent = money(totalH ? totalH

                        .value : 0);



                    // add-ons summary

                    let addonTotal = 0,

                        listHtml = '';

                    document.querySelectorAll('.addon-card').forEach(card => {

                        const id = card.dataset.id;

                        const nm = card.dataset.name || 'Add-on';

                        const totalHid = card.querySelector(`#addon-total-${id}`);

                        const qtyHid = card.querySelector(`#addon-quantity-${id}`);

                        const typeHid = card.querySelector(`#addon-type-${id}`);

                        const startHid = card.querySelector(`#addon-start-${id}`);

                        const endHid = card.querySelector(`#addon-end-${id}`);

                        const daysHid = card.querySelector(`#addon-days-${id}`);

                        if (totalHid && totalHid.disabled) return;



                        const total = parseFloat(totalHid?.value || '0');

                        if (total > 0) {

                            addonTotal += total;

                            listHtml += `

            <div class="d-flex justify-content-between align-items-start mb-1">

              <div>

                <div class="fw-semibold">${nm}</div>

                <div class="text-muted small">${(typeHid?.value||'-').toUpperCase()} | Qty ${qtyHid?.value||1} | ${niceDate(startHid?.value||'')} to ${niceDate(endHid?.value||'')} (${daysHid?.value||0} days)</div>

              </div>

              <div class="fw-semibold">${money(total)}</div>

            </div>`;

                        }

                    });

                    if (!listHtml) listHtml = '<span class="text-muted">No add-ons selected.</span>';

                    document.getElementById('summaryAddOnList').innerHTML = listHtml;

                    document.getElementById('summaryAddonTotal').textContent = money(addonTotal);



                    const vehicleTotal = parseFloat(totalH?.value || '0');

                    document.getElementById('summaryGrandTotal').textContent = money(vehicleTotal +

                        addonTotal);



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



            // Before any real submit from server side (we still block default submits),

            // make sure selected add-ons fields are enabled so they're included in FormData.

            const formEl = document.getElementById('bookingForm');

            if (formEl) {

                formEl.addEventListener('submit', () => {

                    enableSelectedAddonHiddenFields();

                });

            }

        });
    </script>





    <script>
        /* ---------- THANK YOU: population + actions ---------- */

        (function() {

            const bookingThankYouModalEl = document.getElementById('bookingThankYou');

            const vehiclesUrl = "{{ url('/vehicles') }}"; // change if your route differs

            const homeUrl = "{{ url('/') }}";

            const whatsappBase = "https://wa.me/27612345678";

            let currentBookingReference = window.currentBookingReference || null;




            const moneyFmt = (v) => (typeof money === 'function') ?

                money(v) :

                `R${Number(v||0).toFixed(2)}`;



            // build WhatsApp text with encoded reference

            const buildWhatsappHref = (refText) => {

                const txt = `Hi! I just completed my booking (Reference: ${refText}) and need assistance.`;

                const url = new URL(whatsappBase);

                url.searchParams.set('text', txt);

                return url.toString();

            };



            // public: allow other code to set the booking reference before showing

            window.setBookingReference = (ref) => {

                currentBookingReference = ref || currentBookingReference;

            };



            // main population

            window.populateThankYouModal = (methodLabel) => {

                if (!bookingThankYouModalEl) return;



                // Vehicle name (from Blade)

                const tyVehicleNameEl = document.getElementById('tyVehicleName');

                if (tyVehicleNameEl) tyVehicleNameEl.textContent = "{{ addslashes($vehicle->name) }}";



                // Optional subtitle (e.g. "1990 Land Rover Defender 90") - fill if you have it

                const tyVehicleSubEl = document.getElementById('tyVehicleSub');

                if (tyVehicleSubEl) tyVehicleSubEl.textContent = "{{ addslashes($vehicle->model ?? '') }}";



                // Period (we reuse your summary text)

                const periodText = document.getElementById('summaryPeriod')?.textContent?.trim() || 'N/A';

                const tyPeriodEl = document.getElementById('tyPeriod');

                if (tyPeriodEl) tyPeriodEl.textContent = periodText;



                // Reference (prefer server "reference", fallback to #bookingId)

                const bookingIdField = document.getElementById('bookingId');

                const reference = currentBookingReference || (bookingIdField?.value ? `#${bookingIdField.value}` :

                    'N/A');

                const tyReferenceEl = document.getElementById('tyReference');

                if (tyReferenceEl) tyReferenceEl.textContent = reference;



                // Amount + method

                const tyAmountEl = document.getElementById('tyAmount');

                if (tyAmountEl) tyAmountEl.textContent = moneyFmt(typeof computeGrandTotal === 'function' ?

                    computeGrandTotal() : 0);



                const tyMethodEl = document.getElementById('tyMethod');

                if (tyMethodEl) tyMethodEl.textContent = methodLabel || 'N/A';



                // Customer

                const bookingForm = document.getElementById('bookingForm');

                const tyCustomerNameEl = document.getElementById('tyCustomerName');

                if (tyCustomerNameEl) tyCustomerNameEl.textContent = bookingForm?.name?.value?.trim() || 'N/A';



                const contactParts = [];

                if (bookingForm?.email?.value) contactParts.push(bookingForm.email.value.trim());

                if (bookingForm?.phone?.value) contactParts.push(bookingForm.phone.value.trim());

                const tyCustomerContactEl = document.getElementById('tyCustomerContact');

                if (tyCustomerContactEl) tyCustomerContactEl.textContent = contactParts.join(' | ') ||

                    'N/A';



                // WhatsApp link

                const wa = document.getElementById('tyWhatsappBtn');

                if (wa) wa.href = buildWhatsappHref(reference);
                // Continue to vehicles button
                const cont = document.getElementById('tyContinueVehicles');
                if (cont) cont.onclick = () => {
                    window.location.href = vehiclesUrl;
                };
                bookingThankYouModalEl.addEventListener('hidden.bs.modal', () => {
                    window.location.href = vehiclesUrl;
                }, {
                    once: true
                });
            };
            window.showThankYou = (methodLabel) => {
                window.populateThankYouModal(methodLabel);
                const m = bootstrap.Modal.getOrCreateInstance(bookingThankYouModalEl);
                m.show()
            };
        })();
    </script>
