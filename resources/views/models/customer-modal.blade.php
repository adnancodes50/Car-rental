<!-- Customer Details Modal -->
<div class="modal fade" id="customerDetailsModal" tabindex="-1" aria-labelledby="customerDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 shadow">

            <div class="modal-header flex-column text-center">
                <h5 class="modal-title fw-bold" id="customerDetailsModalLabel">Enter Your Details</h5>
                <div class="w-100 fw-bold mt-2">Please provide your information</div>
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="customerDetailsForm" method="POST" action="{{ route('bookings.store') }}">
                    @csrf
                    <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
                    
                    <div class="row g-3">
                        <!-- Full Name -->
                        <div class="col-md-12">
                            <label for="customerName" class="form-label">Full Name</label>
                            <input type="text" class="form-control rounded-3" id="customerName" name="name" placeholder="John Doe" required>
                        </div>

                        <!-- Email -->
                        <div class="col-md-12">
                            <label for="customerEmail" class="form-label">Email</label>
                            <input type="email" class="form-control rounded-3" id="customerEmail" name="email" placeholder="you@example.com" required>
                        </div>

                        <!-- Phone -->
                        <div class="col-md-12">
                            <label for="customerPhone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control rounded-3" id="customerPhone" name="phone" placeholder="+27 123 456 7890" required>
                        </div>

                        <!-- Country -->
                        <div class="col-md-12">
                            <label for="customerCountry" class="form-label">Country of Residence</label>
                            <select id="customerCountry" name="country" class="form-select rounded-3" required>
                                <option value="" selected disabled>Select your country</option>
                                <option value="South Africa">South Africa</option>
                                <option value="United States">United States</option>
                                <option value="United Kingdom">United Kingdom</option>
                                <option value="Germany">Germany</option>
                                <option value="India">India</option>
                                <!-- Add more as needed -->
                            </select>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" 
                        data-bs-target="#addonsModal" data-bs-toggle="modal" data-bs-dismiss="modal">
                    Back
                </button>
                <button type="submit" form="customerDetailsForm" class="btn btn-success fw-bold">
                    Confirm Booking
                </button>
            </div>
        </div>
    </div>
</div>
