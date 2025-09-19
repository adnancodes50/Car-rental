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

<form id="purchaseForm" method="POST">
    @csrf
    <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
    <input type="hidden" name="total_price" value="{{ $vehicle->purchase_price }}">

    <!-- Step 1: Vehicle Info -->
    <div class="modal fade" id="purchaseModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content rounded-4 shadow-lg overflow-hidden">
                <div class="d-flex justify-content-between align-items-center p-4 border-bottom">
                    <h2 class="h4 fw-bold mb-0">Purchase {{ $vehicle->name }}</h2>
                    <button type="button" class="btn-close text-secondary" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <h5 class="fw-semibold mb-2">Purchase Process Information</h5>
                        <p class="text-muted mb-0">Pay a deposit to place this vehicle under offer. Full process
                            continues offline.</p>
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
                    <button type="button" id="purchaseStep1Next" class="btn btn-dark w-100">Continue</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 2: Customer Info -->
    <div class="modal fade" id="purchaseCustomer" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content rounded-4 shadow-lg overflow-hidden">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"> Customer Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">

                    <div class="col-md-12">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control mb-3 rounded-3" placeholder="Full Name"
                            required>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control mb-3 rounded-3" placeholder="Email"
                            required>

                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" class="form-control mb-3 rounded-3" placeholder="Phone Number"
                            required>

                    </div>

                    <div class="mb-3">
                        <label class="form-label">Country</label>

                        <select name="country" class="form-select mb-3 rounded-3" required>
                            <option value="" disabled selected>Select your country</option>
                            @foreach($countries as $country)
                                <option value="{{ $country }}">{{ $country }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" data-bs-target="#purchaseModal"
                        data-bs-toggle="modal">Back</button>
                    <button type="button" id="purchaseStep2Next" class="btn btn-dark">Continue to Payment</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 3a: Payment Method Selection -->
    <div class="modal fade" id="purchasePayment" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 shadow">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Select Payment Method</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 align-items-stretch">
                        <!-- Stripe -->
                        <div class="col-12 col-md-6">
                            <input type="radio" name="payment_method" id="stripe" value="stripe" class="btn-check"
                                autocomplete="off" required>
                            <label for="stripe" class="card pay-option btn w-100">
                                <div class="icon-wrap">
                                    <!-- Use an icon or small logo -->
                                    <i class="bi bi-credit-card"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">Stripe</div>
                                    <small class="text-muted">International Cards</small>
                                </div>
                            </label>
                        </div>

                        <!-- PayFast -->
                        <div class="col-12 col-md-6">
                            <input type="radio" name="payment_method" id="payfast" value="payfast" class="btn-check"
                                autocomplete="off" required>
                            <label for="payfast" class="card pay-option btn w-100">
                                <div class="icon-wrap">
                                    <!-- Replace with your PayFast logo if you have one -->
                                    <i class="bi bi-lightning-charge"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">PayFast</div>
                                    <small class="text-muted">South Africa</small>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary me-auto" data-bs-target="#purchaseCustomer"
                        data-bs-toggle="modal">Back</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 3b: Stripe Card Input -->
    <div class="modal fade" id="stripePaymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 shadow">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Stripe Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 align-items-stretch">
                        <!-- LEFT: Purchase Summary -->
                        <div class="col-12 col-lg-5">
                            <div class="border rounded-3 p-3 h-100 d-flex flex-column">
                                <h5 class="fw-bold text-center mb-3">Purchase Summary</h5>

                                {{-- Vehicle --}}
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <img src="{{ $vehicle->mainImage() }}" alt="{{ $vehicle->name }}" class="rounded"
                                        style="width:72px;height:72px;object-fit:cover;">
                                    <div>
                                        <div class="fw-semibold">{{ $vehicle->name }}</div>
                                        <small class="text-muted">{{ $vehicle->year }} {{ $vehicle->model }}</small>
                                    </div>
                                </div>

                                {{-- Key facts (fill what you have) --}}
                                <ul class="list-unstyled small text-muted mb-3">
                                    @if($vehicle->engine)
                                        <li><i class="bi bi-gear-fill me-1"></i>Engine: {{ $vehicle->engine }}</li>
                                    @endif
                                    @if($vehicle->transmission)
                                        <li><i class="bi bi-gear-wide-connected me-1"></i>Transmission:
                                            {{ $vehicle->transmission }}
                                        </li>
                                    @endif
                                    @if($vehicle->mileage)
                                        <li><i class="bi bi-speedometer2 me-1"></i>Mileage:
                                            {{ number_format($vehicle->mileage) }} km
                                        </li>
                                    @endif
                                    @if($vehicle->location)
                                        <li><i class="bi bi-geo-alt me-1"></i>Location: {{ $vehicle->location }}</li>
                                    @endif
                                </ul>

                                {{-- Prices --}}
                                @php
                                    $vehiclePrice = (float) ($vehicle->purchase_price ?? 0);
                                    $deposit = (float) ($vehicle->deposit_amount ?? 0);
                                    $remaining = max($vehiclePrice - $deposit, 0);
                                @endphp

                                <div class="bg-light rounded p-2 mb-2">
                                    <div class="small text-muted mb-1">Price Details</div>

                                    <div class="d-flex justify-content-between small">
                                        <span>Vehicle Price</span>
                                        <span
                                            id="purchaseSummaryVehiclePrice">R{{ number_format($vehiclePrice, 2) }}</span>
                                    </div>

                                    <div class="d-flex justify-content-between small">
                                        <span>Deposit (Due Now)</span>
                                        <span id="purchaseSummaryDeposit">
                                            {{ $deposit > 0 ? 'R' . number_format($deposit, 2) : '—' }}
                                        </span>
                                    </div>

                                    <div class="d-flex justify-content-between small">
                                        <span>Payable After Deposit</span>
                                        <span id="purchaseSummaryRemaining">
                                            {{ $deposit > 0 ? 'R' . number_format($remaining, 2) : 'R' . number_format($vehiclePrice, 2) }}
                                        </span>
                                    </div>

                                    <div class="border-top mt-2 pt-2 d-flex justify-content-between">
                                        <span class="fw-semibold">Total Due Now</span>
                                        <span class="fw-bold text-success" id="purchaseSummaryTotal">
                                            {{ $deposit > 0 ? 'R' . number_format($deposit, 2) : 'R' . number_format($vehiclePrice, 2) }}
                                        </span>
                                    </div>
                                </div>


                                {{-- Customer (optional — fill via JS if you have a form before) --}}
                                <div class="border-top pt-2 mt-2 small">
                                    <div class="text-muted mb-1">Customer</div>
                                    <div id="purchaseSummaryCustomerName"><!-- JS fill --></div>
                                    <div class="text-muted" id="purchaseSummaryCustomerEmail"><!-- JS fill --></div>
                                </div>

                                {{-- Owner / contact (optional if you have landing settings) --}}
                                @if(isset($landing) && (($landing->phone_link ?? null) || ($landing->phone_btn_text ?? null)))
                                                            <div class="border-top pt-2 mt-2">
                                                                <div class="small text-muted mb-1">Owner / Contact</div>
                                                                <a href="{{ $landing->phone_link ?? '#' }}"
                                                                    class="d-inline-flex align-items-center gap-2 text-decoration-none">
                                                                    <i class="bi bi-telephone"></i>
                                                                    <span>
                                                                        {{ $landing->phone_btn_text
                                    ?? (isset($landing->phone_link) ? preg_replace('/^tel:/', '', $landing->phone_link) : 'Contact') }}
                                                                    </span>
                                                                </a>
                                                            </div>
                                @endif

                                <div class="mt-auto"></div>
                            </div>
                        </div>

                        <!-- RIGHT: Stripe card inputs -->
                        <div class="col-12 col-lg-7">
                            <div id="card-element">
                                <div id="card-number" class="form-control mb-3"></div>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div id="card-expiry" class="form-control"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div id="card-cvc" class="form-control"></div>
                                    </div>
                                </div>
                                <div id="card-errors" class="text-danger mt-2"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary me-auto" data-bs-target="#purchaseCustomer"
                        data-bs-toggle="modal">Back</button>
                    <button type="button" id="purchaseStripePayButton" class="btn btn-dark">Pay with Stripe</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 4: Thank You -->
    <div class="modal fade" id="purchaseThankYou" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content rounded-4 shadow text-center p-4 border-0">
                <div class="modal-body">
                    <h4 class="fw-bold mb-2 text-success">Thank You!</h4>
                    <p class="text-muted mb-4">Your purchase has been successfully completed.</p>
                    <button type="button" class="btn btn-success fw-bold px-4 rounded-pill"
                        data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</form>


<style>
    /* Make each card tall and centered; equal heights */
    #purchasePayment .pay-option {
        min-height: 160px;
        /* adjust height to taste */
        display: flex;
        flex-direction: row;
        /* icon + text side-by-side */
        align-items: center;
        justify-content: center;
        gap: 12px;
        border: 1px solid #dee2e6;
        border-radius: .75rem;
        padding: 20px;
        text-align: left;
        transition: box-shadow .15s ease, transform .15s ease, border-color .15s ease;
    }

    #purchasePayment .pay-option:hover {
        transform: translateY(-2px);
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .08);
    }

    #purchasePayment .btn-check:checked+.pay-option {
        border-color: #0d6efd;
        box-shadow: 0 0 0 .25rem rgba(13, 110, 253, .2);
    }

    /* Icon container */
    #purchasePayment .icon-wrap {
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

    #purchasePayment .icon-wrap i,
    #purchasePayment .icon-wrap img {
        width: 28px;
        height: 28px;
        object-fit: contain;
    }


    #stripePaymentModal .form-control {
        min-height: 44px;
        display: flex;
        align-items: center;
        padding-top: .5rem;
        padding-bottom: .5rem;
    }

    /* Force a single row on md+; stacks on xs for responsiveness */
    @media (min-width: 768px) {
        #purchasePayment .col-md-6 {
            display: flex;
        }

        #purchasePayment .pay-option {
            width: 100%;
        }
    }
