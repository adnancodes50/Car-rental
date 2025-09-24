       {{-- BOOKING MODALS --}}
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
                                           <i class="bi bi-clock display-6 text-warning"></i>
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
                                           <i class="bi bi-calendar-event display-6 text-warning"></i>
                                           <h6 class="mt-2">Weekly Rental</h6>
                                           <p class="small text-muted mb-1">Great for 1–4 weeks</p>
                                           <div class="text-dark">R{{ number_format($vehicle->rental_price_week) }}/week
                                           </div>
                                       </div>
                                   </div>
                               @endif
                               @if ($vehicle->rental_price_month)
                                   <div class="col-md-4">
                                       <div class="option-card p-3 border rounded-4 h-100" data-type="month"
                                           data-price="{{ $vehicle->rental_price_month }}">
                                           <i class="bi bi-box display-6 text-warning"></i>
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
          <div class="addon-card border rounded p-3 mb-3 shadow-sm" data-id="{{ $addOn->id }}" style="cursor:pointer;">
            <div class="d-flex align-items-center justify-content-between">
              <div class="me-3">
                <img src="{{ asset($addOn->image_url) }}" alt="{{ $addOn->name }}"
                     class="rounded border" style="width:60px; height:60px; object-fit:cover;">
              </div>
              <div class="flex-grow-1">
                <h5 class="fw-semibold mb-1">{{ $addOn->name }}</h5>
                <p class="text-muted mb-1">{{ $addOn->description }}</p>
                <p class="small text-muted mb-1">
                  R{{ $addOn->price_day }}/day • R{{ $addOn->price_week }}/week • R{{ $addOn->price_month }}/month
                </p>
              </div>
              <div class="text-end">
                <span class="badge bg-success mb-2">{{ $addOn->remaining_qty }} available</span>
              </div>
            </div>

           <!-- Details -->
<div class="addon-details mt-3 d-none border-top pt-3">
  <!-- Rental Type -->
  <div class="row g-2 mb-2">
    <div class="col-sm-4">
      <div class="card text-center shadow-sm h-100 addon-type-card"
           data-type="day" data-price="{{ $addOn->price_day }}">
        <div class="card-body">
          <h6 class="card-title">Daily</h6>
          <p class="card-text fw-bold text-primary">R{{ number_format($addOn->price_day, 2) }}</p>
        </div>
      </div>
    </div>
    <div class="col-sm-4">
      <div class="card text-center shadow-sm h-100 addon-type-card"
           data-type="week" data-price="{{ $addOn->price_week }}">
        <div class="card-body">
          <h6 class="card-title">Weekly</h6>
          <p class="card-text fw-bold text-primary">R{{ number_format($addOn->price_week, 2) }}</p>
        </div>
      </div>
    </div>
    <div class="col-sm-4">
      <div class="card text-center shadow-sm h-100 addon-type-card"
           data-type="month" data-price="{{ $addOn->price_month }}">
        <div class="card-body">
          <h6 class="card-title">Monthly</h6>
          <p class="card-text fw-bold text-primary">R{{ number_format($addOn->price_month, 2) }}</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Qty + Dates + Extra Days -->
  <div class="row g-2">
    <div class="col-sm-4">
      <label class="form-label small">Quantity</label>
      <select class="form-select form-select-sm addon-qty" data-id="{{ $addOn->id }}">
        @for ($i = 0; $i <= $addOn->qty_total; $i++)
          <option value="{{ $i }}">{{ $i }}</option>
        @endfor
      </select>
    </div>

    <div class="col-sm-4">
      <label class="form-label small">Rental Dates</label>
      <input type="text" class="form-control form-control-sm addon-dates"
             placeholder="Select rental dates" data-id="{{ $addOn->id }}" readonly>
    </div>

    <div class="col-sm-4">
      <label class="form-label small">Extra Days</label>
      <select class="form-select form-select-sm addon-extra-days" data-id="{{ $addOn->id }}" disabled>
        @for ($i = 0; $i <= 6; $i++)
          <option value="{{ $i }}">{{ $i }}</option>
        @endfor
      </select>
    </div>
  </div>

  <!-- Hidden fields -->
  <input type="hidden" name="add_ons[{{ $addOn->id }}][type]"       id="addon-type-{{ $addOn->id }}">
  <input type="hidden" name="add_ons[{{ $addOn->id }}][quantity]"   id="addon-quantity-{{ $addOn->id }}">
  <input type="hidden" name="add_ons[{{ $addOn->id }}][start_date]" id="addon-start-{{ $addOn->id }}">
  <input type="hidden" name="add_ons[{{ $addOn->id }}][end_date]"   id="addon-end-{{ $addOn->id }}">
  <input type="hidden" name="add_ons[{{ $addOn->id }}][extra_days]" id="addon-extra-{{ $addOn->id }}" value="0">
  <input type="hidden" name="add_ons[{{ $addOn->id }}][total]"      id="addon-total-{{ $addOn->id }}">

  <!-- Live output -->
  <div class="small text-muted mt-2" id="addon-period-{{ $addOn->id }}"></div>
  <div class="fw-bold text-primary mt-1" id="addon-price-{{ $addOn->id }}">R0.00</div>
