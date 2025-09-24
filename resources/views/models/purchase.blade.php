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
                {{-- <div class="d-flex justify-content-between align-items-center p-4 border-bottom">
<h2 class="h4 fw-bold mb-0">
  <i class="bi bi-car-front-fill me-2"></i>
  Purchase {{ $vehicle->name }}
</h2>
                    <button type="button" class="btn-close text-secondary" data-bs-dismiss="modal"></button>
                </div> --}}
                <div class="modal-header">
                            <h5 class="modal-title fw-bold"><i class="bi bi-car-front-fill me-2"></i>
  Purchase {{ $vehicle->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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

      <!-- Header -->
      {{-- <div class="modal-header border-0">
        <h5 class="modal-title fw-bold d-flex align-items-center">
          <i class="bi bi-person-circle me-2"></i>
          Enter Your Details
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div> --}}

      <div class="modal-header">
                            <h5 class="modal-title fw-bold"><i class="bi bi-person-circle me-2"></i>
          Enter Your Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>




      <!-- Body -->
      <div class="modal-body px-4">
        <div class="row g-3">

          <div class="col-12">
            <label class="form-label">
              <i class="me-2 text-secondary"></i> Full Name
            </label>
            <input type="text" name="name" class="form-control rounded-3" placeholder="John Doe" required>
          </div>

          <div class="col-12">
            <label class="form-label">
              <i class=" me-2 text-secondary"></i> Email
            </label>
            <input type="email" name="email" class="form-control rounded-3" placeholder="you@example.com" required>
          </div>

          <div class="col-12">
            <label class="form-label">
              <i class="me-2 text-secondary"></i> Phone
            </label>
            <input type="tel" name="phone" class="form-control rounded-3" placeholder="+27 123 456 7890" required>
          </div>

          <div class="col-12">
            <label class="form-label">
              <i class=" me-2 text-secondary"></i> Country
            </label>
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
          data-bs-target="#purchaseModal" data-bs-toggle="modal">
          Back
        </button>
        <button type="button" id="purchaseStep2Next" class="btn btn-dark rounded-3 px-4">
          Continue to Payment
        </button>
      </div>

    </div>
  </div>
</div>


@php
$stripe = Cache::get('payments.stripe', config('payments.stripe'));
$payfast = Cache::get('payments.payfast', config('payments.payfast'));

// Count how many payment methods are enabled
$enabledCount = ($stripe['enabled'] ? 1 : 0) + ($payfast['enabled'] ? 1 : 0);
@endphp

<!-- Purchase Payment Modal -->
<div class="modal fade" id="purchasePayment" tabindex="-1" aria-hidden="true">
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

          @if($stripe['enabled'])
          <!-- Stripe -->
          <div class="col-12 {{ $enabledCount === 2 ? 'col-md-6' : 'col-md-12' }}">
              <input type="radio" name="payment_method" id="purchaseStripe" value="stripe" class="btn-check" autocomplete="off" required>
              <label for="purchaseStripe" class="card btn w-100 purchase-pay-option p-3 flex-column">
                  <div class="text-center mb-2">
                      <img src="{{ asset('images/stripe.png') }}" class="rounded-3" alt="Stripe" style="width: 80px;">
                  </div>
                  <div class="purchase-pay-text">
                      <div class="fw-bold">Stripe (Card)</div>
                      <small class="text-muted">Visa • Mastercard • Amex</small>
                  </div>
              </label>
          </div>
          @endif

          @if($payfast['enabled'])
          <!-- PayFast -->
          <div class="col-12 {{ $enabledCount === 2 ? 'col-md-6' : 'col-md-12' }}">
              <input type="radio" name="payment_method" id="purchasePayfast" value="payfast" class="btn-check" autocomplete="off" required>
              <label for="purchasePayfast" class="card btn w-100 purchase-pay-option p-3 flex-column">
                  <div class="text-center mb-2">
                      <img src="{{ asset('images/payfast.png') }}" class="rounded-3" alt="PayFast" style="width: 80px;">
                  </div>
                  <div class="purchase-pay-text">
                      <div class="fw-bold">PayFast</div>
                      <small class="text-muted">South Africa payments</small>
                  </div>
              </label>
          </div>
          @endif

          @if($enabledCount === 0)
          <div class="col-12">
              <div class="alert alert-warning text-center">
                  No payment methods are currently available.
              </div>
          </div>
          @endif

        </div>
      </div>

      <!-- Footer -->
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-outline-secondary" data-bs-target="#purchaseCustomer" data-bs-toggle="modal">
          Back
        </button>
      </div>

    </div>
  </div>
</div>

    <!-- Step 3b: Stripe Card Input -->
    <div class="modal fade mt-5" id="stripePaymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 shadow">
                <div class="modal-header">
<h5 class="modal-title fw-bold">
  <i class="bi bi-credit-card-fill me-2"></i>
  Stripe Payment and Vehicle Summary
</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
               <div class="modal-body">
  <div class="container-fluid">

    {{-- ROW 1: Vehicle header --}}
    <div class="row g-3 align-items-center mb-3">
      <div class="col-12 d-flex align-items-center gap-3">
        <img
          src="{{ $vehicle->mainImage() }}"
          alt="{{ $vehicle->name }}"
          class="rounded shadow-sm"
          style="width:88px;height:88px;object-fit:cover;"
        >
        <div class="flex-grow-1">
          <div class="d-flex flex-wrap align-items-center justify-content-between">
            <div>
              <h5 class="mb-1">{{ $vehicle->name }}</h5>
              <small class="text-muted">{{ $vehicle->year }} {{ $vehicle->model }}</small>
            </div>
            {{-- Price summary (deposit + totals) --}}
            @php
              $vehiclePrice = (float) ($vehicle->purchase_price ?? 0);
              $deposit      = (float) ($vehicle->deposit_amount ?? 0);
              $remaining    = max($vehiclePrice - $deposit, 0);
            @endphp
            <div class="text-end">
              <div class="small text-muted">Vehicle Price</div>
              <div class="fw-semibold">R{{ number_format($vehiclePrice, 2) }}</div>
            </div>
          </div>

          {{-- quick facts --}}
          <ul class="list-inline small text-muted mb-0 mt-2">
            @if($vehicle->engine)
              <li class="list-inline-item me-3"><i class="bi bi-gear-fill me-1"></i>Engine: {{ $vehicle->engine }}</li>
            @endif
            @if($vehicle->transmission)
              <li class="list-inline-item me-3"><i class="bi bi-gear-wide-connected me-1"></i>{{ $vehicle->transmission }}</li>
            @endif
            @if($vehicle->mileage)
              <li class="list-inline-item me-3"><i class="bi bi-speedometer2 me-1"></i>{{ number_format($vehicle->mileage) }} km</li>
            @endif
            @if($vehicle->location)
              <li class="list-inline-item"><i class="bi bi-geo-alt me-1"></i>{{ $vehicle->location }}</li>
            @endif
          </ul>
        </div>
      </div>
    </div>

    {{-- Optional mini price band --}}
    @php
  $vehiclePrice = (float) ($vehicle->purchase_price ?? 0);
  $deposit      = (float) ($vehicle->deposit_amount ?? 0);

  // keep values sane
  $deposit   = max(0, min($deposit, $vehiclePrice));
  $remaining = max($vehiclePrice - $deposit, 0);
@endphp

<div class="row mb-3">
  <div class="col-12">
    <div class="bg-light rounded-3 p-2 px-3">
      <div class="d-flex justify-content-between small">
        <span>Vehicle Price</span>
        <span>R{{ number_format($vehiclePrice, 2) }}</span>
      </div>

      <div class="d-flex justify-content-between small">
        <span>Deposit (Due Now)</span>
        <span>R{{ number_format($deposit, 2) }}</span>
      </div>

      {{-- <div class="d-flex justify-content-between small">
        <span>Payable After Deposit</span>
        <span>R{{ number_format($remaining, 2) }}</span>
      </div> --}}

      <div class="border-top mt-2 pt-2 d-flex justify-content-between">
        <span class="fw-semibold">Total Due Now</span>
        <span class="fw-bold text-success">R{{ number_format($deposit, 2) }}</span>
      </div>

      <div class="d-flex justify-content-between mt-1">
        <span class="fw-semibold text-muted">Total Payable Later</span>
        <span class="fw-bold text-muted">R{{ number_format($remaining, 2) }}</span>
      </div>
    </div>
  </div>
</div>


{{-- ROW 2: Owner / Contact (modern, simple, icons only) --}}
@if(isset($landing) && (
  ($landing->phone_link ?? null) ||
  ($landing->whatsapp_link ?? null) ||
  ($landing->email_link ?? null)
))
  <div class="row mb-3">
    <div class="col-12">
      <div class="owner-contact-box">
        <div class="small text-muted mb-2">Owner / Contact us For More Information</div>

        <div class="contact-grid">
          {{-- Phone (left) --}}
          <div class="contact-item text-start">
            @if(!empty($landing->phone_link))
              <a href="{{ $landing->phone_link }}"
                 class="contact-btn contact-phone"
                 title="{{ $landing->phone_btn_text ?? preg_replace('/^tel:/i', '', $landing->phone_link) }}"
                 aria-label="Call">
                <i class="bi bi-telephone"></i>
              </a>
            @endif
          </div>

          {{-- WhatsApp (center) --}}
          <div class="contact-item text-center">
            @if(!empty($landing->whatsapp_link))
              @php
                $waTitle = $landing->whatsapp_btn_text
                  ?? preg_replace(['/^https?:\/\/wa\.me\//i','/^whatsapp:\/\/send\?phone=/i'], '', $landing->whatsapp_link);
                $waTitle = $waTitle ?: 'WhatsApp';
              @endphp
              <a href="{{ $landing->whatsapp_link }}"
                 class="contact-btn contact-wa"
                 target="_blank" rel="noopener"
                 title="{{ $waTitle }}" aria-label="WhatsApp">
                <i class="bi bi-whatsapp"></i>
              </a>
            @endif
          </div>

          {{-- Email (right) --}}
          <div class="contact-item text-end">
            @if(!empty($landing->email_link))
              <a href="{{ $landing->email_link }}"
                 class="contact-btn contact-mail"
                 title="{{ $landing->email_btn_text ?? preg_replace('/^mailto:/i', '', $landing->email_link) }}"
                 aria-label="Email">
                <i class="bi bi-envelope"></i>
              </a>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
@endif


    {{-- ROW 3: Stripe card inputs --}}
    <div class="row g-2">
      <div class="col-12">
        <div id="card-number" class="form-control stripe-input mb-2"></div>
      </div>
      <div class="col-12 col-md-6">
        <div id="card-expiry" class="form-control stripe-input"></div>
      </div>
      <div class="col-12 col-md-6">
        <div id="card-cvc" class="form-control stripe-input"></div>
      </div>
      <div class="col-12">
        <div id="card-errors" class="text-danger mt-2 small"></div>
      </div>
    </div>

  </div>
</div>


               <div class="modal-footer container">
    <button type="button" class="btn btn-outline-secondary me-auto"
        data-bs-target="#purchasePayment"
        data-bs-toggle="modal">Back</button>
    <button type="button" id="purchaseStripePayButton" class="btn btn-dark">Purchase Now</button>
</div>

            </div>
        </div>
    </div>

    <!-- Step 4: Thank You -->
    <div class="modal fade" id="purchaseThankYou" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content rounded-4 shadow text-center p-4 border-0">
        <div class="modal-body text-center py-5">

            <!-- Success icon badge -->
            <div class="d-inline-flex align-items-center justify-content-center bg-success-subtle text-success rounded-circle mb-3"
                style="width:84px;height:84px;">
            <i class="bi bi-check2-circle fs-1"></i>
            </div>

            <!-- Title & copy -->
            <h4 class="fw-bold mb-2">Thank you!</h4>
            <p class="text-secondary mb-4">
            Your purchase has been successfully completed.
            </p>

            {{-- <!-- Amount (centered above button) -->
            <div class="fw-bold fs-5 mb-4">
            Amount: <span id="purchaseAmount">R 5,000.00</span>
            </div> --}}

            <!-- Close button -->
            <button type="button"
                    class="btn btn-success fw-semibold px-4 rounded-pill"
                    data-bs-dismiss="modal">
            Close
            </button>
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


/* Wrapper card */
.owner-contact-box{
  border: 1px solid #e9ecef;
  border-radius: 14px;
  padding: 14px 16px;
  background: #fff;
}

/* 3 fixed slots (left/center/right) and nice spacing */
.contact-grid{
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  align-items: center;
}

/* Center content inside each cell */
.contact-item{
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Circular icon buttons */
.contact-btn{
  width: 48px;
  height: 48px;
  border-radius: 9999px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #e5e7eb;
  background: #f9fafb;
  transition: transform .15s ease, box-shadow .15s ease, background .15s ease, border-color .15s ease;
  font-size: 1.15rem;
}

/* Brand tints (subtle) */
.contact-phone{ border-color:#dee2e6; }
.contact-wa{    border-color:#19875433; background: #1987540d; }
.contact-mail{  border-color:#0d6efd33; background: #0d6efd0d; }

/* Hover feel */
.contact-btn:hover{
  transform: translateY(-1px);
  box-shadow: 0 .25rem .75rem rgba(0,0,0,.06);
  background: #ffffff;
}

/* Optional: slightly larger icons on hover */
.contact-btn:hover i{ transform: scale(1.05); }






/* Scope to the purchase modal by ID */
#purchasePayment .purchase-pay-option {
  min-height: 160px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  border: 1px solid #dee2e6;
  border-radius: .75rem;
  padding: 20px;
  text-align: left;
  transition: box-shadow .15s ease, transform .15s ease, border-color .15s ease;
}

#purchasePayment .purchase-pay-option:hover {
  transform: translateY(-2px);
  box-shadow: 0 .5rem 1rem rgba(0,0,0,.08);
}

#purchasePayment .btn-check:checked + .purchase-pay-option {
  border-color: #0d6efd;
  box-shadow: 0 0 0 .25rem rgba(13,110,253,.2);
}

/* Icon container */
#purchasePayment .purchase-icon-wrap {
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

#purchasePayment .purchase-icon-wrap img {
  width: 28px;
  height: 28px;
  object-fit: contain;
}

/* Text block */
#purchasePayment .purchase-pay-text {
  display: flex;
  flex-direction: column;
  text-align: center;
}

/* Responsive: row on md+, stack on xs */
@media (min-width: 768px) {
  #purchasePayment .col-md-6 {
    display: flex;
  }
  #purchasePayment .purchase-pay-option {
    width: 100%;
  }
}

