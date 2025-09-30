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
                                       $availableToday > 0
                                           ? $availableToday . ' available today'
                                           : 'Fully booked today';
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
                                               class="rounded border"
                                               style="width:60px; height:60px; object-fit:cover;">
                                       </div>
                                       <div class="flex-grow-1 pe-3">
                                           <h5 class="fw-semibold mb-1">{{ $addOn->name }}</h5>
                                           <p class="text-muted mb-1">{{ $addOn->description }}</p>
                                           <p class="small text-muted mb-0">
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
                                           <div
                                               class="alert alert-warning small py-2 px-3 addon-unavailable-dates mb-3">
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
                                                           <i class="bi bi-clock display-6"
                                                               style="color: #CF9B4D"></i>
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

                                       <div class="row g-2">
                                           <div class="col-sm-4">
                                               <label class="form-label small">Quantity</label>
                                               <select class="form-select form-select-sm addon-qty"
                                                   data-id="{{ $addOn->id }}"
                                                   @if ($totalStock <= 0) disabled @endif>
                                                   @for ($i = 0; $i <= $addOn->qty_total; $i++)
                                                       <option value="{{ $i }}">{{ $i }}
                                                       </option>
                                                   @endfor
                                               </select>
                                           </div>
                                           <div class="col-sm-4">
                                               <label class="form-label small">Rental Dates</label>
                                               <input type="text" class="form-control form-control-sm addon-dates"
                                                   placeholder="Select rental dates" data-id="{{ $addOn->id }}"
                                                   @if ($totalStock <= 0) disabled @endif readonly>
                                           </div>
                                           <div class="col-sm-4">
                                               <label class="form-label small">Extra Days</label>
                                               <select class="form-select form-select-sm addon-extra-days"
                                                   data-id="{{ $addOn->id }}" disabled>
                                                   @for ($i = 0; $i <= 6; $i++)
                                                       <option value="{{ $i }}">{{ $i }}
                                                       </option>
                                                   @endfor
                                               </select>
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
                                       <div class="fw-bold text-primary mt-1" id="addon-price-{{ $addOn->id }}">
                                           R0.00</div>

                                       <div class="addon-live-summary d-none">
                                           <div class="alert alert-info border-0 rounded-3 py-3 px-3 mb-3">
                                               <div class="fw-semibold mb-1">
                                                   <span class="als-line-1-label">Days</span>:
                                                   <span class="als-qty">0</span>
                                                   × <span class="als-unit">R0.00</span>
                                               </div>
                                               <div class="fw-semibold">
                                                   Total Cost: <span class="als-total">R0.00</span>
                                               </div>
                                           </div>

                                           <div class="row g-3">
                                               <div class="col-md-6">
                                                   <div class="addon-date-pill bg-light border rounded-3 p-3 h-100">
                                                       <div class="text-muted fw-semibold mb-1">Start Date</div>
                                                       <div class="als-start">—</div>
                                                   </div>
                                               </div>
                                               <div class="col-md-6">
                                                   <div class="addon-date-pill bg-light border rounded-3 p-3 h-100">
                                                       <div class="text-muted fw-semibold mb-1">End Date</div>
                                                       <div class="als-end">—</div>
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
                               <button type="button" class="btn btn-outline-secondary"
<button
  type="button"
  class="btn btn-outline-secondary"
  data-bs-toggle="modal"
  data-bs-target="#multiStepBookingModal"
  data-bs-dismiss="modal">
  Back
