<!-- ================= BOOKING FORM (wrap all steps) ================= -->
    <form id="bookingForm" method="POST" action="{{ route('bookings.store') }}">
        @csrf
        <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
        <input type="hidden" name="rental_unit" id="inputRentalUnit">
        <input type="hidden" name="rental_quantity" id="inputRentalQuantity">
        <input type="hidden" name="rental_start_date" id="inputRentalStartDate">
        <input type="hidden" name="total_price" id="inputTotalPrice">

        <!-- Step 1: Multi-Step Booking Modal -->
        <div class="modal fade" id="multiStepBookingModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content rounded-4 shadow-lg">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold"><i class="bi bi-calendar-check me-2"></i> Book {{ $vehicle->name }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <h5 class="fw-bold mb-3">Select Rental Duration</h5>
                        <div class="row text-center g-3">
                            @if($vehicle->rental_price_day)
                                <div class="col-md-4">
                                    <div class="option-card p-3 border rounded-4 bg-light h-100" data-type="day"
                                        data-price="{{ $vehicle->rental_price_day }}">
                                        <i class="bi bi-clock display-6 text-warning"></i>
                                        <h6 class="fw-bold mt-2">Daily Rental</h6>
                                        <p class="small text-muted mb-1">Perfect for short trips</p>
                                        <div class="fw-bold text-dark">R{{ number_format($vehicle->rental_price_day) }}/day
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if($vehicle->rental_price_week)
                                <div class="col-md-4">
                                    <div class="option-card p-3 border rounded-4 h-100" data-type="week"
                                        data-price="{{ $vehicle->rental_price_week }}">
                                        <i class="bi bi-calendar-event display-6 text-warning"></i>
                                        <h6 class="fw-bold mt-2">Weekly Rental</h6>
                                        <p class="small text-muted mb-1">Great for 1–4 weeks</p>
                                        <div class="fw-bold text-dark">R{{ number_format($vehicle->rental_price_week) }}/week
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if($vehicle->rental_price_month)
                                <div class="col-md-4">
                                    <div class="option-card p-3 border rounded-4 h-100" data-type="month"
                                        data-price="{{ $vehicle->rental_price_month }}">
                                        <i class="bi bi-box display-6 text-warning"></i>
                                        <h6 class="fw-bold mt-2">Monthly Rental</h6>
                                        <p class="small text-muted mb-1">Best for long stays</p>
                                        <div class="fw-bold text-dark">R{{ number_format($vehicle->rental_price_month) }}/month
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <hr class="my-4">

                        <!-- Start Date -->
                        <div class="mb-3 d-none" id="dateSection">
                            <label for="rentalStartDate" class="form-label">Rental Start Date</label>
                            <input type="text" id="rentalStartDate" class="form-control rounded-3"
                                placeholder="Select a date">
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
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Close</button>
                        <button type="button" id="continueFromStep1"
                            class="btn btn-warning fw-bold rounded-3">Continue</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Add-Ons Modal -->
        <div class="modal fade" id="addonsStep" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content rounded-4 shadow-lg">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold"><i class="bi bi-box-seam"></i> Select Add-Ons</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @foreach($addOns as $addOn)
                            <div class="d-flex align-items-center justify-content-between border rounded p-3 mb-3 shadow-sm">
                                <div class="me-3">
                                    <img src="{{ $addOn->image_url }}" alt="{{ $addOn->name }}" class="rounded border"
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
                        <button type="button" class="btn btn-outline-secondary" data-bs-target="#multiStepBookingModal"
                            data-bs-toggle="modal">Back</button>
                        <button type="button" class="btn btn-primary" data-bs-target="#customerStep"
                            data-bs-toggle="modal">Continue</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3: Customer Details Modal -->
        <div class="modal fade" id="customerStep" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content rounded-4 shadow">
                    <div class="modal-header flex-column text-center">
                        <h5 class="modal-title fw-bold">Enter Your Details</h5>
                        <div class="w-100 fw-bold mt-2">Please provide your information</div>
                        <button type="button" class="btn-close position-absolute top-0 end-0 m-3"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control rounded-3" name="name" placeholder="John Doe"
                                    required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control rounded-3" name="email"
                                    placeholder="you@example.com" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control rounded-3" name="phone" placeholder="+27 123 456 7890"
                                    required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Country of Residence</label>
                                <select name="country" class="form-select rounded-3" required>
                                    <option value="" disabled selected>Select your country</option>
                                    <option value="South Africa">South Africa</option>
                                    <option value="United States">United States</option>
                                    <option value="United Kingdom">United Kingdom</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-target="#addonsStep"
                            data-bs-toggle="modal">Back</button>
                        <button type="button" id="goToSummary" class="btn btn-success fw-bold">Review Booking</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 4: Booking Summary -->
        <div class="modal fade" id="summaryStep" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
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
                        <button type="button" class="btn btn-outline-secondary" data-bs-target="#customerStep"
                            data-bs-toggle="modal">Back</button>
                        <button type="submit" class="btn btn-success fw-bold">Confirm & Pay</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