</style>
<script src="https://js.stripe.com/v3/"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
  const stripe = Stripe("{{ config('services.stripe.key') }}");
  const elements = stripe.elements();
  const style = { base: { fontSize: '16px', color: '#32325d', '::placeholder': { color: '#a0aec0' } } };
  const cardNumber = elements.create('cardNumber', { style });
  const cardExpiry = elements.create('cardExpiry', { style });
  const cardCvc = elements.create('cardCvc', { style });
  cardNumber.mount('#card-number');
  cardExpiry.mount('#card-expiry');
  cardCvc.mount('#card-cvc');

  const form = document.getElementById("purchaseForm");

  // 🔹 Step 1 → Step 2
  document.getElementById("purchaseStep1Next").addEventListener("click", function () {
    bootstrap.Modal.getInstance(document.getElementById("purchaseModal"))?.hide();
    new bootstrap.Modal(document.getElementById("purchaseCustomer")).show();
  });

  // 🔹 Step 2 → Step 3a (Payment Method Selection)
  document.getElementById("purchaseStep2Next").addEventListener("click", function () {
    const name = (form.name.value || '').trim();
    const email = (form.email.value || '').trim();
    const phone = (form.phone.value || '').trim();
    const country = form.country.value;

    if (!name || !email || !phone || !country) {
      Swal.fire({
        icon: 'error',
        title: 'Missing Information',
        text: 'Please fill in all required customer details before continuing.'
      });
      return;
    }

    // Save details
    fetch("{{ route('purchase.store') }}", {
      method: "POST",
      headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
      body: JSON.stringify({
        name, email, phone, country,
        vehicle_id: form.vehicle_id.value,
        total_price: form.total_price.value
      })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        // ensure hidden purchase_id input exists
        let purchaseIdInput = form.querySelector("input[name='purchase_id']");
        if (!purchaseIdInput) {
          purchaseIdInput = document.createElement("input");
          purchaseIdInput.type = "hidden";
          purchaseIdInput.name = "purchase_id";
          form.appendChild(purchaseIdInput);
        }
        purchaseIdInput.value = data.purchase_id;

        // ✅ Go directly to Payment modal
        bootstrap.Modal.getInstance(document.getElementById("purchaseCustomer"))?.hide();
        new bootstrap.Modal(document.getElementById("purchasePayment")).show();
      } else {
        Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Unable to save details.' });
      }
    })
    .catch(err => {
      console.error(err);
      Swal.fire({ icon: 'error', title: 'Network error', text: 'Please try again.' });
    });
  });

