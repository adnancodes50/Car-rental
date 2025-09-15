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
                                <h2 class="fw-bold">{{ $vehicle->name }}</h2>
                                <p class="text-muted">{{ $vehicle->year }} {{ $vehicle->model }}</p>
                            </div>
                            <span class="badge rounded-pill bg-success-subtle text-success">
                                {{ ucfirst($vehicle->status ?? 'Available') }}
                            </span>
                        </div>
                        <p class="text-muted">{{ $vehicle->description }}</p>
                        <!-- Pricing and Actions (unchanged) -->
                        <div class="row text-center g-2 mb-4">
                            @if($vehicle->rental_price_day)
                                <div class="col">
                                    <div class="border rounded p-2">
                                        <div class="fw-bold">R{{ number_format($vehicle->rental_price_day) }}/day</div>
                                        <small class="text-muted">Daily Rental</small>
                                    </div>
                                </div>
                            @endif
                            @if($vehicle->rental_price_week)
                                <div class="col">
                                    <div class="border rounded p-2">
                                        <div class="fw-bold">R{{ number_format($vehicle->rental_price_week) }}/week</div>
                                        <small class="text-muted">Weekly Rental</small>
                                    </div>
                                </div>
                            @endif
                            @if($vehicle->rental_price_month)
                                <div class="col">
                                    <div class="border rounded p-2">
                                        <div class="fw-bold">R{{ number_format($vehicle->rental_price_month) }}/month</div>
                                        <small class="text-muted">Monthly Rental</small>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="d-flex gap-3 mb-4">
                            <button type="button" class="btn btn-warning flex-fill fw-bold" data-bs-toggle="modal"
                                data-bs-target="#multiStepBookingModal">
                                <i class="bi bi-calendar-check me-2"></i> Book this {{ $vehicle->name }}
                            </button>
                            @if($vehicle->purchase_price)
                                <a href="#" class="btn btn-dark flex-fill fw-bold" data-bs-toggle="modal"
                                    data-bs-target="#purchaseModal">
                                    Purchase (R{{ number_format($vehicle->purchase_price) }})
                                </a>
                            @endif
                        </div>
                        <!-- Specs (unchanged) -->
                        <div class="row g-3 text-muted small">
                            @if($vehicle->engine)
                                <div class="col-6"><i class="bi bi-gear me-2"></i><strong>Engine:</strong>
                                    {{ $vehicle->engine }}</div>
                            @endif
                            @if($vehicle->transmission)
                                <div class="col-6"><i class="bi bi-shuffle me-2"></i><strong>Transmission:</strong>
                                    {{ $vehicle->transmission }}</div>
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
                                <div class="col-6"><i class="bi bi-speedometer2 me-2"></i><strong>Mileage:</strong>
                                    {{ number_format($vehicle->mileage) }} km</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= BOOKING FORM (wrap all steps) ================= -->
   <div>
    @include('models.booking')
   </div>

   @include('models.purchase')