</button>                               <button type="button" class="btn btn-dark rounded-3" data-bs-target="#customerStep"
                                   data-bs-toggle="modal">Continue to Details</button>
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
           use App\Models\SystemSetting;
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
                                   <label for="bookingStripe"
                                       class="card btn w-100 booking-pay-option p-3 flex-column">
                                       <div class="text-center mb-2">
                                           <img src="{{ asset('images/stripe.png') }}" class="rounded-3"
                                               alt="Stripe" style="width: 80px;">
                                       </div>
                                       <div class="booking-pay-text text-center">
                                           <div class="fw-bold">Stripe (Card)</div>
                                           <small class="text-muted">Visa • Mastercard • Amex</small>
                                       </div>
                                   </label>
                               </div>
                           @endif

                           @if ($settings->payfast_enabled)
                               <!-- PayFast -->
                               <div class="col-12 {{ $enabledCount === 2 ? 'col-md-6' : 'col-md-12' }}">
                                   <input type="radio" name="booking_payment_method" id="bookingPayfast"
                                       value="payfast" class="btn-check" autocomplete="off" required>
                                   <label for="bookingPayfast"
                                       class="card btn w-100 booking-pay-option p-3 flex-column">
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
       <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

       <!-- ADD-ONS + SUMMARY (keeps SweetAlerts + overlap checks) -->
       <script>
           document.addEventListener('DOMContentLoaded', function() {
               /* ─────────────────────────────────────────── Helpers */
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
               const diffDaysIncl = (a, b) => (!a || !b) ? 0 : Math.floor((fromYMD(b) - fromYMD(a)) / DAY_MS) + 1;
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

               function ensureOpt(sel, v) {
                   const vs = String(v);
                   for (const o of sel.options) {
                       if (o.value === vs) return;
                   }
                   const o = document.createElement('option');
                   o.value = vs;
                   o.textContent = vs;
                   sel.appendChild(o);
               }

               // SweetAlert2 helper (debounced)
               const alertDebounce = new Map();

               function notify(key, {
                   icon = 'warning',
                   title = 'Notice',
                   text = ''
               }) {
                   const now = Date.now(),
                       last = alertDebounce.get(key) || 0;
                   if (now - last < 600) return;
                   alertDebounce.set(key, now);
                   if (window.Swal?.fire) {
                       window.Swal.fire({
                           icon,
                           title,
                           text,
                           confirmButtonText: 'OK'
                       });
                   } else {
                       alert(`${title}\n\n${text}`);
                   }
               }

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

               // overlap utilities
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
                       if (start <= end) res.push(`${niceDate(toYMD(start))} → ${niceDate(toYMD(end))}`);
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
                       const blockedTxt = where.length ? `Blocked on: ${where.join(', ')}` :
                           'Some dates are unavailable.';
                       notify(`overlap-plan-${addonId}`, {
                           icon: 'error',
                           title: 'Dates unavailable',
                           text: `${blockedTxt}\nAvailable quantity for this add-on: ${availableQty}.`
                       });
                       return true;
                   }
                   return false;
               }

               function setAddonHiddenDisabled(detailsEl, disabled) {
                   detailsEl.querySelectorAll('input[type="hidden"]').forEach(h => h.disabled = !!disabled);
               }

               // ⬇️ add-on helpers (also exported to window for the payment script)
               function computeAddonsTotal() {
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

               // Optional: inspect current selections from devtools
               const addonSelections = {};
               window.bookingAddonSelections = addonSelections;

               /* ─────────────────────────────────────────── Add-ons */
               document.querySelectorAll('.addon-card').forEach(card => {
                   const addonId = card.dataset.id;
                   const totalStock = parseInt(card.dataset.total || '0', 10);
                   const availToday = parseInt(card.dataset.available || '0', 10);

                   const details = card.querySelector('.addon-details');
                   const qtySel = details.querySelector('.addon-qty');
                   const dateInput = details.querySelector('.addon-dates');
                   const planCards = details.querySelectorAll('.addon-type-card');
                   const extraSel = details.querySelector('.addon-extra-days');
                   const extraCol = extraSel.closest('.col-sm-4');
                   const extraLbl = extraCol ? extraCol.querySelector('.form-label') : null;
                   const badgeEl = card.querySelector('.availability-badge');

                   // hidden fields
                   const typeH = details.querySelector(`#addon-type-${addonId}`);
                   const qtyH = details.querySelector(`#addon-quantity-${addonId}`);
                   const startH = details.querySelector(`#addon-start-${addonId}`);
                   const endH = details.querySelector(`#addon-end-${addonId}`);
                   const extraH = details.querySelector(`#addon-extra-${addonId}`);
                   const totalH = details.querySelector(`#addon-total-${addonId}`);
                   const daysH = details.querySelector(`#addon-days-${addonId}`);

                   // UI
                   const priceEl = details.querySelector(`#addon-price-${addonId}`);

                   // Live summary
                   const live = details.querySelector('.addon-live-summary');
                   const alsQty = live ? live.querySelector('.als-qty') : null;
                   const alsUnit = live ? live.querySelector('.als-unit') : null;
                   const alsTotal = live ? live.querySelector('.als-total') : null;
                   const alsStart = live ? live.querySelector('.als-start') : null;
                   const alsEnd = live ? live.querySelector('.als-end') : null;
                   const alsLbl = live ? live.querySelector('.als-line-1-label') : null;

                   // Blocked ranges (from backend)
                   let blockedRanges = [];
                   try {
                       blockedRanges = JSON.parse(card.dataset.blocked || '[]') || [];
                   } catch {
                       blockedRanges = [];
                   }
                   blockedRanges = blockedRanges.filter(r => r && r.from && r.to);
                   const disabledRanges = blockedRanges.map(r => ({
                       from: r.from,
                       to: r.to
                   }));

                   // Hiddens off until valid
                   setAddonHiddenDisabled(details, true);

                   // reentrancy guard
                   let syncing = false;
                   const runSilent = (fn) => {
                       if (syncing) return;
                       syncing = true;
                       try {
                           fn();
                       } finally {
                           syncing = false;
                       }
                   };
                   const setRangeSilently = (fp, start, end) => {
                       if (!fp) return;
                       runSilent(() => fp.setDate([start, end], false));
                   };

                   const rangeOverlapsBlocked = (startYMD, endYMD) => hasOverlap(startYMD, endYMD,
                       blockedRanges);

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
                                   icon: 'warning',
                                   title: 'Out of stock',
                                   text: `${blockedTxt ? blockedTxt+'\n' : ''}Available quantity for these dates: 0.`
                               });
                           } else if (prev > newVal) {
                               notify(`clamped-${addonId}-${startYMD}-${endYMD}`, {
                                   icon: 'warning',
                                   title: 'Limited availability',
                                   text: `${blockedTxt ? blockedTxt+'\n' : ''}Only ${newMax} unit${newMax>1?'s':''} are available for these dates. Your quantity was adjusted.`
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
                               const blockedTxt = where.length ? `Blocked on: ${where.join(', ')}` :
                                   'Some dates are unavailable.';
                               notify(`overlap-${addonId}-${startYMD}-${endYMD}`, {
                                   icon: 'error',
                                   title: 'Dates unavailable',
                                   text: `${blockedTxt}\nAvailable quantity for this add-on: 0.`
                               });
                               return 0;
                           } else {
                               setBadge(`${totalStock} available for these dates`, true);
                               clampQtyTo(totalStock, startYMD, endYMD);
                               return totalStock;
                           }
                       } else {
                           if (availToday > 0) setBadge(`${availToday} available today`, true);
                           else setBadge('Fully booked today', false);
                           clampQtyTo(Math.max(0, availToday));
                           return availToday;
                       }
                   }

                   // open/close
                   card.addEventListener('click', (e) => {
                       if (e.target.closest('.addon-details')) return;
                       details.classList.toggle('d-none');
                       if (!details.classList.contains('d-none')) {
                           ensureActivePlan();
                           initPicker();
                           const fp = dateInput._flatpickr;
                           if (fp) {
                               let start = startH.value ? fromYMD(startH.value) : null;
                               if (!start) {
                                   const next = nextAvailableFrom(new Date(), blockedRanges);
                                   if (next) start = next;
                               } else if (blockedRanges.some(r => inRange(start, r))) {
                                   const next = nextAvailableFrom(start, blockedRanges);
                                   if (next) start = next;
                               }
                               if (start) {
                                   const unit = typeH.value || 'day';
                                   if (unit === 'day') {
                                       const d = Math.max(1, parseInt(daysH.value || extraSel.value ||
                                           '1', 10));
                                       setRangeSilently(fp, start, addDays(start, d - 1));
                                   } else {
                                       const extra = Math.max(0, parseInt(extraH.value || extraSel
                                           .value || '0', 10));
                                       const overlapped = warnIfPlannedRangeOverlaps({
                                           unit,
                                           startDate: start,
                                           extraDays: extra,
                                           addonId,
                                           blockedRanges,
                                           availableQty: totalStock
                                       });
                                       if (!overlapped) setRangeSilently(fp, start, addDays(
                                           snapEndForUnit(start, unit), extra));
                                   }
                               }
                               updateAvailabilityForDates(startH.value, endH.value);
                               fp.open();
                           }
                           updateTotal();
                       }
                   });

                   function configureTimeControlsForUnit(unit) {
                       if (extraLbl) extraLbl.textContent = (unit === 'day') ? 'Days' : 'Extra Days';

                       const fp = dateInput._flatpickr;
                       const selectedStart = fp?.selectedDates?.[0] || null;

                       if (unit === 'day') {
                           const daysSel = Math.max(1, parseInt(daysH.value || '1', 10));
                           extraSel.disabled = false;
                           fillSelect(extraSel, 1, 30, daysSel);
                           extraH.value = '0';
                           if (selectedStart) setRangeSilently(fp, selectedStart, addDays(selectedStart,
                               daysSel - 1));
                       } else {
                           const maxExtra = (unit === 'week') ? 6 : 29;
                           const cur = Math.max(0, Math.min(maxExtra, parseInt(extraH.value || '0', 10)));
                           extraSel.disabled = false;
                           fillSelect(extraSel, 0, maxExtra, cur);
                           if (selectedStart) {
                               const overlapped = warnIfPlannedRangeOverlaps({
                                   unit,
                                   startDate: selectedStart,
                                   extraDays: cur,
                                   addonId,
                                   blockedRanges,
                                   availableQty: totalStock
                               });
                               if (!overlapped) setRangeSilently(fp, selectedStart, addDays(snapEndForUnit(
                                   selectedStart, unit), cur));
                           }
                       }
                   }

                   function ensureActivePlan() {
                       const cards = [...planCards];
                       let active = cards.find(c => c.classList.contains('active')) || (typeH.value ? cards
                           .find(c => c.dataset.type === typeH.value) : null) || cards[0];
                       cards.forEach(c => c.classList.remove('active'));
                       active.classList.add('active');

                       const unit = active.dataset.type;
                       typeH.value = unit;
                       if (!qtySel.value || qtySel.value === '0') qtySel.value = '1';
                       qtyH.value = qtySel.value;
                       configureTimeControlsForUnit(unit);
                   }

                   function initPicker() {
                       if (dateInput._flatpickr) return;
                       flatpickr(dateInput, {
                           mode: 'range',
                           dateFormat: 'Y-m-d',
                           minDate: 'today',
                           disable: disabledRanges,
                           onChange(selectedDates, _str, instance) {
                               if (syncing) return;
                               handleDateSelection(selectedDates, instance);
                           },
                           onOpen: (_sel, _str, inst) => {
                               const next = nextAvailableFrom(new Date(), blockedRanges);
                               if (next && (!inst.selectedDates || inst.selectedDates.length ===
                                       0)) {
                                   const unit = typeH.value || 'day';
                                   if (unit === 'day') {
                                       const d = Math.max(1, parseInt(daysH.value || extraSel
                                           .value || '1', 10));
                                       inst.setDate([next, addDays(next, d - 1)], false);
                                   } else {
                                       const extra = Math.max(0, parseInt(extraH.value || extraSel
                                           .value || '0', 10));
                                       const overlapped = warnIfPlannedRangeOverlaps({
                                           unit,
                                           startDate: next,
                                           extraDays: extra,
                                           addonId,
                                           blockedRanges,
                                           availableQty: totalStock
                                       });
                                       if (!overlapped) inst.setDate([next, addDays(snapEndForUnit(
                                           next, unit), extra)], false);
                                   }
                               }
                           },
                           onClose() {
                               updateTotal();
                           }
                       });
                   }

                   function handleDateSelection(selectedDates, instance) {
                       const unit = (details.querySelector('.addon-type-card.active') || {}).dataset?.type ||
                           typeH.value || 'day';

                       if (selectedDates.length === 1) {
                           runSilent(() => {
                               if (unit === 'day') {
                                   const d = Math.max(1, parseInt(extraSel.value || daysH.value || '1',
                                       10));
                                   instance.setDate([selectedDates[0], addDays(selectedDates[0], d -
                                       1)], false);
                               } else {
                                   const extra = Math.max(0, parseInt(extraH.value || extraSel.value ||
                                       '0', 10));
                                   const overlapped = warnIfPlannedRangeOverlaps({
                                       unit,
                                       startDate: selectedDates[0],
                                       extraDays: extra,
                                       addonId,
                                       blockedRanges,
                                       availableQty: totalStock
                                   });
                                   if (!overlapped) instance.setDate([selectedDates[0], addDays(
                                       snapEndForUnit(selectedDates[0], unit), extra)], false);
                               }
                           });
                       }

                       if (selectedDates.length === 2) {
                           startH.value = toYMD(selectedDates[0]);
                           endH.value = toYMD(selectedDates[1]);

                           if (unit === 'day') {
                               const d = diffDaysIncl(startH.value, endH.value);
                               daysH.value = String(Math.max(1, d));
                               ensureOpt(extraSel, daysH.value);
                               extraSel.value = daysH.value;
                               extraH.value = '0';
                           } else {
                               const baseEnd = snapEndForUnit(selectedDates[0], unit);
                               const diff = diffDaysIncl(toYMD(baseEnd), endH.value) - unitDays(unit);
                               const extra = Math.max(0, diff);
                               ensureOpt(extraSel, extra);
                               extraSel.value = String(extra);
                               extraH.value = String(extra);
                               daysH.value = String(diffDaysIncl(startH.value, endH.value));
                           }

                           updateAvailabilityForDates(startH.value, endH.value);
                           updateTotal();
                       }
                   }

                   // Plan click
                   planCards.forEach(pc => {
                       pc.addEventListener('click', (e) => {
                           e.stopPropagation();
                           planCards.forEach(c => c.classList.remove('active'));
                           pc.classList.add('active');
                           typeH.value = pc.dataset.type;

                           if (!qtySel.value || qtySel.value === '0') qtySel.value = '1';
                           qtyH.value = qtySel.value;

                           initPicker();
                           configureTimeControlsForUnit(typeH.value);

                           const fp = dateInput._flatpickr;
                           if (fp) {
                               let start = fp.selectedDates?.[0];
                               if (!start) {
                                   const next = nextAvailableFrom(new Date(), blockedRanges);
                                   if (next) {
                                       start = next;
                                       runSilent(() => fp.setDate([start], false));
                                   }
                               }
                               const u = typeH.value;
                               if (u === 'day') {
                                   const d = Math.max(1, parseInt(extraSel.value || daysH
                                       .value || '1', 10));
                                   setRangeSilently(fp, start, addDays(start, d - 1));
                               } else {
                                   const extra = Math.max(0, parseInt(extraH.value || extraSel
                                       .value || '0', 10));
                                   const overlapped = warnIfPlannedRangeOverlaps({
                                       unit: u,
                                       startDate: start,
                                       extraDays: extra,
                                       addonId,
                                       blockedRanges,
                                       availableQty: totalStock
                                   });
                                   if (!overlapped) setRangeSilently(fp, start, addDays(
                                       snapEndForUnit(start, u), extra));
                               }
                           }

                           updateAvailabilityForDates(startH.value, endH.value);
                           updateTotal();
                       });
                   });

                   // Quantity change
                   qtySel.addEventListener('change', () => {
                       qtyH.value = qtySel.value || '0';
                       updateTotal();
                   });

                   // Days / extra-days change
                   extraSel.addEventListener('change', () => {
                       const unit = typeH.value || 'day';
                       const fp = dateInput._flatpickr;

                       details.querySelectorAll('input[type="hidden"]').forEach(h => h.disabled =
                           false);

                       if (unit === 'day') {
                           const days = Math.max(1, parseInt(extraSel.value || '1', 10));
                           daysH.value = String(days);
                           extraH.value = '0';

                           if (fp && fp.selectedDates[0]) {
                               const s = fp.selectedDates[0];
                               const e = addDays(s, days - 1);
                               setRangeSilently(fp, s, e);
                               startH.value = toYMD(s);
                               endH.value = toYMD(e);
                           }
                       } else {
                           const extra = Math.max(0, parseInt(extraSel.value || '0', 10));
                           extraH.value = String(extra);

                           if (fp && fp.selectedDates[0]) {
                               const s = fp.selectedDates[0];
                               const overlapped = warnIfPlannedRangeOverlaps({
                                   unit,
                                   startDate: s,
                                   extraDays: extra,
                                   addonId,
                                   blockedRanges,
                                   availableQty: totalStock
                               });
                               if (overlapped) {
                                   updateAvailabilityForDates(startH.value, endH.value);
                                   updateTotal();
                                   return;
                               }

                               const snapEnd = snapEndForUnit(s, unit);
                               const e = addDays(snapEnd, extra);
                               setRangeSilently(fp, s, e);
                               startH.value = toYMD(s);
                               endH.value = toYMD(e);
                               daysH.value = String(diffDaysIncl(startH.value, endH.value));
                           }
                       }

                       updateAvailabilityForDates(startH.value, endH.value);
                       updateTotal();
                   });

                   function updateTotal() {
                       const unit = typeH.value || 'day';
                       const activePlan = details.querySelector('.addon-type-card.active') || [...details
                           .querySelectorAll('.addon-type-card')
                       ].find(c => c.dataset.type === unit);
                       const unitPrice = activePlan ? parseFloat(activePlan.dataset.price || '0') : 0;
                       const quantity = parseInt(qtySel.value || '0', 10);
                       const start = startH.value;
                       const end = endH.value;
                       const extraDays = (unit === 'week' || unit === 'month') ? parseInt((extraH.value ||
                           '0'), 10) : 0;

                       if (!quantity || !start || !end) {
                           setPrice(0);
                           setAddonHiddenDisabled(details, true);
                           removeSelection(true);
                           return;
                       }

                       const days = diffDaysIncl(start, end);
                       const uDays = unitDays(unit);
                       const fullUnits = uDays > 0 ? Math.floor(days / uDays) : 0;
                       const remainder = Math.max(0, days - (fullUnits * uDays));

                       let proratePerDay = unitPrice;
                       if (unit === 'week') proratePerDay = unitPrice / 7;
                       if (unit === 'month') proratePerDay = unitPrice / 30;

                       const totalForOne = (fullUnits * unitPrice) + ((remainder + (unit === 'day' ? 0 :
                           extraDays)) * proratePerDay);
                       const total = Number((totalForOne * quantity).toFixed(2));

                       totalH.value = total.toFixed(2);
                       qtyH.value = quantity;
                       daysH.value = String(days);
                       if (unit === 'day') extraH.value = '0';

                       setAddonHiddenDisabled(details, false);
                       setPrice(total);

                       // live summary
                       if (live) {
                           const niceStart = niceDate(start),
                               niceEnd = niceDate(end);
                           const rateForLine = (unit === 'day') ? unitPrice : proratePerDay;
                           if (alsLbl) alsLbl.textContent = 'Days';
                           if (alsQty) alsQty.textContent = String(days);
                           if (alsUnit) alsUnit.textContent = money(rateForLine);
                           if (alsTotal) alsTotal.textContent = money(total);
                           if (alsStart) alsStart.textContent = niceStart;
                           if (alsEnd) alsEnd.textContent = niceEnd;
                           live.classList.remove('d-none');
                       }

                       addonSelections[addonId] = {
                           id: addonId,
                           name: card.dataset.name || '',
                           type: unit,
                           qty: quantity,
                           unitPrice,
                           perUnitTotal: Number(totalForOne.toFixed(2)),
                           total,
                           start,
                           end,
                           days,
                           extraDays,
                           remainderDays: remainder,
                           fullUnits
                       };
                   }

                   function setPrice(amount) {
                       priceEl.textContent = money(amount);
                   }

                   function removeSelection(keepDates) {
                       delete addonSelections[addonId];
                       qtyH.value = '0';
                       extraH.value = '0';
                       totalH.value = '';
                       daysH.value = '0';
                       if (!keepDates) {
                           startH.value = '';
                           endH.value = '';
                       }
                       setPrice(0);
                       if (live) {
                           live.classList.add('d-none');
                           if (alsQty) alsQty.textContent = '0';
                           if (alsUnit) alsUnit.textContent = money(0);
                           if (alsTotal) alsTotal.textContent = money(0);
                           if (alsStart) alsStart.textContent = '—';
                           if (alsEnd) alsEnd.textContent = '—';
                       }
                   }
               });

               /* ───────────────────────────── Summary (unchanged aside from totals calc kept server-side safe) */
               const goToSummaryBtn = document.getElementById('goToSummary');
               if (goToSummaryBtn) {
                   goToSummaryBtn.addEventListener('click', function() {
                       const form = document.getElementById('bookingForm');
                       const name = form.querySelector('[name="name"]');
                       const email = form.querySelector('[name="email"]');
                       const phone = form.querySelector('[name="phone"]');
                       const country = form.querySelector('[name="country"]');
                       if (!name.value.trim() || !email.value.trim() || !phone.value.trim() || !country
                           .value) {
                           notify('cust-missing', {
                               icon: 'warning',
                               title: 'Details required',
                               text: 'Please complete your details.'
                           });
                           return;
                       }

                       const unitH = document.getElementById('inputRentalUnit');
                       const startVH = document.getElementById('inputRentalStartDate');
                       const extraVH = document.getElementById('inputExtraDays');
                       const totalVH = document.getElementById('inputTotalPrice');

                       const typeLabel = ({
                           day: 'Daily',
                           week: 'Weekly',
                           month: 'Monthly'
                       })[unitH.value] || (unitH.value || '—');
                       document.getElementById('summaryType').textContent = typeLabel;

                       let vehiclePeriod = '';
                       if (startVH && startVH.value) {
                           vehiclePeriod = niceDate(startVH.value);
                           if (extraVH && (unitH.value === 'week' || unitH.value === 'month')) {
                               vehiclePeriod += ` + ${extraVH.value || 0} extra day(s)`;
                           }
                       }
                       document.getElementById('summaryPeriod').textContent = vehiclePeriod || '—';
                       document.getElementById('summaryVehicleTotal').textContent = money(totalVH ? totalVH
                           .value : 0);

                       // add-ons
                       let addonTotal = 0,
                           listHtml = '';
                       document.querySelectorAll('.addon-card').forEach(card => {
                           const id = card.dataset.id;
                           const nm = card.dataset.name || 'Add-on';
                           const totalH = card.querySelector(`#addon-total-${id}`);
                           const qtyH = card.querySelector(`#addon-quantity-${id}`);
                           const typeH = card.querySelector(`#addon-type-${id}`);
                           const startH = card.querySelector(`#addon-start-${id}`);
                           const endH = card.querySelector(`#addon-end-${id}`);
                           const daysH = card.querySelector(`#addon-days-${id}`);
                           if (totalH && totalH.disabled) return;

                           const total = parseFloat(totalH?.value || '0');
                           if (total > 0) {
                               addonTotal += total;
                               listHtml += `
            <div class="d-flex justify-content-between align-items-start mb-1">
              <div>
                <div class="fw-semibold">${nm}</div>
                <div class="text-muted small">${(typeH?.value||'-').toUpperCase()} • Qty ${qtyH?.value||1} • ${niceDate(startH?.value||'')} → ${niceDate(endH?.value||'')} (${daysH?.value||0} days)</div>
              </div>
              <div class="fw-semibold">${money(total)}</div>
            </div>`;
                           }
                       });
                       if (!listHtml) listHtml = '<span class="text-muted">No add-ons selected.</span>';
                       document.getElementById('summaryAddOnList').innerHTML = listHtml;
                       document.getElementById('summaryAddonTotal').textContent = money(addonTotal);

                       const vehicleTotal = parseFloat(totalVH?.value || '0');
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

               // Enable hidden fields for selected add-ons right before submit (so DB saves extras)
               const formEl = document.getElementById('bookingForm');
               if (formEl) {
                   formEl.addEventListener('submit', () => {
                       enableSelectedAddonHiddenFields();
                   });
               }
           });
       </script>
