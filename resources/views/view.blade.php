{{-- resources/views/show.blade.php --}}
@extends('layouts.frontend')

@section('title', $vehicle->name)

@section('content')
    <div class="container py-5">
        <div class="container m-5">
            <!-- Back link -->
            <a href="{{ url('/') }}" class="text-muted mb-4 d-inline-flex align-items-center">
                <i class="bi bi-arrow-left me-2"></i> Back to vehicles
            </a>



            <div class="row g-5">
                <!-- Gallery and Vehicle Details (unchanged) -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm mb-3">
                        <img id="mainImage" src="{{ $vehicle->mainImage() }}" class="card-img-top rounded"
                            alt="{{ $vehicle->name }}" style="object-fit: cover; max-height: 380px;">
                    </div>
                    @if($vehicle->images->count() > 0)
                        <div class="d-flex gap-2">
                            <img src="{{ $vehicle->mainImage() }}" class="img-thumbnail"
                                style="width: 80px; height: 60px; cursor:pointer; object-fit:cover;"
                                onclick="document.getElementById('mainImage').src=this.src">
                            @foreach($vehicle->images as $image)
                                <img src="{{ $image->url }}" class="img-thumbnail"
                                    style="width: 80px; height: 60px; cursor:pointer; object-fit:cover;"
                                    onclick="document.getElementById('mainImage').src=this.src">
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm p-4 rounded-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h2 class="">{{ $vehicle->name }}</h2>
                                <p class="text-muted">{{ $vehicle->year }} {{ $vehicle->model }}</p>
                            </div>
                            <span class="badge rounded-pill bg-success-subtle text-success">
                                {{ ucfirst($vehicle->status ?? 'Available') }}
                            </span>
                        </div>
                        <p class="text-muted">{{ $vehicle->description }}</p>
                        <!-- Pricing and Actions (unchanged) -->
                        <div class="row text-center text-muted g-2 mb-4">
                            @if($vehicle->rental_price_day)
                                <div class="col">
                                    <div class="border rounded p-2">
                                        <div class="">R{{ number_format($vehicle->rental_price_day) }}/day</div>
                                        <small class="text-muted">Daily Rental</small>
                                    </div>
                                </div>
                            @endif
                            @if($vehicle->rental_price_week)
                                <div class="col">
                                    <div class="border rounded p-2">
                                        <div class="">R{{ number_format($vehicle->rental_price_week) }}/week</div>
                                        <small class="text-muted">Weekly Rental</small>
                                    </div>
                                </div>
                            @endif
                            @if($vehicle->rental_price_month)
                                <div class="col">
                                    <div class="border rounded p-2">
                                        <div class="">R{{ number_format($vehicle->rental_price_month) }}/month</div>
                                        <small class="text-muted">Monthly Rental</small>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="d-flex gap-3 mb-4">
                            <button type="button" class="btn btn-warning flex-fill text-black text-bold"
                                data-bs-toggle="modal" data-bs-target="#multiStepBookingModal">
                                <i class="bi bi-calendar-check me-2"></i> Book this {{ $vehicle->name }}
                            </button>
                            @if($vehicle->purchase_price)
                                <a href="#" class="btn btn-dark flex-fill " data-bs-toggle="modal"
                                    data-bs-target="#purchaseModal">
                                    Purchase (R{{ number_format($vehicle->purchase_price) }})
                                </a>
                            @endif
                        </div>
                        <!-- Specs (unchanged) -->
                        <div class="row g-3 text-muted small">
                            @if($vehicle->engine)
                                <div class="col-6"><i class="bi bi-gear-fill me-2"></i><strong>Engine:</strong>
                                    {{ $vehicle->engine }}</div>
                            @endif
                            @if($vehicle->transmission)
                                <div class="col-6"><i class="bi bi-shuffle" style="color: dark; font-weight: bold;"></i>
                                    <strong>Transmission:</strong>
                                    {{ $vehicle->transmission }}
                                </div>
                            @endif
                            @if($vehicle->seats)
                                <div class="col-6"><i class="bi bi-people-fill me-2"></i><strong>Seating:</strong>
                                    {{ $vehicle->seats }}</div>
                            @endif
                            @if($vehicle->fuel_type)
                                <div class="col-6"><i class="bi bi-fuel-pump-fill me-2"></i><strong>Fuel:</strong>
                                    {{ $vehicle->fuel_type }}</div>
                            @endif
                            @if($vehicle->location)
                                <div class="col-6"><i class="bi bi-geo-alt-fill me-2"></i><strong>Location:</strong>
                                    {{ $vehicle->location }}</div>
                            @endif
                            @if($vehicle->mileage)
                                <div class="col-6"><i class="bi bi-speedometer2  text-dark"></i>
                                    <strong>Mileage:</strong>
                                    {{ number_format($vehicle->mileage) }} km
                                </div>
                            @endif
                            <div class="container row g-3 small">
                                <h4 class="text-bold fw-5">Features and Equipment</h4>

                                @if(!empty($vehicle->features) && is_array($vehicle->features))
                                    @foreach($vehicle->features as $feature)
                                        @if($feature) <!-- Only show non-empty features -->
                                            <div class="col-6">
                                                <i class="bi bi-check-circle-fill " style="color: rgb(97, 94, 94)"></i> {{ ucfirst($feature) }}
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

            <!-- ================= BOOKING FORM (wrap all steps) ================= -->
            <div>
                @include('models.booking')
                <!-- ================= BOOKING FORM (wrap all steps) ================= -->


            </div>

            @include('models.purchase');

