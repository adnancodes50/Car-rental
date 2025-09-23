{{-- resources/views/show.blade.php --}}
@extends('layouts.frontend')

@section('title', $vehicle->name)

@section('content')
    <div class="container py-4 py-lg-5 mt-5">
        <!-- Back link -->
        <a href="{{ url('/') }}" class="text-muted mb-4 d-inline-flex align-items-center">
            <i class="bi bi-arrow-left me-2"></i> Back to vehicles
        </a>

        <div class="row g-4 g-lg-5">
            <!-- Gallery -->
            <div class="col-12 col-md-6">
                <div class="card border-0 shadow-sm mb-3">
                    <img id="mainImage" src="{{ $vehicle->mainImage() }}" class="card-img-top rounded img-fluid"
                        alt="{{ $vehicle->name }}" style="object-fit: cover; max-height: 380px;">
                </div>

                @if($vehicle->images->count() > 0)
                    <div class="d-flex flex-wrap gap-2">
                        <img src="{{ $vehicle->mainImage() }}" class="img-thumbnail"
                            style="width: 80px; height: 60px; object-fit: cover; cursor: pointer;"
                            onclick="document.getElementById('mainImage').src=this.src">
                        @foreach($vehicle->images as $image)
                            <img src="{{ $image->url }}" class="img-thumbnail"
                                style="width: 80px; height: 60px; object-fit: cover; cursor: pointer;"
                                onclick="document.getElementById('mainImage').src=this.src">
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Vehicle details -->
            <div class="col-12 col-md-6">
                <div class="card border-0 shadow-sm p-3 p-lg-4 rounded-4">
                    <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                        <div>
                            <h2 class="h4 h-md-2">{{ $vehicle->name }}</h2>
                            <p class="text-muted small mb-2">{{ $vehicle->year }} {{ $vehicle->model }}</p>
                        </div>
                        <span class="badge rounded-pill bg-success-subtle text-success">
                            {{ ucfirst($vehicle->status ?? 'Available') }}
                        </span>
                    </div>

                    <p class="text-muted small mb-3">{{ $vehicle->description }}</p>

                    <!-- Pricing -->
                    <div class="row text-center g-2 mb-4">
                        @if($vehicle->rental_price_day)
                            <div class="col-6 col-lg-4">
                                <div class="border rounded p-2">
                                    <div class="fw-semibold">R{{ number_format($vehicle->rental_price_day) }}/day</div>
                                    <small class="text-muted">Daily</small>
                                </div>
                            </div>
                        @endif
                        @if($vehicle->rental_price_week)
                            <div class="col-6 col-lg-4">
                                <div class="border rounded p-2">
                                    <div class="fw-semibold">R{{ number_format($vehicle->rental_price_week) }}/week</div>
                                    <small class="text-muted">Weekly</small>
                                </div>
                            </div>
                        @endif
                        @if($vehicle->rental_price_month)
                            <div class="col-6 col-lg-4">
                                <div class="border rounded p-2">
                                    <div class="fw-semibold">R{{ number_format($vehicle->rental_price_month) }}/month</div>
                                    <small class="text-muted">Monthly</small>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="d-flex flex-column flex-md-row gap-3 mb-4">
                        <button type="button" class="btn btn-warning text-black w-100" style="padding: 15px;"
                            data-bs-toggle="modal" data-bs-target="#multiStepBookingModal">
                            <i class="bi bi-calendar-check me-2"></i> Book this {{ $vehicle->name }}
                        </button>
                        @if($vehicle->purchase_price)
                            <a href="#" class="btn btn-dark w-100 d-flex align-items-center justify-content-center"
                                data-bs-toggle="modal" data-bs-target="#purchaseModal">
                                Purchase (R{{ number_format($vehicle->purchase_price) }})
                            </a>
                        @endif
                    </div>

                    <!-- Specs -->
                    <div class="row g-3 text-muted small">
                        @if($vehicle->engine)
                            <div class="col-6"><i class="bi bi-gear-fill me-2"></i><strong>Engine:</strong>
                                {{ $vehicle->engine }}</div>
                        @endif
                        @if($vehicle->transmission)
                            <div class="col-6"><i class="bi bi-gear-wide-connected me-2"></i><strong>Transmission:</strong>
                                {{ $vehicle->transmission }}</div>
                        @endif
                        @if($vehicle->seats)
                            <div class="col-6"><i class="bi bi-people-fill me-2"></i><strong>Seating:</strong>
                                {{ $vehicle->seats }}</div>
                        @endif
                        @if($vehicle->fuel_type)
                            <div class="col-6"><i class="bi bi-fuel-pump-fill me-2"></i><strong>Fuel:</strong>
                                {{ $vehicle->fuel_type }}</div>
                        @endif
                        @if($vehicle->location)
                            <div class="col-6"><i class="bi bi-geo-alt-fill me-2"></i><strong>Location:</strong>
                                {{ $vehicle->location }}</div>
                        @endif
                        @if($vehicle->mileage)
                            <div class="col-6"><i class="bi bi-speedometer me-2"></i><strong>Mileage:</strong>
                                {{ number_format($vehicle->mileage) }} km</div>
                        @endif
                    </div>

                    <!-- Features -->
                    <div class="mt-4">
                        <h5 class="fw-bold">Features & Equipment</h5>
                        <div class="row row-cols-2 row-cols-md-2 row-cols-lg-3 g-2 small">
                            @if(!empty($vehicle->features) && is_array($vehicle->features))
                                @foreach($vehicle->features as $feature)
                                    @if($feature)
                                        <div class="col d-flex align-items-center">
                                            <i class="bi bi-check-circle-fill me-2 text-secondary"></i>
                                            {{ ucfirst($feature) }}
                                        </div>
                                    @endif
                                @endforeach
                            @else
                                <div class="col-12 text-muted">No features available</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- BOOKING MODALS --}}
        @php
            $countries = [
                "Afghanistan",
                "Albania",
                "Algeria",
                "Andorra",
                "Angola",
                "Antigua and Barbuda",
                "Argentina",
                "Armenia",
                "Australia",
                "Austria",
                "Azerbaijan",
                "Bahamas",
                "Bahrain",
                "Bangladesh",
                "Barbados",
                "Belarus",
                "Belgium",
                "Belize",
                "Benin",
                "Bhutan",
                "Bolivia",
                "Bosnia and Herzegovina",
                "Botswana",
                "Brazil",
                "Brunei",
                "Bulgaria",
                "Burkina Faso",
                "Burundi",
                "Cabo Verde",
                "Cambodia",
                "Cameroon",
                "Canada",
                "Central African Republic",
                "Chad",
                "Chile",
                "China",
                "Colombia",
                "Comoros",
                "Congo (Congo-Brazzaville)",
                "Costa Rica",
                "Croatia",
                "Cuba",
                "Cyprus",
                "Czechia",
                "Democratic Republic of the Congo",
                "Denmark",
                "Djibouti",
                "Dominica",
                "Dominican Republic",
                "Ecuador",
                "Egypt",
                "El Salvador",
                "Equatorial Guinea",
                "Eritrea",
                "Estonia",
                "Eswatini",
                "Ethiopia",
                "Fiji",
                "Finland",
                "France",
                "Gabon",
                "Gambia",
                "Georgia",
                "Germany",
                "Ghana",
                "Greece",
                "Grenada",
                "Guatemala",
                "Guinea",
                "Guinea-Bissau",
                "Guyana",
                "Haiti",
                "Holy See",
                "Honduras",
                "Hungary",
                "Iceland",
                "India",
                "Indonesia",
                "Iran",
                "Iraq",
                "Ireland",
                "Israel",
                "Italy",
                "Jamaica",
                "Japan",
                "Jordan",
                "Kazakhstan",
                "Kenya",
                "Kiribati",
                "Kuwait",
                "Kyrgyzstan",
                "Laos",
                "Latvia",
                "Lebanon",
                "Lesotho",
                "Liberia",
                "Libya",
                "Liechtenstein",
                "Lithuania",
                "Luxembourg",
                "Madagascar",
                "Malawi",
                "Malaysia",
                "Maldives",
                "Mali",
                "Malta",
                "Marshall Islands",
                "Mauritania",
                "Mauritius",
                "Mexico",
                "Micronesia",
                "Moldova",
                "Monaco",
                "Mongolia",
                "Montenegro",
                "Morocco",
                "Mozambique",
                "Myanmar",
                "Namibia",
                "Nauru",
                "Nepal",
                "Netherlands",
                "New Zealand",
                "Nicaragua",
                "Niger",
                "Nigeria",
                "North Korea",
                "North Macedonia",
                "Norway",
                "Oman",
                "Pakistan",
                "Palau",
                "Palestine State",
                "Panama",
                "Papua New Guinea",
                "Paraguay",
                "Peru",
                "Philippines",
                "Poland",
                "Portugal",
                "Qatar",
                "Romania",
                "Russia",
                "Rwanda",
                "Saint Kitts and Nevis",
                "Saint Lucia",
                "Saint Vincent and the Grenadines",
                "Samoa",
                "San Marino",
                "Sao Tome and Principe",
                "Saudi Arabia",
                "Senegal",
                "Serbia",
                "Seychelles",
                "Sierra Leone",
                "Singapore",
                "Slovakia",
                "Slovenia",
                "Solomon Islands",
                "Somalia",
                "South Africa",
                "South Korea",
                "South Sudan",
                "Spain",
                "Sri Lanka",
                "Sudan",
                "Suriname",
                "Sweden",
                "Switzerland",
                "Syria",
                "Tajikistan",
                "Tanzania",
                "Thailand",
                "Timor-Leste",
                "Togo",
                "Tonga",
                "Trinidad and Tobago",
                "Tunisia",
                "Turkey",
                "Turkmenistan",
                "Tuvalu",
                "Uganda",
                "Ukraine",
                "United Arab Emirates",
                "United Kingdom",
                "United States",
                "Uruguay",
                "Uzbekistan",
                "Vanuatu",
                "Venezuela",
                "Vietnam",
                "Yemen",
                "Zambia",
                "Zimbabwe"
            ];
        @endphp

        {{-- Booking FORM (only data collection — we won’t submit to go to payment) --}}
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
            <div class="modal fade " id="multiStepBookingModal" tabindex="-1" aria-hidden="true" style="height: 90vh; margin-top: 4rem;">
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
                                @if($vehicle->rental_price_day)
                                    <div class="col-md-4">
                                        <div class="option-card p-3 border rounded-4 bg-light h-100" data-type="day"
                                            data-price="{{ $vehicle->rental_price_day }}">
                                            <i class="bi bi-clock display-6 text-warning"></i>
                                            <h6 class="mt-2">Daily Rental</h6>
                                            <p class="small text-muted mb-1">Perfect for short trips</p>
                                            <div class="text-dark">R{{ number_format($vehicle->rental_price_day) }}/day</div>
                                        </div>
                                    </div>
                                @endif
                                @if($vehicle->rental_price_week)
                                    <div class="col-md-4">
                                        <div class="option-card p-3 border rounded-4 h-100" data-type="week"
                                            data-price="{{ $vehicle->rental_price_week }}">
                                            <i class="bi bi-calendar-event display-6 text-warning"></i>
                                            <h6 class="mt-2">Weekly Rental</h6>
                                            <p class="small text-muted mb-1">Great for 1–4 weeks</p>
                                            <div class="text-dark">R{{ number_format($vehicle->rental_price_week) }}/week</div>
                                        </div>
                                    </div>
                                @endif
                                @if($vehicle->rental_price_month)
                                    <div class="col-md-4">
                                        <div class="option-card p-3 border rounded-4 h-100" data-type="month"
                                            data-price="{{ $vehicle->rental_price_month }}">
                                            <i class="bi bi-box display-6 text-warning"></i>
                                            <h6 class="mt-2">Monthly Rental</h6>
                                            <p class="small text-muted mb-1">Best for long stays</p>
                                            <div class="text-dark">R{{ number_format($vehicle->rental_price_month) }}/month
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
            <div class="modal fade mt-5" id="addonsStep" tabindex="-1" aria-hidden="true" style="height: 90vh; margin-top: 4rem;">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content rounded-4 shadow-lg">
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold"><i class="bi bi-box-seam"></i> Select Add-Ons</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            @foreach($addOns as $addOn)
                                <div
                                    class="d-flex align-items-center justify-content-between border rounded p-3 mb-3 shadow-sm">
     <div class="me-3">
    <img src="{{ asset($addOn->image_url) }}"
         alt="{{ $addOn->name }}"
         class="rounded border"
         style="width:60px; height:60px; object-fit:cover;">