</div>

          </div>
        @endforeach
      </div>

      <div class="modal-footer">
        <div class="d-flex justify-content-between w-100">
          <button type="button" class="btn btn-outline-secondary" data-bs-target="#multiStepBookingModal" data-bs-toggle="modal">Back</button>
          <button type="button" class="btn btn-dark rounded-3" data-bs-target="#customerStep" data-bs-toggle="modal">Continue to Details</button>
        </div>
      </div>
    </div>
  </div>
</div>


           <!-- Step 3: Customer Details Modal -->
           <div class="modal fade" id="customerStep" tabindex="-1" aria-hidden="true"
               style="margin-bottom: 10rem">
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
                                       placeholder="you@example.com" required>
                               </div>

                               <div class="col-12">
                                   <label class="form-label">Phone Number</label>
                                   <input type="tel" class="form-control rounded-3" name="phone"
                                       placeholder="+27 123 456 7890" required>
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
                               <button type="button" class="btn btn-outline-secondary"
                                   data-bs-target="#customerStep" data-bs-toggle="modal">Back</button>

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
           $stripeEnabled = Cache::get('payments.stripe', config('payments.stripe'));
           $payfastEnabled = Cache::get('payments.payfast', config('payments.payfast'));

           // Count enabled payment methods
           $enabledCount = ($stripeEnabled ? 1 : 0) + ($payfastEnabled ? 1 : 0);
       @endphp

       <div class="modal fade" id="bookingPayment" tabindex="-1" aria-hidden="true">
           <div class="modal-dialog modal-lg modal-dialog-centered">
               <div class="modal-content rounded-4 shadow">

                   <!-- Header -->
                   <div class="modal-header">
                       <h5 class="modal-title fw-bold">
                           <i class="bi bi-credit-card me-2"></i> Select Payment Method
                       </h5>
                       <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                   </div>

                   <!-- Body -->
                   <div class="modal-body">
                       <div class="row g-3 align-items-stretch justify-content-center">

                           @if ($stripeEnabled)
                               <!-- Stripe -->
                               <div class="col-12 {{ $enabledCount === 2 ? 'col-md-6' : 'col-md-12' }}">
                                   <input type="radio" name="booking_payment_method" id="bookingStripe"
                                       value="stripe" class="btn-check" autocomplete="off">
                                   <label for="bookingStripe" class="card btn w-100 booking-pay-option p-3">
                                       <div class="text-center mb-2">
                                           <img src="{{ asset('images/stripe.png') }}" class="rounded-3"
                                               alt="Stripe" style="width: 80px; height:auto;">
                                       </div>
                                       <div class="booking-pay-text text-center">
                                           <div class="fw-bold">Stripe (Card)</div>
                                           <small class="text-muted">Visa • Mastercard • Amex</small>
                                       </div>
                                   </label>
                               </div>
                           @endif

                           @if ($payfastEnabled)
                               <!-- PayFast -->
                               <div class="col-12 {{ $enabledCount === 2 ? 'col-md-6' : 'col-md-12' }}">
                                   <input type="radio" name="booking_payment_method" id="bookingPayfast"
                                       value="payfast" class="btn-check" autocomplete="off">
                                   <label for="bookingPayfast" class="card btn w-100 booking-pay-option p-3">
                                       <div class="text-center mb-2">
                                           <img src="{{ asset('images/payfast.png') }}" class="rounded-3"
                                               alt="PayFast" style="width: 80px; height:auto;">
                                       </div>
                                       <div class="booking-pay-text text-center">
                                           <div class="fw-bold">PayFast</div>
                                           <small class="text-muted">South Africa payments</small>
                                       </div>
                                   </label>
                               </div>
                           @endif

                       </div>
                   </div>

                   <!-- Footer -->
                   <div class="modal-footer justify-content-between">
                       <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal"
                           data-bs-target="#summaryStep">
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
    box-shadow: 0 .5rem 1rem rgba(13,110,253,.15) !important;
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
<script>
document.addEventListener('DOMContentLoaded', function () {
  // helpers
  function toYMD(d){ const y=d.getFullYear(), m=String(d.getMonth()+1).padStart(2,'0'), day=String(d.getDate()).padStart(2,'0'); return `${y}-${m}-${day}`; }
  function addDays(d, n){ const x=new Date(d.getFullYear(), d.getMonth(), d.getDate()); x.setDate(x.getDate()+n); return x; }
  function diffDaysIncl(a, b){
    const d1 = new Date(a+"T00:00:00"), d2 = new Date(b+"T00:00:00");
    return Math.floor((d2 - d1) / 86400000) + 1;
  }
  function unitsBetween(days, unit){
    if (unit === 'day')   return days;               // exact days
    if (unit === 'week')  return Math.floor(days/7); // full weeks only
    if (unit === 'month') return Math.floor(days/30);// full months only
    return 0;
  }
  function unitDays(unit){ return unit === 'week' ? 7 : (unit === 'month' ? 30 : 1); }
  function snapEndForUnit(start, unit){
    if (!start) return null;
    if (unit === 'day')   return start;               // 1 day
    if (unit === 'week')  return addDays(start, 6);   // 7 days inclusive
    if (unit === 'month') return addDays(start, 29);  // 30 days inclusive
    return null;
  }
  function money(v){ return `R${Number(v).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2})}`; }

  document.querySelectorAll('.addon-card').forEach(card => {
    const addonId   = card.dataset.id;

    // local elements
    const details   = card.querySelector('.addon-details');
    const qtySel    = details.querySelector('.addon-qty');
    const dateInput = details.querySelector('.addon-dates');
    const planCards = details.querySelectorAll('.addon-type-card');
    const extraSel  = details.querySelector('.addon-extra-days');

    // hidden fields
    const typeH   = details.querySelector(`#addon-type-${addonId}`);
    const qtyH    = details.querySelector(`#addon-quantity-${addonId}`);
    const startH  = details.querySelector(`#addon-start-${addonId}`);
    const endH    = details.querySelector(`#addon-end-${addonId}`);
    const extraH  = details.querySelector(`#addon-extra-${addonId}`);
    const totalH  = details.querySelector(`#addon-total-${addonId}`);

    // UI outputs
    const periodEl = details.querySelector(`#addon-period-${addonId}`);
    const priceEl  = details.querySelector(`#addon-price-${addonId}`);

    // toggle open
    card.addEventListener('click', (e)=>{
      if (e.target.closest('.addon-details')) return;
      details.classList.toggle('d-none');
      if (!dateInput._flatpickr) initPicker();
    });

    function initPicker(){
      flatpickr(dateInput, {
        mode: 'range',
        dateFormat: 'Y-m-d',
        onChange: (selectedDates, _str, instance) => {
          const active = details.querySelector('.addon-type-card.active');

          // if only start picked and a plan is active → snap end to a full unit
          if (selectedDates.length === 1 && active){
            const unit = active.dataset.type;
            const snappedEnd = snapEndForUnit(selectedDates[0], unit);
            if (snappedEnd){
              instance.setDate([selectedDates[0], snappedEnd], true);
              return; // will trigger onChange again
            }
          }

          if (selectedDates.length === 2){
            startH.value = toYMD(selectedDates[0]);
            endH.value   = toYMD(selectedDates[1]);
            // close the calendar once a full range is chosen
            instance.close();
            updateTotal();
          }
        }
      });
    }

    // plan select
    planCards.forEach(pc=>{
      pc.addEventListener('click', function(){
        // mark active
        planCards.forEach(c=>c.classList.remove('active'));
        this.classList.add('active');

        // set hidden type
        const unit = this.dataset.type; // day|week|month
        typeH.value = unit;

        // enable/disable extra days select
        if (unit === 'week' || unit === 'month') {
          extraSel.disabled = false;
        } else {
          extraSel.disabled = true;
          extraSel.value = '0';
          extraH.value   = '0';
        }

        // default qty at least 1
        if (!qtySel.value || qtySel.value === '0') qtySel.value = '1';
        qtyH.value = qtySel.value;

        if (!dateInput._flatpickr) initPicker();
        const fp = dateInput._flatpickr;
        fp.open();

        const sd = fp.selectedDates?.[0] || null;
        if (sd){
          const end = snapEndForUnit(sd, unit);
          if (end){
            fp.setDate([sd, end], true);
          }
        }
        updateTotal();
      });
    });

    // qty change
    qtySel.addEventListener('change', ()=>{
      qtyH.value = qtySel.value || '0';
      updateTotal();
    });

    // extra days change: extend end date and update
    extraSel.addEventListener('change', ()=>{
      extraH.value = extraSel.value || '0';
      const fp = dateInput._flatpickr;
      const active = details.querySelector('.addon-type-card.active');
      if (fp && active && startH.value){
        const unit = active.dataset.type;
        if (unit === 'week' || unit === 'month'){
          const sd = new Date(startH.value+"T00:00:00");
          const endBase = snapEndForUnit(sd, unit); // 7 or 30 days inclusive
          const endPlus = addDays(endBase, parseInt(extraSel.value || '0', 10));
          fp.setDate([sd, endPlus], true); // sync input + hidden
        }
      }
      updateTotal();
    });

    function updateTotal(){
      const active = details.querySelector('.addon-type-card.active');
      if (!active){ setPrice(0, ''); clearHiddenIfEmpty(); return; }

      const unit      = active.dataset.type;                    // day|week|month
      const unitPrice = parseFloat(active.dataset.price || '0');// price per full unit
      const items     = parseInt(qtySel.value || '0', 10);
      const start     = startH.value;
      const end       = endH.value;
      const extraDays = parseInt(extraSel.value || '0', 10);

      if (!items || !start || !end){
        setPrice(0, '');
        return;
      }

      const days = diffDaysIncl(start, end); // inclusive day count

      // Split into full units + remainder (we *also* allow user-chosen extra days)
      const uDays      = unitDays(unit);              // 1 / 7 / 30
      const fullUnits  = unitsBetween(days, unit);    // floor by design
      const remainder  = Math.max(0, days - fullUnits * uDays);

      // price = (full units * unitPrice) + (extra days * prorated)   all times quantity
      let proratePerDay = unit === 'day' ? unitPrice
                           : unit === 'week' ? (unitPrice / 7)
                           : (unitPrice / 30);

      const totalForOne = (fullUnits * unitPrice) + ((remainder + extraDays) * proratePerDay);
      const total = totalForOne * items;

      // update UI
      const txt = `Start: ${start} → End: ${end} (${days} days) ${ (unit!=='day' && (remainder||extraDays)) ? ' • remainder '+remainder+'d + extra '+extraDays+'d' : '' }`;
      setPrice(total, txt);

      // write hiddens
      totalH.value = Math.round(total);
      extraH.value = extraDays;
    }

    function setPrice(val, periodText){
      priceEl.textContent = money(val);
      periodEl.innerHTML  = periodText || '';
    }

    function clearHiddenIfEmpty(){
      if (!startH.value || !endH.value){
        startH.value = '';
        endH.value   = '';
        totalH.value = '';
      }
    }
  });
});
</script>