@endsection

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const optionCards = document.querySelectorAll(".option-card");
        const dateSection = document.getElementById("dateSection");
        const quantitySection = document.getElementById("quantitySection");
        const quantityLabel = document.getElementById("quantityLabel");
        const quantitySelect = document.getElementById("rentalQuantity");
        const totalPriceDiv = document.getElementById("totalPrice");
        const rentalPeriodDiv = document.getElementById("rentalPeriod");
        const rentalStartDate = document.getElementById("rentalStartDate");

        // Booked dates from backend
        const bookedDates = @json($bookedRanges ?? $bookedDates); // either single dates or ranges

        // Initialize flatpickr
        flatpickr("#rentalStartDate", {
            dateFormat: "Y-m-d",
            minDate: "today",
            disable: [
                function (date) {
                    return @json($bookedRanges).some(range => {
                        const start = new Date(range.from);
                        const end = new Date(range.to);
                        return date >= start && date <= end;
                    });
                }
            ],
            onChange: function (selectedDates, dateStr) {
                if (!dateStr) return;

                const qty = parseInt(quantitySelect.value) || 1;

                // Find the selected option card
                const selectedCard = Array.from(optionCards).find(card => card.classList.contains('border-warning'));
                if (!selectedCard) {
                    alert("Please select a rental type first.");
                    rentalStartDate.value = '';
                    return;
                }
                const unit = selectedCard.getAttribute('data-type');

                // Compute end date
                let endDate = new Date(dateStr);
                if (unit === 'day') endDate.setDate(endDate.getDate() + qty - 1);
                if (unit === 'week') endDate.setDate(endDate.getDate() + (qty * 7) - 1);
                if (unit === 'month') endDate.setMonth(endDate.getMonth() + qty);

                // Check for overlap
                const conflict = @json($bookedRanges).some(range => {
                    const start = new Date(range.from);
                    const end = new Date(range.to);
                    return endDate >= start && new Date(dateStr) <= end;
                });

                if (conflict) {
                    alert("The selected rental period overlaps with an existing booking. Please choose another start date.");
                    rentalStartDate.value = '';
                } else {
                    calculateTotal();
                }
            }
        });
        // Rest of your variables for summary
        const summaryType = document.getElementById("summaryType");
        const summaryPeriod = document.getElementById("summaryPeriod");
        const summaryPrice = document.getElementById("summaryPrice");
        const summaryCustomerName = document.getElementById("summaryCustomerName");
        const summaryCustomerEmail = document.getElementById("summaryCustomerEmail");
        const summaryCustomerPhone = document.getElementById("summaryCustomerPhone");
        const summaryCustomerCountry = document.getElementById("summaryCustomerCountry");

        let selectedPrice = 0;
        let selectedType = "";
        let totalPrice = 0;
        let rentalPeriodText = "";

        // STEP 1: Rental selection
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

                let max = selectedType === "day" ? 6 : (selectedType === "week" ? 4 : 4);
                let label = selectedType === "day" ? "Number of Days" :
                    selectedType === "week" ? "Number of Weeks" : "Number of Months";

                quantityLabel.textContent = label;
                quantitySelect.innerHTML = "";
                for (let i = 1; i <= max; i++) {
                    let opt = document.createElement("option");
                    opt.value = i;
                    opt.textContent = i + " " + label.split(" ")[1];
                    quantitySelect.appendChild(opt);
                }

                calculateTotal();
            });
        });

        quantitySelect.addEventListener("change", calculateTotal);
        rentalStartDate.addEventListener("change", calculateTotal);

        function calculateTotal() {
            const qty = parseInt(quantitySelect.value) || 1;
            totalPrice = qty * selectedPrice;
            totalPriceDiv.textContent = "Total Price: R" + totalPrice.toLocaleString();

            const startDate = rentalStartDate.value;
            if (startDate) {
                let endDate = new Date(startDate);
                if (selectedType === "day") endDate.setDate(endDate.getDate() + qty);
                if (selectedType === "week") endDate.setDate(endDate.getDate() + (qty * 7));
                if (selectedType === "month") endDate.setMonth(endDate.getMonth() + qty);

                rentalPeriodText = formatDate(new Date(startDate)) + " → " + formatDate(endDate);
                rentalPeriodDiv.textContent = "Rental Period: " + rentalPeriodText;
            } else {
                rentalPeriodText = "";
                rentalPeriodDiv.textContent = "Select a start date to see rental period.";
            }
        }

        function formatDate(date) {
            return date.toLocaleDateString("en-GB", { day: "2-digit", month: "short", year: "numeric" });
        }

        // Step 1 → Step 2
        document.getElementById("continueFromStep1").addEventListener("click", function () {
            if (!selectedType) { alert("Please select a rental type."); return; }
            if (!rentalStartDate.value) { alert("Please select a rental start date."); return; }

            let step1 = bootstrap.Modal.getInstance(document.getElementById("multiStepBookingModal"));
            step1.hide();
            new bootstrap.Modal(document.getElementById("addonsStep")).show();
        });

        // Step 3 → Step 4
        document.getElementById("goToSummary").addEventListener("click", function () {
            const form = document.getElementById("bookingForm");

            const name = form.querySelector("input[name='name']").value;
            const email = form.querySelector("input[name='email']").value;
            const phone = form.querySelector("input[name='phone']").value;
            const country = form.querySelector("select[name='country']").value;

            if (!name || !email || !phone || !country) { alert("Please complete all fields."); return; }

            // Populate summary
            summaryType.textContent = selectedType;
            summaryPeriod.textContent = rentalPeriodText;
            summaryPrice.textContent = "R" + totalPrice.toLocaleString();
            summaryCustomerName.textContent = name;
            summaryCustomerEmail.textContent = email;
            summaryCustomerPhone.textContent = phone;
            summaryCustomerCountry.textContent = country;

            document.getElementById("inputRentalUnit").value = selectedType;
            document.getElementById("inputRentalQuantity").value = quantitySelect.value;
            document.getElementById("inputRentalStartDate").value = rentalStartDate.value;
            document.getElementById("inputTotalPrice").value = totalPrice;

            let customerModal = bootstrap.Modal.getInstance(document.getElementById("customerStep"));
            customerModal.hide();
            new bootstrap.Modal(document.getElementById("summaryStep")).show();
        });

    });
</script>
