﻿{{-- resources/views/models/purchase.blade.php --}}
@php
    // $item => Equipment or Vehicle (controller should eager-load: for equipment -> category, stocks.location)
    // $type => 'equipment' | 'vehicle'

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
    $countries = array_diff($countries, ['South Africa']);
    sort($countries);
    array_unshift($countries, 'South Africa');

    $isSold = ($item->status ?? null) === 'sold';

    if ($type === 'equipment') {
        // Use Category pricing via accessors on Equipment (sale_price, deposit_amount)
        $price = (float) ($item->sale_price ?? 0);
        $deposit = (float) ($item->deposit_amount ?? 0);

        // Build a map for JS (location_id, name, stock)
        $locationStock = $item->stocks
            ? $item->stocks
                ->map(fn($s) => ['id' => $s->location->id, 'name' => $s->location->name, 'stock' => (int) $s->stock])
                ->values()
                ->all()
            : [];
    } else {
        $price = (float) ($item->purchase_price ?? 0);
        $deposit = (float) ($item->deposit_amount ?? 0);
        $locationStock = [];
    }

    $deposit = max(0, min($deposit, $price));
    $remaining = max($price - $deposit, 0);
@endphp

@if ($isSold)
    <div class="alert alert-warning d-flex align-items-center mb-3">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        This {{ $type }} has been <strong>sold</strong> and is no longer available.
    </div>
    <style>
        .purchase-trigger {
            pointer-events: none;
            opacity: .55
        }
    </style>
@endif