</div>


                                    <div class="flex-grow-1">
                                        <h5 class="fw-semibold mb-1">{{ $addOn->name }}</h5>
                                        <p class="text-muted mb-1">{{ $addOn->description }}</p>
                                        <p class="small text-muted mb-1">
                                            R{{ $addOn->price_day }}/day • R{{ $addOn->price_week }}/week •
                                            R{{ $addOn->price_month }}/month
                                        </p>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-success mb-2">{{ $addOn->qty_total }} available</span>
                                        <select class="form-select form-select-sm" name="add_ons[{{ $addOn->id }}]">
                                            @for($i = 0; $i <= $addOn->qty_total; $i++)
                                                <option value="{{ $i }}">{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="modal-footer">
                            <div class="d-flex justify-content-between w-100">
                                <button type="button" class="btn btn-outline-secondary"
                                    data-bs-target="#multiStepBookingModal" data-bs-toggle="modal">Back</button>
                                <button type="button" class="btn btn-dark rounded-3" data-bs-target="#customerStep"
                                    data-bs-toggle="modal">Continue to Details</button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Step 3: Customer Details Modal -->
            <div class="modal fade" id="customerStep" tabindex="-1" aria-hidden="true" style="margin-bottom: 10rem">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content rounded-4 shadow">

      <!-- Header -->
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold d-flex align-items-center">
          <i class="bi bi-person-circle me-2"></i>
          Enter Your Details
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <hr>



      <!-- Body -->
      <div class="modal-body px-4">
        <div class="row g-3">

          <div class="col-12">
            <label class="form-label">Full Name</label>
            <input type="text" class="form-control rounded-3" name="name" placeholder="John Doe" required>
          </div>

          <div class="col-12">
            <label class="form-label">Email</label>
            <input type="email" class="form-control rounded-3" name="email" placeholder="you@example.com" required>
          </div>

          <div class="col-12">
            <label class="form-label">Phone Number</label>
            <input type="tel" class="form-control rounded-3" name="phone" placeholder="+27 123 456 7890" required>
          </div>

          <div class="col-12">
            <label class="form-label">Country</label>
            <select name="country" class="form-select rounded-3" required>
              <option value="" disabled selected>Select your country</option>
              @foreach($countries as $country)
                <option value="{{ $country }}">{{ $country }}</option>
              @endforeach
            </select>
          </div>

        </div>
      </div>

      <!-- Footer -->
      <div class="modal-footer border-0 d-flex justify-content-between">
        <button type="button" class="btn btn-outline-secondary rounded-3"
          data-bs-target="#addonsStep" data-bs-toggle="modal">
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
            <div class="modal fade mt-5" id="summaryStep" tabindex="-1" aria-hidden="true" style="height: 90vh; margin-top: 4rem;">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content rounded-4 shadow-lg">
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold"><i class="bi bi-clipboard-check me-2"></i> Booking Summary</h5>
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
                                <h6 class="fw-semibold">Price Breakdown</h6>
                                <div class="d-flex justify-content-between">
                                    <span class="fw-semibold">Total</span>
                                    <span class="fw-bold text-success" id="summaryPrice"></span>
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
                                        <p class="small text-muted mb-1">Country</p>
                                        <p class="fw-bold" id="summaryCustomerCountry"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div class="d-flex justify-content-between w-100">
                                <button type="button" class="btn btn-outline-secondary" data-bs-target="#customerStep"
                                    data-bs-toggle="modal">Back</button>

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


        <!-- Step 5a: Payment Method (centered, taller, pretty) -->

        <div class="modal fade" id="bookingPayment" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content rounded-4 shadow">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold w-100 text-center">Select Payment Method</h5>
                        <button type="button" class="btn-close position-absolute end-0 me-3"
                            data-bs-dismiss="modal"></button>
                    </div>

                   <div class="modal-body">
  <div class="row g-3 align-items-stretch justify-content-center">
    <!-- Stripe -->
    <div class="col-12 col-md-6">
      <input
        type="radio"
        name="booking_payment_method"
        id="bookingStripe"
        value="stripe"
        class="btn-check"
        autocomplete="off"
      >
      <label for="bookingStripe" class="card btn w-100 booking-pay-option">
        <div class="booking-icon-wrap">
          <!-- Swap to a logo if you prefer -->
          <i class="bi bi-credit-card"></i>
          <!-- <img src="/images/payments/stripe.svg" alt="Stripe"> -->
        </div>
        <div class="booking-pay-text">
          <div class="fw-bold">Stripe (Card)</div>
          <small class="text-muted">Visa • Mastercard • Amex</small>
        </div>
      </label>
    </div>

    <!-- PayFast -->
    <div class="col-12 col-md-6">
      <input
        type="radio"
        name="booking_payment_method"
        id="bookingPayfast"
        value="payfast"
        class="btn-check"
        autocomplete="off"
      >
      <label for="bookingPayfast" class="card btn w-100 booking-pay-option">
        <div class="booking-icon-wrap">
          <i class="bi bi-lightning-charge"></i>
          <!-- <img src="/images/payments/payfast.svg" alt="PayFast"> -->
        </div>
        <div class="booking-pay-text">
          <div class="fw-bold">PayFast</div>
          <small class="text-muted">South Africa payments</small>
        </div>
      </label>
    </div>
  </div>
</div>


                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal"
                            data-bs-target="#summaryStep">
                            Back
                        </button>
                        <!-- Optional: proceed button if you want an explicit Next -->
                        <!-- <button type="button" class="btn btn-dark" id="proceedAfterMethod">Continue</button> -->
                    </div>
                </div>
            </div>
        </div>


        <!-- Step 5b: Stripe Card -->
        <div class="modal fade" id="bookingStripeModal" tabindex="-1" aria-hidden="true" style="margin-top: 4rem; height:90vh;">
            <div class="modal-dialog modal-md modal-dialog-centered">
                <div class="modal-content rounded-4 shadow">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Stripe Payment</h5>
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
                        <button type="button" class="btn btn-outline-secondary me-auto" data-bs-toggle="modal"
                            data-bs-target="#bookingPayment">
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
                <div class="modal-content rounded-4 shadow text-center p-4 border-0">
                    <div class="modal-body">
                        <h4 class="fw-bold mb-2 text-success">Thank You!</h4>
                        <p class="text-muted mb-4">Your booking payment has been received.</p>
                        <button type="button" class="btn btn-success fw-bold px-4 rounded-pill"
                            data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>



        <style>
/* Scope to the booking modal by ID to avoid clashes */
#bookingPayment .booking-pay-option {
  min-height: 160px;                     /* tweak height to taste */
  display: flex;
  align-items: center;
  justify-content: center;               /* center icon + text as a block */
  gap: 12px;                             /* space between icon and text */
  border: 1px solid #dee2e6;
  border-radius: .75rem;
  padding: 20px;
  text-align: left;
  transition: box-shadow .15s ease, transform .15s ease, border-color .15s ease;
}

