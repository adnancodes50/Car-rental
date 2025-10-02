@php
    $countries = [
        'Afghanistan','Albania','Algeria','Andorra','Angola','Antigua and Barbuda','Argentina','Armenia','Australia',
        'Austria','Azerbaijan','Bahamas','Bahrain','Bangladesh','Barbados','Belarus','Belgium','Belize','Benin',
        'Bhutan','Bolivia','Bosnia and Herzegovina','Botswana','Brazil','Brunei','Bulgaria','Burkina Faso','Burundi',
        'Cabo Verde','Cambodia','Cameroon','Canada','Central African Republic','Chad','Chile','China','Colombia',
        'Comoros','Congo (Congo-Brazzaville)','Costa Rica','Croatia','Cuba','Cyprus','Czechia','Democratic Republic of the Congo',
        'Denmark','Djibouti','Dominica','Dominican Republic','Ecuador','Egypt','El Salvador','Equatorial Guinea','Eritrea',
        'Estonia','Eswatini','Ethiopia','Fiji','Finland','France','Gabon','Gambia','Georgia','Germany','Ghana','Greece',
        'Grenada','Guatemala','Guinea','Guinea-Bissau','Guyana','Haiti','Holy See','Honduras','Hungary','Iceland','India',
        'Indonesia','Iran','Iraq','Ireland','Israel','Italy','Jamaica','Japan','Jordan','Kazakhstan','Kenya','Kiribati',
        'Kuwait','Kyrgyzstan','Laos','Latvia','Lebanon','Lesotho','Liberia','Libya','Liechtenstein','Lithuania','Luxembourg',
        'Madagascar','Malawi','Malaysia','Maldives','Mali','Malta','Marshall Islands','Mauritania','Mauritius','Mexico',
        'Micronesia','Moldova','Monaco','Mongolia','Montenegro','Morocco','Mozambique','Myanmar','Namibia','Nauru','Nepal',
        'Netherlands','New Zealand','Nicaragua','Niger','Nigeria','North Korea','North Macedonia','Norway','Oman','Pakistan',
        'Palau','Palestine State','Panama','Papua New Guinea','Paraguay','Peru','Philippines','Poland','Portugal','Qatar',
        'Romania','Russia','Rwanda','Saint Kitts and Nevis','Saint Lucia','Saint Vincent and the Grenadines','Samoa',
        'San Marino','Sao Tome and Principe','Saudi Arabia','Senegal','Serbia','Seychelles','Sierra Leone','Singapore',
        'Slovakia','Slovenia','Solomon Islands','Somalia','South Africa','South Korea','South Sudan','Spain','Sri Lanka',
        'Sudan','Suriname','Sweden','Switzerland','Syria','Tajikistan','Tanzania','Thailand','Timor-Leste','Togo','Tonga',
        'Trinidad and Tobago','Tunisia','Turkey','Turkmenistan','Tuvalu','Uganda','Ukraine','United Arab Emirates',
        'United Kingdom','United States','Uruguay','Uzbekistan','Vanuatu','Venezuela','Vietnam','Yemen','Zambia','Zimbabwe'
    ];
    // Put South Africa on top, sort the rest
    $countries = array_diff($countries, ['South Africa']);
    sort($countries);
    array_unshift($countries, 'South Africa');
@endphp