<form id="purchaseForm" method="POST">
    @csrf
    @if ($type === 'equipment')
        <input type="hidden" name="equipment_id" value="{{ $item->id }}">
    @else
        <input type="hidden" name="vehicle_id" value="{{ $item->id }}">
    @endif
    {{-- client-sent total is ignored server-side, but we can keep it for display --}}
    <input type="hidden" name="total_price" value="{{ $price }}">

    {{-- Step 1: Info --}}
    <div class="modal fade" id="purchaseModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content rounded-4 shadow-lg overflow-hidden">

                {{-- Header --}}
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold d-flex align-items-center">
                        <i class="bi bi-bag-check-fill me-2 text-success fs-5"></i>
                        Purchase {{ $item->name }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>


                    <hr class="mt-3 mb-0 opacity-10">

                {{-- Body --}}
                <div class="modal-body p-4 pt-3">

                    {{-- Sold Alert --}}
                    {{-- @if ($isSold)
                        <div class="alert alert-danger mb-3 rounded-3">
                            This {{ $type }} is sold and cannot be purchased.
                        </div>
                    @endif --}}

                    {{-- Title --}}
                    <div class="text-center mb-4">
                        <h5 class="fw-normal mb-0">Choose Quantity &amp; Location</h5>
                        {{-- <hr class="mt-2 mb-0 opacity-10"> --}}
                    </div>

                    {{-- Equipment Section --}}
                    @if ($type === 'equipment')
                        <div class="row g-3 align-items-end mb-4">
                            {{-- Location --}}
                            <div class="col-12 col-md-9">
                                <div class="d-flex justify-content-between align-items-baseline">
                                    <label class="form-label mb-1">Location</label>
                                    <small class="text-muted ms-2" id="stockHint">
                                        Select a location to view available stock.
                                    </small>
                                </div>

                                <select name="location_id" id="locationSelect" class="form-select rounded-3" required
                                    {{ $isSold ? 'disabled' : '' }}>
                                    <option value="" disabled selected>Select a location</option>
                                    @foreach ($item->stocks ?? [] as $s)
                                        <option value="{{ $s->location->id }}" data-stock="{{ (int) $s->stock }}">
                                            {{ $s->location->name }} — In stock: {{ (int) $s->stock }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Quantity --}}
                            <div class="col-6 col-md-3">
                                <label class="form-label mb-1 d-flex justify-content-between">
                                    <span>Quantity</span>
                                    <small class="text-muted" id="qtyHint" style="font-weight:400;"></small>
                                </label>

                                <select name="quantity" id="qtySelect" class="form-select rounded-3" required
                                    {{ $isSold ? 'disabled' : '' }}>
                                    <option value="" selected disabled>Select</option>
                                </select>
                            </div>
                        </div>
                    @endif

                    {{-- Summary Card --}}
                    <div class="bg-light rounded-4 p-3 border border-light shadow-sm mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-medium text-secondary">{{ ucfirst($type) }}:</span>
                            <span class="fw-semibold text-dark" id="itemNameDisplay">{{ $item->name }}</span>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-medium text-secondary">Unit Price:</span>
                            <span class="fw-bold text-dark" id="unitPriceDisplay">
                                R{{ number_format($price, 2) }} ZAR
                            </span>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-medium text-secondary">Deposit (per unit):</span>
                            <span class="fw-bold text-dark" id="unitDepositDisplay">
                                R{{ number_format($deposit, 2) }} ZAR
                            </span>
                        </div>

                        @if ($type === 'equipment')
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-medium text-secondary">Selected Quantity:</span>
                                <span class="fw-semibold text-dark" id="quantityDisplay">1 unit</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-medium text-secondary">Selected Location:</span>
                                <span class="fw-semibold text-dark" id="selectedLocationDisplay">
                                    Select a location
                                </span>
                            </div>
                        @endif

                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-medium text-secondary">Total Price:</span>
                            <span class="fw-semibold text-dark" id="totalPriceDisplay">
                                R{{ number_format($price, 2) }} ZAR
                            </span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-medium text-secondary">Deposit Due Now:</span>
                            <span class="fw-bold " id="totalDepositDisplay" style="color: #679767;">
                                R{{ number_format($deposit, 2) }} ZAR
                            </span>
                        </div>
                    </div>

                    {{-- Continue Button --}}
                    <button type="button" id="purchaseStep1Next" class="btn btn-dark w-100 py-2 fw-semibold rounded-3"
                        {{ $isSold ? 'disabled' : '' }}>
                        Continue
                    </button>

                </div>
            </div>
        </div>
    </div>




    {{-- Step 2: Customer (+ Location & Quantity for equipment) --}}
    <div class="modal fade" id="purchaseCustomer" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content rounded-4 shadow-lg overflow-hidden">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-person-circle me-2"></i> Enter Your Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4">
                    @if ($isSold)
                        <div class="alert alert-danger mb-3">This {{ $type }} is sold and cannot be purchased.
                        </div>
                    @endif
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control rounded-3"
                                placeholder="John Doe" required {{ $isSold ? 'disabled' : '' }}>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control rounded-3"
                                placeholder="you@example.com" inputmode="email" autocomplete="email" required
                                pattern="^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$"
                                title="Enter a valid email address, e.g. you@example.com"
                                {{ $isSold ? 'disabled' : '' }}>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Phone</label>
                            <input type="tel" name="phone" class="form-control rounded-3"
                                placeholder="+27 123 456 7890" inputmode="tel" autocomplete="tel" required
                                pattern="^\+?[0-9]{1,4}(?:[ -]?[0-9]{2,4}){2,4}$"
                                title="Use digits with optional spaces or dashes, e.g. +27 123 456 7890"
                                {{ $isSold ? 'disabled' : '' }}>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Country</label>
                            <select name="country" class="form-select rounded-3" required
                                {{ $isSold ? 'disabled' : '' }}>
                                <option value="" disabled selected>Select your country</option>
                                @foreach ($countries as $country)
                                    <option value="{{ $country }}">{{ $country }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                </div>
                <div class="modal-footer border-0 d-flex justify-content-between">
                    <button type="button" id="purchaseBackToStep1"
                        class="btn btn-outline-secondary rounded-3">Back</button>
                    <button type="button" id="purchaseStep2Next" class="btn btn-dark rounded-3 px-4"
                        {{ $isSold ? 'disabled' : '' }}>
                        Continue to Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

    @php
        use Illuminate\Support\Facades\Cache;
        $settings =
            $paymentConfig ??
            (app()->environment('local')
                ? (\App\Models\SystemSetting::first() ?:
                new \App\Models\SystemSetting(['stripe_enabled' => false, 'payfast_enabled' => false]))
                : Cache::remember(
                    'payments.settings',
                    60,
                    fn() => \App\Models\SystemSetting::first() ?:
                    new \App\Models\SystemSetting(['stripe_enabled' => false, 'payfast_enabled' => false]),
                ));
        $enabledCount = ((bool) $settings->stripe_enabled ? 1 : 0) + ((bool) $settings->payfast_enabled ? 1 : 0);
    @endphp

    {{-- Step 3a: Payment method --}}
    <div class="modal fade" id="purchasePayment" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 shadow">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-credit-card me-2"></i> Select Payment Method</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if ($isSold)
                        <div class="alert alert-danger mb-3">This {{ $type }} is sold and cannot be purchased.
                        </div>
                    @endif

                    <div class="row g-3 align-items-stretch justify-content-center">
                        @if ($settings->stripe_enabled)
                            <div class="col-12 {{ $enabledCount === 2 ? 'col-md-6' : 'col-md-12' }}">
                                <input type="radio" name="payment_method" id="purchaseStripe" value="stripe"
                                    class="btn-check" autocomplete="off" required {{ $isSold ? 'disabled' : '' }}>
                                <label for="purchaseStripe"
                                    class="card btn w-100 purchase-pay-option p-3 flex-column {{ $isSold ? 'disabled' : '' }}">
                                    <div class="text-center mb-2">
                                        <img src="{{ asset('images/stripe.png') }}" class="rounded-3" alt="Stripe"
                                            style="width:80px;">
                                    </div>
                                    <div class="purchase-pay-text">
                                        <div class="fw-bold">Stripe (Card)</div>
                                        <small class="text-muted">Visa . Mastercard . Amex</small>
                                    </div>
                                </label>
                            </div>
                        @endif

                        @if ($settings->payfast_enabled)
                            <div class="col-12 {{ $enabledCount === 2 ? 'col-md-6' : 'col-md-12' }}">
                                <input type="radio" name="payment_method" id="purchasePayfast" value="payfast"
                                    class="btn-check" autocomplete="off" required {{ $isSold ? 'disabled' : '' }}>
                                <label for="purchasePayfast"
                                    class="card btn w-100 purchase-pay-option p-3 flex-column {{ $isSold ? 'disabled' : '' }}">
                                    <div class="text-center mb-2">
                                        <img src="{{ asset('images/payfast.png') }}" class="rounded-3"
                                            alt="PayFast" style="width:80px;">
                                    </div>
                                    <div class="purchase-pay-text">
                                        <div class="fw-bold">PayFast</div>
                                        <small class="text-muted">South Africa payments</small>
                                    </div>
                                </label>
                            </div>
                        @endif

                        @if ($enabledCount === 0)
                            <div class="col-12">
                                <div class="alert alert-warning text-center">No payment methods are currently
                                    available.</div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" id="backToCustomer" class="btn btn-outline-secondary">Back</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Step 3b: Stripe --}}
    <div class="modal fade mt-2" id="stripePaymentModal" tabindex="-1" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 shadow">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-credit-card-fill me-2"></i> Stripe Payment and
                        {{ ucfirst($type) }} Summary</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if ($isSold)
                        <div class="alert alert-danger mb-3">This {{ $type }} is sold and cannot be purchased.
                        </div>
                    @endif

                    <div class="container-fluid">
                        <div class="row g-3 align-items-center mb-3">
                            <div class="col-12 d-flex align-items-center gap-3">
                                <img src="{{ method_exists($item, 'mainImage') ? $item->mainImage() : (isset($item->image) ? asset('storage/' . $item->image) : asset('images/placeholder.png')) }}"
                                    alt="{{ $item->name }}" class="rounded shadow-sm"
                                    style="width:88px;height:88px;object-fit:cover;">
                                <div class="flex-grow-1">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                                        <div>
                                            <h5 class="mb-1">{{ $item->name }}</h5>
                                            @if (isset($item->year) || isset($item->model))
                                                <small
                                                    class="text-muted">{{ trim(($item->year ?? '') . ' ' . ($item->model ?? '')) }}</small>
                                            @endif
                                        </div>
                                        <div class="text-end">
                                            <div class="small text-muted">Price</div>
                                            <div class="fw-semibold" id="stripeUnitPriceDisplay">
                                                R{{ number_format($price, 2) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Totals --}}
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="bg-light rounded-3 p-2 px-3">
                                    <div class="d-flex justify-content-between small">
                                        <span>Total Price</span>
                                        <span id="stripeTotalPriceDisplay">R{{ number_format($price, 2) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between small">
                                        <span>Deposit (Due Now)</span>
                                        <span id="stripeDepositDisplay">R{{ number_format($deposit, 2) }}</span>
                                    </div>
                                    <div class="border-top mt-2 pt-2 d-flex justify-content-between">
                                        <span class="fw-semibold">Total Due Now</span>
                                        <span class="fw-bold text-success"
                                            id="stripeTotalDueNowDisplay">R{{ number_format($deposit, 2) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1">
                                        <span class="fw-semibold text-muted">Total Payable Later</span>
                                        <span class="fw-bold text-muted"
                                            id="stripeRemainingDisplay">R{{ number_format($remaining, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Elements --}}
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
                    <button type="button" id="purchaseStripeBackToPayment"
                        class="btn btn-outline-secondary me-auto">Back</button>
                    <button type="button" id="purchaseStripePayButton" class="btn btn-dark"
                        {{ $isSold ? 'disabled' : '' }}>Purchase Now</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Step 4: Thank you --}}
    <div class="modal fade" id="purchaseThankYou" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content rounded-4 shadow text-center p-4 border-0">
                <div class="modal-body text-center py-5">
                    <div class="d-inline-flex align-items-center justify-content-center bg-success-subtle text-success rounded-circle mb-3"
                        style="width:84px;height:84px;">
                        <i class="bi bi-check2-circle fs-1"></i>
                    </div>
                    <h4 class="fw-bold mb-2">Thank you!</h4>
                    <p class="text-secondary mb-4">Your purchase has been successfully completed.</p>
                    <button type="button" class="btn btn-success fw-semibold px-4 rounded-pill"
                        data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</form>

<style>
    .modal-backdrop.show {
        background-color: transparent;
        opacity: 0;
    }

    #stripePaymentModal .modal-body {
        max-height: 70vh;
        overflow: auto;
    }

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
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .08);
    }

    #purchasePayment .btn-check:checked+.purchase-pay-option {
        border-color: #0d6efd;
        box-shadow: 0 0 0 .25rem rgba(13, 110, 253, .2);
    }

    #purchasePayment .purchase-pay-text {
        display: flex;
        flex-direction: column;
        text-align: center;
    }

    .purchase-pay-option.disabled {
        pointer-events: none;
        opacity: .45;
    }

    @media (min-width:768px) {
        #purchasePayment .col-md-6 {
            display: flex;
        }

        #purchasePayment .purchase-pay-option {
            width: 100%;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://js.stripe.com/v3/"></script>

