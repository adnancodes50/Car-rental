{{-- resources/views/show.blade.php --}}
@extends('layouts.frontend')

@section('title', $vehicle->name)

@section('content')
    <div class="container py-5">
        <div class="container  m-5">
            <!-- Back link -->
            <a href="{{ url('/') }}" class="text-muted mb-4 d-inline-flex align-items-center">
                <i class="bi bi-arrow-left me-2"></i> Back to vehicles
            </a>

            <div class="row g-5">
                <!-- Gallery -->
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

                <!-- Vehicle Details -->
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

                        <!-- Pricing -->
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

                        <!-- Actions -->
                        <div class="d-flex gap-3 mb-4">
                            <!-- Trigger modal -->
                            <button type="button" class="btn btn-warning flex-fill fw-bold" data-bs-toggle="modal"
                                data-bs-target="#bookingModal">
                                <i class="bi bi-calendar-check me-2"></i> Book this Land Rover
                            </button>

                            @if($vehicle->purchase_price)
                                <a href="#" class="btn btn-dark flex-fill fw-bold" data-bs-toggle="modal"
                                    data-bs-target="#purchaseModal">
                                    Purchase (R{{ number_format($vehicle->purchase_price) }})
                                </a>
                            @endif

                        </div>

                        <!-- Specs -->
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

    <!-- Booking Modal -->
    <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 shadow">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="bookingModalLabel">Book {{ $vehicle->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <h5 class="fw-bold mb-3">Select Rental Duration</h5>
                    <div class="row text-center g-3">
                        <!-- Daily -->
                        <div class="col-md-4">
                            <div class="option-card p-3 border rounded-4 bg-light h-100" data-type="day"
                                data-price="{{ $vehicle->rental_price_day }}">
                                <i class="bi bi-clock display-6 text-warning"></i>
                                <h6 class="fw-bold mt-2">Daily Rental</h6>
                                <p class="small text-muted mb-1">Perfect for short trips (1–6 days)</p>
                                <div class="fw-bold text-dark">R{{ number_format($vehicle->rental_price_day) }}/day</div>
                            </div>
                        </div>

                        <!-- Weekly -->
                        <div class="col-md-4">
                            <div class="option-card p-3 border rounded-4 h-100" data-type="week"
                                data-price="{{ $vehicle->rental_price_week }}">
                                <i class="bi bi-calendar-event display-6 text-warning"></i>
                                <h6 class="fw-bold mt-2">Weekly Rental</h6>
                                <p class="small text-muted mb-1">Great for extended adventures (1–4 weeks)</p>
                                <div class="fw-bold text-dark">R{{ number_format($vehicle->rental_price_week) }}/week</div>
                            </div>
                        </div>

                        <!-- Monthly -->
                        <div class="col-md-4">
                            <div class="option-card p-3 border rounded-4 h-100" data-type="month"
                                data-price="{{ $vehicle->rental_price_month }}">
                                <i class="bi bi-box display-6 text-warning"></i>
                                <h6 class="fw-bold mt-2">Monthly Rental</h6>
                                <p class="small text-muted mb-1">Best value for long expeditions (1–4 months)</p>
                                <div class="fw-bold text-dark">R{{ number_format($vehicle->rental_price_month) }}/month
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Rental Start Date -->
                    <div class="mb-3 d-none" id="dateSection">
                        <label for="rentalStartDate" class="form-label">Rental Start Date</label>
                        <input type="date" id="rentalStartDate" class="form-control rounded-3">
                    </div>

                    <!-- Quantity (days/weeks/months) -->
                    <div class="mb-3 d-none" id="quantitySection">
                        <label for="rentalQuantity" class="form-label" id="quantityLabel"></label>
                        <select id="rentalQuantity" class="form-select rounded-3"></select>
                    </div>

                    <!-- Total Price -->
                    <div class="alert alert-info fw-bold d-none" id="totalPrice"></div>

                    <!-- Rental Period -->
                    <div class="alert alert-secondary fw-bold d-none" id="rentalPeriod"></div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Close</button>
                    <!-- Trigger Button -->
                    <button type="button" class="btn btn-warning fw-bold rounded-3" data-bs-toggle="modal"
                        data-bs-target="#addonsModal">
                        Continue to Add-Ons
                    </button>
                </div>
            </div>
        </div>
    </div>


    @if($vehicle->purchase_price)
        <!-- Purchase Modal -->
        <div class="modal fade" id="purchaseModal" tabindex="-1" aria-labelledby="purchaseModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered">
                <div class="modal-content shadow">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="purchaseModalLabel">
                            Purchase {{ $vehicle->name }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body text-center">
                        <!-- Warning Icon -->
                        <div class="mb-3">
                            <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 2.5rem;"></i>
                        </div>

                        <!-- Heading -->
                        <h5 class="fw-bold mb-3">Purchase Process Information</h5>

                        <!-- Description -->
                        <p class="text-muted">
                            To begin the purchase process, please pay a deposit to place this vehicle under offer.
                            The full purchase process will continue offline with one of our representatives.
                        </p>

                        <!-- Vehicle Purchase Info Box -->
                        <div class="p-4 border rounded-4 bg-light text-start mx-auto" style="max-width: 420px;">
                            <p class="mb-2"><strong>Vehicle:</strong> {{ $vehicle->name }}</p>
                            <p class="mb-2"><strong>Sale Price:</strong> R{{ number_format($vehicle->purchase_price) }} ZAR</p>

                            @php
                                // Example: 10% deposit required
                                $deposit = $vehicle->purchase_price * 0.10;
                            @endphp

                            <p class="mb-0">
                                <strong>Required Deposit:</strong>
                                <span class="text-danger fw-bold">R{{ number_format($deposit) }} ZAR</span>
                            </p>
                        </div>
                    </div>


                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-warning fw-bold rounded-3 px-4">
                            Continue to Payment
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif






    <!-- Bootstrap Modal -->
    <div class="modal fade" id="addonsModal" tabindex="-1" aria-labelledby="addonsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content rounded-4 shadow-lg">

                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="addonsModalLabel">
                        <i class="bi bi-calendar-event"></i> Book Mountain Ranger
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Body -->
                <div class="modal-body">
                    <!-- Add-Ons Section -->
                    <div class="text-center mb-4">
                        <h2 class="h4 fw-bold text-dark mb-2">Select Add-Ons</h2>
                        <p class="text-muted">Enhance your adventure with our optional equipment</p>
                    </div>

                    @foreach($addOns as $addOn)
                        <div class="d-flex align-items-center justify-content-between border rounded p-3 mb-3 shadow-sm">
                            <!-- Left: Image -->
                            <div class="me-3">
                                <img src="{{ $addOn->image_url }}" alt="{{ $addOn->name }}" class="rounded border"
                                    style="width:60px; height:60px; object-fit:cover;">
                            </div>

                            <!-- Middle: Content -->
                            <div class="flex-grow-1">
                                <h5 class="fw-semibold mb-1">{{ $addOn->name }}</h5>
                                <p class="text-muted mb-1">{{ $addOn->description }}</p>
                                <p class="small text-muted mb-1">
                                    R{{ $addOn->price_day }}/day •
                                    R{{ $addOn->price_week }}/week •
                                    R{{ $addOn->price_month }}/month
                                </p>
                            </div>

                            <!-- Right: Stock + Dropdown -->
                            <div class="text-end">
                                <span class="badge bg-success mb-2">{{ $addOn->qty_total }} available</span>
                                <div>
                                    <select class="form-select form-select-sm" name="addOns[{{ $addOn->id }}]">
                                        @for($i = 0; $i <= $addOn->qty_total; $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Back</button>
                    <!-- Trigger form modal -->
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#customerDetailsModal">
                        Continue to Details
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- Customer Details Modal -->
    <div class="modal fade" id="customerDetailsModal" tabindex="-1" aria-labelledby="customerDetailsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 shadow">

                <!-- Header -->
                <div class="modal-header flex-column text-center">
                    <h5 class="modal-title fw-bold" id="customerDetailsModalLabel">Enter Your Details</h5>
                    <div class="w-100 fw-bold mt-2">
                        Please provide your information
                    </div>
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>


                <!-- Body -->
                <div class="modal-body">
                    <form id="customerDetailsForm" method="POST" action="#">
                        @csrf
                        <div class="row g-3">

                            <!-- Full Name -->
                            <div class="col-md-12">
                                <label for="customerName" class="form-label">Full Name</label>
                                <input type="text" class="form-control rounded-3" id="customerName" name="name"
                                    placeholder="John Doe" required>
                            </div>

                            <!-- Email -->
                            <div class="col-md-12">
                                <label for="customerEmail" class="form-label">Email</label>
                                <input type="email" class="form-control rounded-3" id="customerEmail" name="email"
                                    placeholder="you@example.com" required>
                            </div>

                            <!-- Phone -->
                            <div class="col-md-12">
                                <label for="customerPhone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control rounded-3" id="customerPhone" name="phone"
                                    placeholder="+27 123 456 7890" required>
                            </div>


                            <!-- Country -->
                            <div class="col-md-12">
                                <label for="customerCountry" class="form-label">Country of Residence</label>
                                <select id="customerCountry" name="country" class="form-select rounded-3" required>
                                    <option value="" selected disabled>Select your country</option>
                                    <option value="Afghanistan">Afghanistan</option>
                                    <option value="Albania">Albania</option>
                                    <option value="Algeria">Algeria</option>
                                    <option value="Andorra">Andorra</option>
                                    <option value="Angola">Angola</option>
                                    <option value="Argentina">Argentina</option>
                                    <option value="Armenia">Armenia</option>
                                    <option value="Australia">Australia</option>
                                    <option value="Austria">Austria</option>
                                    <option value="Azerbaijan">Azerbaijan</option>
                                    <option value="Bahamas">Bahamas</option>
                                    <option value="Bahrain">Bahrain</option>
                                    <option value="Bangladesh">Bangladesh</option>
                                    <option value="Barbados">Barbados</option>
                                    <option value="Belarus">Belarus</option>
                                    <option value="Belgium">Belgium</option>
                                    <option value="Belize">Belize</option>
                                    <option value="Benin">Benin</option>
                                    <option value="Bhutan">Bhutan</option>
                                    <option value="Bolivia">Bolivia</option>
                                    <option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
                                    <option value="Botswana">Botswana</option>
                                    <option value="Brazil">Brazil</option>
                                    <option value="Brunei">Brunei</option>
                                    <option value="Bulgaria">Bulgaria</option>
                                    <option value="Burkina Faso">Burkina Faso</option>
                                    <option value="Burundi">Burundi</option>
                                    <option value="Cambodia">Cambodia</option>
                                    <option value="Cameroon">Cameroon</option>
                                    <option value="Canada">Canada</option>
                                    <option value="Cape Verde">Cape Verde</option>
                                    <option value="Central African Republic">Central African Republic</option>
                                    <option value="Chad">Chad</option>
                                    <option value="Chile">Chile</option>
                                    <option value="China">China</option>
                                    <option value="Colombia">Colombia</option>
                                    <option value="Comoros">Comoros</option>
                                    <option value="Congo">Congo</option>
                                    <option value="Costa Rica">Costa Rica</option>
                                    <option value="Croatia">Croatia</option>
                                    <option value="Cuba">Cuba</option>
                                    <option value="Cyprus">Cyprus</option>
                                    <option value="Czech Republic">Czech Republic</option>
                                    <option value="Denmark">Denmark</option>
                                    <option value="Djibouti">Djibouti</option>
                                    <option value="Dominican Republic">Dominican Republic</option>
                                    <option value="Ecuador">Ecuador</option>
                                    <option value="Egypt">Egypt</option>
                                    <option value="El Salvador">El Salvador</option>
                                    <option value="Estonia">Estonia</option>
                                    <option value="Eswatini">Eswatini</option>
                                    <option value="Ethiopia">Ethiopia</option>
                                    <option value="Fiji">Fiji</option>
                                    <option value="Finland">Finland</option>
                                    <option value="France">France</option>
                                    <option value="Gabon">Gabon</option>
                                    <option value="Gambia">Gambia</option>
                                    <option value="Georgia">Georgia</option>
                                    <option value="Germany">Germany</option>
                                    <option value="Ghana">Ghana</option>
                                    <option value="Greece">Greece</option>
                                    <option value="Guatemala">Guatemala</option>
                                    <option value="Guinea">Guinea</option>
                                    <option value="Guyana">Guyana</option>
                                    <option value="Haiti">Haiti</option>
                                    <option value="Honduras">Honduras</option>
                                    <option value="Hong Kong">Hong Kong</option>
                                    <option value="Hungary">Hungary</option>
                                    <option value="Iceland">Iceland</option>
                                    <option value="India">India</option>
                                    <option value="Indonesia">Indonesia</option>
                                    <option value="Iran">Iran</option>
                                    <option value="Iraq">Iraq</option>
                                    <option value="Ireland">Ireland</option>
                                    <option value="Israel">Israel</option>
                                    <option value="Italy">Italy</option>
                                    <option value="Jamaica">Jamaica</option>
                                    <option value="Japan">Japan</option>
                                    <option value="Jordan">Jordan</option>
                                    <option value="Kazakhstan">Kazakhstan</option>
                                    <option value="Kenya">Kenya</option>
                                    <option value="Kuwait">Kuwait</option>
                                    <option value="Kyrgyzstan">Kyrgyzstan</option>
                                    <option value="Laos">Laos</option>
                                    <option value="Latvia">Latvia</option>
                                    <option value="Lebanon">Lebanon</option>
                                    <option value="Lesotho">Lesotho</option>
                                    <option value="Liberia">Liberia</option>
                                    <option value="Libya">Libya</option>
                                    <option value="Lithuania">Lithuania</option>
                                    <option value="Luxembourg">Luxembourg</option>
                                    <option value="Madagascar">Madagascar</option>
                                    <option value="Malawi">Malawi</option>
                                    <option value="Malaysia">Malaysia</option>
                                    <option value="Maldives">Maldives</option>
                                    <option value="Mali">Mali</option>
                                    <option value="Malta">Malta</option>
                                    <option value="Mauritania">Mauritania</option>
                                    <option value="Mauritius">Mauritius</option>
                                    <option value="Mexico">Mexico</option>
                                    <option value="Moldova">Moldova</option>
                                    <option value="Monaco">Monaco</option>
                                    <option value="Mongolia">Mongolia</option>
                                    <option value="Montenegro">Montenegro</option>
                                    <option value="Morocco">Morocco</option>
                                    <option value="Mozambique">Mozambique</option>
                                    <option value="Myanmar">Myanmar</option>
                                    <option value="Namibia">Namibia</option>
                                    <option value="Nepal">Nepal</option>
                                    <option value="Netherlands">Netherlands</option>
                                    <option value="New Zealand">New Zealand</option>
                                    <option value="Nicaragua">Nicaragua</option>
                                    <option value="Niger">Niger</option>
                                    <option value="Nigeria">Nigeria</option>
                                    <option value="North Korea">North Korea</option>
                                    <option value="North Macedonia">North Macedonia</option>
                                    <option value="Norway">Norway</option>
                                    <option value="Oman">Oman</option>
                                    <option value="Pakistan">Pakistan</option>
                                    <option value="Panama">Panama</option>
                                    <option value="Papua New Guinea">Papua New Guinea</option>
                                    <option value="Paraguay">Paraguay</option>
                                    <option value="Peru">Peru</option>
                                    <option value="Philippines">Philippines</option>
                                    <option value="Poland">Poland</option>
                                    <option value="Portugal">Portugal</option>
                                    <option value="Qatar">Qatar</option>
                                    <option value="Romania">Romania</option>
                                    <option value="Russia">Russia</option>
                                    <option value="Rwanda">Rwanda</option>
                                    <option value="Saudi Arabia">Saudi Arabia</option>
                                    <option value="Senegal">Senegal</option>
                                    <option value="Serbia">Serbia</option>
                                    <option value="Seychelles">Seychelles</option>
                                    <option value="Singapore">Singapore</option>
                                    <option value="Slovakia">Slovakia</option>
                                    <option value="Slovenia">Slovenia</option>
                                    <option value="Somalia">Somalia</option>
                                    <option value="South Africa">South Africa</option>
                                    <option value="South Korea">South Korea</option>
                                    <option value="Spain">Spain</option>
                                    <option value="Sri Lanka">Sri Lanka</option>
                                    <option value="Sudan">Sudan</option>
                                    <option value="Suriname">Suriname</option>
                                    <option value="Sweden">Sweden</option>
                                    <option value="Switzerland">Switzerland</option>
                                    <option value="Syria">Syria</option>
                                    <option value="Taiwan">Taiwan</option>
                                    <option value="Tajikistan">Tajikistan</option>
                                    <option value="Tanzania">Tanzania</option>
                                    <option value="Thailand">Thailand</option>
                                    <option value="Togo">Togo</option>
                                    <option value="Trinidad and Tobago">Trinidad and Tobago</option>
                                    <option value="Tunisia">Tunisia</option>
                                    <option value="Turkey">Turkey</option>
                                    <option value="Turkmenistan">Turkmenistan</option>
                                    <option value="Uganda">Uganda</option>
                                    <option value="Ukraine">Ukraine</option>
                                    <option value="United Arab Emirates">United Arab Emirates</option>
                                    <option value="United Kingdom">United Kingdom</option>
                                    <option value="United States">United States</option>
                                    <option value="Uruguay">Uruguay</option>
                                    <option value="Uzbekistan">Uzbekistan</option>
                                    <option value="Venezuela">Venezuela</option>
                                    <option value="Vietnam">Vietnam</option>
                                    <option value="Yemen">Yemen</option>
                                    <option value="Zambia">Zambia</option>
                                    <option value="Zimbabwe">Zimbabwe</option>
                                </select>
                            </div>


                        </div>
                    </form>
                </div>

                <!-- Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Back</button>
                    <button type="submit" form="customerDetailsForm" class="btn btn-success fw-bold">
                        Confirm Booking
                    </button>
                </div>
            </div>
        </div>
    </div>


@endsection



<!-- JS Logic -->
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

        let selectedPrice = 0;
        let selectedType = "";

        optionCards.forEach(card => {
            card.addEventListener("click", () => {
                // Highlight selection
                optionCards.forEach(c => c.classList.remove("border-warning", "bg-warning-subtle"));
                card.classList.add("border-warning", "bg-warning-subtle");

                // Get values
                selectedPrice = parseFloat(card.getAttribute("data-price"));
                selectedType = card.getAttribute("data-type");

                // Show sections
                dateSection.classList.remove("d-none");
                quantitySection.classList.remove("d-none");
                totalPriceDiv.classList.remove("d-none");
                rentalPeriodDiv.classList.remove("d-none");

                // Populate quantity dropdown
                let max = selectedType === "day" ? 6 : (selectedType === "week" ? 4 : 4);
                let label = selectedType === "day" ? "Number of Days" :
                    selectedType === "week" ? "Number of Weeks" : "Number of Months";

                quantityLabel.textContent = label;
                quantitySelect.innerHTML = "";
                for (let i = 1; i <= max; i++) {
                    let opt = document.createElement("option");
                    opt.value = i;
                    opt.textContent = i + " " + label.slice(9); // Days/Weeks/Months
                    quantitySelect.appendChild(opt);
                }

                calculateTotal();
            });
        });

        quantitySelect.addEventListener("change", calculateTotal);
        rentalStartDate.addEventListener("change", calculateTotal);

        function calculateTotal() {
            const qty = parseInt(quantitySelect.value) || 1;
            const total = qty * selectedPrice;
            totalPriceDiv.textContent = "Total Price: R" + total.toLocaleString();

            // Calculate rental period
            const startDate = rentalStartDate.value;
            if (startDate) {
                let endDate = new Date(startDate);
                if (selectedType === "day") {
                    endDate.setDate(endDate.getDate() + qty);
                } else if (selectedType === "week") {
                    endDate.setDate(endDate.getDate() + (qty * 7));
                } else if (selectedType === "month") {
                    endDate.setMonth(endDate.getMonth() + qty);
                }

                rentalPeriodDiv.textContent =
                    "Rental Period: " + formatDate(new Date(startDate)) + " → " + formatDate(endDate);
            } else {
                rentalPeriodDiv.textContent = "Select a start date to see rental period.";
            }
        }

        function formatDate(date) {
            return date.toLocaleDateString("en-GB", {
                day: "2-digit", month: "short", year: "numeric"
            });
        }
    });


    document.addEventListener("DOMContentLoaded", function () {
        const confirmBtn = document.getElementById("confirmPurchaseBtn");

        confirmBtn.addEventListener("click", function () {
            const name = document.getElementById("buyerName").value;
            const email = document.getElementById("buyerEmail").value;
            const phone = document.getElementById("buyerPhone").value;

            if (!name || !email || !phone) {
                alert("Please fill in all fields before confirming.");
                return;
            }

            // For now just simulate confirmation
            alert("Thank you " + name + "! Your purchase request for {{ $vehicle->name }} has been submitted.");
            const modal = bootstrap.Modal.getInstance(document.getElementById("purchaseModal"));
            modal.hide();
        });
    });
</script>