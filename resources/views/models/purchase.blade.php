@php
$countries = [
    "Afghanistan","Albania","Algeria","Andorra","Angola","Antigua and Barbuda","Argentina","Armenia",
    "Australia","Austria","Azerbaijan","Bahamas","Bahrain","Bangladesh","Barbados","Belarus","Belgium",
    "Belize","Benin","Bhutan","Bolivia","Bosnia and Herzegovina","Botswana","Brazil","Brunei","Bulgaria",
    "Burkina Faso","Burundi","Cabo Verde","Cambodia","Cameroon","Canada","Central African Republic",
    "Chad","Chile","China","Colombia","Comoros","Congo (Congo-Brazzaville)","Costa Rica","Croatia",
    "Cuba","Cyprus","Czechia","Democratic Republic of the Congo","Denmark","Djibouti","Dominica",
    "Dominican Republic","Ecuador","Egypt","El Salvador","Equatorial Guinea","Eritrea","Estonia",
    "Eswatini","Ethiopia","Fiji","Finland","France","Gabon","Gambia","Georgia","Germany","Ghana",
    "Greece","Grenada","Guatemala","Guinea","Guinea-Bissau","Guyana","Haiti","Holy See","Honduras",
    "Hungary","Iceland","India","Indonesia","Iran","Iraq","Ireland","Israel","Italy","Jamaica","Japan",
    "Jordan","Kazakhstan","Kenya","Kiribati","Kuwait","Kyrgyzstan","Laos","Latvia","Lebanon","Lesotho",
    "Liberia","Libya","Liechtenstein","Lithuania","Luxembourg","Madagascar","Malawi","Malaysia","Maldives",
    "Mali","Malta","Marshall Islands","Mauritania","Mauritius","Mexico","Micronesia","Moldova","Monaco",
    "Mongolia","Montenegro","Morocco","Mozambique","Myanmar","Namibia","Nauru","Nepal","Netherlands",
    "New Zealand","Nicaragua","Niger","Nigeria","North Korea","North Macedonia","Norway","Oman","Pakistan",
    "Palau","Palestine State","Panama","Papua New Guinea","Paraguay","Peru","Philippines","Poland",
    "Portugal","Qatar","Romania","Russia","Rwanda","Saint Kitts and Nevis","Saint Lucia",
    "Saint Vincent and the Grenadines","Samoa","San Marino","Sao Tome and Principe","Saudi Arabia",
    "Senegal","Serbia","Seychelles","Sierra Leone","Singapore","Slovakia","Slovenia","Solomon Islands",
    "Somalia","South Africa","South Korea","South Sudan","Spain","Sri Lanka","Sudan","Suriname",
    "Sweden","Switzerland","Syria","Tajikistan","Tanzania","Thailand","Timor-Leste","Togo","Tonga",
    "Trinidad and Tobago","Tunisia","Turkey","Turkmenistan","Tuvalu","Uganda","Ukraine","United Arab Emirates",
    "United Kingdom","United States","Uruguay","Uzbekistan","Vanuatu","Venezuela","Vietnam","Yemen","Zambia","Zimbabwe"
];
@endphp



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

                    <button type="button" id="purchaseStep1Next" class="btn btn-warning w-100 fw-bold">Continue</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 2: Customer Information -->
    <div class="modal fade" id="purchaseCustomer" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content rounded-4 shadow-lg overflow-hidden">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Step 2 of 3: Customer Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control rounded-3" name="name" placeholder="John Doe" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control rounded-3" name="email" placeholder="you@example.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" class="form-control rounded-3" name="phone" placeholder="+27 123 456 7890" required>
                    </div>
                   <div class="mb-3">
    <label class="form-label">Country</label>
    <select name="country" class="form-select rounded-3" required>
        <option value="" disabled selected>Select your country</option>
        @foreach($countries as $country)
            <option value="{{ $country }}">{{ $country }}</option>
        @endforeach
    </select>
</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-target="#purchaseModal" data-bs-toggle="modal">Back</button>
                    <button type="button" id="purchaseStep2Next" class="btn btn-warning fw-bold">Continue to Payment</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 3: Payment Gateway -->
    <div class="modal fade" id="purchasePayment" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 shadow">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Step 3 of 3: Payment</h5>
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
                    <!-- Add payment gateway integration -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-target="#purchaseCustomer" data-bs-toggle="modal">Back</button>
                    <button type="submit" class="btn btn-success fw-bold">Confirm & Pay</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 4: Thank You -->
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
document.addEventListener("DOMContentLoaded", function() {
    // Step 1 → Step 2
    document.getElementById("purchaseStep1Next").addEventListener("click", function() {
        const step1Modal = bootstrap.Modal.getInstance(document.getElementById("purchaseModal"));
        if(step1Modal) step1Modal.hide();

        new bootstrap.Modal(document.getElementById("purchaseCustomer")).show();
    });

    // Step 2 → Step 3 (AJAX save)
    document.getElementById("purchaseStep2Next").addEventListener("click", function() {
        const form = document.getElementById("purchaseForm");
        const name = form.querySelector("input[name='name']").value;
        const email = form.querySelector("input[name='email']").value;
        const phone = form.querySelector("input[name='phone']").value;
        const country = form.querySelector("select[name='country']").value;
        const vehicle_id = form.querySelector("input[name='vehicle_id']").value;
        const total_price = form.querySelector("input[name='total_price']").value;

        if(!name || !email || !phone || !country){
            alert("Please fill in all required fields.");
            return;
        }

        // Submit customer info via AJAX
        fetch("{{ route('purchase.store') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                name, email, phone, country, vehicle_id, total_price
            })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success){
                // Save purchase_id in hidden input for later if needed
                let purchaseIdInput = form.querySelector("input[name='purchase_id']");
                if(!purchaseIdInput){
                    purchaseIdInput = document.createElement("input");
                    purchaseIdInput.type = "hidden";
                    purchaseIdInput.name = "purchase_id";
                    form.appendChild(purchaseIdInput);
                }
                purchaseIdInput.value = data.purchase_id;

                // Move to Step 3
                const step2Modal = bootstrap.Modal.getInstance(document.getElementById("purchaseCustomer"));
                if(step2Modal) step2Modal.hide();
                new bootstrap.Modal(document.getElementById("purchasePayment")).show();
            } else {
                alert("Failed to save customer info.");
            }
        })
        .catch(err => console.error(err));
    });

    // Step 3 → Thank You
    const purchaseForm = document.getElementById("purchaseForm");
    purchaseForm.addEventListener("submit", function(e){
        e.preventDefault();
        // Here you can integrate payment gateway using the saved purchase_id

        const step3Modal = bootstrap.Modal.getInstance(document.getElementById("purchasePayment"));
        if(step3Modal) step3Modal.hide();

        new bootstrap.Modal(document.getElementById("purchaseThankYou")).show();
    });
});
</script>