#bookingPayment .booking-pay-option:hover {
  transform: translateY(-2px);
  box-shadow: 0 .5rem 1rem rgba(0,0,0,.08);
}

/* Selected state when radio is checked */
#bookingPayment .btn-check:checked + .booking-pay-option {
  border-color: #0d6efd;
  box-shadow: 0 0 0 .25rem rgba(13,110,253,.2);
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

        {{-- PURCHASE MODALS (unchanged) --}}
        @include('models.purchase')
    </div>
@endsection


@push('css')

    <style>
        /* Equal height, centered option cards */
        #bookingPayment .pay-option {
            min-height: 600px;
            /* increase card height */
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: .5rem;
        }

        #bookingPayment .pay-option .icon-wrap {
            width: 56px;
            /* icon size */
            height: 56px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            /* subtle bg behind icon */
            border: 1px solid #e9ecef;
        }

        #bookingPayment .pay-option img,
        #bookingPayment .pay-option i {
            width: 32px;
            /* inner icon size */
            height: 32px;
            object-fit: contain;
        }

        /* Nice focus/selected state */
        #bookingPayment .btn-check:checked+.card {
            border-color: #0d6efd;
            box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .15);
        }

        #bookingPayment .card {
            transition: box-shadow .15s ease, transform .15s ease, border-color .15s ease;
        }

        #bookingPayment .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .08);
        }
    </style>