<form id="purchaseForm" method="POST">
    @csrf
    <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
    <input type="hidden" name="total_price" value="{{ $vehicle->purchase_price }}">

    <!-- Step 1: Vehicle Info -->
    <div class="modal fade" id="purchaseModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content rounded-4 shadow-lg overflow-hidden">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-car-front-fill me-2"></i>Purchase {{ $vehicle->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <h5 class="fw-semibold mb-2">Purchase Process Information</h5>
                        <p class="text-muted mb-0">Pay a deposit to place this vehicle under offer. Full process continues offline.</p>
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
                            <span class="fw-bold" style="color:#CF9B4D">R{{ number_format($vehicle->deposit_amount) }} ZAR</span>
                        </div>
                    </div>
                    <!-- No data-bs-* here -->
                    <button type="button" id="purchaseStep1Next" class="btn btn-dark w-100">Continue</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 2: Customer Info -->
    <div class="modal fade" id="purchaseCustomer" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content rounded-4 shadow-lg overflow-hidden">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-person-circle me-2"></i> Enter Your Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control rounded-3" placeholder="John Doe" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control rounded-3" placeholder="you@example.com" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Phone</label>
                            <input type="tel" name="phone" class="form-control rounded-3" placeholder="+27 123 456 7890" required>
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
                <div class="modal-footer border-0 d-flex justify-content-between">
                    <!-- No data-bs-* here -->
                    <button type="button" id="backToStep1" class="btn btn-outline-secondary rounded-3">Back</button>
                    <button type="button" id="purchaseStep2Next" class="btn btn-dark rounded-3 px-4">Continue to Payment</button>
                </div>
            </div>
        </div>
    </div>

    @php
        use App\Models\SystemSetting;
        use Illuminate\Support\Facades\Cache;

        if (app()->environment('local')) {
            $settings = SystemSetting::first() ?: new SystemSetting([
                'stripe_enabled' => false,
                'payfast_enabled' => false,
            ]);
        } else {
            $settings = Cache::remember('payments.settings', 60, function () {
                return SystemSetting::first() ?: new SystemSetting([
                    'stripe_enabled' => false,
                    'payfast_enabled' => false,
                ]);
            });
        }
        $enabledCount = ((bool)$settings->stripe_enabled ? 1 : 0) + ((bool)$settings->payfast_enabled ? 1 : 0);
    @endphp

    <!-- Step 3a: Select Payment Method -->
    <div class="modal fade" id="purchasePayment" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 shadow">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-credit-card me-2"></i> Select Payment Method</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 align-items-stretch justify-content-center">
                        @if ($settings->stripe_enabled)
                        <div class="col-12 {{ $enabledCount === 2 ? 'col-md-6' : 'col-md-12' }}">
                            <input type="radio" name="payment_method" id="purchaseStripe" value="stripe" class="btn-check" autocomplete="off" required>
                            <label for="purchaseStripe" class="card btn w-100 purchase-pay-option p-3 flex-column">
                                <div class="text-center mb-2">
                                    <img src="{{ asset('images/stripe.png') }}" class="rounded-3" alt="Stripe" style="width:80px;">
                                </div>
                                <div class="purchase-pay-text">
                                    <div class="fw-bold">Stripe (Card)</div>
                                    <small class="text-muted">Visa • Mastercard • Amex</small>
                                </div>
                            </label>
                        </div>
                        @endif

                        @if ($settings->payfast_enabled)
                        <div class="col-12 {{ $enabledCount === 2 ? 'col-md-6' : 'col-md-12' }}">
                            <input type="radio" name="payment_method" id="purchasePayfast" value="payfast" class="btn-check" autocomplete="off" required>
                            <label for="purchasePayfast" class="card btn w-100 purchase-pay-option p-3 flex-column">
                                <div class="text-center mb-2">
                                    <img src="{{ asset('images/payfast.png') }}" class="rounded-3" alt="PayFast" style="width:80px;">
                                </div>
                                <div class="purchase-pay-text">
                                    <div class="fw-bold">PayFast</div>
                                    <small class="text-muted">South Africa payments</small>
                                </div>
                            </label>
                        </div>
                        @endif

                        @if ($enabledCount === 0)
                        <div class="col-12"><div class="alert alert-warning text-center">No payment methods are currently available.</div></div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <!-- No data-bs-* here -->
                    <button type="button" id="backToCustomer" class="btn btn-outline-secondary">Back</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 3b: Stripe Card Input -->
   <!-- Step 3b: Stripe Card Input (FULL with preview) -->