// 🔹 Step 3a → Step 3b (Stripe / PayFast)
document.querySelectorAll("input[name='payment_method']").forEach(input => {
  input.addEventListener("change", async function () {
    const selected = this.value;
    const paymentModal = bootstrap.Modal.getInstance(document.getElementById("purchasePayment"));
    paymentModal?.hide();

    if (selected === "stripe") {
      new bootstrap.Modal(document.getElementById("stripePaymentModal")).show();
      return;
    }

    // ----- PAYFAST -----
    const formEl = document.getElementById("purchaseForm");
    const purchaseIdInput = formEl.querySelector("input[name='purchase_id']");

    if (!purchaseIdInput || !purchaseIdInput.value) {
      Swal.fire({ icon: 'error', title: 'Missing purchase', text: 'Please save your details first.' });
      // Reopen payment modal for them
      new bootstrap.Modal(document.getElementById("purchasePayment")).show();
      this.checked = false;
      return;
    }

const confirmed = await Swal.fire({
  icon: 'question',
  title: 'Proceed with PayFast?',
  text: 'You will be redirected to PayFast to complete your payment.',
  showCancelButton: true,
  confirmButtonText: 'Continue',
  cancelButtonText: 'Back',
  reverseButtons: true,
  customClass: {
    popup: 'rounded-4 shadow-lg', // 🔹 rounded SweetAlert box itself
    confirmButton: 'btn btn-dark rounded-pill px-4', // 🔹 pill button
    cancelButton: 'btn btn-outline-secondary rounded-pill me-3 px-4'
  },
  buttonsStyling: false
});



    if (!confirmed.isConfirmed) {
      this.checked = false;
      new bootstrap.Modal(document.getElementById("purchasePayment")).show();
      return;
    }

    try {
      // We’ll send name/email to help prefill PayFast form
      const res = await fetch(`/purchase/${encodeURIComponent(purchaseIdInput.value)}/payfast/init`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        body: JSON.stringify({
          name:  formEl.name?.value || '',
          email: formEl.email?.value || ''
        })
      });

      const data = await res.json();
      if (!res.ok || !data.success) {
        throw new Error(data.message || 'Failed to initialize PayFast.');
      }

      // Build a POST form to PayFast and auto-submit
      const pfForm = document.createElement('form');
      pfForm.method = 'POST';
      pfForm.action = data.action;
      pfForm.style.display = 'none';

      Object.entries(data.fields).forEach(([k, v]) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = k;
        input.value = v;
        pfForm.appendChild(input);
      });

      document.body.appendChild(pfForm);
      pfForm.submit();
    } catch (err) {
      console.error(err);
      Swal.fire({ icon: 'error', title: 'PayFast error', text: err.message || 'Could not redirect to PayFast.' });
      // Let the user re-open the payment modal
      new bootstrap.Modal(document.getElementById("purchasePayment")).show();
      this.checked = false;
    }
  });
});


