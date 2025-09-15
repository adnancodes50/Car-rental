<!-- Booking Modal -->
<div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="bookingModalLabel">Book {{ $vehicle->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <h5 class="fw-bold mb-3">Select Rental Duration</h5>
                <div class="row text-center g-3">
                    <!-- Daily -->
                    <div class="col-md-4">
                        <div class="option-card p-3 border rounded-4 bg-light h-100" data-type="day"
                             data-price="{{ $vehicle->rental_price_day }}">
                            <i class="bi bi-clock display-6 text-warning"></i>
                            <h6 class="fw-bold mt-2">Daily Rental</h6>
                            <p class="small text-muted mb-1">Perfect for short trips (1–6 days)</p>
                            <div class="fw-bold text-dark">R{{ number_format($vehicle->rental_price_day) }}/day</div>
                        </div>
                    </div>

                    <!-- Weekly -->
                    <div class="col-md-4">
                        <div class="option-card p-3 border rounded-4 h-100" data-type="week"
                             data-price="{{ $vehicle->rental_price_week }}">
                            <i class="bi bi-calendar-event display-6 text-warning"></i>
                            <h6 class="fw-bold mt-2">Weekly Rental</h6>
                            <p class="small text-muted mb-1">Great for adventures (1–4 weeks)</p>
                            <div class="fw-bold text-dark">R{{ number_format($vehicle->rental_price_week) }}/week</div>
                        </div>
                    </div>

                    <!-- Monthly -->
                    <div class="col-md-4">
                        <div class="option-card p-3 border rounded-4 h-100" data-type="month"
                             data-price="{{ $vehicle->rental_price_month }}">
                            <i class="bi bi-box display-6 text-warning"></i>
                            <h6 class="fw-bold mt-2">Monthly Rental</h6>
                            <p class="small text-muted mb-1">Best value (1–4 months)</p>
                            <div class="fw-bold text-dark">R{{ number_format($vehicle->rental_price_month) }}/month</div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Rental Start Date -->
                <div class="mb-3 d-none" id="dateSection">
                    <label for="rentalStartDate" class="form-label">Rental Start Date</label>
                    <input type="date" id="rentalStartDate" class="form-control rounded-3">
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
                <!-- Step 2 -->
                <button type="button" class="btn btn-warning fw-bold rounded-3" 
                        data-bs-target="#addonsModal" data-bs-toggle="modal" data-bs-dismiss="modal">
                    Continue to Add-Ons
                </button>
            </div>
        </div>
    </div>
</div>