<script>
    (() => {
        const TYPE = @json($type);
        const ITEM_NAME = @json($item->name);
        const ITEM_STATUS = @json($item->status ?? null);
        const IS_SOLD = ITEM_STATUS === 'sold';

        // Use server-rendered numbers (equipment uses Category via PHP above)
        const ITEM_PRICE = {{ (float) $price }};
        const ITEM_DEPOSIT = {{ (float) $deposit }};
        const LOCATION_STOCK = @json($locationStock); // [{id,name,stock}] (equipment only)

        const ZAR = new Intl.NumberFormat('en-ZA', {
            style: 'currency',
            currency: 'ZAR'
        });
        const WHATSAPP_LINK = "https://wa.link/8bgpe5";
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
        const phonePattern = /^\+?[0-9]{1,4}(?:[ -]?[0-9]{2,4}){2,4}$/;

        // Build URLs inline
        const STORE_URL = @json(
            $type === 'equipment'
                ? (\Illuminate\Support\Facades\Route::has('equipment.purchase.store')
                    ? route('equipment.purchase.store')
                    : url('/equipment-purchase/store'))
                : (\Illuminate\Support\Facades\Route::has('purchase.store')
                    ? route('purchase.store')
                    : url('/purchase/store')));

        const STRIPE_URL_TPL = @json($type === 'equipment' ? url('/equipment-purchase/{id}/pay-with-stripe') : url('/purchase/{id}/pay-with-stripe'));

        const PAYFAST_INIT_TPL = @json($type === 'equipment' ? url('/equipment-purchase/{id}/payfast/init') : url('/purchase/{id}/payfast/init'));

        let formRef = null;
        let selectedLocationId = '';
        let selectedLocationName = '';
        let selectedQuantity = 1;

        const formatCurrency = (value) => ZAR.format(Number(value) || 0);
        const findLocationRecord = (locId) =>
            LOCATION_STOCK.find(x => String(x.id) === String(locId));

        function resetQuantitySelect(placeholder = 'Select location first') {
            const select = document.getElementById('qtySelect');
            if (!select) return;
            select.innerHTML = '';
            const opt = document.createElement('option');
            opt.value = '';
            opt.textContent = placeholder;
            opt.disabled = true;
            opt.selected = true;
            select.appendChild(opt);
            select.disabled = true;
            selectedQuantity = 0;
        }

        function populateQuantityOptions(stock, {
            preserveCurrent = false
        } = {}) {
            const select = document.getElementById('qtySelect');
            if (!select) return;

            if (!stock || stock < 1) {
                resetQuantitySelect('Out of stock');
                return;
            }

            const currentValue = preserveCurrent ?
                parseInt(select.value || '1', 10) :
                1;

            const nextValue = Number.isInteger(currentValue) && currentValue > 0 ?
                Math.min(currentValue, stock) :
                1;

            select.disabled = false;
            select.innerHTML = '';

            for (let i = 1; i <= stock; i++) {
                const opt = document.createElement('option');
                opt.value = String(i);
                opt.textContent = String(i);
                select.appendChild(opt);
            }

            select.value = String(nextValue);
            selectedQuantity = nextValue;
        }

        function notify(key, {
            icon = 'error',
            title = 'Invalid input',
            text = ''
        }) {
            window.__deb ||= new Map();
            const now = Date.now(),
                last = window.__deb.get(key) || 0;
            if (now - last < 600) return;
            window.__deb.set(key, now);
            if (window.Swal?.fire) {
                Swal.fire({
                    icon,
                    title,
                    text,
                    confirmButtonText: 'OK'
                });
            } else {
                alert(`${title}\n\n${text}`);
            }
        }

        const modalInst = (el) =>
            bootstrap.Modal.getOrCreateInstance(el, {
                backdrop: 'static',
                focus: true,
                keyboard: false,
            });

        function swapModal(fromId, toId) {
            const fromEl = document.getElementById(fromId);
            const toEl = document.getElementById(toId);
            if (!fromEl || !toEl) return;
            const from = modalInst(fromEl),
                to = modalInst(toEl);
            fromEl.addEventListener(
                'hidden.bs.modal',
                function onHidden() {
                    fromEl.removeEventListener('hidden.bs.modal', onHidden);
                    to.show();
                }, {
                    once: true
                }
            );
            from.hide();
        }

        function updatePricingSummary() {
            if (!formRef) return;

            let locRecord = null;

            if (TYPE === 'equipment') {
                // --- LOCATION + QTY handling ---
                selectedLocationId = formRef.location_id?.value || '';
                locRecord = findLocationRecord(selectedLocationId);
                selectedLocationName = locRecord?.name || '';

                const quantityField = formRef.quantity;
                let qty = parseInt(quantityField?.value ?? '', 10);

                if (!Number.isInteger(qty) || qty < 1) {
                    qty = quantityField && quantityField.disabled ? 0 : 1;
                }

                if (locRecord && locRecord.stock >= 0) {
                    if (qty === 0 && quantityField && !quantityField.disabled) {
                        qty = 1;
                    }

                    if (qty > locRecord.stock) {
                        qty = locRecord.stock > 0 ? locRecord.stock : 0;
                        if (quantityField && !quantityField.disabled) {
                            quantityField.value = qty > 0 ? String(qty) : '';
                        }
                    }
                }
                selectedQuantity = qty;

                // Update hints near labels
                const stockHint = document.getElementById('stockHint');
                const qtyHint = document.getElementById('qtyHint');

                if (stockHint) {
                    if (locRecord) {
                        stockHint.textContent = ``;
                    } else {
                        stockHint.textContent = '';
                    }
                }

                if (qtyHint) {
                    if (selectedLocationId && selectedQuantity > 0) {
                        qtyHint.textContent = ``;
                    } else {
                        qtyHint.textContent = '';
                    }
                }

            } else {
                // --- VEHICLE flow (no per-location stock) ---
                selectedLocationId = '';
                selectedLocationName = '';
                selectedQuantity = 1;
            }

            // --- totals ---
            const quantityForTotals =
                TYPE === 'equipment' ?
                (selectedLocationId && selectedQuantity > 0 ? selectedQuantity : 0) :
                Math.max(selectedQuantity || 1, 1);

            const totalPrice = ITEM_PRICE * quantityForTotals;
            const totalDeposit = ITEM_DEPOSIT * quantityForTotals;
            const remaining = Math.max(totalPrice - totalDeposit, 0);

            // --- Step 1 summary card ---
            const quantityDisplay = document.getElementById('quantityDisplay');
            if (quantityDisplay) {
                if (selectedLocationId && selectedQuantity > 0) {
                    quantityDisplay.textContent =
                        selectedQuantity === 1 ? '1 unit' : `${selectedQuantity} units`;
                } else {
                    quantityDisplay.textContent = '—';
                }
            }

            const selectedLocationDisplay = document.getElementById('selectedLocationDisplay');
            if (selectedLocationDisplay) {
                if (locRecord && locRecord.stock < 1) {
                    selectedLocationDisplay.textContent =
                        `${selectedLocationName} (Out of stock)`;
                } else {
                    selectedLocationDisplay.textContent =
                        selectedLocationName || 'Select a location';
                }
            }

            const unitPriceDisplay = document.getElementById('unitPriceDisplay');
            if (unitPriceDisplay)
                unitPriceDisplay.textContent = `${formatCurrency(ITEM_PRICE)} ZAR`;

            const unitDepositDisplay = document.getElementById('unitDepositDisplay');
            if (unitDepositDisplay)
                unitDepositDisplay.textContent = `${formatCurrency(ITEM_DEPOSIT)} ZAR`;

            const totalPriceDisplay = document.getElementById('totalPriceDisplay');
            if (totalPriceDisplay)
                totalPriceDisplay.textContent = `${formatCurrency(totalPrice)} ZAR`;

            const totalDepositDisplay = document.getElementById('totalDepositDisplay');
            if (totalDepositDisplay)
                totalDepositDisplay.textContent = `${formatCurrency(totalDeposit)} ZAR`;

            // --- Stripe modal summary ---
            const summaryItemName = document.getElementById('summaryItemName');
            if (summaryItemName) summaryItemName.textContent = ITEM_NAME;

            const summaryUnitPrice = document.getElementById('summaryUnitPrice');
            if (summaryUnitPrice)
                summaryUnitPrice.textContent = `${formatCurrency(ITEM_PRICE)} ZAR`;

            const summaryUnitDeposit = document.getElementById('summaryUnitDeposit');
            if (summaryUnitDeposit)
                summaryUnitDeposit.textContent = `${formatCurrency(ITEM_DEPOSIT)} ZAR`;

            const summaryLocation = document.getElementById('summaryLocation');
            if (summaryLocation) {
                if (locRecord && locRecord.stock < 1) {
                    summaryLocation.textContent =
                        `${selectedLocationName} (Out of stock)`;
                } else {
                    summaryLocation.textContent =
                        selectedLocationName || 'Select a location';
                }
            }

            const summaryQuantity = document.getElementById('summaryQuantity');
            if (summaryQuantity) {
                summaryQuantity.textContent =
                    selectedLocationId && selectedQuantity > 0 ?
                    String(selectedQuantity) :
                    '—';
            }

            const summaryTotalPrice = document.getElementById('summaryTotalPrice');
            if (summaryTotalPrice)
                summaryTotalPrice.textContent = `${formatCurrency(totalPrice)} ZAR`;

            const summaryDeposit = document.getElementById('summaryDeposit');
            if (summaryDeposit)
                summaryDeposit.textContent = `${formatCurrency(totalDeposit)} ZAR`;

            const stripeUnitPriceDisplay = document.getElementById('stripeUnitPriceDisplay');
            if (stripeUnitPriceDisplay)
                stripeUnitPriceDisplay.textContent =
                `${formatCurrency(ITEM_PRICE)} ZAR`;

            const stripeTotalPriceDisplay = document.getElementById('stripeTotalPriceDisplay');
            if (stripeTotalPriceDisplay)
                stripeTotalPriceDisplay.textContent =
                `${formatCurrency(totalPrice)} ZAR`;

            const stripeDepositDisplay = document.getElementById('stripeDepositDisplay');
            if (stripeDepositDisplay)
                stripeDepositDisplay.textContent =
                `${formatCurrency(totalDeposit)} ZAR`;

            const stripeTotalDueNowDisplay = document.getElementById(
                'stripeTotalDueNowDisplay'
            );
            if (stripeTotalDueNowDisplay)
                stripeTotalDueNowDisplay.textContent =
                `${formatCurrency(totalDeposit)} ZAR`;

            const stripeRemainingDisplay = document.getElementById(
                'stripeRemainingDisplay'
            );
            if (stripeRemainingDisplay)
                stripeRemainingDisplay.textContent =
                `${formatCurrency(remaining)} ZAR`;

            // Keep hidden input in sync for server
            if (formRef.total_price) {
                formRef.total_price.value = totalPrice.toFixed(2);
            }
        }

        function getEquipmentSelection(showAlerts = true) {
            if (TYPE !== 'equipment') {
                return {
                    ok: true,
                    quantity: 1
                };
            }
            if (!formRef) return {
                ok: false
            };

            const locId = formRef.location_id?.value || '';
            const qty = parseInt(formRef.quantity?.value || '1', 10);

            if (!locId) {
                if (showAlerts)
                    notify('loc', {
                        title: 'Location required',
                        text: 'Please select a location.',
                    });
                return {
                    ok: false
                };
            }

            const record = findLocationRecord(locId);
            if (!record) {
                if (showAlerts)
                    notify('loc2', {
                        title: 'Invalid location',
                        text: 'Selected location is not valid for this item.',
                    });
                return {
                    ok: false
                };
            }

            if (!Number.isInteger(qty) || qty < 1) {
                if (record && record.stock < 1) {
                    if (showAlerts)
                        notify('qty0', {
                            title: 'Out of stock',
                            text: `${record.name} currently has no stock available.`,
                        });
                } else if (showAlerts) {
                    notify('qty', {
                        title: 'Invalid quantity',
                        text: 'Enter a valid quantity (1 or more).',
                    });
                }
                return {
                    ok: false
                };
            }

            if (qty > record.stock) {
                if (showAlerts)
                    notify('qty2', {
                        title: 'Insufficient stock',
                        text: `Only ${record.stock} available at ${record.name}.`,
                    });
                return {
                    ok: false
                };
            }

            return {
                ok: true,
                locationId: locId,
                quantity: qty,
                location: record,
            };
        }

        // block clicks when sold
        document.addEventListener('click', (e) => {
            if (!IS_SOLD) return;
            const opener = e.target.closest(
                '[data-bs-target="#purchaseModal"], [data-bs-target="#purchaseCustomer"], [data-bs-target="#purchasePayment"], [data-bs-target="#stripePaymentModal"]'
            );
            const ids = [
                'purchaseStep1Next',
                'purchaseStep2Next',
                'purchaseStripePayButton',
            ];
            if (opener || ids.includes(e.target?.id)) {
                e.preventDefault();
                Swal?.fire({
                    icon: 'info',
                    title: 'Sold',
                    text: `This ${TYPE} has been sold and cannot be purchased.`,
                });
            }
        });

        // modal stacking
        const Z_BASE = 1055,
            Z_STEP = 20;
        const visibleModals = () =>
            Array.from(document.querySelectorAll('.modal.show'));
        const restack = () => {
            const open = visibleModals();
            open.forEach(
                (m, i) => (m.style.zIndex = String(Z_BASE + i * Z_STEP))
            );
            if (open.length) {
                document.body.classList.add('modal-open');
            } else {
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('padding-right');
            }
        };
        ['show.bs.modal', 'shown.bs.modal', 'hidden.bs.modal'].forEach((evt) =>
            document.addEventListener(evt, () => setTimeout(restack, 0))
        );

        function openWhatsAppNewTab() {
            const a = document.createElement('a');
            a.href = WHATSAPP_LINK;
            a.target = '_blank';
            a.rel = 'noopener noreferrer';
            a.style.cssText =
                'position:fixed;top:0;left:0;width:1px;height:1px;opacity:.01;';
            document.body.appendChild(a);
            a.click();
            setTimeout(() => a.remove(), 100);
        }

        function clampQtyByLocation(locId) {
            const rec = findLocationRecord(locId);
            if (!rec) {
                resetQuantitySelect();
                updatePricingSummary();
                return;
            }
            populateQuantityOptions(rec.stock);
            updatePricingSummary();
        }

        document.addEventListener('change', (e) => {
            if (e.target?.id === 'locationSelect')
                clampQtyByLocation(e.target.value);
        });

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.modal').forEach((m) => modalInst(m));
            const $ = (id) => document.getElementById(id);
            formRef = $('purchaseForm');
            const form = formRef;

            if (TYPE === 'equipment') {
                resetQuantitySelect();
                const initialLocation = form?.location_id?.value;
                if (initialLocation) {
                    clampQtyByLocation(initialLocation);
                }
            }

            updatePricingSummary();

            form?.location_id?.addEventListener('change', (event) => {
                clampQtyByLocation(event.target.value);
            });

            form?.quantity?.addEventListener('change', () =>
                updatePricingSummary()
            );

            $('purchaseStep1Next')?.addEventListener('click', () => {
                if (IS_SOLD)
                    return notify('sold1', {
                        title: 'Sold',
                        text: `This ${TYPE} is sold.`,
                    });
                const selection = getEquipmentSelection(true);
                if (!selection.ok) return;
                updatePricingSummary();
                swapModal('purchaseModal', 'purchaseCustomer');
            });

            $('purchaseBackToStep1')?.addEventListener('click', () =>
                swapModal('purchaseCustomer', 'purchaseModal')
            );

            $('purchaseStep2Next')?.addEventListener('click', async () => {
                if (IS_SOLD)
                    return notify('sold2', {
                        title: 'Sold',
                        text: `This ${TYPE} is sold.`,
                    });

                updatePricingSummary();

                const name = (form.name?.value || '').trim();
                const email = (form.email?.value || '').trim();
                const phone = (form.phone?.value || '').trim();
                const country = (form.country?.value || '').trim();

                if (!name || !email || !phone || !country) {
                    notify('missing', {
                        title: 'Missing Information',
                        text: 'Please fill in all required customer details.',
                    });
                    return;
                }
                if (!emailPattern.test(email)) {
                    notify('email', {
                        title: 'Invalid Email',
                        text: 'Enter a valid email address.',
                    });
                    form.email?.focus();
                    return;
                }
                if (!phonePattern.test(phone)) {
                    notify('phone', {
                        title: 'Invalid Phone Number',
                        text: 'Use digits with optional spaces or dashes.',
                    });
                    form.phone?.focus();
                    return;
                }

                const payload = {
                    name,
                    email,
                    phone,
                    country,
                    total_price: form.total_price.value,
                };
                const idName =
                    TYPE === 'equipment' ? 'equipment_id' : 'vehicle_id';
                payload[idName] = form[idName].value;

                // Extra validation for equipment
                let selection = {
                    ok: true,
                    locationId: null,
                    quantity: 1
                };
                if (TYPE === 'equipment') {
                    selection = getEquipmentSelection(true);
                    if (!selection.ok) return;
                    payload.location_id = selection.locationId;
                    payload.quantity = selection.quantity;
                }

                try {
                    const res = await fetch(STORE_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        },
                        body: JSON.stringify(payload),
                    });
                    const data = await res.json();
                    if (!res.ok || !data?.success)
                        throw new Error(
                            data?.message || 'Unable to save details.'
                        );

                    let hid = form.querySelector(
                        'input[name="purchase_id"]'
                    );
                    if (!hid) {
                        hid = document.createElement('input');
                        hid.type = 'hidden';
                        hid.name = 'purchase_id';
                        form.appendChild(hid);
                    }
                    hid.value = data.purchase_id;

                    updatePricingSummary();
                    swapModal('purchaseCustomer', 'purchasePayment');
                } catch (e) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: e.message || 'Network error.',
                    });
                }
            });

            $('backToCustomer')?.addEventListener('click', () =>
                swapModal('purchasePayment', 'purchaseCustomer')
            );

            $('purchasePayment')?.addEventListener('show.bs.modal', () => {
                updatePricingSummary();
                document
                    .querySelectorAll(
                        '#purchasePayment input[name="payment_method"]'
                    )
                    .forEach((r) => (r.checked = false));
            });

            // Stripe
            let stripe,
                elements,
                cardNumber,
                cardExpiry,
                cardCvc;
            async function ensureStripeMounted() {
                if (stripe) return true;
                const publishableKey = @json($settings->stripe_key ?? (config('services.stripe.key') ?? ''));
                if (!publishableKey) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Stripe not configured',
                        text: 'Publishable key is missing.',
                    });
                    return false;
                }
                try {
                    stripe = Stripe(publishableKey);
                    elements = stripe.elements();
                    const style = {
                        base: {
                            fontSize: '16px',
                            color: '#32325d',
                            '::placeholder': {
                                color: '#a0aec0',
                            },
                        },
                    };
                    cardNumber = elements.create('cardNumber', {
                        style
                    });
                    cardExpiry = elements.create('cardExpiry', {
                        style
                    });
                    cardCvc = elements.create('cardCvc', {
                        style
                    });
                    cardNumber.mount('#card-number');
                    cardExpiry.mount('#card-expiry');
                    cardCvc.mount('#card-cvc');
                    return true;
                } catch (e) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Stripe error',
                        text: e.message || 'Failed to initialize Stripe.',
                    });
                    return false;
                }
            }

            document
                .querySelectorAll('input[name="payment_method"]')
                .forEach((radio) => {
                    radio.addEventListener('change', async function() {
                        const choice = this.value;
                        if (IS_SOLD) {
                            this.checked = false;
                            return notify('sold3', {
                                title: 'Sold',
                                text: `This ${TYPE} is sold.`,
                            });
                        }

                        const pid = form.querySelector(
                            'input[name="purchase_id"]'
                        )?.value;
                        if (!pid) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Missing purchase',
                                text: 'Please save your details first.',
                            });
                            this.checked = false;
                            return;
                        }

                        if (choice === 'stripe') {
                            const ok = await ensureStripeMounted();
                            if (!ok) {
                                this.checked = false;
                                return;
                            }
                            swapModal('purchasePayment', 'stripePaymentModal');
                            return;
                        }

                        // PayFast flow
                        const confirmed = await Swal.fire({
                            icon: 'question',
                            title: 'Proceed with PayFast?',
                            text: 'You will be redirected to PayFast.',
                            showCancelButton: true,
                            confirmButtonText: 'Continue',
                            cancelButtonText: 'Back',
                            reverseButtons: true,
                            buttonsStyling: false,
                            customClass: {
                                confirmButton: 'btn btn-dark',
                                cancelButton: 'btn btn-outline-secondary me-3',
                            },
                        });
                        if (!confirmed.isConfirmed) {
                            this.checked = false;
                            return;
                        }

                        try {
                            const initUrl = PAYFAST_INIT_TPL.replace(
                                '{id}',
                                encodeURIComponent(pid)
                            );
                            const res = await fetch(initUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                                },
                                body: JSON.stringify({
                                    name: form.name?.value || '',
                                    email: form.email?.value || '',
                                }),
                            });
                            const data = await res.json();
                            if (!res.ok || !data?.success)
                                throw new Error(
                                    data?.message ||
                                    'Failed to initialize PayFast.'
                                );

                            const pfForm =
                                document.createElement('form');
                            pfForm.method = 'POST';
                            pfForm.action = data.action;
                            pfForm.style.display = 'none';
                            Object.entries(data.fields || {}).forEach(
                                ([k, v]) => {
                                    const inp =
                                        document.createElement('input');
                                    inp.type = 'hidden';
                                    inp.name = k;
                                    inp.value = v;
                                    pfForm.appendChild(inp);
                                }
                            );
                            document.body.appendChild(pfForm);
                            pfForm.submit();
                        } catch (e) {
                            Swal.fire({
                                icon: 'error',
                                title: 'PayFast error',
                                text: e.message ||
                                    'Could not redirect to PayFast.',
                            });
                            this.checked = false;
                        }
                    });
                });

            $('purchaseStripeBackToPayment')?.addEventListener('click', () =>
                swapModal('stripePaymentModal', 'purchasePayment')
            );

            $('purchaseStripePayButton')?.addEventListener(
                'click',
                async () => {
                    if (IS_SOLD)
                        return notify('sold4', {
                            title: 'Sold',
                            text: `This ${TYPE} is sold.`,
                        });

                    const pid = form.querySelector(
                        'input[name="purchase_id"]'
                    )?.value;
                    if (!pid) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Missing purchase',
                            text: 'Purchase ID is missing. Please save your details again.',
                        });
                        return;
                    }

                    if (!stripe || !cardNumber) {
                        const ok = await ensureStripeMounted();
                        if (!ok) return;
                    }

                    Swal.fire({
                        title: 'Processing payment',
                        html: 'Please do not close this window.',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading(),
                    });

                    const {
                        paymentMethod,
                        error
                    } =
                    await stripe.createPaymentMethod({
                        type: 'card',
                        card: cardNumber,
                        billing_details: {
                            name: form.name?.value || '',
                            email: form.email?.value || '',
                        },
                    });
                    if (error) {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Card error',
                            text: error.message,
                        });
                        return;
                    }

                    try {
                        const stripeUrl = STRIPE_URL_TPL.replace(
                            '{id}',
                            encodeURIComponent(pid)
                        );
                        const res = await fetch(stripeUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                            },
                            body: JSON.stringify({
                                payment_method_id: paymentMethod.id,
                            }),
                        });
                        const data = await res.json();
                        Swal.close();

                        if (data?.success) {
                            const go =
                                data.redirect_to ||
                                '/?purchase=success';
                            Swal.fire({
                                icon: 'success',
                                title: 'Payment Successful!',
                                html: `<div class="text-start">
                    <p class="mb-1"><strong>Item:</strong> ${ITEM_NAME}</p>
                    <p class="mb-1"><strong>Amount paid:</strong> ${ZAR.format(data.paid ?? ITEM_DEPOSIT)}</p>
                    <p class="mb-1"><strong>Reference:</strong> #${data.purchase_id}</p>
                    ${data.receipt_url ? `<p class="mb-0"><a href="${data.receipt_url}" target="_blank" rel="noopener">View Stripe receipt</a></p>` : ''}
                  </div><hr class="my-2"><p class="mb-0"><strong>Opening WhatsApp chat for you...</strong></p>`,
                                showConfirmButton: true,
                                confirmButtonText: 'Continue to WhatsApp',
                                allowOutsideClick: false,
                            }).then(() => {
                                openWhatsAppNewTab();
                                window.location.href = go;
                            });
                            return;
                        }

                        if (
                            data?.requires_action &&
                            data.payment_intent_client_secret
                        ) {
                            const result =
                                await stripe.confirmCardPayment(
                                    data.payment_intent_client_secret
                                );
                            if (result.error) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Authentication failed',
                                    text: result.error.message,
                                });
                            } else if (
                                result.paymentIntent?.status ===
                                'succeeded'
                            ) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Payment Successful!',
                                    html: `<div class="text-start">
                      <p class="mb-1"><strong>Item:</strong> ${ITEM_NAME}</p>
                      <p class="mb-1"><strong>Amount paid:</strong> ${ZAR.format(ITEM_DEPOSIT)}</p>
                    </div><hr class="my-2"><p class="mb-0"><strong>Opening WhatsApp chat for you...</strong></p>`,
                                    showConfirmButton: true,
                                    confirmButtonText: 'Continue to WhatsApp',
                                    allowOutsideClick: false,
                                }).then(() => {
                                    openWhatsAppNewTab();
                                    window.location.href =
                                        '/?purchase=success';
                                });
                            } else {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Payment status unknown',
                                    text: 'Please check your email for a receipt.',
                                });
                            }
                            return;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Payment failed',
                            text: data?.message ||
                                'There was a problem processing your payment.',
                        });
                    } catch (e) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Network error',
                            text: 'Unable to complete payment. Please try again.',
                        });
                    }
                }
            );

            // Post-success WhatsApp popup (redirect from Stripe / PayFast)
            try {
                const params = new URLSearchParams(
                    window.location.search
                );
                if (
                    params.get('purchase') === 'success' ||
                    params.get('payfast_success')
                ) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Payment Successful!',
                        html: `<p>Your deposit payment was successful!</p><p><strong>Click below to open WhatsApp chat</strong></p>`,
                        showConfirmButton: true,
                        confirmButtonText: 'Open WhatsApp Chat',
                        allowOutsideClick: false,
                    }).then(() => {
                        openWhatsAppNewTab();
                        window.location.href = '/';
                    });

                    params.delete('purchase');
                    params.delete('payfast_success');
                    const newUrl = `${location.pathname}${
                    params.toString()
                        ? '?' + params.toString()
                        : ''
                }${location.hash}`;
                    window.history.replaceState({}, '', newUrl);
                }
            } catch {}

            @if (session('payfast_success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Payment Successful!',
                    html: `<p>{{ session('payfast_success') }}</p><p><strong>Click below to open WhatsApp chat</strong></p>`,
                    showConfirmButton: true,
                    confirmButtonText: 'Open WhatsApp Chat',
                    allowOutsideClick: false,
                }).then(() => {
                    openWhatsAppNewTab();
                    window.location.href = '/';
                });
            @endif
        });
    })();
</script>