document.querySelectorAll("input[name='payment_method']").forEach(input => {
  input.addEventListener("change", async function () {
    const selected = this.value;
    const paymentModal = bootstrap.Modal.getInstance(document.getElementById("purchasePayment"));
    paymentModal?.hide();

    if (selected === "stripe") {
      new bootstrap.Modal(document.getElementById("stripePaymentModal")).show();
      return;
    }

    // ----- PAYFAST -----
    const formEl = document.getElementById("purchaseForm");
    const purchaseIdInput = formEl.querySelector("input[name='purchase_id']");

    if (!purchaseIdInput || !purchaseIdInput.value) {
      Swal.fire({ icon: 'error', title: 'Missing purchase', text: 'Please save your details first.' });
      // Reopen payment modal for them
      new bootstrap.Modal(document.getElementById("purchasePayment")).show();
      this.checked = false;
      return;
    }

    const confirmed = await Swal.fire({
  icon: 'question',
  title: 'Proceed with PayFast?',
  text: 'You will be redirected to PayFast to complete your payment.',
  showCancelButton: true,
  confirmButtonText: 'Continue',
  cancelButtonText: 'Back',
  reverseButtons: true, // 🔹 puts "Continue" on the right, "Back" on the left
  customClass: {
    confirmButton: 'btn btn-dark',   // dark button
    cancelButton: 'btn btn-outline-secondary me-3' // lighter back button
  },
  buttonsStyling: false // 🔹 lets Bootstrap classes take effect
});


    if (!confirmed.isConfirmed) {
      this.checked = false;
      new bootstrap.Modal(document.getElementById("purchasePayment")).show();
      return;
    }

    try {
      // We’ll send name/email to help prefill PayFast form
      const res = await fetch(`/purchase/${encodeURIComponent(purchaseIdInput.value)}/payfast/init`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        body: JSON.stringify({
          name:  formEl.name?.value || '',
          email: formEl.email?.value || ''
        })
      });

      const data = await res.json();
      if (!res.ok || !data.success) {
        throw new Error(data.message || 'Failed to initialize PayFast.');
      }

      // Build a POST form to PayFast and auto-submit
      const pfForm = document.createElement('form');
      pfForm.method = 'POST';
      pfForm.action = data.action;
      pfForm.style.display = 'none';

      Object.entries(data.fields).forEach(([k, v]) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = k;
        input.value = v;
        pfForm.appendChild(input);
      });

      document.body.appendChild(pfForm);
      pfForm.submit();
    } catch (err) {
      console.error(err);
      Swal.fire({ icon: 'error', title: 'PayFast error', text: err.message || 'Could not redirect to PayFast.' });
      // Let the user re-open the payment modal
      new bootstrap.Modal(document.getElementById("purchasePayment")).show();
      this.checked = false;
    }
  });
});


  // 🔹 Stripe payment handling
  document.getElementById("purchaseStripePayButton").addEventListener("click", async function () {
    const purchaseIdInput = form.querySelector("input[name='purchase_id']");
    if (!purchaseIdInput || !purchaseIdInput.value) {
      Swal.fire({ icon: 'error', title: 'Missing booking', text: 'Purchase ID is missing. Please go back and save your details.' });
      return;
    }

    Swal.fire({
      title: 'Processing payment',
      html: 'Please do not close this window.',
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading()
    });

    const { paymentMethod: pm, error } = await stripe.createPaymentMethod({
      type: 'card',
      card: cardNumber,
      billing_details: { name: form.name.value, email: form.email.value }
    });

    if (error) {
      Swal.close();
      Swal.fire({ icon: 'error', title: 'Card error', text: error.message });
      return;
    }

    const purchase_id = purchaseIdInput.value;
    fetch(`/purchase/${purchase_id}/pay-with-stripe`, {
      method: 'POST',
      headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
      body: JSON.stringify({ payment_method_id: pm.id })
    })
    .then(res => res.json())
    .then(async data => {
      Swal.close();
      if (data.success) {
        Swal.fire({ icon: 'success', title: 'Payment successful', text: 'Thank you — your purchase is complete.', timer: 1400, showConfirmButton: false })
        .then(() => {
          bootstrap.Modal.getInstance(document.getElementById("stripePaymentModal"))?.hide();
          new bootstrap.Modal(document.getElementById("purchaseThankYou")).show();
        });
      } else if (data.requires_action) {
        const result = await stripe.confirmCardPayment(data.payment_intent_client_secret);
        if (result.error) {
          Swal.fire({ icon: 'error', title: 'Authentication failed', text: result.error.message });
        } else {
          Swal.fire({ icon: 'success', title: 'Payment confirmed', text: 'Payment authentication completed.' })
          .then(() => {
            bootstrap.Modal.getInstance(document.getElementById("stripePaymentModal"))?.hide();
            new bootstrap.Modal(document.getElementById("purchaseThankYou")).show();
          });
        }
      } else {
        Swal.fire({ icon: 'error', title: 'Payment failed', text: data.message || 'There was a problem processing your payment.' });
      }
    })
    .catch(err => {
      console.error(err);
      Swal.close();
      Swal.fire({ icon: 'error', title: 'Network error', text: 'Unable to complete payment. Please try again.' });
    });
  });

  // 🔹 Enable tooltips
  if (window.bootstrap?.Tooltip) {
    document.querySelectorAll('.contact-btn[title]').forEach(el => new bootstrap.Tooltip(el));
  }
});

document.getElementById("purchasePayment").addEventListener("show.bs.modal", function () {
  document.querySelectorAll("#purchasePayment input[name='payment_method']").forEach(el => {
    el.checked = false; // reset selection
  });
});

</script>

@if(session('payfast_success'))
<script>
Swal.fire({
    icon: 'success',
    title: 'Payment Successful 🎉',
    text: @json(session('payfast_success')),
    confirmButtonText: 'OK',
    customClass: {
        confirmButton: 'btn btn-dark'
    },
    buttonsStyling: false
});
</script>
@endif