<div class="modal fade mt-5" id="stripePaymentModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rounded-4 shadow">
      <div class="modal-header">
        <h5 class="modal-title fw-bold">
          <i class="bi bi-credit-card-fill me-2"></i>
          Stripe Payment and Vehicle Summary
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="container-fluid">

          {{-- ROW 1: Vehicle header --}}
          <div class="row g-3 align-items-center mb-3">
            <div class="col-12 d-flex align-items-center gap-3">
              <img src="{{ $vehicle->mainImage() }}" alt="{{ $vehicle->name }}"
                   class="rounded shadow-sm" style="width:88px;height:88px;object-fit:cover;">
              <div class="flex-grow-1">
                <div class="d-flex flex-wrap align-items-center justify-content-between">
                  <div>
                    <h5 class="mb-1">{{ $vehicle->name }}</h5>
                    <small class="text-muted">{{ $vehicle->year }} {{ $vehicle->model }}</small>
                  </div>
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
                  @if ($vehicle->engine)
                    <li class="list-inline-item me-3">
                      <i class="bi bi-gear-fill me-1"></i>Engine: {{ $vehicle->engine }}
                    </li>
                  @endif
                  @if ($vehicle->transmission)
                    <li class="list-inline-item me-3">
                      <i class="bi bi-gear-wide-connected me-1"></i>{{ $vehicle->transmission }}
                    </li>
                  @endif
                  @if ($vehicle->mileage)
                    <li class="list-inline-item me-3">
                      <i class="bi bi-speedometer2 me-1"></i>{{ number_format($vehicle->mileage) }} km
                    </li>
                  @endif
                  @if ($vehicle->location)
                    <li class="list-inline-item">
                      <i class="bi bi-geo-alt me-1"></i>{{ $vehicle->location }}
                    </li>
                  @endif
                </ul>
              </div>
            </div>
          </div>

          {{-- Mini price band --}}
          @php
            $vehiclePrice = (float) ($vehicle->purchase_price ?? 0);
            $deposit      = (float) ($vehicle->deposit_amount ?? 0);
            $deposit      = max(0, min($deposit, $vehiclePrice));
            $remaining    = max($vehiclePrice - $deposit, 0);
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

          {{-- Owner / Contact (optional) --}}
          @if (isset($landing) &&
              (($landing->phone_link ?? null) || ($landing->whatsapp_link ?? null) || ($landing->email_link ?? null)))
            <div class="row mb-3">
              <div class="col-12">
                <div class="owner-contact-box">
                  <div class="small text-muted mb-2">Owner / Contact us For More Information</div>
                  <div class="contact-grid">
                    {{-- Phone --}}
                    <div class="contact-item text-start">
                      @if (!empty($landing->phone_link))
                        <a href="{{ $landing->phone_link }}" class="contact-btn contact-phone"
                           title="{{ $landing->phone_btn_text ?? preg_replace('/^tel:/i', '', $landing->phone_link) }}"
                           aria-label="Call">
                          <i class="bi bi-telephone"></i>
                        </a>
                      @endif
                    </div>
                    {{-- WhatsApp --}}
                    <div class="contact-item text-center">
                      @if (!empty($landing->whatsapp_link))
                        @php
                          $waTitle = $landing->whatsapp_btn_text ??
                                     preg_replace(
                                       ['/^https?:\/\/wa\.me\//i','/^whatsapp:\/\/send\?phone=/i'],
                                       '',
                                       $landing->whatsapp_link
                                     );
                          $waTitle = $waTitle ?: 'WhatsApp';
                        @endphp
                        <a href="{{ $landing->whatsapp_link }}" class="contact-btn contact-wa"
                           target="_blank" rel="noopener" title="{{ $waTitle }}" aria-label="WhatsApp">
                          <i class="bi bi-whatsapp"></i>
                        </a>
                      @endif
                    </div>
                    {{-- Email --}}
                    <div class="contact-item text-end">
                      @if (!empty($landing->email_link))
                        <a href="{{ $landing->email_link }}" class="contact-btn contact-mail"
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

          {{-- Stripe Elements --}}
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
        <!-- Back wired in JS with swapModal('stripePaymentModal','purchasePayment') -->
        <button type="button" id="stripeBackToPayment" class="btn btn-outline-secondary me-auto">Back</button>
        <button type="button" id="purchaseStripePayButton" class="btn btn-dark">Purchase Now</button>
      </div>
    </div>
  </div>
