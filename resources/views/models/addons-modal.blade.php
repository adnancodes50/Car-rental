<!-- Add-Ons Modal -->
<div class="modal fade" id="addonsModal" tabindex="-1" aria-labelledby="addonsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content rounded-4 shadow-lg">

            <div class="modal-header">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="addonsModalLabel">
                    <i class="bi bi-plus-circle"></i> Select Add-Ons
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="text-center mb-4">
                    <h2 class="h4 fw-bold text-dark mb-2">Enhance Your Adventure</h2>
                    <p class="text-muted">Choose optional equipment for your booking</p>
                </div>

                @foreach($addOns as $addOn)
                    <div class="d-flex align-items-center justify-content-between border rounded p-3 mb-3 shadow-sm">
                        <!-- Image -->
                        <div class="me-3">
                            <img src="{{ $addOn->image_url }}" alt="{{ $addOn->name }}" class="rounded border"
                                 style="width:60px; height:60px; object-fit:cover;">
                        </div>

                        <!-- Content -->
                        <div class="flex-grow-1">
                            <h5 class="fw-semibold mb-1">{{ $addOn->name }}</h5>
                            <p class="text-muted mb-1">{{ $addOn->description }}</p>
                            <p class="small text-muted mb-1">
                                R{{ $addOn->price_day }}/day • R{{ $addOn->price_week }}/week • R{{ $addOn->price_month }}/month
                            </p>
                        </div>

                        <!-- Dropdown -->
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

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" 
                        data-bs-target="#bookingModal" data-bs-toggle="modal" data-bs-dismiss="modal">
                    Back
                </button>
                <!-- Step 3 -->
                <button type="button" class="btn btn-primary"
                        data-bs-target="#customerDetailsModal" data-bs-toggle="modal" data-bs-dismiss="modal">
                    Continue to Details
                </button>
            </div>
        </div>
    </div>
</div>
