<!-- ================= PURCHASE MODAL ================= -->
<form id="purchaseForm" method="POST">
    @csrf
    <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
    <input type="hidden" name="total_price" value="{{ $vehicle->purchase_price }}">

    <!-- Step 1: Vehicle & Deposit Info -->
    <div class="modal fade" id="purchaseModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content rounded-4 shadow-lg overflow-hidden">

                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center p-4 border-bottom">
                    <h2 class="h4 fw-bold mb-0">Purchase {{ $vehicle->name }}</h2>
                    <button type="button" class="btn-close text-secondary" data-bs-dismiss="modal"></button>
                </div>

                <!-- Body -->
                <div class="modal-body p-4">
                    <!-- Info Section -->
                    <div class="text-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-warning mb-3">
                            <circle cx="24" cy="24" r="22"></circle>
                            <line x1="24" y1="16" x2="24" y2="28"></line>
                            <line x1="24" y1="36" x2="24.01" y2="36"></line>
                        </svg>
                        <h5 class="fw-semibold mb-2">Purchase Process Information</h5>
                        <p class="text-muted mb-0">To begin the purchase process, please pay a deposit to place this
                            vehicle under offer. The full purchase process will continue offline with one of our
                            representatives.</p>
                    </div>

                    <!-- Vehicle Details Section -->
                    <div class="bg-light rounded-3 p-3 mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-medium text-secondary">Vehicle:</span>
                            <span class="fw-normal text-dark">{{ $vehicle->name }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-medium text-secondary">Sale Price:</span>
                            <span class="fw-normal text-dark">R{{ number_format($vehicle->purchase_price) }} ZAR</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="fw-medium text-secondary">Required Deposit:</span>
                            <span class="fw-bold text-warning">R{{ number_format($vehicle->deposit_amount) }} ZAR</span>
                        </div>
                    </div>

                    <!-- Continue Button -->
                    <button type="button" id="purchaseStep1Next" class="btn btn-warning w-100 fw-bold">Continue to
                        Payment</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 2: Payment Gateway -->
    <div class="modal fade" id="purchasePayment" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 shadow">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Step 2 of 2: Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="fw-bold">Total Price: R{{ number_format($vehicle->purchase_price) }}</p>
                    <p>Select your payment method:</p>
                    <div class="mb-3">
                        <input type="radio" id="card" name="payment_method" value="card" required>
                        <label for="card">Credit/Debit Card</label>
                    </div>
                    <div class="mb-3">
                        <input type="radio" id="paypal" name="payment_method" value="paypal" required>
                        <label for="paypal">PayPal</label>
                    </div>
                    <!-- Add your payment gateway integration here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-target="#purchaseModal"
                        data-bs-toggle="modal">Back</button>
                    <button type="submit" class="btn btn-success fw-bold">Confirm & Pay</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 3: Thank You -->
    <div class="modal fade" id="purchaseThankYou" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content rounded-4 shadow text-center p-4">
                <h4 class="fw-bold mb-3">Thank You!</h4>
                <p>Your purchase has been successfully completed.</p>
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</form>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const step1Next = document.getElementById("purchaseStep1Next");
        const purchaseForm = document.getElementById("purchaseForm");

        step1Next.addEventListener("click", function () {
            // Directly proceed to Step 2 (no customer info needed)
            const step1Modal = bootstrap.Modal.getInstance(document.getElementById("purchaseModal"));
            if (step1Modal) step1Modal.hide();

            const step2Modal = new bootstrap.Modal(document.getElementById("purchasePayment"));
            step2Modal.show();
        });

        // Back button from Step 2 → Step 1
        const step2BackBtn = document.querySelector("#purchasePayment [data-bs-target='#purchaseModal']");
        step2BackBtn.addEventListener("click", function (e) {
            e.preventDefault();
            const step2Modal = bootstrap.Modal.getInstance(document.getElementById("purchasePayment"));
            if (step2Modal) step2Modal.hide();

            const step1Modal = new bootstrap.Modal(document.getElementById("purchaseModal"));
            step1Modal.show();
        });

        // Form submit → Step 3 Thank You
        purchaseForm.addEventListener("submit", function (e) {
            e.preventDefault(); // Remove if using normal submit
            // Process payment here (AJAX / Stripe / PayPal)

            // Hide Step 2
            const step2Modal = bootstrap.Modal.getInstance(document.getElementById("purchasePayment"));
            if (step2Modal) step2Modal.hide();

            // Show Thank You modal
            const thankYouModal = new bootstrap.Modal(document.getElementById("purchaseThankYou"));
            thankYouModal.show();
        });
    });
</script>
