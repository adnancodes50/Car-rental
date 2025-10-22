{{-- resources/views/show.blade.php --}}
@extends('layouts.frontend')

@section('title', $vehicle->name)

@section('content')
@include('frontend.partials.navbar')
    <div class="container py-4 py-lg-5 mt-5">

        <!-- Back link -->
        <a href="{{ url('/') }}" class="text-muted mb-4 d-inline-flex align-items-center">
            <i class="bi bi-arrow-left me-2"></i> Back to vehicles
        </a>

        <div class="row g-4 g-lg-5">
            <!-- Gallery -->
            <div class="col-12 col-md-6">
                <div class="card border-0 shadow-sm mb-3">
                    <img id="mainImage" src="{{ $vehicle->mainImage() }}" class="card-img-top rounded img-fluid"
                        alt="{{ $vehicle->name }}" style="object-fit: cover; max-height: 380px;">
                </div>

                @if ($vehicle->images->count() > 0)
                    <div class="d-flex flex-wrap gap-2">
                        <img src="{{ $vehicle->mainImage() }}" class="img-thumbnail"
                            style="width: 80px; height: 60px; object-fit: cover; cursor: pointer;"
                            onclick="document.getElementById('mainImage').src=this.src">
                        @foreach ($vehicle->images as $image)
                            <img src="{{ $image->url }}" class="img-thumbnail"
                                style="width: 80px; height: 60px; object-fit: cover; cursor: pointer;"
                                onclick="document.getElementById('mainImage').src=this.src">
                        @endforeach
                    </div>
                @endif
            </div>



            {{-- Extra Features --}}


            <!-- Vehicle details -->
            <div class="col-12 col-md-6">
                <div class="card border-0 shadow-sm p-3 p-lg-4 rounded-4">
                    <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                        <div>
                            <h2 class="h4 h-md-2">{{ $vehicle->name }}</h2>
                            <p class="text-muted small mb-2">{{ $vehicle->year }} {{ $vehicle->model }}</p>
                        </div>
                        <span class="badge rounded-pill bg-success-subtle text-success">
                            {{ ucfirst($vehicle->status ?? 'Available') }}
                        </span>
                    </div>

                    <p class="text-muted small mb-3">{{ $vehicle->description }}</p>

                    <!-- Pricing -->
                    <div class="row text-center g-2 mb-4">
                        @if ($vehicle->rental_price_day)
                            <div class="col-6 col-lg-4">
                                <div class="border rounded p-2">
                                    <div class="fw-semibold">R{{ number_format($vehicle->rental_price_day) }}/day</div>
                                    <small class="text-muted">Daily</small>
                                </div>
                            </div>
                        @endif
                        @if ($vehicle->rental_price_week)
                            <div class="col-6 col-lg-4">
                                <div class="border rounded p-2">
                                    <div class="fw-semibold">R{{ number_format($vehicle->rental_price_week) }}/week</div>
                                    <small class="text-muted">Weekly</small>
                                </div>
                            </div>
                        @endif
                        @if ($vehicle->rental_price_month)
                            <div class="col-6 col-lg-4">
                                <div class="border rounded p-2">
                                    <div class="fw-semibold">R{{ number_format($vehicle->rental_price_month) }}/month</div>
                                    <small class="text-muted">Monthly</small>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="d-flex flex-column flex-md-row gap-3 mb-4">
                        <button type="button" class="btn text-black w-100" style="padding: 15px; background-color:#CF9B4D"
                            data-bs-toggle="modal" data-bs-target="#multiStepBookingModal">
                            <i class="bi bi-calendar-check me-2"></i> Book this {{ $vehicle->name }}
                        </button>
                        @if ($vehicle->purchase_price)
                            <a href="#" class="btn btn-dark w-100 d-flex align-items-center justify-content-center"
                                data-bs-toggle="modal" data-bs-target="#purchaseModal">
                                Purchase (R{{ number_format($vehicle->purchase_price) }})
                            </a>
                        @endif
                    </div>

                    <!-- Specs -->
                    <div class="row g-3 text-muted small">
                        @if ($vehicle->engine)
                            <div class="col-6"><i class="bi bi-gear-fill me-2"></i><strong>Engine:</strong>
                                {{ $vehicle->engine }}</div>
                        @endif
                        @if ($vehicle->transmission)
                            <div class="col-6"><i class="bi bi-gear-wide-connected me-2"></i><strong>Transmission:</strong>
                                {{ $vehicle->transmission }}</div>
                        @endif
                        @if ($vehicle->seats)
                            <div class="col-6"><i class="bi bi-people-fill me-2"></i><strong>Seating:</strong>
                                {{ $vehicle->seats }}</div>
                        @endif
                        @if ($vehicle->fuel_type)
                            <div class="col-6"><i class="bi bi-fuel-pump-fill me-2"></i><strong>Fuel:</strong>
                                {{ $vehicle->fuel_type }}</div>
                        @endif
                        @if ($vehicle->location)
                            <div class="col-6"><i class="bi bi-geo-alt-fill me-2"></i><strong>Location:</strong>
                                {{ $vehicle->location }}</div>
                        @endif
                        @if ($vehicle->mileage)
                            <div class="col-6"><i class="bi bi-speedometer me-2"></i><strong>Mileage:</strong>
                                {{ number_format($vehicle->mileage) }} km</div>
                        @endif
                    </div>

                    <!-- Features -->
                    <div class="mt-4">
                        <h5 class="fw-bold">Features & Equipment</h5>
                        <div class="row row-cols-2 row-cols-md-2 row-cols-lg-3 g-2 small">
                            @if (!empty($vehicle->features) && is_array($vehicle->features))
                                @foreach ($vehicle->features as $feature)
                                    @if ($feature)
                                        <div class="col d-flex align-items-center">
                                            <i class="bi bi-check-circle-fill me-2 text-secondary"></i>
                                            {{ ucfirst($feature) }}
                                        </div>
                                    @endif
                                @endforeach
                            @else
                                <div class="col-12 text-muted">No features available</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- BOOKING MODALS (unchanged) --}}
        @include('models.booking')

        {{-- PURCHASE MODALS (unchanged) --}}
        @include('models.purchase')
    </div>
