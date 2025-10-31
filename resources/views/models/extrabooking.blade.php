@php
    use App\Models\SystemSetting;
    use App\Models\StripeSetting;
    use Illuminate\Support\Facades\Cache;

    $item = $equipment ?? $vehicle ?? null;
    $category = $item?->category ?? null;
    $stocks = collect($item?->stocks ?? [])->filter(function ($stock) {
        return $stock && $stock->location;
    })->values();

    $unitOptions = [
        'day' => [
            'label' => 'Daily Rental',
            'description' => 'Perfect for short trips',
            'icon' => 'bi-clock',
            'price' => $category->daily_price ?? $item->daily_price ?? $item->rental_price_day ?? null,
            'suffix' => '/day',
        ],
        'week' => [
            'label' => 'Weekly Rental',
            'description' => 'Great for 1-4 weeks',
            'icon' => 'bi-calendar-event',
            'price' => $category->weekly_price ?? $item->weekly_price ?? $item->rental_price_week ?? null,
            'suffix' => '/week',
        ],
        'month' => [
            'label' => 'Monthly Rental',
            'description' => 'Best for long stays',
            'icon' => 'bi-box',
            'price' => $category->monthly_price ?? $item->monthly_price ?? $item->rental_price_month ?? null,
            'suffix' => '/month',
        ],
    ];

    $unitOptions = array_filter($unitOptions, static function ($option) {
        return !is_null($option['price']) && $option['price'] > 0;
    });

    $defaultUnit = array_key_first($unitOptions);
    $categoryId = $category?->id ?? $item?->category_id ?? null;
    $today = now()->toDateString();

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

    $stripeConfig = StripeSetting::first();
    $stripePublicKey = $stripeConfig->stripe_key ?? '';
    $paymentMethodsEnabled = ($settings->stripe_enabled ? 1 : 0) + ($settings->payfast_enabled ? 1 : 0);
@endphp