@endpush

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        /* ------------------------ Date helpers ------------------------ */

        function parseYMD(ymd) { const [y, m, d] = ymd.split('-').map(Number); return new Date(y, m - 1, d, 0, 0, 0, 0); }
        function toMidnight(val) { if (typeof val === 'string') return parseYMD(val); return new Date(val.getFullYear(), val.getMonth(), val.getDate(), 0, 0, 0, 0); }
        function addDays(d, n) { return new Date(d.getFullYear(), d.getMonth(), d.getDate() + n, 0, 0, 0, 0); }
        function fmt(d) { return d.toLocaleDateString("en-GB", { day: "2-digit", month: "short", year: "numeric" }); }

        // Inclusive month math: start + qty months (exclusive) - 1 day + extras
        function addMonthsInclusive(start, months, extraDays) {
            const exclusive = new Date(start.getFullYear(), start.getMonth() + months, start.getDate(), 0, 0, 0, 0);
            return toMidnight(addDays(exclusive, -1 + (extraDays || 0)));
        }

        /* ----------------- Disable & check booked ranges ---------------- */
        const rawRanges = @json($bookedRanges ?? []);
        const bookedRanges = rawRanges.map(r => ({ from: toMidnight(r.from), to: toMidnight(r.to) }));
        function hasOverlap(a1, a2) { return bookedRanges.some(({ from, to }) => a1 <= to && a2 >= from); }

        /* ------------------------ Grab elements ------------------------ */
        const optionCards = document.querySelectorAll(".option-card");
        const dateSection = document.getElementById("dateSection");
        const quantitySection = document.getElementById("quantitySection");
        const quantitySelect = document.getElementById("rentalQuantity");
        const rentalStartDate = document.getElementById("rentalStartDate");
        const totalPriceDiv = document.getElementById("totalPrice");
        const rentalPeriodDiv = document.getElementById("rentalPeriod");

        const inputRentalUnit = document.getElementById("inputRentalUnit");
        const inputRentalQuantity = document.getElementById("inputRentalQuantity");
        const inputRentalStartDate = document.getElementById("inputRentalStartDate");
        const inputTotalPrice = document.getElementById("inputTotalPrice");
        const bookingIdField = document.getElementById("bookingId");

        let inputExtraDays = document.getElementById("inputExtraDays");
        if (!inputExtraDays) {
            inputExtraDays = document.createElement("input");
            inputExtraDays.type = "hidden"; inputExtraDays.name = "extra_days"; inputExtraDays.id = "inputExtraDays"; inputExtraDays.value = 0;
            document.getElementById("bookingForm").appendChild(inputExtraDays);
        }

        // Summary
        const summaryType = document.getElementById("summaryType");
        const summaryPeriod = document.getElementById("summaryPeriod");
        const summaryPrice = document.getElementById("summaryPrice");
        const summaryCustomerName = document.getElementById("summaryCustomerName");
        const summaryCustomerEmail = document.getElementById("summaryCustomerEmail");
        const summaryCustomerPhone = document.getElementById("summaryCustomerPhone");
        const summaryCustomerCountry = document.getElementById("summaryCustomerCountry");

        /* ----------------------- Flatpickr init ------------------------ */
        const fp = flatpickr("#rentalStartDate", {
            dateFormat: "Y-m-d",
            minDate: "today",
            disable: [
                (date) => {
                    const d = toMidnight(date);
                    return bookedRanges.some(({ from, to }) => d >= from && d <= to);
                }
            ],
            onChange: () => {
                // When a date is chosen → reveal the rest & compute
                revealAfterDate();
                calculateTotal();
            }
        });

        /* ---------------------- State & helpers ------------------------ */
        let selectedType = "";   // 'day' | 'week' | 'month'
        let selectedPrice = 0;
        let totalPrice = 0;
        let rentalPeriodText = "";

        // Start state: hide everything except the 3 cards
        function resetVisibility() {
            // Date only (hidden until a type is picked)
            dateSection.classList.add("d-none");
            // Hidden until a date is picked
            quantitySection.classList.add("d-none");
            totalPriceDiv.classList.add("d-none");
            rentalPeriodDiv.classList.add("d-none");
        }
        resetVisibility();

        // After type is picked, show only the date picker
        function revealDateOnly() {
            dateSection.classList.remove("d-none");
            quantitySection.classList.add("d-none");
            totalPriceDiv.classList.add("d-none");
            rentalPeriodDiv.classList.add("d-none");
        }

        // After date is picked, show quantity/extra + totals/period
        function revealAfterDate() {
            quantitySection.classList.remove("d-none");
            totalPriceDiv.classList.remove("d-none");
            rentalPeriodDiv.classList.remove("d-none");
        }

        function buildQuantityAndExtras() {
            quantitySection.innerHTML = "";
            const wrapper = document.createElement("div");
            wrapper.className = "d-flex align-items-end gap-2 flex-wrap";

            // Quantity
            const qCol = document.createElement("div");
            qCol.style.flex = "1 1 220px";
            qCol.innerHTML = `
      <label class="form-label mb-1">${selectedType === 'day' ? 'Number of Days' : selectedType === 'week' ? 'Number of Weeks' : 'Number of Months'}</label>
    `;
            quantitySelect.innerHTML = "";
            const maxQty = (selectedType === "day") ? 6 : 4;
            for (let i = 1; i <= maxQty; i++) {
                const opt = document.createElement("option");
                opt.value = i;
                opt.textContent = i + " " + (selectedType === 'day' ? 'day' : selectedType === 'week' ? 'week' : 'month') + (i > 1 ? 's' : '');
                quantitySelect.appendChild(opt);
            }
            qCol.appendChild(quantitySelect);
            wrapper.appendChild(qCol);

            // Extra days (only week/month)
            if (selectedType !== "day") {
                const extraCol = document.createElement("div");
                extraCol.style.flex = "1 1 220px";
                extraCol.innerHTML = `<label class="form-label mb-1">Extra Days</label>`;
                const extraSelect = document.createElement("select");
                extraSelect.id = "extraDays";
                extraSelect.className = "form-select";
                for (let i = 0; i <= 6; i++) {
                    const opt = document.createElement("option");
                    opt.value = i; opt.textContent = i === 0 ? "0 days" : (i === 1 ? "1 day" : `${i} days`);
                    extraSelect.appendChild(opt);
                }
                extraSelect.addEventListener("change", calculateTotal);
                extraCol.appendChild(extraSelect);
                wrapper.appendChild(extraCol);
            }

            quantitySection.appendChild(wrapper);
        }

        function calculateTotal() {
            const startStr = rentalStartDate.value;
            if (!startStr || !selectedType) {
                // nothing chosen yet
                totalPriceDiv.classList.add("d-none");
                rentalPeriodDiv.classList.add("d-none");
                return;
            }

            const qty = parseInt(quantitySelect.value || "1", 10);
            const extraSel = document.getElementById("extraDays");
            const extraDays = extraSel ? parseInt(extraSel.value || "0", 10) : 0;
            inputExtraDays.value = extraDays;

            const start = toMidnight(startStr);
            let end;

            if (selectedType === "day") {
                end = addDays(start, qty + extraDays - 1);
            } else if (selectedType === "week") {
                end = addDays(start, qty * 7 + extraDays - 1);
            } else { // month
                end = addMonthsInclusive(start, qty, extraDays);
            }

            // Overlap guard
            if (hasOverlap(start, end)) {
                const clashes = bookedRanges
                    .filter(({ from, to }) => start <= to && end >= from)
                    .map(({ from, to }) => `${fmt(from)} → ${fmt(to)}`)
                    .join("<br>");
                Swal.fire({
                    icon: 'error',
                    title: 'Booking Conflict',
                    html: `
    <p>Unfortunately, your selected dates overlap with an existing reservation.</p>
    <p><strong>Unavailable Dates:</strong></p>
    <div style="text-align:center; font-size:14px;">${clashes}</div>
    <p>Please adjust your booking period and try again.</p>
  `
                });

                rentalStartDate.value = "";
                revealDateOnly(); // go back to date-only
                return;
            }

            // Pricing
            const base = qty * selectedPrice;
            let extraPrice = 0;
            if (extraDays > 0) {
                if (selectedType === "day") extraPrice = extraDays * selectedPrice;
                if (selectedType === "week") extraPrice = extraDays * (selectedPrice / 7);
                if (selectedType === "month") extraPrice = extraDays * (selectedPrice / 30);
            }
            totalPrice = Math.round(base + extraPrice);

            // UI
            rentalPeriodText = `
      <div class="d-flex justify-content-between">
        <div class="text-muted small">Start Date<br>${fmt(start)}</div>
        <div class="text-muted small">End Date<br>${fmt(end)}</div>
      </div>`;
            rentalPeriodDiv.innerHTML = rentalPeriodText;

            let costHtml = `<div style="font-size:14px;">
      ${selectedType === 'day' ? 'Days' : selectedType === 'week' ? 'Weeks' : 'Months'}:
      ${qty} × R${selectedPrice.toLocaleString()}
    </div>`;
            if (extraDays > 0) {
                const perExtra = (selectedType === 'week') ? (selectedPrice / 7) : (selectedType === 'month' ? (selectedPrice / 30) : selectedPrice);
                costHtml += `<div style="font-size:14px;">Extra Days: ${extraDays} × R${perExtra.toFixed(2)}</div>`;
            }
            costHtml += `<div class="mt-2" style="font-size:14px;">Total Cost: R${totalPrice.toLocaleString()}</div>`;
            totalPriceDiv.innerHTML = costHtml;

            // Keep hidden inputs in sync
            inputRentalUnit.value = selectedType;
            inputRentalQuantity.value = qty;
            inputRentalStartDate.value = startStr;
            inputTotalPrice.value = totalPrice;
        }

        /* ------------------- Rental type selection --------------------- */
        optionCards.forEach(card => {
            card.addEventListener("click", () => {
                // Visual select
                optionCards.forEach(c => c.classList.remove("border-warning", "bg-warning-subtle"));
                card.classList.add("border-warning", "bg-warning-subtle");

                // Set type + price
                selectedType = card.getAttribute("data-type");
                selectedPrice = parseFloat(card.getAttribute("data-price") || "0");

                // Reset date & UI for new type
                rentalStartDate.value = "";
                revealDateOnly();

                // Build quantity/extra controls (but keep hidden until a date is picked)
                buildQuantityAndExtras();

                // Open the calendar right away
                setTimeout(() => fp.open(), 0);
            });
        });

        // Recalculate when quantity changes (after date is picked)
        quantitySelect.addEventListener("change", calculateTotal);

        /* -------------------- Summary step button ---------------------- */
        document.getElementById("continueFromStep1").addEventListener("click", function () {
            if (!selectedType) {
                Swal.fire({ icon: 'error', title: 'Choose a rental type', text: 'Please select Day, Week or Month.' });
                return;
            }
            if (!rentalStartDate.value) {
                Swal.fire({ icon: 'error', title: 'Select a start date', text: 'Please pick a start date from the calendar.' });
                return;
            }
            bootstrap.Modal.getInstance(document.getElementById("multiStepBookingModal"))?.hide();
            new bootstrap.Modal(document.getElementById("addonsStep")).show();
        });

        document.getElementById("goToSummary").addEventListener("click", function () {
            const form = document.getElementById("bookingForm");
            const name = form.querySelector("input[name='name']").value;
            const email = form.querySelector("input[name='email']").value;
            const phone = form.querySelector("input[name='phone']").value;
            const country = form.querySelector("select[name='country']").value;

            if (!name || !email || !phone || !country) {
                Swal.fire({
                    icon: 'error',
                    title: 'Missing Information',
                    text: 'Please fill in all required customer details before continuing.'
                });
                return;
            }

            summaryType.textContent = selectedType;
            summaryPeriod.innerHTML = rentalPeriodText;
            summaryPrice.textContent = "R" + totalPrice.toLocaleString();
            summaryCustomerName.textContent = name;
            summaryCustomerEmail.textContent = email;
            summaryCustomerPhone.textContent = phone;
            summaryCustomerCountry.textContent = country;

            inputRentalUnit.value = selectedType;
            inputRentalQuantity.value = quantitySelect.value;
            inputRentalStartDate.value = rentalStartDate.value;
            inputTotalPrice.value = totalPrice;

            bootstrap.Modal.getInstance(document.getElementById("customerStep"))?.hide();
            new bootstrap.Modal(document.getElementById("summaryStep")).show();
        });

        /* ------------------- Payment modals & flow --------------------- */
        // Create booking then open payment method modal
        document.getElementById('openPayment').addEventListener('click', async function () {
            if (!bookingIdField.value) {
                const bookingForm = document.getElementById('bookingForm');
                const formData = new FormData(bookingForm);
                try {
                    const res = await fetch(bookingForm.action, {
                        method: "POST",
                        body: formData,
                        headers: {
                            "X-Requested-With": "XMLHttpRequest",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                        }
                    });
                    const text = await res.text();
                    let data;
                    try { data = JSON.parse(text); } catch { data = { success: false, message: text }; }

                    if (!res.ok || !data.success) {
                        Swal.fire({ icon: 'error', title: 'Oops...', text: data.message || 'Failed to create booking.' });
                        return;
                    }
                    // Accept either `booking_id` or `id` from backend
                    bookingIdField.value = data.booking_id || data.id;
                    if (!bookingIdField.value) {
                        Swal.fire({ icon: 'error', title: 'Oops...', text: 'Booking created but no ID returned.' });
                        return;
                    }
                } catch (e) {
                    console.error(e);
                    Swal.fire({ icon: 'error', title: 'Oops...', text: 'Network error while creating booking.' });
                    return;
                }
            }
            bootstrap.Modal.getInstance(document.getElementById("summaryStep"))?.hide();
            new bootstrap.Modal(document.getElementById("bookingPayment")).show();
        });

        // Choose payment method
       // Choose payment method