@endsection

        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const optionCards = document.querySelectorAll(".option-card");
                const dateSection = document.getElementById("dateSection");
                const quantitySection = document.getElementById("quantitySection");
                const quantitySelect = document.getElementById("rentalQuantity");
                const totalPriceDiv = document.getElementById("totalPrice");
                const rentalPeriodDiv = document.getElementById("rentalPeriod");
                const rentalStartDate = document.getElementById("rentalStartDate");

                // Hidden form inputs
                const inputRentalUnit = document.getElementById("inputRentalUnit");
                const inputRentalQuantity = document.getElementById("inputRentalQuantity");
                const inputRentalStartDate = document.getElementById("inputRentalStartDate");
                const inputTotalPrice = document.getElementById("inputTotalPrice");

                // Extra days input (ensure exists)
                let inputExtraDays = document.getElementById("inputExtraDays");
                if (!inputExtraDays) {
                    inputExtraDays = document.createElement("input");
                    inputExtraDays.type = "hidden";
                    inputExtraDays.name = "extra_days";
                    inputExtraDays.id = "inputExtraDays";
                    inputExtraDays.value = 0;
                    document.getElementById("bookingForm").appendChild(inputExtraDays);
                }

                // Summary fields
                const summaryType = document.getElementById("summaryType");
                const summaryPeriod = document.getElementById("summaryPeriod");
                const summaryPrice = document.getElementById("summaryPrice");
                const summaryCustomerName = document.getElementById("summaryCustomerName");
                const summaryCustomerEmail = document.getElementById("summaryCustomerEmail");
                const summaryCustomerPhone = document.getElementById("summaryCustomerPhone");
                const summaryCustomerCountry = document.getElementById("summaryCustomerCountry");

                // Booked ranges from backend
                const bookedRanges = @json($bookedRanges ?? []);

                function isOverlapping(startDate, endDate) {
                    // Return all overlapping ranges instead of just true/false
                    return bookedRanges.filter(range => {
                        const rangeStart = new Date(range.from);
                        const rangeEnd = new Date(range.to);
                        return startDate <= rangeEnd && endDate >= rangeStart;
                    });
                }

                let selectedPrice = 0;
                let selectedType = "";
                let totalPrice = 0;
                let rentalPeriodText = "";

                // Initialize flatpickr
                const flatpickrInstance = flatpickr("#rentalStartDate", {
                    dateFormat: "Y-m-d",
                    minDate: "today",
                    disable: bookedRanges.map(range => ({ from: range.from, to: range.to })),
                    onChange: calculateTotal
                });

                function formatDate(date) {
                    return date.toLocaleDateString("en-GB", { day: "2-digit", month: "short", year: "numeric" });
                }

                function isOverlapping(startDate, endDate) {
                    // Return all overlapping ranges instead of just true/false
                    return bookedRanges.filter(range => {
                        const rangeStart = new Date(range.from);
                        const rangeEnd = new Date(range.to);
                        return startDate <= rangeEnd && endDate >= rangeStart;
                    });
                }

                function calculateTotal() {
                    const qty = parseInt(quantitySelect.value) || 1;
                    const extraDaysSelect = document.getElementById('extraDays');
                    const extraDays = extraDaysSelect ? parseInt(extraDaysSelect.value) : 0;

                    inputExtraDays.value = extraDays;

                    const startDateStr = rentalStartDate.value;
                    if (!startDateStr) {
                        totalPriceDiv.classList.add("d-none");
                        rentalPeriodDiv.classList.add("d-none");
                        if (extraDaysSelect) extraDaysSelect.parentElement.style.display = "none";
                        return;
                    }

                    // Calculate rental period
                    let endDate = new Date(startDateStr);
                    if (selectedType === "day") endDate.setDate(endDate.getDate() + qty + extraDays - 1);
                    if (selectedType === "week") endDate.setDate(endDate.getDate() + (qty * 7) + extraDays - 1);
                    if (selectedType === "month") {
                        endDate.setMonth(endDate.getMonth() + qty);
                        endDate.setDate(endDate.getDate() + extraDays);
                    }

                    // ✅ Check overlap with booked ranges
                    const startDate = new Date(startDateStr);
                    const overlappingRanges = isOverlapping(startDate, endDate);

                    if (overlappingRanges.length > 0) {
                        // Format unavailable ranges nicely
                        let unavailableText = overlappingRanges.map(range => {
                            const rangeStart = new Date(range.from);
                            const rangeEnd = new Date(range.to);
                            return `${formatDate(rangeStart)} → ${formatDate(rangeEnd)}`;
                        }).join("\n");

                        Swal.fire({
                            icon: 'error',
                            title: 'Unavailable',
                            html: `<p>Your selected period overlaps with existing bookings.</p>
           <p><strong>Unavailable dates:</strong></p>
           <pre style="text-align:center; font-size:14px; white-space:pre-wrap;">${unavailableText}</pre>`
                        });


                        rentalStartDate.value = ""; // reset selection
                        totalPriceDiv.classList.add("d-none");
                        rentalPeriodDiv.classList.add("d-none");
                        return;
                    }

                    // Show extra days if applicable
                    if (extraDaysSelect) extraDaysSelect.parentElement.style.display = "block";
                    totalPriceDiv.classList.remove("d-none");
                    rentalPeriodDiv.classList.remove("d-none");

                    // Calculate base price
                    let basePrice = qty * selectedPrice;

                    // Calculate extra days price
                    let extraPrice = 0;
                    if (extraDays > 0) {
                        if (selectedType === "day") extraPrice = extraDays * selectedPrice;
                        else if (selectedType === "week") extraPrice = extraDays * (selectedPrice / 7);
                        else if (selectedType === "month") extraPrice = extraDays * (selectedPrice / 30);
                    }

                    totalPrice = basePrice + extraPrice;

                    rentalPeriodText = `<div class="d-flex justify-content-between">
    <div class="text-muted small">
        Start Date<br>${formatDate(new Date(startDateStr))}
    </div>
    <div class="text-muted small">
        End Date<br>${formatDate(endDate)}
    </div>
</div>

    </div>`;
                    rentalPeriodDiv.innerHTML = rentalPeriodText;

                    // Update total price HTML with smaller font
                    let costHtml = `<div style="font-size: 14px; font-weight: normal;">
    ${selectedType === 'day' ? 'Days' : selectedType === 'week' ? 'Weeks' : 'Months'}: ${qty} × R${selectedPrice.toLocaleString()}
</div>`;

                    if (extraDays > 0) {
                        costHtml += `<div style="font-size: 14px; font-weight: normal;">
        Extra Days: ${extraDays} × R${(
                                selectedType === "week" ? (selectedPrice / 7) :
                                    selectedType === "month" ? (selectedPrice / 30) :
                                        selectedPrice
                            ).toLocaleString()}
    </div>`;
                    }

                    costHtml += `<div class="mt-2" style="font-size: 14px; font-weight: normal;">
    Total Cost: R${totalPrice.toLocaleString()}
</div>`;

                    totalPriceDiv.innerHTML = costHtml;

                }


                // Rental type selection
                optionCards.forEach(card => {
                    card.addEventListener("click", () => {
                        optionCards.forEach(c => c.classList.remove("border-warning", "bg-warning-subtle"));
                        card.classList.add("border-warning", "bg-warning-subtle");

                        selectedPrice = parseFloat(card.getAttribute("data-price"));
                        selectedType = card.getAttribute("data-type");

                        dateSection.classList.remove("d-none");
                        quantitySection.classList.remove("d-none");
                        totalPriceDiv.classList.remove("d-none");
                        rentalPeriodDiv.classList.remove("d-none");

                        // Clear previous content
                        quantitySection.innerHTML = "";

                        const flexContainer = document.createElement("div");
                        flexContainer.className = "d-flex align-items-center gap-2";

                        // Quantity select
                        const qtyDiv = document.createElement("div");
                        qtyDiv.style.flex = "1";
                        const qtyLabelElem = document.createElement("label");
                        qtyLabelElem.className = "form-label mb-1";
                        qtyLabelElem.textContent = selectedType === "day" ? "Number of Days" : selectedType === "week" ? "Number of Weeks" : "Number of Months";
                        qtyDiv.appendChild(qtyLabelElem);

                        quantitySelect.innerHTML = "";
                        let maxQty = selectedType === "day" ? 6 : 4;
                        for (let i = 1; i <= maxQty; i++) {
                            const opt = document.createElement("option");
                            opt.value = i;
                            opt.textContent = i === 1 ? `${i} ${selectedType}` : `${i} ${selectedType}s`;
                            quantitySelect.appendChild(opt);
                        }
                        qtyDiv.appendChild(quantitySelect);
                        flexContainer.appendChild(qtyDiv);

                        // Extra days select for week/month
                        if (selectedType !== "day") {
                            const extraDiv = document.createElement("div");
                            extraDiv.style.flex = "1";
                            const extraLabel = document.createElement("label");
                            extraLabel.className = "form-label mb-1";
                            extraLabel.textContent = "Extra Days";
                            extraDiv.appendChild(extraLabel);

                            const extraSelect = document.createElement("select");
                            extraSelect.id = "extraDays";
                            extraSelect.className = "form-select";
                            for (let i = 0; i <= 6; i++) {
                                const opt = document.createElement("option");
                                opt.value = i;
                                opt.textContent = i === 0 ? "0 days" : i === 1 ? "1 day" : `${i} days`;
                                extraSelect.appendChild(opt);
                            }
                            extraSelect.addEventListener("change", calculateTotal);
                            extraDiv.appendChild(extraSelect);
                            extraDiv.style.display = "none"; // initially hidden
                            flexContainer.appendChild(extraDiv);
                        }

                        quantitySection.appendChild(flexContainer);

                        calculateTotal();
                        flatpickrInstance.open();
                    });
                });

                quantitySelect.addEventListener("change", calculateTotal);
                rentalStartDate.addEventListener("change", calculateTotal);

                // Step 1 → Step 2
                document.getElementById("continueFromStep1").addEventListener("click", function () {
                    if (!selectedType) {
                        Swal.fire({ icon: 'error', title: 'Oops...', text: 'Please select a rental type.' });
                        return;
                    }
                    if (!rentalStartDate.value) {
                        Swal.fire({ icon: 'error', title: 'Oops...', text: 'Please select a rental start date.' });
                        return;
                    }

                    bootstrap.Modal.getInstance(document.getElementById("multiStepBookingModal")).hide();
                    new bootstrap.Modal(document.getElementById("addonsStep")).show();
                });

                // Step 3 → Step 4 (Summary)
                document.getElementById("goToSummary").addEventListener("click", function () {
                    const form = document.getElementById("bookingForm");
                    const name = form.querySelector("input[name='name']").value;
                    const email = form.querySelector("input[name='email']").value;
                    const phone = form.querySelector("input[name='phone']").value;
                    const country = form.querySelector("select[name='country']").value;

                    if (!name || !email || !phone || !country) {
                        Swal.fire({ icon: 'error', title: 'Oops...', text: 'Please complete all fields.' });
                        return;
                    }

                    summaryType.textContent = selectedType;
                    summaryPeriod.innerHTML = rentalPeriodText;
                    summaryPrice.textContent = "R" + totalPrice.toLocaleString();
                    summaryCustomerName.textContent = name;
                    summaryCustomerEmail.textContent = email;
                    summaryCustomerPhone.textContent = phone;
                    summaryCustomerCountry.textContent = country;

                    // Update hidden inputs
                    inputRentalUnit.value = selectedType;
                    inputRentalQuantity.value = quantitySelect.value;
                    inputRentalStartDate.value = rentalStartDate.value;
                    inputTotalPrice.value = totalPrice;

                    bootstrap.Modal.getInstance(document.getElementById("customerStep")).hide();
                    new bootstrap.Modal(document.getElementById("summaryStep")).show();
                });

                // Booking form submission with SweetAlert success
                const bookingForm = document.getElementById("bookingForm");
                bookingForm.addEventListener("submit", function (e) {
                    e.preventDefault();

                    // Ensure extraDays is always sent
                    if (!inputExtraDays.value) inputExtraDays.value = 0;

                    const formData = new FormData(bookingForm);

                    fetch(bookingForm.action, {
                        method: "POST",
                        body: formData,
                        headers: {
                            "X-Requested-With": "XMLHttpRequest",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Booking Confirmed!',
                                    text: 'Your booking has been successfully submitted.'
                                }).then(() => {
                                    window.location.href = data.redirect ?? '/';
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: data.message || 'Something went wrong. Please try again.'
                                });
                            }
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'An unexpected error occurred.'
                            });
                            console.error(error);
                        });
                });
            });
        </script>