</div>

    <!-- Step 4: Thank You -->
    <div class="modal fade" id="purchaseThankYou" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content rounded-4 shadow text-center p-4 border-0">
                <div class="modal-body text-center py-5">
                    <div class="d-inline-flex align-items-center justify-content-center bg-success-subtle text-success rounded-circle mb-3" style="width:84px;height:84px;">
                        <i class="bi bi-check2-circle fs-1"></i>
                    </div>
                    <h4 class="fw-bold mb-2">Thank you!</h4>
                    <p class="text-secondary mb-4">Your purchase has been successfully completed.</p>
                    <button type="button" class="btn btn-success fw-semibold px-4 rounded-pill" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</form>

<style>
  /* Remove/disable backdrop entirely */
  .modal-backdrop, .modal-backdrop.show { opacity:0 !important; display:none !important; }

   #stripePaymentModal .modal-body {
    max-height: 70vh;   /* adjust to taste */
    overflow: auto;
  }
  /* Payment card styles (yours) */
  #purchasePayment .purchase-pay-option{
    min-height:160px; display:flex; align-items:center; justify-content:center; gap:12px;
    border:1px solid #dee2e6; border-radius:.75rem; padding:20px; text-align:left;
    transition: box-shadow .15s ease, transform .15s ease, border-color .15s ease;
  }
  #purchasePayment .purchase-pay-option:hover{ transform:translateY(-2px); box-shadow:0 .5rem 1rem rgba(0,0,0,.08); }
  #purchasePayment .btn-check:checked+.purchase-pay-option{ border-color:#0d6efd; box-shadow:0 0 0 .25rem rgba(13,110,253,.2); }
  #purchasePayment .purchase-pay-text{ display:flex; flex-direction:column; text-align:center; }
  @media (min-width:768px){ #purchasePayment .col-md-6{ display:flex; } #purchasePayment .purchase-pay-option{ width:100%; } }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://js.stripe.com/v3/"></script>

<script>
(() => {
  // ===== Blade → JS values =====
  const VEHICLE_NAME    = @json($vehicle->name);
  const VEHICLE_PRICE   = {{ (float) ($vehicle->purchase_price ?? 0) }};
  const VEHICLE_DEPOSIT = {{ (float) ($vehicle->deposit_amount ?? 0) }};
  const ZAR             = new Intl.NumberFormat('en-ZA', { style:'currency', currency:'ZAR' });
  const WHATSAPP_LINK   = "https://wa.link/koo7b6";

  // Modal helper (force no backdrop)
  const modalInst = (el) => bootstrap.Modal.getOrCreateInstance(el, { backdrop:false, focus:true, keyboard:true });

  // Reliable modal swapper: hide current → wait until hidden → show next
  function swapModal(fromId, toId) {
    const fromEl = document.getElementById(fromId);
    const toEl   = document.getElementById(toId);
    if (!fromEl || !toEl) return;
    const from = modalInst(fromEl);
    const to   = modalInst(toEl);

    fromEl.addEventListener('hidden.bs.modal', function onHidden() {
      fromEl.removeEventListener('hidden.bs.modal', onHidden);
      to.show();
    }, { once:true });

    from.hide();
  }

  // Keep stacking/z-index sane with no backdrop
  const Z_BASE = 1055, Z_STEP = 20;
  const visibleModals = () => Array.from(document.querySelectorAll('.modal.show'));
  const restack = () => {
    const open = visibleModals();
    open.forEach((m,i) => m.style.zIndex = String(Z_BASE + i * Z_STEP));
    if (open.length) { document.body.classList.add('modal-open'); }
    else { document.body.classList.remove('modal-open'); document.body.style.removeProperty('padding-right'); }
  };
  ['show.bs.modal','shown.bs.modal','hidden.bs.modal'].forEach(evt =>
    document.addEventListener(evt, () => setTimeout(restack, 0))
  );

  // Open WhatsApp in a new tab reliably
  function openWhatsAppNewTab() {
    const a = document.createElement('a');
    a.href = WHATSAPP_LINK; a.target = '_blank'; a.rel = 'noopener noreferrer';
    a.style.cssText = 'position:fixed;top:0;left:0;width:1px;height:1px;opacity:.01;';
    document.body.appendChild(a); a.click(); setTimeout(() => a.remove(), 100);
  }

  document.addEventListener('DOMContentLoaded', () => {
    // Init all modals up front
    document.querySelectorAll('.modal').forEach(m => modalInst(m));

    const $ = (id) => document.getElementById(id);
    const form = $('purchaseForm');

    // Step 1 → Step 2
    $('purchaseStep1Next').addEventListener('click', () => swapModal('purchaseModal','purchaseCustomer'));

    // Step 2 Back → Step 1
    $('backToStep1').addEventListener('click', () => swapModal('purchaseCustomer','purchaseModal'));

    // Step 2 → Payment (save details first)
    $('purchaseStep2Next').addEventListener('click', async () => {
      const name    = (form.name?.value || '').trim();
      const email   = (form.email?.value || '').trim();
      const phone   = (form.phone?.value || '').trim();
      const country = form.country?.value;

      if (!name || !email || !phone || !country) {
        Swal.fire({ icon:'error', title:'Missing Information', text:'Please fill in all required customer details.' });
        return;
      }

      try {
        const res = await fetch("{{ route('purchase.store') }}", {
          method:'POST',
          headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN':"{{ csrf_token() }}" },
          body: JSON.stringify({ name, email, phone, country, vehicle_id: form.vehicle_id.value, total_price: form.total_price.value })
        });
        const data = await res.json();
        if (!res.ok || !data?.success) throw new Error(data?.message || 'Unable to save details.');

        let hid = form.querySelector('input[name="purchase_id"]');
        if (!hid) { hid = document.createElement('input'); hid.type='hidden'; hid.name='purchase_id'; form.appendChild(hid); }
        hid.value = data.purchase_id;

        swapModal('purchaseCustomer','purchasePayment');
      } catch (e) {
        Swal.fire({ icon:'error', title:'Error', text:e.message || 'Network error.' });
      }
    });

    // Payment Back → Customer
    $('backToCustomer').addEventListener('click', () => swapModal('purchasePayment','purchaseCustomer'));

    // Reset radios each time Payment opens
    $('purchasePayment').addEventListener('show.bs.modal', () => {
      document.querySelectorAll('#purchasePayment input[name="payment_method"]').forEach(r => r.checked = false);
    });

    // Stripe lazy setup
    let stripe, elements, cardNumber, cardExpiry, cardCvc;
    async function ensureStripeMounted() {
      if (stripe) return true;
      const publishableKey = "{{ $settings->stripe_key ?? (config('services.stripe.key') ?? '') }}";
      if (!publishableKey) {
        Swal.fire({ icon:'error', title:'Stripe not configured', text:'Publishable key is missing.' });
        return false;
      }
      try {
        stripe   = Stripe(publishableKey);
        elements = stripe.elements();
        const style = { base:{ fontSize:'16px', color:'#32325d', '::placeholder':{ color:'#a0aec0' } } };
        cardNumber = elements.create('cardNumber', { style });
        cardExpiry = elements.create('cardExpiry', { style });
        cardCvc    = elements.create('cardCvc',    { style });
        cardNumber.mount('#card-number'); cardExpiry.mount('#card-expiry'); cardCvc.mount('#card-cvc');
        return true;
      } catch (e) {
        Swal.fire({ icon:'error', title:'Stripe error', text:e.message || 'Failed to initialize Stripe.' });
        return false;
      }
    }

    // Choose payment method
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
      radio.addEventListener('change', async function() {
        const choice = this.value;

        if (choice === 'stripe') {
          const ok = await ensureStripeMounted();
          if (!ok) { this.checked = false; return; }
          swapModal('purchasePayment','stripePaymentModal');
          return;
        }

        // PayFast flow
        const pid = form.querySelector('input[name="purchase_id"]')?.value;
        if (!pid) { Swal.fire({ icon:'error', title:'Missing purchase', text:'Please save your details first.' }); this.checked = false; return; }

        const confirmed = await Swal.fire({
          icon:'question', title:'Proceed with PayFast?', text:'You will be redirected to PayFast to complete your payment.',
          showCancelButton:true, confirmButtonText:'Continue', cancelButtonText:'Back', reverseButtons:true,
          buttonsStyling:false, customClass:{ confirmButton:'btn btn-dark', cancelButton:'btn btn-outline-secondary me-3' }
        });
        if (!confirmed.isConfirmed) { this.checked = false; return; }

        try {
          const res = await fetch(`/purchase/${encodeURIComponent(pid)}/payfast/init`, {
            method:'POST', headers:{ 'Content-Type':'application/json','X-CSRF-TOKEN':"{{ csrf_token() }}" },
            body: JSON.stringify({ name: form.name?.value || '', email: form.email?.value || '' })
          });
          const data = await res.json();
          if (!res.ok || !data?.success) throw new Error(data?.message || 'Failed to initialize PayFast.');

          const pfForm = document.createElement('form');
          pfForm.method='POST'; pfForm.action=data.action; pfForm.style.display='none';
          Object.entries(data.fields || {}).forEach(([k,v]) => {
            const inp = document.createElement('input'); inp.type='hidden'; inp.name=k; inp.value=v; pfForm.appendChild(inp);
          });
          document.body.appendChild(pfForm); pfForm.submit();
        } catch (e) {
          Swal.fire({ icon:'error', title:'PayFast error', text:e.message || 'Could not redirect to PayFast.' });
          this.checked = false;
        }
      });
    });

    // Stripe Back → Payment
    $('stripeBackToPayment').addEventListener('click', () => swapModal('stripePaymentModal','purchasePayment'));

    // Stripe purchase
    $('purchaseStripePayButton').addEventListener('click', async () => {
      const pid = form.querySelector('input[name="purchase_id"]')?.value;
      if (!pid) { Swal.fire({ icon:'error', title:'Missing purchase', text:'Purchase ID is missing. Please save your details again.' }); return; }
      if (!stripe || !cardNumber) { const ok = await ensureStripeMounted(); if (!ok) return; }

      Swal.fire({ title:'Processing payment', html:'Please do not close this window.', allowOutsideClick:false, didOpen:() => Swal.showLoading() });

      const { paymentMethod, error } = await stripe.createPaymentMethod({
        type:'card', card:cardNumber, billing_details:{ name: form.name?.value || '', email: form.email?.value || '' }
      });
      if (error) { Swal.close(); Swal.fire({ icon:'error', title:'Card error', text:error.message }); return; }

      try {
        const res = await fetch(`/purchase/${encodeURIComponent(pid)}/pay-with-stripe`, {
          method:'POST', headers:{ 'Content-Type':'application/json','X-CSRF-TOKEN':"{{ csrf_token() }}" },
          body: JSON.stringify({ payment_method_id: paymentMethod.id })
        });
        const data = await res.json(); Swal.close();

        if (data?.success) {
          const go = data.redirect_to || '/?purchase=success';
          Swal.fire({
            icon:'success', title:'Payment Successful!',
            html:`<div class="text-start">
                    <p class="mb-1"><strong>Vehicle:</strong> ${VEHICLE_NAME}</p>
                    <p class="mb-1"><strong>Amount paid:</strong> ${ZAR.format(data.paid ?? VEHICLE_DEPOSIT)}</p>
                    <p class="mb-1"><strong>Reference:</strong> #${data.purchase_id}</p>
                    ${data.receipt_url ? `<p class="mb-0"><a href="${data.receipt_url}" target="_blank" rel="noopener">View Stripe receipt</a></p>` : ''}
                  </div><hr class="my-2"><p class="mb-0"><strong>Opening WhatsApp chat for you...</strong></p>`,
            showConfirmButton:true, confirmButtonText:'Continue to WhatsApp', allowOutsideClick:false
          }).then(() => { openWhatsAppNewTab(); window.location.href = go; });
          return;
        }

        if (data?.requires_action && data.payment_intent_client_secret) {
          const result = await stripe.confirmCardPayment(data.payment_intent_client_secret);
          if (result.error) {
            Swal.fire({ icon:'error', title:'Authentication failed', text: result.error.message });
          } else if (result.paymentIntent?.status === 'succeeded') {
            Swal.fire({
              icon:'success', title:'Payment Successful!',
              html:`<div class="text-start">
                      <p class="mb-1"><strong>Vehicle:</strong> ${VEHICLE_NAME}</p>
                      <p class="mb-1"><strong>Amount paid:</strong> ${ZAR.format(VEHICLE_DEPOSIT)}</p>
                      ${data.purchase_id ? `<p class="mb-1"><strong>Reference:</strong> #${data.purchase_id}</p>` : ''}
                      ${data.receipt_url ? `<p class="mb-0"><a href="${data.receipt_url}" target="_blank" rel="noopener">View Stripe receipt</a></p>` : ''}
                    </div><hr class="my-2"><p class="mb-0"><strong>Opening WhatsApp chat for you...</strong></p>`,
              showConfirmButton:true, confirmButtonText:'Continue to WhatsApp', allowOutsideClick:false
            }).then(() => { openWhatsAppNewTab(); window.location.href='/?purchase=success'; });
          } else {
            Swal.fire({ icon:'warning', title:'Payment status unknown', text:'Please check your email for a receipt.' });
          }
          return;
        }

        Swal.fire({ icon:'error', title:'Payment failed', text: data?.message || 'There was a problem processing your payment.' });
      } catch (e) {
        Swal.fire({ icon:'error', title:'Network error', text:'Unable to complete payment. Please try again.' });
      }
    });

    // Catch PayFast success by URL/session
    try {
      const params = new URLSearchParams(window.location.search);
      if (params.get('purchase') === 'success' || params.get('payfast_success')) {
        Swal.fire({
          icon:'success', title:'Payment Successful!',
          html:`<p>Your deposit payment was successful!</p><p><strong>Click below to open WhatsApp chat</strong></p>`,
          showConfirmButton:true, confirmButtonText:'Open WhatsApp Chat', allowOutsideClick:false
        }).then(() => { openWhatsAppNewTab(); window.location.href='/'; });

        params.delete('purchase'); params.delete('payfast_success');
        const newUrl = `${location.pathname}${params.toString() ? '?' + params.toString() : ''}${location.hash}`;
        window.history.replaceState({}, '', newUrl);
      }
    } catch {}

    @if(session('payfast_success'))
      Swal.fire({
        icon:'success', title:'Payment Successful!',
        html:`<p>{{ session('payfast_success') }}</p><p><strong>Click below to open WhatsApp chat</strong></p>`,
        showConfirmButton:true, confirmButtonText:'Open WhatsApp Chat', allowOutsideClick:false
      }).then(() => { openWhatsAppNewTab(); window.location.href='/'; });
    @endif
  });
})();
</script>