document.addEventListener('change', async function (e) {
  if (e.target && e.target.name === 'booking_payment_method') {
    const method = e.target.value;
    const paymentModalEl = document.getElementById("bookingPayment");
    const paymentModal   = bootstrap.Modal.getInstance(paymentModalEl);
    paymentModal?.hide();

    if (method === 'stripe') {
      new bootstrap.Modal(document.getElementById("bookingStripeModal")).show();
      return;
    }

    // ----- PAYFAST flow -----
    // We need an existing booking ID
    const bookingId = document.getElementById('bookingId').value;
    if (!bookingId) {
      await Swal.fire({ icon: 'error', title: 'No booking yet', text: 'Please create the booking first.' });
      new bootstrap.Modal(paymentModalEl).show();
      e.target.checked = false;
      return;
    }

    // Confirm with SweetAlert first
    const confirmRes = await Swal.fire({
      icon: 'question',
      title: 'Proceed with PayFast?',
      text: 'You will be redirected to PayFast to complete your payment.',
      showCancelButton: true,
      confirmButtonText: 'Continue',
      cancelButtonText: 'Back',
      reverseButtons: true,
      customClass: {
        confirmButton: 'btn btn-dark',
        cancelButton: 'btn btn-outline-secondary me-3'
      },
      buttonsStyling: false
    });

    if (!confirmRes.isConfirmed) {
      e.target.checked = false;
      new bootstrap.Modal(paymentModalEl).show();
      return;
    }

    // Call init to get PayFast action + signed fields
    try {
      const res = await fetch(`/payfast/booking/init/${encodeURIComponent(bookingId)}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        // optional buyer params to prefill at PayFast; safe to omit
        body: JSON.stringify({
          // name/email if you captured them earlier in customer step:
          name:  document.querySelector('#bookingForm [name="name"]')?.value || '',
          email: document.querySelector('#bookingForm [name="email"]')?.value || ''
        })
      });

      const data = await res.json();
      if (!res.ok || !data.success) {
        throw new Error(data.message || 'Failed to initialize PayFast.');
      }

      // Build a hidden POST form and auto-submit to PayFast
      const pfForm = document.createElement('form');
      pfForm.method = 'POST';
      pfForm.action = data.action; // sandbox or live endpoint
      pfForm.style.display = 'none';

      Object.entries(data.fields).forEach(([k, v]) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = k;
        input.value = v;
        pfForm.appendChild(input);
      });

      document.body.appendChild(pfForm);
      pfForm.submit(); // 🚀 go to PayFast
    } catch (err) {
      console.error(err);
      await Swal.fire({ icon: 'error', title: 'PayFast error', text: err.message || 'Could not redirect to PayFast.' });
      e.target.checked = false;
      new bootstrap.Modal(paymentModalEl).show();
    }
  }
});


        /* ----------------------- Stripe checkout ----------------------- */
        const stripe = Stripe("{{ config('services.stripe.key') }}");
        const elements = stripe.elements();
        const style = { base: { fontSize: '16px', color: '#32325d', '::placeholder': { color: '#a0aec0' } } };
        const cardNumber = elements.create('cardNumber', { style });
        const cardExpiry = elements.create('cardExpiry', { style });
        const cardCvc = elements.create('cardCvc', { style });
        cardNumber.mount('#booking-card-number');
        cardExpiry.mount('#booking-card-expiry');
        cardCvc.mount('#booking-card-cvc');

        document.getElementById("bookingStripePayButton").addEventListener("click", async function () {
            const form = document.getElementById("bookingForm");
            const bookingId = bookingIdField.value;

            if (!bookingId) {
                Swal.fire({ icon: 'error', title: 'Oops...', text: 'Booking not created yet. Please try again.' });
                return;
            }

            const { paymentMethod, error } = await stripe.createPaymentMethod({
                type: 'card',
                card: cardNumber,
                billing_details: {
                    name: form.name?.value || '',
                    email: form.email?.value || ''
                }
            });

            if (error) {
                document.getElementById("booking-card-errors").textContent = error.message;
                return;
            }

            try {
                const res = await fetch(`/bookings/${encodeURIComponent(bookingId)}/pay-with-stripe`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ payment_method_id: paymentMethod.id })
                });

                const text = await res.text();
                let data;
                try { data = JSON.parse(text); } catch { data = { success: false, message: text }; }

                if (!res.ok) {
                    console.error('Server error:', data);
                    alert(data.message || 'Payment failed (server error).');
                    return;
                }

                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById("bookingStripeModal"))?.hide();
                    new bootstrap.Modal(document.getElementById("bookingThankYou")).show();
                } else if (data.requires_action && data.payment_intent_client_secret) {
                    const result = await stripe.confirmCardPayment(data.payment_intent_client_secret);
                    if (result.error) {
                        alert(result.error.message);
                    } else {
                        bootstrap.Modal.getInstance(document.getElementById("bookingStripeModal"))?.hide();
                        new bootstrap.Modal(document.getElementById("bookingThankYou")).show();
                    }
                } else {
                    alert(data.message || 'Payment failed.');
                }
            } catch (e) {
                console.error(e);
                alert('Network error while charging card.');
            }
        });

        // Never submit the <form> directly; we handle via AJAX
        document.getElementById("bookingForm").addEventListener("submit", function (e) {
            e.preventDefault();
        });
    });
</script>