@if ($item && $categoryId && count($unitOptions) > 0)
    <div class="modal fade" id="bookingModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
            <div class="modal-content rounded-4">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-calendar-check me-2"></i>Book {{ $item->name }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body pt-0">
                    <div class="booking-stepper mb-4">
                        <div class="booking-step active" data-step-index="0">
                            <span class="booking-step-number">1</span>
                            <span class="booking-step-label">Options</span>
                        </div>
                        <div class="booking-step" data-step-index="1">
                            <span class="booking-step-number">2</span>
                            <span class="booking-step-label">Your Details</span>
                        </div>
                        <div class="booking-step" data-step-index="2">
                            <span class="booking-step-number">3</span>
                            <span class="booking-step-label">Review</span>
                        </div>
                    </div>

                    <form id="bookingForm" method="POST" action="{{ route('bookings.store') }}" novalidate>
                        @csrf
                        <input type="hidden" name="category_id" value="{{ $categoryId }}">
                        <input type="hidden" name="equipment_id" value="{{ $item->id }}">
                        <input type="hidden" name="rental_unit" id="bookingRentalUnit"
                            value="{{ $defaultUnit ?? '' }}">
                        <input type="hidden" name="total_price" id="bookingTotalInput" value="0">
                        <input type="hidden" name="equipment_stock_id" id="bookingStockIdInput" value="">
                        <input type="hidden" name="booking_id" id="bookingId">

                        <div data-booking-step="0">
                            <div class="alert alert-danger d-none" id="bookingStep1Error"></div>

                            <h6 class="fw-bold mb-3">Select Rental Duration</h6>
                            <div class="row g-3 mb-4">
                                @foreach ($unitOptions as $unit => $option)
                                    <div class="col-12 col-md-4">
                                        <button type="button"
                                            class="booking-unit-card {{ $loop->first ? 'active' : '' }}"
                                            data-unit-card data-unit="{{ $unit }}"
                                            data-price="{{ $option['price'] }}"
                                            data-suffix="{{ $option['suffix'] }}">
                                            <span class="booking-unit-icon"><i
                                                    class="bi {{ $option['icon'] }}"></i></span>
                                            <span class="booking-unit-title">{{ $option['label'] }}</span>
                                            <span class="booking-unit-description text-muted small">
                                                {{ $option['description'] }}
                                            </span>
                                            <span class="booking-unit-price fw-semibold">
                                                R{{ number_format($option['price'], 2) }}{{ $option['suffix'] }}
                                            </span>
                                        </button>
                                    </div>
                                @endforeach
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-6">
                                    <label for="bookingStartDate" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="bookingStartDate"
                                        name="rental_start_date" value="{{ $today }}" min="{{ $today }}" required>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="bookingQuantity">
                                        <span data-quantity-label>Number of days</span>
                                    </label>
                                    <select id="bookingQuantity" name="rental_quantity" class="form-select" required>
                                        <option value="1" selected>1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-6" data-extra-days-wrap>
                                    <label for="bookingExtraDays" class="form-label">Extra Days (optional)</label>
                                    <select id="bookingExtraDays" name="extra_days" class="form-select">
                                        <option value="0" selected>0</option>
                                    </select>
                                    <div class="form-text">Add extra days on top of the selected plan.</div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="bookingLocation" class="form-label">Select Location</label>
                                    <select id="bookingLocation" name="location_id" class="form-select" required
                                        {{ $stocks->isEmpty() ? 'disabled' : '' }}>
                                        @if ($stocks->isEmpty())
                                            <option value="" selected>No locations available</option>
                                        @else
                                            @foreach ($stocks as $stock)
                                                <option value="{{ $stock->location_id }}"
                                                    data-available="{{ (int) $stock->stock }}"
                                                    data-stock-id="{{ $stock->id }}"
                                                    data-location-name="{{ $stock->location->name }}">
                                                    {{ $stock->location->name }} ({{ (int) $stock->stock }} in stock)
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @if ($stocks->isEmpty())
                                        <div class="form-text text-danger">No stock available for this item.</div>
                                    @endif
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label for="bookingStock" class="form-label">Units to Reserve</label>
                                    <select id="bookingStock" name="stock_quantity" class="form-select"
                                        {{ $stocks->isEmpty() ? 'disabled' : '' }}>
                                        <option value="1" selected>1 unit</option>
                                    </select>
                                    <div class="form-text">Controls how many units are reserved at the location.</div>
                                </div>
                            </div>

                            <div class="booking-summary-panel mt-4">
                                <div class="booking-summary-row">
                                    <span class="text-muted small">Vehicle total</span>
                                    <strong id="bookingTotalDisplay">R0.00</strong>
                                </div>
                                <div class="booking-summary-row border-0 pt-3">
                                    <div>
                                        <div class="text-muted small">Start date</div>
                                        <strong id="bookingPeriodStart">-</strong>
                                    </div>
                                    <div class="text-end">
                                        <div class="text-muted small">End date</div>
                                        <strong id="bookingPeriodEnd">-</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="button" class="btn btn-dark w-100" id="bookingStep1Next"
                                    {{ $stocks->isEmpty() ? 'disabled' : '' }}>
                                    Continue
                                </button>
                            </div>
                        </div>

                        <div class="d-none" data-booking-step="1">
                            <div class="alert alert-danger d-none" id="bookingStep2Error"></div>

                            <h6 class="fw-bold mb-3">Your Details</h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="bookingName" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="bookingName" name="name"
                                        placeholder="John Smith" required>
                                </div>
                                <div class="col-12">
                                    <label for="bookingEmail" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="bookingEmail" name="email"
                                        placeholder="you@example.com" required>
                                </div>
                                <div class="col-12">
                                    <label for="bookingPhone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="bookingPhone" name="phone"
                                        placeholder="+27 123 456 7890" required>
                                </div>
                                <div class="col-12">
                                    <label for="bookingCountry" class="form-label">Country (optional)</label>
                                    <input type="text" class="form-control" id="bookingCountry" name="country"
                                        placeholder="South Africa">
                                </div>
                            </div>

                            <div class="mt-4 d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary flex-grow-1"
                                    id="bookingStep2Back">
                                    Back
                                </button>
                                <button type="button" class="btn btn-dark flex-grow-1" id="bookingStep2Next">
                                    Review Booking
                                </button>
                            </div>
                        </div>

                        <div class="d-none" data-booking-step="2">
                            <h6 class="fw-bold mb-3">Review & Confirm</h6>

                            <div class="booking-review mb-4">
                                <h6 class="fw-semibold mb-2">Rental Details</h6>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Plan</span>
                                        <span data-summary="unit">-</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Rate</span>
                                        <span data-summary="rate">-</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Quantity</span>
                                        <span data-summary="quantity">-</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Extra days</span>
                                        <span data-summary="extra_days">-</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Start</span>
                                        <span data-summary="start">-</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>End</span>
                                        <span data-summary="end">-</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Location</span>
                                        <span data-summary="location">-</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Units reserved</span>
                                        <span data-summary="stock_qty">-</span>
                                    </li>
                                </ul>
                            </div>

                            <div class="booking-review mb-4">
                                <h6 class="fw-semibold mb-2">Contact</h6>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Name</span>
                                        <span data-summary="name">-</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Email</span>
                                        <span data-summary="email">-</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Phone</span>
                                        <span data-summary="phone">-</span>
                                    </li>
                                </ul>
                            </div>

                            <div class="booking-total-card mb-3">
                                <span class="text-muted small">Total price</span>
                                <span class="h5 mb-0" data-summary="total">R0.00</span>
                            </div>

                            <div class="alert alert-danger d-none" id="bookingSummaryError" role="alert"></div>

                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary flex-grow-1"
                                    id="bookingStep3Back">
                                    Back
                                </button>
                                <button type="button" class="btn btn-dark flex-grow-1" id="openPayment"
                                    {{ $stocks->isEmpty() ? 'disabled' : '' }}>
                                    Continue to Payment
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer border-0">
                    <small class="text-muted">Need help? Contact us and we will assist with your booking.</small>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="bookingPayment" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 shadow">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-credit-card-fill me-2"></i>Select Payment Method
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3 align-items-stretch justify-content-center">
                        @if ($settings->stripe_enabled)
                            <div class="col-12 {{ $paymentMethodsEnabled === 2 ? 'col-md-6' : 'col-md-12' }}">
                                <input type="radio" name="booking_payment_method" id="bookingStripe"
                                    value="stripe" class="btn-check" autocomplete="off">
                                <label for="bookingStripe" class="card btn w-100 booking-pay-option p-3 flex-column">
                                    <div class="text-center mb-2">
                                        <img src="{{ asset('images/stripe.png') }}" class="rounded-3" alt="Stripe"
                                            style="width: 80px;">
                                    </div>
                                    <div class="booking-pay-text text-center">
                                        <div class="fw-bold">Stripe (Card)</div>
                                        <small class="text-muted">Visa � Mastercard � Amex</small>
                                    </div>
                                </label>
                            </div>
                        @endif

                        @if ($settings->payfast_enabled)
                            <div class="col-12 {{ $paymentMethodsEnabled === 2 ? 'col-md-6' : 'col-md-12' }}">
                                <input type="radio" name="booking_payment_method" id="bookingPayfast"
                                    value="payfast" class="btn-check" autocomplete="off">
                                <label for="bookingPayfast" class="card btn w-100 booking-pay-option p-3 flex-column">
                                    <div class="text-center mb-2">
                                        <img src="{{ asset('images/payfast.png') }}" class="rounded-3"
                                            alt="PayFast" style="width: 80px;">
                                    </div>
                                    <div class="booking-pay-text text-center">
                                        <div class="fw-bold">PayFast</div>
                                        <small class="text-muted">South African payments</small>
                                    </div>
                                </label>
                            </div>
                        @endif

                        @if ($paymentMethodsEnabled === 0)
                            <div class="col-12">
                                <div class="alert alert-warning text-center mb-0">
                                    No payment methods are currently available.
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" id="paymentBackToSummary">
                        Back
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="bookingStripeModal" tabindex="-1" aria-hidden="true"
        style="margin-top: 4rem; height:90vh;">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content rounded-4 shadow">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-credit-card-fill me-2"></i>Stripe Payment
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
                    <button type="button" class="btn btn-outline-secondary me-auto" id="stripeBackToPayment">
                        Back
                    </button>
                    <button type="button" id="bookingStripePayButton" class="btn btn-dark">
                        Pay with Stripe
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="bookingPaymentLoader" class="booking-payment-loader d-none" role="alert" aria-live="polite"
        aria-hidden="true">
        <div class="booking-payment-loader-backdrop"></div>
        <div class="booking-payment-loader-content">
            <div class="spinner-border text-light" role="status" aria-hidden="true"></div>
            <span id="bookingPaymentLoaderText" class="mt-3 text-white fw-semibold">Processing payment...</span>
        </div>
    </div>

    <div class="modal fade" id="bookingThankYou" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-sm-down">
            <div class="modal-content rounded-4 shadow border-0">
                <div class="modal-body p-4 p-md-5">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10"
                            style="width:56px;height:56px;">
                            <i class="bi bi-check-lg text-success fs-3"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-1">Payment Successful!</h4>
                            <p class="text-muted mb-0">We've confirmed your booking. A receipt has been sent to your
                                email.</p>
                        </div>
                    </div>

                    <div class="booking-review mb-4">
                        <h6 class="fw-semibold mb-2">Booking Summary</h6>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Item</span>
                                <span id="tyItemName">{{ $item->name }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Reference</span>
                                <span id="tyReference">-</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Period</span>
                                <span id="tyPeriod">-</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Payment method</span>
                                <span id="tyMethod">-</span>
                            </li>
                        </ul>
                    </div>

                    <div class="booking-total-card mb-4">
                        <span class="text-muted small">Total paid</span>
                        <span class="h5 mb-0" id="tyAmount">R0.00</span>
                    </div>

                    <div class="alert alert-success d-flex align-items-center gap-2" role="alert">
                        <i class="bi bi-envelope-fill"></i>
                        <div id="tyCustomerContact">We will email your confirmation shortly.</div>
                    </div>

                    <div class="d-flex flex-column flex-md-row gap-2 mt-4">
                        <button type="button" class="btn btn-success w-100" data-bs-dismiss="modal">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        #bookingModal .booking-stepper {
            display: flex;
            gap: .75rem;
            justify-content: space-between;
        }

        #bookingModal .booking-step {
            flex: 1;
            padding: .75rem;
            border-radius: 1rem;
            background: #f1f3f2;
            text-align: center;
            transition: all .2s ease;
        }

        #bookingModal .booking-step.completed {
            background: #dce8dc;
            color: #2d6a4f;
        }

        #bookingModal .booking-step.active {
            background: #679767;
            color: #fff;
        }

        #bookingModal .booking-step-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .2);
            margin-bottom: .35rem;
            font-weight: 600;
        }

        #bookingModal .booking-step-label {
            display: block;
            font-size: .8rem;
            font-weight: 600;
        }

        #bookingModal .booking-unit-card {
            width: 100%;
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            background: #fff;
            padding: 1rem;
            text-align: left;
            display: flex;
            flex-direction: column;
            gap: .5rem;
            transition: all .2s ease;
        }

        #bookingModal .booking-unit-card:hover {
            border-color: #679767;
            transform: translateY(-2px);
        }

        #bookingModal .booking-unit-card.active {
            border-color: #679767;
            box-shadow: 0 12px 24px rgba(103, 151, 103, .15);
        }

        #bookingModal .booking-unit-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: #f5faf5;
            color: #679767;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        #bookingModal .booking-summary-panel {
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            padding: 1rem;
            background: #f8faf8;
        }

        #bookingModal .booking-summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #e5e7eb;
            padding-top: .75rem;
            margin-top: .75rem;
        }

        #bookingModal .booking-summary-row:first-child {
            border-top: none;
            margin-top: 0;
            padding-top: 0;
        }

        #bookingModal .booking-review .list-group-item {
            padding-left: 0;
            padding-right: 0;
            border: none;
        }

        #bookingModal .booking-total-card {
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f5faf5;
        }

        #bookingModal .form-text {
            font-size: .75rem;
        }

        #bookingPayment .booking-pay-option {
            min-height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            border: 1px solid #dee2e6;
            border-radius: .75rem;
            padding: 20px;
            text-align: center;
            transition: box-shadow .15s ease, transform .15s ease, border-color .15s ease;
        }

        #bookingPayment .booking-pay-option:hover {
            border-color: #679767;
            box-shadow: 0 12px 20px rgba(103, 151, 103, .15);
            transform: translateY(-2px);
        }

        #bookingPayment .btn-check:checked+.booking-pay-option {
            border-color: #679767;
            background: rgba(103, 151, 103, .08);
            box-shadow: 0 12px 24px rgba(103, 151, 103, .2);
        }

        .booking-payment-loader {
            position: fixed;
            inset: 0;
            z-index: 1080;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .booking-payment-loader.d-none {
            display: none;
        }

        .booking-payment-loader-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(33, 37, 41, .6);
        }

        .booking-payment-loader-content {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            border-radius: 1rem;
            background: rgba(0, 0, 0, .3);
            backdrop-filter: blur(4px);
        }
    </style>
    <script>
        (() => {
            if (window.__extraBookingModalInitialized) {
                return;
            }
            window.__extraBookingModalInitialized = true;

            const modalEl = document.getElementById('bookingModal');
            if (!modalEl) {
                return;
            }

            const form = modalEl.querySelector('#bookingForm');
            if (!form) {
                return;
            }

            const steps = Array.from(modalEl.querySelectorAll('[data-booking-step]'));
            const stepIndicators = Array.from(modalEl.querySelectorAll('.booking-step'));
            const stepErrors = {
                0: modalEl.querySelector('#bookingStep1Error'),
                1: modalEl.querySelector('#bookingStep2Error'),
            };
            const summaryErrorEl = modalEl.querySelector('#bookingSummaryError');

            const unitCards = Array.from(modalEl.querySelectorAll('[data-unit-card]'));
            const rentalUnitInput = form.querySelector('#bookingRentalUnit');
            const quantitySelect = form.querySelector('#bookingQuantity');
            const quantityLabel = modalEl.querySelector('[data-quantity-label]');
            const extraDaysWrap = modalEl.querySelector('[data-extra-days-wrap]');
            const extraDaysSelect = form.querySelector('#bookingExtraDays');
            const startDateInput = form.querySelector('#bookingStartDate');
            const locationSelect = form.querySelector('#bookingLocation');
            const stockSelect = form.querySelector('#bookingStock');
            const stockIdInput = form.querySelector('#bookingStockIdInput');
            const totalInput = form.querySelector('#bookingTotalInput');
            const bookingIdField = form.querySelector('#bookingId');

            const totalDisplay = modalEl.querySelector('#bookingTotalDisplay');
            const periodStartDisplay = modalEl.querySelector('#bookingPeriodStart');
            const periodEndDisplay = modalEl.querySelector('#bookingPeriodEnd');

            const openPaymentBtn = modalEl.querySelector('#openPayment');

            const paymentModalEl = document.getElementById('bookingPayment');
            const paymentBackBtn = document.getElementById('paymentBackToSummary');
            const paymentMethodInputs = paymentModalEl
                ? Array.from(paymentModalEl.querySelectorAll('input[name="booking_payment_method"]'))
                : [];

            const stripeModalEl = document.getElementById('bookingStripeModal');
            const stripeBackBtn = document.getElementById('stripeBackToPayment');
            const stripePayBtn = document.getElementById('bookingStripePayButton');
            const stripeCardErrorsEl = document.getElementById('booking-card-errors');

            const paymentLoaderEl = document.getElementById('bookingPaymentLoader');
            const paymentLoaderTextEl = document.getElementById('bookingPaymentLoaderText');

            const thankYouModalEl = document.getElementById('bookingThankYou');
            const thankYouReferenceEl = document.getElementById('tyReference');
            const thankYouPeriodEl = document.getElementById('tyPeriod');
            const thankYouMethodEl = document.getElementById('tyMethod');
            const thankYouAmountEl = document.getElementById('tyAmount');
            const thankYouContactEl = document.getElementById('tyCustomerContact');

            const summaryFields = {
                unit: modalEl.querySelector('[data-summary="unit"]'),
                rate: modalEl.querySelector('[data-summary="rate"]'),
                quantity: modalEl.querySelector('[data-summary="quantity"]'),
                extraDays: modalEl.querySelector('[data-summary="extra_days"]'),
                start: modalEl.querySelector('[data-summary="start"]'),
                end: modalEl.querySelector('[data-summary="end"]'),
                location: modalEl.querySelector('[data-summary="location"]'),
                stockQty: modalEl.querySelector('[data-summary="stock_qty"]'),
                total: modalEl.querySelector('[data-summary="total"]'),
                name: modalEl.querySelector('[data-summary="name"]'),
                email: modalEl.querySelector('[data-summary="email"]'),
                phone: modalEl.querySelector('[data-summary="phone"]'),
            };

            const fmtCurrency = new Intl.NumberFormat('en-ZA', {
                style: 'currency',
                currency: 'ZAR',
                minimumFractionDigits: 2,
            });

            const fmtDate = new Intl.DateTimeFormat('en-GB', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
            });

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            let currentStep = 0;
            let bookingCreationInFlight = false;
            let currentBookingReference = null;

            let stripeInstance = null;
            let stripeElements = null;
            let stripeCardNumber = null;
            let stripeCardExpiry = null;
            let stripeCardCvc = null;

            const stripePublicKey = "{{ $stripePublicKey }}";

            function setStep(index) {
                currentStep = index;
                steps.forEach((stepEl, idx) => {
                    stepEl.classList.toggle('d-none', idx !== index);
                });
                stepIndicators.forEach((indicator, idx) => {
                    indicator.classList.toggle('active', idx === index);
                    indicator.classList.toggle('completed', idx < index);
                });
            }

            function showStepError(index, message) {
                const target = stepErrors[index];
                if (!target) {
                    return;
                }
                if (!message) {
                    target.classList.add('d-none');
                    target.textContent = '';
                    return;
                }
                target.textContent = message;
                target.classList.remove('d-none');
            }

            function setSummaryError(message) {
                if (!summaryErrorEl) {
                    return;
                }
                if (!message) {
                    summaryErrorEl.classList.add('d-none');
                    summaryErrorEl.textContent = '';
                    return;
                }
                summaryErrorEl.textContent = message;
                summaryErrorEl.classList.remove('d-none');
            }

            function selectedUnitCard() {
                return unitCards.find((card) => card.classList.contains('active'));
            }

            function ensureSelectOptions(selectEl, count, formatter) {
                if (!selectEl) {
                    return;
                }
                const currentValue = parseInt(selectEl.value || '1', 10) || 1;
                selectEl.innerHTML = '';
                for (let i = 1; i <= count; i += 1) {
                    const option = document.createElement('option');
                    option.value = String(i);
                    option.textContent = formatter(i);
                    if (i === Math.min(currentValue, count)) {
                        option.selected = true;
                    }
                    selectEl.appendChild(option);
                }
            }

            function updateQuantityOptions(unit) {
                if (!quantitySelect) {
                    return;
                }

                let max = 30;
                let label = 'Number of days';
                if (unit === 'week') {
                    max = 12;
                    label = 'Number of weeks';
                } else if (unit === 'month') {
                    max = 12;
                    label = 'Number of months';
                }

                if (quantityLabel) {
                    quantityLabel.textContent = label;
                }

                ensureSelectOptions(quantitySelect, max, (i) => `${i}`);
            }

            function updateExtraDays(unit) {
                if (!extraDaysSelect || !extraDaysWrap) {
                    return;
                }

                if (unit === 'week') {
                    extraDaysWrap.classList.remove('d-none');
                    extraDaysSelect.innerHTML = '';
                    for (let i = 0; i <= 6; i += 1) {
                        const option = document.createElement('option');
                        option.value = String(i);
                        option.textContent = `${i} day${i === 1 ? '' : 's'}`;
                        if (i === 0) option.selected = true;
                        extraDaysSelect.appendChild(option);
                    }
                    return;
                }

                if (unit === 'month') {
                    extraDaysWrap.classList.remove('d-none');
                    extraDaysSelect.innerHTML = '';
                    for (let i = 0; i <= 30; i += 1) {
                        const option = document.createElement('option');
                        option.value = String(i);
                        option.textContent = `${i} day${i === 1 ? '' : 's'}`;
                        if (i === 0) option.selected = true;
                        extraDaysSelect.appendChild(option);
                    }
                    return;
                }

                extraDaysWrap.classList.add('d-none');
                extraDaysSelect.innerHTML = '<option value="0" selected>0</option>';
            }

            function updateStockSelect() {
                if (!locationSelect || !stockSelect || !stockIdInput) {
                    return;
                }

                if (locationSelect.disabled || locationSelect.options.length === 0) {
                    stockSelect.innerHTML = '<option value="0">Not available</option>';
                    stockSelect.disabled = true;
                    stockIdInput.value = '';
                    return;
                }

                const option = locationSelect.options[locationSelect.selectedIndex];
                const available = parseInt(option?.dataset.available || '0', 10) || 0;
                const stockId = option?.dataset.stockId || '';
                stockIdInput.value = stockId;

                stockSelect.innerHTML = '';

                if (available <= 0) {
                    const noOption = document.createElement('option');
                    noOption.value = '0';
                    noOption.textContent = 'Not available';
                    stockSelect.appendChild(noOption);
                    stockSelect.disabled = true;
                    return;
                }

                stockSelect.disabled = false;
                for (let i = 1; i <= Math.min(available, 20); i += 1) {
                    const newOption = document.createElement('option');
                    newOption.value = String(i);
                    newOption.textContent = `${i} unit${i === 1 ? '' : 's'}`;
                    if (i === 1) newOption.selected = true;
                    stockSelect.appendChild(newOption);
                }
            }

            function computeTotals() {
                const unitCard = selectedUnitCard();
                const unit = rentalUnitInput?.value || unitCard?.dataset.unit || '';
                const pricePerUnit = parseFloat(unitCard?.dataset.price || '0') || 0;
                const quantity = parseInt(quantitySelect?.value || '0', 10) || 0;
                let extraDays = parseInt(extraDaysSelect?.value || '0', 10) || 0;
                const unitsReserved = parseInt(stockSelect?.value || '1', 10) || 1;

                if (unit === 'day') {
                    extraDays = 0;
                }

                const startValue = startDateInput?.value ? `${startDateInput.value}T00:00:00` : '';
                const startDate = startValue ? new Date(startValue) : null;

                let totalPerUnit = pricePerUnit * quantity;
                if (unit === 'week') {
                    totalPerUnit += (pricePerUnit / 7) * extraDays;
                } else if (unit === 'month') {
                    totalPerUnit += (pricePerUnit / 30) * extraDays;
                } else {
                    totalPerUnit += pricePerUnit * extraDays;
                }
                const total = Math.round(totalPerUnit * unitsReserved * 100) / 100;

                let endDate = null;

                if (startDate) {
                    if (unit === 'day') {
                        endDate = new Date(startDate);
                        endDate.setDate(endDate.getDate() + quantity + extraDays - 1);
                    } else if (unit === 'week') {
                        endDate = new Date(startDate);
                        endDate.setDate(endDate.getDate() + (quantity * 7) + extraDays - 1);
                    } else if (unit === 'month') {
                        endDate = new Date(startDate);
                        endDate.setMonth(endDate.getMonth() + quantity);
                        endDate.setDate(endDate.getDate() - 1 + extraDays);
                    }
                }

                return {
                    total,
                    endDate,
                    unit,
                    quantity,
                    extraDays,
                    unitsReserved,
                };
            }

            function niceDate(ymd) {
                if (!ymd) return '-';
                const parsed = new Date(`${ymd}T00:00:00`);
                if (Number.isNaN(parsed.getTime())) return '-';
                return fmtDate.format(parsed);
            }

            function updateTotalsAndSummary() {
                const totals = computeTotals();
                if (totalDisplay) {
                    totalDisplay.textContent = fmtCurrency.format(totals.total);
                }
                if (totalInput) {
                    totalInput.value = totals.total.toFixed(2);
                }

                const startValue = startDateInput?.value || '';
                if (periodStartDisplay) {
                    periodStartDisplay.textContent = niceDate(startValue);
                }
                if (periodEndDisplay) {
                    const endYmd = totals.endDate
                        ? `${totals.endDate.getFullYear()}-${String(totals.endDate.getMonth() + 1).padStart(2, '0')}-${String(totals.endDate.getDate()).padStart(2, '0')}`
                        : '';
                    periodEndDisplay.textContent = niceDate(endYmd);
                }

                updateSummary();
            }

            function updateSummary() {
                const unitCard = selectedUnitCard();
                const totals = computeTotals();

                if (summaryFields.unit) {
                    summaryFields.unit.textContent = unitCard
                        ? unitCard.querySelector('.booking-unit-title')?.textContent || '-'
                        : '-';
                }
                if (summaryFields.rate) {
                    summaryFields.rate.textContent = unitCard
                        ? `${fmtCurrency.format(parseFloat(unitCard.dataset.price || '0'))}${unitCard.dataset.suffix || ''}`
                        : '-';
                }
                if (summaryFields.quantity) {
                    const qty = parseInt(quantitySelect?.value || '0', 10) || 0;
                    const unit = totals.unit || 'unit';
                    summaryFields.quantity.textContent = qty
                        ? `${qty} ${unit}${qty === 1 ? '' : 's'}`
                        : '-';
                }
                if (summaryFields.extraDays) {
                    const extra = totals.extraDays || 0;
                    summaryFields.extraDays.textContent = `${extra} day${extra === 1 ? '' : 's'}`;
                }
                if (summaryFields.start) {
                    summaryFields.start.textContent = niceDate(startDateInput?.value || '');
                }
                if (summaryFields.end) {
                    summaryFields.end.textContent = totals.endDate
                        ? fmtDate.format(totals.endDate)
                        : '-';
                }
                if (summaryFields.location && locationSelect) {
                    const option = locationSelect.options[locationSelect.selectedIndex];
                    summaryFields.location.textContent = option
                        ? option.dataset.locationName || option.textContent
                        : '-';
                }
                if (summaryFields.stockQty) {
                    const unitsReserved = totals.unitsReserved || 1;
                    summaryFields.stockQty.textContent = `${unitsReserved} unit${unitsReserved === 1 ? '' : 's'}`;
                }
                if (summaryFields.total) {
                    summaryFields.total.textContent = fmtCurrency.format(totals.total);
                }
                if (summaryFields.name) {
                    summaryFields.name.textContent = form.elements.name?.value?.trim() || '-';
                }
                if (summaryFields.email) {
                    summaryFields.email.textContent = form.elements.email?.value?.trim() || '-';
                }
                if (summaryFields.phone) {
                    summaryFields.phone.textContent = form.elements.phone?.value?.trim() || '-';
                }
            }
            function validateStep1() {
                showStepError(0, '');
                setSummaryError('');

                const unitCard = selectedUnitCard();
                if (!unitCard) {
                    showStepError(0, 'Please select a rental duration.');
                    return false;
                }

                if (!startDateInput?.value) {
                    showStepError(0, 'Please choose a start date.');
                    return false;
                }

                if (!locationSelect || locationSelect.options.length === 0 || locationSelect.disabled) {
                    showStepError(0, 'No locations are available for this item at the moment.');
                    return false;
                }

                const option = locationSelect.options[locationSelect.selectedIndex];
                const available = parseInt(option?.dataset.available || '0', 10) || 0;

                if (available <= 0) {
                    showStepError(0, 'Selected location currently has no stock available.');
                    return false;
                }

                return true;
            }

            function validateStep2() {
                showStepError(1, '');
                setSummaryError('');
                const requiredFields = ['name', 'email', 'phone'];
                for (const fieldName of requiredFields) {
                    const field = form.elements[fieldName];
                    if (!field || !field.value.trim()) {
                        showStepError(1, 'Please fill in all required contact fields.');
                        return false;
                    }
                }
                return true;
            }

            function resetPaymentChoices() {
                paymentMethodInputs.forEach((input) => {
                    input.checked = false;
                });
            }

            function resetFormState() {
                showStepError(0, '');
                showStepError(1, '');
                setSummaryError('');

                form.reset();

                const firstCard = unitCards[0];
                unitCards.forEach((card) => card.classList.remove('active'));
                if (firstCard) {
                    firstCard.classList.add('active');
                    rentalUnitInput.value = firstCard.dataset.unit || '';
                }

                updateQuantityOptions(rentalUnitInput.value || firstCard?.dataset.unit || 'day');
                updateExtraDays(rentalUnitInput.value || firstCard?.dataset.unit || 'day');

                if (startDateInput && startDateInput.getAttribute('min')) {
                    startDateInput.value = startDateInput.getAttribute('min');
                }

                if (locationSelect && locationSelect.options.length) {
                    locationSelect.selectedIndex = 0;
                }

                updateStockSelect();
                updateTotalsAndSummary();
                setStep(0);
                resetPaymentChoices();

                if (openPaymentBtn) {
                    openPaymentBtn.disabled = !!(locationSelect && locationSelect.disabled);
                    openPaymentBtn.textContent = 'Continue to Payment';
                }

                if (bookingIdField) {
                    bookingIdField.value = '';
                }

                currentBookingReference = null;
                bookingCreationInFlight = false;

                if (stripePayBtn) {
                    stripePayBtn.dataset.amount = '0';
                    stripePayBtn.disabled = !stripeInstance;
                    stripePayBtn.textContent = 'Pay with Stripe';
                }
            }

            unitCards.forEach((card) => {
                card.addEventListener('click', () => {
                    unitCards.forEach((c) => c.classList.remove('active'));
                    card.classList.add('active');
                    rentalUnitInput.value = card.dataset.unit || '';
                    updateQuantityOptions(card.dataset.unit || 'day');
                    updateExtraDays(card.dataset.unit || 'day');
                    updateTotalsAndSummary();
                });
            });

            if (quantitySelect) {
                quantitySelect.addEventListener('change', updateTotalsAndSummary);
            }

            if (extraDaysSelect) {
                extraDaysSelect.addEventListener('change', updateTotalsAndSummary);
            }

            if (startDateInput) {
                startDateInput.addEventListener('change', updateTotalsAndSummary);
            }

            if (locationSelect) {
                locationSelect.addEventListener('change', () => {
                    updateStockSelect();
                    updateTotalsAndSummary();
                });
            }

            if (stockSelect) {
                stockSelect.addEventListener('change', updateSummary);
            }

            ['name', 'email', 'phone', 'country'].forEach((fieldName) => {
                const field = form.elements[fieldName];
                if (field) {
                    field.addEventListener('input', updateSummary);
                }
            });

            const step1Next = modalEl.querySelector('#bookingStep1Next');
            if (step1Next) {
                step1Next.addEventListener('click', () => {
                    if (!validateStep1()) {
                        return;
                    }
                    updateSummary();
                    setStep(1);
                });
            }

            const step2Back = modalEl.querySelector('#bookingStep2Back');
            if (step2Back) {
                step2Back.addEventListener('click', () => setStep(0));
            }

            const step2Next = modalEl.querySelector('#bookingStep2Next');
            if (step2Next) {
                step2Next.addEventListener('click', () => {
                    if (!validateStep2()) {
                        return;
                    }
                    updateSummary();
                    setStep(2);
                });
            }

            const step3Back = modalEl.querySelector('#bookingStep3Back');
            if (step3Back) {
                step3Back.addEventListener('click', () => setStep(1));
            }

            function setOpenPaymentLoading(isLoading) {
                if (!openPaymentBtn) {
                    return;
                }
                if (isLoading) {
                    openPaymentBtn.disabled = true;
                    openPaymentBtn.textContent = 'Preparing booking...';
                } else {
                    openPaymentBtn.disabled = false;
                    openPaymentBtn.textContent = 'Continue to Payment';
                }
            }

            async function ensureBookingCreated() {
                if (!bookingIdField) {
                    return false;
                }
                if (bookingIdField.value) {
                    return true;
                }

                if (bookingCreationInFlight) {
                    return false;
                }

                bookingCreationInFlight = true;
                setOpenPaymentLoading(true);

                const formData = new FormData(form);
                if (extraDaysWrap?.classList.contains('d-none')) {
                    formData.set('extra_days', '0');
                }
                if (!stockSelect || stockSelect.disabled) {
                    formData.delete('stock_quantity');
                }
                formData.delete('booking_id');

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                    });

                    const text = await response.text();
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch {
                        data = {
                            success: false,
                            message: text,
                        };
                    }

                    if (!response.ok || !data?.success) {
                        setSummaryError(data?.message || 'Failed to create booking. Please try again.');
                        return false;
                    }

                    bookingIdField.value = data.booking_id || data.id || '';
                    currentBookingReference = data.reference || null;

                    if (stripePayBtn) {
                        stripePayBtn.dataset.amount = totalInput?.value || '0';
                    }

                    return Boolean(bookingIdField.value);
                } catch (error) {
                    console.error(error);
                    setSummaryError('Could not create the booking. Please try again.');
                    return false;
                } finally {
                    bookingCreationInFlight = false;
                    setOpenPaymentLoading(false);
                }
            }

            function showPaymentLoader(message = 'Processing payment...') {
                if (!paymentLoaderEl) {
                    return;
                }
                if (paymentLoaderTextEl) {
                    paymentLoaderTextEl.textContent = message;
                }
                paymentLoaderEl.classList.remove('d-none');
                paymentLoaderEl.setAttribute('aria-hidden', 'false');
            }

            function hidePaymentLoader() {
                if (!paymentLoaderEl) {
                    return;
                }
                paymentLoaderEl.classList.add('d-none');
                paymentLoaderEl.setAttribute('aria-hidden', 'true');
            }

            function populateThankYouModal(methodLabel) {
                if (thankYouReferenceEl) {
                    const reference = currentBookingReference
                        || (bookingIdField?.value ? `#${bookingIdField.value}` : '-');
                    thankYouReferenceEl.textContent = reference;
                }

                if (thankYouPeriodEl) {
                    const start = summaryFields.start?.textContent || '-';
                    const end = summaryFields.end?.textContent || '-';
                    thankYouPeriodEl.textContent = `${start} ? ${end}`;
                }

                if (thankYouMethodEl) {
                    thankYouMethodEl.textContent = methodLabel || '-';
                }

                if (thankYouAmountEl) {
                    thankYouAmountEl.textContent = summaryFields.total?.textContent || fmtCurrency.format(0);
                }

                if (thankYouContactEl) {
                    const contactParts = [];
                    if (form.elements.email?.value) {
                        contactParts.push(form.elements.email.value.trim());
                    }
                    if (form.elements.phone?.value) {
                        contactParts.push(form.elements.phone.value.trim());
                    }
                    thankYouContactEl.textContent = contactParts.length
                        ? `We will reach out at ${contactParts.join(' � ')}`
                        : 'We will email your confirmation shortly.';
                }
            }

            function showThankYou(methodLabel) {
                populateThankYouModal(methodLabel);
                if (thankYouModalEl) {
                    bootstrap.Modal.getOrCreateInstance(thankYouModalEl).show();
                }
            }

            if (openPaymentBtn) {
                openPaymentBtn.addEventListener('click', async () => {
                    setSummaryError('');
                    if (!validateStep1()) {
                        setStep(0);
                        return;
                    }
                    if (!validateStep2()) {
                        setStep(1);
                        return;
                    }

                    updateSummary();

                    const created = await ensureBookingCreated();
                    if (!created) {
                        return;
                    }

                    if (paymentModalEl) {
                        resetPaymentChoices();
                        bootstrap.Modal.getOrCreateInstance(paymentModalEl).show();
                    }
                });
            }

            if (paymentBackBtn) {
                paymentBackBtn.addEventListener('click', () => {
                    if (paymentModalEl) {
                        bootstrap.Modal.getOrCreateInstance(paymentModalEl).hide();
                    }
                });
            }
            async function startPayfastFlow() {
                if (!bookingIdField?.value) {
                    await Swal.fire({
                        icon: 'error',
                        title: 'Booking missing',
                        text: 'Please create the booking first.',
                    });
                    if (paymentModalEl) {
                        bootstrap.Modal.getOrCreateInstance(paymentModalEl).show();
                    }
                    return;
                }

                showPaymentLoader('Redirecting to PayFast...');
                try {
                    const res = await fetch(
                        `/payfast/booking/init/${encodeURIComponent(bookingIdField.value)}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({
                                booking_id: bookingIdField.value,
                            }),
                        });

                    const data = await res.json();
                    if (!res.ok || !data?.success) {
                        throw new Error(data?.message || 'Failed to prepare PayFast checkout.');
                    }

                    const formEl = document.createElement('form');
                    formEl.method = 'POST';
                    formEl.action = data.action;
                    formEl.style.display = 'none';

                    Object.entries(data.fields || {}).forEach(([key, value]) => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = key;
                        input.value = value;
                        formEl.appendChild(input);
                    });

                    document.body.appendChild(formEl);
                    formEl.submit();
                } catch (error) {
                    console.error(error);
                    await Swal.fire({
                        icon: 'error',
                        title: 'PayFast error',
                        text: error.message || 'Could not redirect to PayFast.',
                    });
                    if (paymentModalEl) {
                        bootstrap.Modal.getOrCreateInstance(paymentModalEl).show();
                    }
                } finally {
                    hidePaymentLoader();
                }
            }

            paymentMethodInputs.forEach((input) => {
                input.addEventListener('change', async () => {
                    if (!input.checked) {
                        return;
                    }

                    if (paymentModalEl) {
                        bootstrap.Modal.getOrCreateInstance(paymentModalEl).hide();
                    }

                    if (input.value === 'stripe') {
                        if (!stripeInstance) {
                            await Swal.fire({
                                icon: 'error',
                                title: 'Stripe unavailable',
                                text: 'Card payments are not available at the moment.',
                            });
                            if (paymentModalEl) {
                                bootstrap.Modal.getOrCreateInstance(paymentModalEl).show();
                            }
                            resetPaymentChoices();
                            return;
                        }

                        if (stripePayBtn) {
                            stripePayBtn.dataset.amount = totalInput?.value || '0';
                            stripePayBtn.textContent = `Pay ${fmtCurrency.format(parseFloat(stripePayBtn.dataset.amount || '0'))}`;
                        }

                        bootstrap.Modal.getOrCreateInstance(stripeModalEl).show();
                        return;
                    }

                    if (input.value === 'payfast') {
                        await startPayfastFlow();
                        resetPaymentChoices();
                    }
                });
            });

            if (stripeBackBtn) {
                stripeBackBtn.addEventListener('click', () => {
                    if (stripeModalEl) {
                        bootstrap.Modal.getOrCreateInstance(stripeModalEl).hide();
                    }
                    if (paymentModalEl) {
                        bootstrap.Modal.getOrCreateInstance(paymentModalEl).show();
                    }
                    resetPaymentChoices();
                });
            }

            if (typeof Stripe !== 'undefined' && stripePublicKey) {
                stripeInstance = Stripe(stripePublicKey);
                stripeElements = stripeInstance.elements();
                const stripeStyle = {
                    base: {
                        color: '#212529',
                        fontFamily: 'system-ui, -apple-system, "Segoe UI", sans-serif',
                        fontSize: '16px',
                        '::placeholder': { color: '#adb5bd' },
                    },
                    invalid: {
                        color: '#dc3545',
                    },
                };

                stripeCardNumber = stripeElements.create('cardNumber', { style: stripeStyle });
                stripeCardExpiry = stripeElements.create('cardExpiry', { style: stripeStyle });
                stripeCardCvc = stripeElements.create('cardCvc', { style: stripeStyle });

                const cardNumberMount = document.getElementById('booking-card-number');
                const cardExpiryMount = document.getElementById('booking-card-expiry');
                const cardCvcMount = document.getElementById('booking-card-cvc');

                if (cardNumberMount) stripeCardNumber.mount(cardNumberMount);
                if (cardExpiryMount) stripeCardExpiry.mount(cardExpiryMount);
                if (cardCvcMount) stripeCardCvc.mount(cardCvcMount);
            } else if (stripePublicKey) {
                console.warn('Stripe.js not loaded or publishable key missing.');
            }

            if (stripePayBtn && stripeInstance) {
                stripePayBtn.addEventListener('click', async function() {
                    if (!bookingIdField?.value) {
                        await Swal.fire({
                            icon: 'error',
                            title: 'Booking missing',
                            text: 'Please create the booking first.',
                        });
                        return;
                    }

                    if (!stripeCardNumber || !stripeCardExpiry || !stripeCardCvc) {
                        await Swal.fire({
                            icon: 'error',
                            title: 'Stripe unavailable',
                            text: 'Payment form is not ready yet.',
                        });
                        return;
                    }

                    if (stripeCardErrorsEl) {
                        stripeCardErrorsEl.textContent = '';
                    }

                    const originalText = this.textContent;
                    this.disabled = true;
                    this.textContent = 'Processing...';
                    showPaymentLoader();

                    try {
                        const { paymentMethod, error } = await stripeInstance.createPaymentMethod({
                            type: 'card',
                            card: stripeCardNumber,
                            billing_details: {
                                name: form?.name?.value || '',
                                email: form?.email?.value || '',
                            },
                        });

                        if (error) {
                            if (stripeCardErrorsEl) {
                                stripeCardErrorsEl.textContent = error.message || 'Payment method error.';
                            }
                            return;
                        }

                        const res = await fetch(
                            `/bookings/${encodeURIComponent(bookingIdField.value)}/pay-with-stripe`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                },
                                body: JSON.stringify({
                                    payment_method_id: paymentMethod.id,
                                    amount: parseFloat(this.dataset.amount || '0'),
                                }),
                            });

                        const text = await res.text();
                        let data;
                        try {
                            data = JSON.parse(text);
                        } catch {
                            data = {
                                success: false,
                                message: text,
                            };
                        }

                        if (!res.ok || !data) {
                            await Swal.fire({
                                icon: 'error',
                                title: 'Payment failed',
                                text: data?.message || 'Server error while processing payment.',
                            });
                            return;
                        }

                        if (data.success) {
                            bootstrap.Modal.getInstance(stripeModalEl)?.hide();
                            showThankYou('Stripe');
                            return;
                        }

                        if (data.requires_action && data.payment_intent_client_secret) {
                            const result = await stripeInstance.confirmCardPayment(
                                data.payment_intent_client_secret);
                            if (result.error) {
                                await Swal.fire({
                                    icon: 'error',
                                    title: 'Authentication failed',
                                    text: result.error.message || 'Unable to confirm your card.',
                                });
                            } else {
                                bootstrap.Modal.getInstance(stripeModalEl)?.hide();
                                showThankYou('Stripe');
                            }
                        } else {
                            await Swal.fire({
                                icon: 'error',
                                title: 'Payment failed',
                                text: data.message || 'Unable to charge your card.',
                            });
                        }
                    } catch (error) {
                        console.error(error);
                        await Swal.fire({
                            icon: 'error',
                            title: 'Payment error',
                            text: error.message || 'An unexpected error occurred.',
                        });
                    } finally {
                        hidePaymentLoader();
                        this.disabled = false;
                        this.textContent = originalText;
                    }
                });
            } else if (stripePayBtn) {
                stripePayBtn.disabled = true;
            }

            modalEl.addEventListener('shown.bs.modal', resetFormState);
            modalEl.addEventListener('hidden.bs.modal', resetFormState);

            updateStockSelect();
            updateTotalsAndSummary();
        })();
    </script>
@else
    {{-- Booking modal unavailable because pricing or category data is missing --}}
@endif