</style>

<script src="https://js.stripe.com/v3/"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // --- Stripe setup ---
        const STRIPE_PK = "{{ config('services.stripe.key') }}";
        if (!STRIPE_PK) {
            console.warn('Stripe publishable key missing. Set services.stripe.key');
        }
        const stripe = Stripe(STRIPE_PK);
        const elements = stripe.elements();
        const style = { base: { fontSize: '16px', color: '#32325d', '::placeholder': { color: '#a0aec0' } } };

        let cardNumber, cardExpiry, cardCvc;
        let stripeMounted = false; // prevent double mounting

        const cardErrorsEl = document.getElementById("card-errors");
        const purchaseForm = document.getElementById("purchaseForm");

        // Mount Elements the first time the Stripe modal is shown
        const stripeModalEl = document.getElementById("stripePaymentModal");
        stripeModalEl.addEventListener('show.bs.modal', () => {
            // Fill customer summary (left column)
            const name = purchaseForm.name?.value || '';
            const email = purchaseForm.email?.value || '';
            const nameEl = document.getElementById('purchaseSummaryCustomerName');
            const emailEl = document.getElementById('purchaseSummaryCustomerEmail');
            if (nameEl) nameEl.textContent = name;
            if (emailEl) emailEl.textContent = email;

            // Mount once
            if (!stripeMounted) {
                cardNumber = elements.create('cardNumber', { style });
                cardExpiry = elements.create('cardExpiry', { style });
                cardCvc = elements.create('cardCvc', { style });

                cardNumber.mount('#card-number');
                cardExpiry.mount('#card-expiry');
                cardCvc.mount('#card-cvc');

                cardNumber.on('change', (event) => {
                    cardErrorsEl.textContent = event.error ? event.error.message : '';
                });
                cardExpiry.on('change', (event) => {
                    if (event.error) cardErrorsEl.textContent = event.error.message;
                });
                cardCvc.on('change', (event) => {
                    if (event.error) cardErrorsEl.textContent = event.error.message;
                });

                stripeMounted = true;
            }

            // Clear any old error
            cardErrorsEl.textContent = '';
        });

        // --- Step 1 → Step 2 ---
        document.getElementById("purchaseStep1Next").addEventListener("click", function () {
            bootstrap.Modal.getInstance(document.getElementById("purchaseModal"))?.hide();
            new bootstrap.Modal(document.getElementById("purchaseCustomer")).show();
        });

        // --- Step 2 → Step 3a (save customer + open payment methods) ---
        document.getElementById("purchaseStep2Next").addEventListener("click", function () {
            const name = purchaseForm.name.value;
            const email = purchaseForm.email.value;
            const phone = purchaseForm.phone.value;
            const country = purchaseForm.country.value;

            if (!name || !email || !phone || !country) {
                alert("Fill all required fields."); return;
            }

            fetch("{{ route('purchase.store') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                body: JSON.stringify({
                    name, email, phone, country,
                    vehicle_id: purchaseForm.vehicle_id.value,
                    total_price: purchaseForm.total_price.value
                })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        let purchaseIdInput = purchaseForm.querySelector("input[name='purchase_id']");
                        if (!purchaseIdInput) {
                            purchaseIdInput = document.createElement("input");
                            purchaseIdInput.type = "hidden";
                            purchaseIdInput.name = "purchase_id";
                            purchaseForm.appendChild(purchaseIdInput);
                        }
                        purchaseIdInput.value = data.purchase_id;

                        bootstrap.Modal.getInstance(document.getElementById("purchaseCustomer"))?.hide();
                        new bootstrap.Modal(document.getElementById("purchasePayment")).show();
                    } else {
                        alert(data.message || "Failed to save customer info.");
                    }
                })
                .catch((e) => {
                    console.error(e);
                    alert("Network error while saving customer.");
                });
        });

        // --- Step 3a: choose payment method ---
        document.querySelectorAll("input[name='payment_method']").forEach(input => {
            input.addEventListener("change", function () {
                const selected = this.value;
                const paymentModal = bootstrap.Modal.getInstance(document.getElementById("purchasePayment"));
                paymentModal?.hide();

                if (selected === "stripe") {
                    new bootstrap.Modal(document.getElementById("stripePaymentModal")).show();
                } else {
                    // PayFast flow would go here (redirect or modal). For now show Thank You.
                    new bootstrap.Modal(document.getElementById("purchaseThankYou")).show();
                }
            });
        });

        // --- Pay with Stripe (card) ---
        document.getElementById("purchaseStripePayButton").addEventListener("click", async function () {
            cardErrorsEl.textContent = '';

            const purchaseIdInput = purchaseForm.querySelector("input[name='purchase_id']");
            if (!purchaseIdInput || !purchaseIdInput.value) {
                cardErrorsEl.textContent = "We couldn't find your purchase record. Please go back and complete your details.";
                return;
            }
            const purchase_id = purchaseIdInput.value;

            // Create PaymentMethod from card inputs
            const { paymentMethod, error: pmError } = await stripe.createPaymentMethod({
                type: 'card',
                card: cardNumber,
                billing_details: {
                    name: purchaseForm.name?.value || '',
                    email: purchaseForm.email?.value || ''
                }
            });
            if (pmError) { cardErrorsEl.textContent = pmError.message; return; }

            let res, raw, data;
            try {
                res = await fetch(`/purchase/${encodeURIComponent(purchase_id)}/pay-with-stripe`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({ payment_method_id: paymentMethod.id })
                });
                raw = await res.text();
                try { data = JSON.parse(raw); } catch { data = { success: false, message: raw }; }
            } catch (e) {
                console.error(e);
                cardErrorsEl.textContent = 'Network error while charging card.';
                return;
            }

            if (!res.ok) {
                cardErrorsEl.textContent = data.message || 'Payment failed (server error).';
                return;
            }

            // 3DS required
            if (data.requires_action && data.payment_intent_client_secret) {
                const result = await stripe.confirmCardPayment(data.payment_intent_client_secret);
                if (result.error) {
                    cardErrorsEl.textContent = result.error.message || 'Authentication failed.';
                    return;
                }
                // Success after 3DS
                bootstrap.Modal.getInstance(document.getElementById("stripePaymentModal"))?.hide();
                new bootstrap.Modal(document.getElementById("purchaseThankYou")).show();
                return;
            }

            if (data.success) {
                // Paid immediately
                bootstrap.Modal.getInstance(document.getElementById("stripePaymentModal"))?.hide();
                new bootstrap.Modal(document.getElementById("purchaseThankYou")).show();
                return;
            }

            cardErrorsEl.textContent = data.message || 'Payment could not be completed.';
        });
    });
</script>