@endsection


@push('css')

    <style>
        /* Equal height, centered option cards */
        #bookingPayment .pay-option {
            min-height: 600px;
            /* increase card height */
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: .5rem;
        }

        #bookingPayment .pay-option .icon-wrap {
            width: 56px;
            /* icon size */
            height: 56px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            /* subtle bg behind icon */
            border: 1px solid #e9ecef;
        }

        #bookingPayment .pay-option img,
        #bookingPayment .pay-option i {
            width: 32px;
            /* inner icon size */
            height: 32px;
            object-fit: contain;
        }

        /* Nice focus/selected state */
        #bookingPayment .btn-check:checked+.card {
            border-color: #0d6efd;
            box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .15);
        }

        #bookingPayment .card {
            transition: box-shadow .15s ease, transform .15s ease, border-color .15s ease;
        }

        #bookingPayment .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .08);
        }
    </style>


@endpush

@push('scripts')
    <script src="https://js.stripe.com/v3/"></script>
    <!-- BOOKING CORE + PAYMENT (now pays vehicle + add-ons) -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            /* ------------------------ Date helpers ------------------------ */
            function parseYMD(ymd) {
                const [y, m, d] = ymd.split('-').map(Number);
                return new Date(y, m - 1, d, 0, 0, 0, 0);
            }

            function toMidnight(val) {
                if (typeof val === 'string') return parseYMD(val);
                return new Date(val.getFullYear(), val.getMonth(), val.getDate(), 0, 0, 0, 0);
            }

            function addDays(d, n) {
                return new Date(d.getFullYear(), d.getMonth(), d.getDate() + n, 0, 0, 0, 0);
            }

            function fmt(d) {
                return d.toLocaleDateString("en-GB", {
                    day: "2-digit",
                    month: "short",
                    year: "numeric"
                });
            }

            function addMonthsInclusive(start, months, extraDays) {
                const exclusive = new Date(start.getFullYear(), start.getMonth() + months, start.getDate(), 0, 0, 0,
                    0);
                return toMidnight(addDays(exclusive, -1 + (extraDays || 0)));
            }

            /* ----------------- Disable & check booked ranges ---------------- */
            const rawRanges = @json($bookedRanges ?? []);
            const bookedRanges = rawRanges.map(r => ({
                from: toMidnight(r.from),
                to: toMidnight(r.to)
            }));

            function hasOverlap(a1, a2) {
                return bookedRanges.some(({
                    from,
                    to
                }) => a1 <= to && a2 >= from);
            }

            /* ------------------------ Grab elements ------------------------ */
            const optionCards = document.querySelectorAll(".option-card");
            const dateSection = document.getElementById("dateSection");
            const quantitySection = document.getElementById("quantitySection");
            const quantitySelect = document.getElementById("rentalQuantity");
            const rentalStartDate = document.getElementById("rentalStartDate");
            const totalPriceDiv = document.getElementById("totalPrice");
            const rentalPeriodDiv = document.getElementById("rentalPeriod");

            const inputRentalUnit = document.getElementById("inputRentalUnit");
            const inputRentalQuantity = document.getElementById("inputRentalQuantity");
            const inputRentalStartDate = document.getElementById("inputRentalStartDate");
            const inputExtraDays = document.getElementById("inputExtraDays") || (() => {
                const i = document.createElement('input');
                i.type = 'hidden';
                i.name = 'extra_days';
                i.id = 'inputExtraDays';
                i.value = 0;
                document.getElementById("bookingForm").appendChild(i);
                return i;
            })();
            const inputTotalPrice = document.getElementById("inputTotalPrice");
            const bookingIdField = document.getElementById("bookingId");
            const openPaymentBtn = document.getElementById("openPayment");
            let bookingCreationInFlight = false;

            // Summary
            const summaryType = document.getElementById("summaryType");
            const summaryPeriod = document.getElementById("summaryPeriod");
            const summaryPrice = document.getElementById("summaryPrice");
            const summaryCustomerName = document.getElementById("summaryCustomerName");
            const summaryCustomerEmail = document.getElementById("summaryCustomerEmail");
            const summaryCustomerPhone = document.getElementById("summaryCustomerPhone");
            const summaryCustomerCountry = document.getElementById("summaryCustomerCountry");

            /* ----------------------- Flatpickr init ------------------------ */
            const fp = flatpickr("#rentalStartDate", {
                dateFormat: "Y-m-d",
                minDate: "today",
                disable: [(date) => {
                    const d = toMidnight(date);
                    return bookedRanges.some(({
                        from,
                        to
                    }) => d >= from && d <= to);
                }],
                onChange: () => {
                    revealAfterDate();
                    calculateTotal();
                }
            });

            /* ---------------------- State & helpers ------------------------ */
            let selectedType = ""; // 'day' | 'week' | 'month'
            let selectedPrice = 0;
            let totalPrice = 0;
            let rentalPeriodText = "";

            function resetVisibility() {
                dateSection.classList.add("d-none");
                quantitySection.classList.add("d-none");
                totalPriceDiv.classList.add("d-none");
                rentalPeriodDiv.classList.add("d-none");
            }
            resetVisibility();

            function revealDateOnly() {
                dateSection.classList.remove("d-none");
                quantitySection.classList.add("d-none");
                totalPriceDiv.classList.add("d-none");
                rentalPeriodDiv.classList.add("d-none");
            }

            function revealAfterDate() {
                quantitySection.classList.remove("d-none");
                totalPriceDiv.classList.remove("d-none");
                rentalPeriodDiv.classList.remove("d-none");
            }

            function buildQuantityAndExtras() {
                quantitySection.innerHTML = "";
                const wrapper = document.createElement("div");
                wrapper.className = "d-flex align-items-end gap-2 flex-wrap";

                const qCol = document.createElement("div");
                qCol.style.flex = "1 1 220px";
                qCol.innerHTML =
                    `<label class="form-label mb-1">${selectedType==='day'?'Number of Days':selectedType==='week'?'Number of Weeks':'Number of Months'}</label>`;
                quantitySelect.innerHTML = "";
                const maxQty = (selectedType === "day") ? 6 : 4;
                for (let i = 1; i <= maxQty; i++) {
                    const opt = document.createElement("option");
                    opt.value = i;
                    opt.textContent = i + " " + (selectedType === 'day' ? 'day' : selectedType === 'week' ? 'week' :
                        'month') + (i > 1 ? 's' : '');
                    quantitySelect.appendChild(opt);
                }
                qCol.appendChild(quantitySelect);
                wrapper.appendChild(qCol);

                if (selectedType !== "day") {
                    const extraCol = document.createElement("div");
                    extraCol.style.flex = "1 1 220px";
                    extraCol.innerHTML = `<label class="form-label mb-1">Extra Days</label>`;
                    const extraSelect = document.createElement("select");
                    extraSelect.id = "extraDays";
                    extraSelect.className = "form-select";
                    for (let i = 0; i <= 6; i++) {
                        const opt = document.createElement("option");
                        opt.value = i;
                        opt.textContent = (i === 0 ? "0 days" : (i === 1 ? "1 day" : `${i} days`));
                        extraSelect.appendChild(opt);
                    }
                    extraSelect.addEventListener("change", calculateTotal);
                    extraCol.appendChild(extraSelect);
                    wrapper.appendChild(extraCol);
                }

                quantitySection.appendChild(wrapper);
            }

            function calculateTotal() {
                const startStr = rentalStartDate.value;
                if (!startStr || !selectedType) {
                    totalPriceDiv.classList.add("d-none");
                    rentalPeriodDiv.classList.add("d-none");
                    return;
                }

                const qty = parseInt(quantitySelect.value || "1", 10);
                const extraSel = document.getElementById("extraDays");
                const extraDays = extraSel ? parseInt(extraSel.value || "0", 10) : 0;
                inputExtraDays.value = extraDays;

                const start = toMidnight(startStr);
                let end;
                if (selectedType === "day") end = addDays(start, qty + extraDays - 1);
                else if (selectedType === "week") end = addDays(start, qty * 7 + extraDays - 1);
                else end = addMonthsInclusive(start, qty, extraDays);

                // overlap guard
                if (hasOverlap(start, end)) {
                    const clashes = bookedRanges.filter(({
                            from,
                            to
                        }) => start <= to && end >= from)
                        .map(({
                            from,
                            to
                        }) => `${fmt(from)} → ${fmt(to)}`).join("<br>");
                    Swal.fire({
                        icon: 'error',
                        title: 'Booking Conflict',
                        html: `<p>Unfortunately, your selected dates overlap with an existing reservation.</p><p><strong>Unavailable Dates:</strong></p><div style="text-align:center; font-size:14px;">${clashes}</div><p>Please adjust your booking period and try again.</p>`
                    });
                    rentalStartDate.value = "";
                    revealDateOnly();
                    return;
                }

                const base = qty * selectedPrice;
                let extraPrice = 0;
                if (extraDays > 0) {
                    if (selectedType === "day") extraPrice = extraDays * selectedPrice;
                    if (selectedType === "week") extraPrice = extraDays * (selectedPrice / 7);
                    if (selectedType === "month") extraPrice = extraDays * (selectedPrice / 30);
                }
                totalPrice = Math.round(base + extraPrice);

                // UI
                rentalPeriodText = `
      <div class="d-flex justify-content-between">
        <div class="text-muted small">Start Date<br>${fmt(start)}</div>
        <div class="text-muted small">End Date<br>${fmt(end)}</div>
      </div>`;
                rentalPeriodDiv.innerHTML = rentalPeriodText;

                let costHtml =
                    `<div style="font-size:14px;">${selectedType==='day'?'Days':selectedType==='week'?'Weeks':'Months'}: ${qty} × R${selectedPrice.toLocaleString()}</div>`;
                if (extraDays > 0) {
                    const perExtra = (selectedType === 'week') ? (selectedPrice / 7) : (selectedType === 'month' ? (
                        selectedPrice / 30) : selectedPrice);
                    costHtml +=
                        `<div style="font-size:14px;">Extra Days: ${extraDays} × R${perExtra.toFixed(2)}</div>`;
                }
                costHtml +=
                    `<div class="mt-2" style="font-size:14px;">Total Cost: R${totalPrice.toLocaleString()}</div>`;
                totalPriceDiv.innerHTML = costHtml;

                // hidden inputs
                inputRentalUnit.value = selectedType;
                inputRentalQuantity.value = qty;
                inputRentalStartDate.value = startStr;
                inputTotalPrice.value = totalPrice;
            }

            /* ------------------- Rental type selection --------------------- */
            optionCards.forEach(card => {
                card.addEventListener("click", () => {
                    optionCards.forEach(c => c.classList.remove("border-warning",
                        "bg-warning-subtle"));
                    card.classList.add("border-warning", "bg-warning-subtle");
                    selectedType = card.getAttribute("data-type");
                    selectedPrice = parseFloat(card.getAttribute("data-price") || "0");
                    rentalStartDate.value = "";
                    revealDateOnly();
                    buildQuantityAndExtras();
                    setTimeout(() => fp.open(), 0);
                });
            });
            quantitySelect.addEventListener("change", calculateTotal);

            /* -------------------- Summary step button ---------------------- */
            document.getElementById("continueFromStep1").addEventListener("click", function() {
                if (!selectedType) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Choose a rental type',
                        text: 'Please select Day, Week or Month.'
                    });
                    return;
                }
                if (!rentalStartDate.value) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Select a start date',
                        text: 'Please pick a start date from the calendar.'
                    });
                    return;
                }
                bootstrap.Modal.getInstance(document.getElementById("multiStepBookingModal"))?.hide();
                new bootstrap.Modal(document.getElementById("addonsStep")).show();
            });

            document.getElementById("goToSummary").addEventListener("click", function() {
                const form = document.getElementById("bookingForm");
                const name = form.querySelector("input[name='name']").value;
                const email = form.querySelector("input[name='email']").value;
                const phone = form.querySelector("input[name='phone']").value;
                const country = form.querySelector("select[name='country']").value;

                if (!name || !email || !phone || !country) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Missing Information',
                        text: 'Please fill in all required customer details before continuing.'
                    });
                    return;
                }

                summaryType.textContent = selectedType;
                summaryPeriod.innerHTML = rentalPeriodText;
                summaryPrice.textContent = "R" + totalPrice.toLocaleString();
                summaryCustomerName.textContent = name;
                summaryCustomerEmail.textContent = email;
                summaryCustomerPhone.textContent = phone;
                summaryCustomerCountry.textContent = country;

                inputRentalUnit.value = selectedType;
                inputRentalQuantity.value = quantitySelect.value;
                inputRentalStartDate.value = rentalStartDate.value;
                inputTotalPrice.value = totalPrice;

                bootstrap.Modal.getInstance(document.getElementById("customerStep"))?.hide();
                new bootstrap.Modal(document.getElementById("summaryStep")).show();
            });

            /* ------------------- Payment modals & flow --------------------- */
            // Create booking then open payment method modal
            if (openPaymentBtn) {
                const openPaymentDefaultLabel = (openPaymentBtn.textContent || '').trim() || 'Continue to Payment';
                openPaymentBtn.dataset.originalLabel = openPaymentDefaultLabel;

                const setOpenPaymentLoading = (isLoading) => {
                    if (isLoading) {
                        openPaymentBtn.disabled = true;
                        openPaymentBtn.textContent = 'Preparing booking...';
                    } else {
                        openPaymentBtn.disabled = false;
                        openPaymentBtn.textContent = openPaymentBtn.dataset.originalLabel || openPaymentDefaultLabel;
                    }
                };

                openPaymentBtn.addEventListener('click', async function() {
                    if (window.enableSelectedAddonHiddenFields) window.enableSelectedAddonHiddenFields();

                    if (bookingCreationInFlight) return;

                    if (!bookingIdField.value) {
                        bookingCreationInFlight = true;
                        setOpenPaymentLoading(true);

                        const bookingForm = document.getElementById('bookingForm');
                        const formData = new FormData(bookingForm);
                        if (!bookingIdField.value) {
                            formData.delete('booking_id');
                        }

                        let creationFailed = false;
                        try {
                            const res = await fetch(bookingForm.action, {
                                method: "POST",
                                body: formData,
                                headers: {
                                    "X-Requested-With": "XMLHttpRequest",
                                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                                }
                            });
                            const text = await res.text();
                            let data;
                            try {
                                data = JSON.parse(text);
                            } catch {
                                data = {
                                    success: false,
                                    message: text
                                };
                            }

                            if (!res.ok || !data.success) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: data.message || 'Failed to create booking.'
                                });
                                creationFailed = true;
                            } else {
                                bookingIdField.value = data.booking_id || data.id;
                                if (!bookingIdField.value) {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Oops...',
                                        text: 'Booking created but no ID returned.'
                                    });
                                    creationFailed = true;
                                }
                            }
                        } catch (e) {
                            console.error(e);
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Network error while creating booking.'
                            });
                            creationFailed = true;
                        } finally {
                            bookingCreationInFlight = false;
                            setOpenPaymentLoading(false);
                        }

                        if (creationFailed) return;
                    }

                    if (!bookingIdField.value) return;

                    bootstrap.Modal.getInstance(document.getElementById("summaryStep"))?.hide();
                    new bootstrap.Modal(document.getElementById("bookingPayment")).show();
                });
            }

            // Choose payment method (Stripe / PayFast)
            document.addEventListener('change', async function(e) {
                if (!(e.target && e.target.name === 'booking_payment_method')) return;

                const method = e.target.value;
                const paymentModalEl = document.getElementById("bookingPayment");
                const paymentModal = bootstrap.Modal.getInstance(paymentModalEl);
                paymentModal?.hide();

                // compute grand total (vehicle + add-ons)
                const vehicleTotal = parseFloat(document.getElementById('inputTotalPrice')?.value ||
                    '0');
                const addonTotal = window.computeAddonsTotal ? window.computeAddonsTotal() : 0;
                const grandTotal = Math.round((vehicleTotal + addonTotal) * 100) / 100;

                if (method === 'stripe') {
                    // Open Stripe card modal; the actual charge uses grandTotal below
                    new bootstrap.Modal(document.getElementById("bookingStripeModal")).show();
                    // Stash the grand total on the button for later
                    document.getElementById('bookingStripePayButton').dataset.amount = String(
                        grandTotal);
                    return;
                }

                // ----- PAYFAST flow -----
                const bookingId = document.getElementById('bookingId').value;
                if (!bookingId) {
                    await Swal.fire({
                        icon: 'error',
                        title: 'No booking yet',
                        text: 'Please create the booking first.'
                    });
                    new bootstrap.Modal(paymentModalEl).show();
                    e.target.checked = false;
                    return;
                }

                const confirmRes = await Swal.fire({
                    icon: 'question',
                    title: 'Proceed with PayFast?',
                    text: 'You will be redirected to PayFast to complete your payment.',
                    showCancelButton: true,
                    confirmButtonText: 'Continue',
                    cancelButtonText: 'Back',
                    reverseButtons: true,
                    customClass: {
                        confirmButton: 'btn btn-dark',
                        cancelButton: 'btn btn-outline-secondary me-3'
                    },
                    buttonsStyling: false
                });
                if (!confirmRes.isConfirmed) {
                    e.target.checked = false;
                    new bootstrap.Modal(paymentModalEl).show();
                    return;
                }

                try {
                    const res = await fetch(`/payfast/booking/init/${encodeURIComponent(bookingId)}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content')
                        },
                        body: JSON.stringify({
                            name: document.querySelector('#bookingForm [name="name"]')
                                ?.value || '',
                            email: document.querySelector('#bookingForm [name="email"]')
                                ?.value || '',
                            // ⬇️ send grand total so backend can set amount for PayFast
                            amount: grandTotal
                        })
                    });

                    const data = await res.json();
                    if (!res.ok || !data.success) throw new Error(data.message ||
                        'Failed to initialize PayFast.');

                    const pfForm = document.createElement('form');
                    pfForm.method = 'POST';
                    pfForm.action = data.action;
                    pfForm.style.display = 'none';

                    // allow backend to override amount, but if not present, patch it
                    if (data.fields && typeof data.fields.amount === 'undefined') {
                        data.fields.amount = String(grandTotal.toFixed(2));
                    }

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
                    await Swal.fire({
                        icon: 'error',
                        title: 'PayFast error',
                        text: err.message || 'Could not redirect to PayFast.'
                    });
                    e.target.checked = false;
                    new bootstrap.Modal(paymentModalEl).show();
                }
            });

            /* ----------------------- Stripe checkout ----------------------- */
 const stripe = Stripe("{{ $stripeConfig->stripe_key ?? '' }}");
             const elements = stripe.elements();
            const style = {
                base: {
                    fontSize: '16px',
                    color: '#32325d',
                    '::placeholder': {
                        color: '#a0aec0'
                    }
                }
            };
            const cardNumber = elements.create('cardNumber', {
                style
            });
            const cardExpiry = elements.create('cardExpiry', {
                style
            });
            const cardCvc = elements.create('cardCvc', {
                style
            });
            cardNumber.mount('#booking-card-number');
            cardExpiry.mount('#booking-card-expiry');
            cardCvc.mount('#booking-card-cvc');

            document.getElementById("bookingStripePayButton").addEventListener("click", async function() {
                const form = document.getElementById("bookingForm");
                const bookingId = bookingIdField.value;
                if (!bookingId) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Booking not created yet. Please try again.'
                    });
                    return;
                }

                const amount = parseFloat(this.dataset.amount ||
                '0'); // grand total passed from selector
                const {
                    paymentMethod,
                    error
                } = await stripe.createPaymentMethod({
                    type: 'card',
                    card: cardNumber,
                    billing_details: {
                        name: form.name?.value || '',
                        email: form.email?.value || ''
                    }
                });
                if (error) {
                    document.getElementById("booking-card-errors").textContent = error.message;
                    return;
                }

                try {
                    // ⬇️ send amount so backend charges vehicle + add-ons
                    const res = await fetch(
                        `/bookings/${encodeURIComponent(bookingId)}/pay-with-stripe`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .content
                            },
                            body: JSON.stringify({
                                payment_method_id: paymentMethod.id,
                                amount
                            })
                        });

                    const text = await res.text();
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch {
                        data = {
                            success: false,
                            message: text
                        };
                    }

                    if (!res.ok) {
                        console.error('Server error:', data);
                        alert(data.message || 'Payment failed (server error).');
                        return;
                    }

                    if (data.success) {
                        bootstrap.Modal.getInstance(document.getElementById("bookingStripeModal"))
                            ?.hide();
                        new bootstrap.Modal(document.getElementById("bookingThankYou")).show();
                    } else if (data.requires_action && data.payment_intent_client_secret) {
                        const result = await stripe.confirmCardPayment(data
                            .payment_intent_client_secret);
                        if (result.error) {
                            alert(result.error.message);
                        } else {
                            bootstrap.Modal.getInstance(document.getElementById("bookingStripeModal"))
                                ?.hide();
                            new bootstrap.Modal(document.getElementById("bookingThankYou")).show();
                        }
                    } else {
                        alert(data.message || 'Payment failed.');
                    }
                } catch (e) {
                    console.error(e);
                    alert('Network error while charging card.');
                }
            });

            // Never submit the <form> directly; we handle via AJAX
            document.getElementById("bookingForm").addEventListener("submit", function(e) {
                e.preventDefault();
            });
        });
    </script>
