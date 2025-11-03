@extends('adminlte::page')

@section('title', 'Customers')

@section('content_header')
    <h1 class="container text-bold">Customers Detail</h1>
    <hr>
@stop

@section('content')
    {{-- Header actions --}}
    <div class="container d-flex justify-content-between align-items-center">
        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Customers
        </a>

        <form action="{{ route('customers.destroy', $customer->id) }}" method="POST"
            onsubmit="return confirm('Delete this customer? This cannot be undone.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash-alt"></i> Delete Customer
            </button>
        </form>
    </div>

    {{-- Customer details + stats --}}
    <div class="container mt-4">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white border-0">
                <h3 class="card-title text-bold">Customer Details</h3>
            </div>
            <div class="card-body">
                <div class="row g-4 border py-3 bg-light">
                    {{-- Customer form --}}
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border bg-light">
                            <form id="customer-update-form" action="{{ route('customers.update', $customer->id) }}"
                                method="POST">
                                @csrf
                                @method('PATCH')

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="customer_name" class="form-label"><strong>Name:</strong></label>
                                        <input type="text" id="customer_name" name="name" class="form-control"
                                            value="{{ old('name', $customer->name) }}" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="customer_email" class="form-label"><strong>Email:</strong></label>
                                        <input type="email" id="customer_email" name="email" class="form-control"
                                            value="{{ old('email', $customer->email) }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="customer_phone" class="form-label"><strong>Phone:</strong></label>
                                        <input type="text" id="customer_phone" name="phone" class="form-control"
                                            value="{{ old('phone', $customer->phone) }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label"><strong>Customer Address:</strong></label>
                                        <input type="text" id="customer_country" name="country" class="form-control"
                                            placeholder="Start typing address..."
                                            value="{{ old('country', $customer->country) }}" autocomplete="off">
                                        <small class="text-muted">Select an address from suggestions</small>
                                    </div>

                                    <div class="col-md-12 mt-3">
                                        <label for="customer_notes" class="form-label"><strong>Notes:</strong></label>
                                        <textarea id="customer_notes" name="notes" class="form-control" rows="3"
                                            placeholder="Enter any notes about this customer...">{{ old('notes', $customer->notes) }}</textarea>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-3">
                                    <button type="submit" class="btn btn-sm py-2 btn-success">
                                        <i class="fas fa-save"></i> Update Customer
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Stats --}}
                    <div class="col-md-6 mt-2">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="p-3 text-center mb-2 py-4 text-white rounded" style="background-color:#6dce12">
                                    <h6 class="mb-1">Bookings</h6>
                                    <h4 class="fw-bold">{{ $customer->bookings_count ?? 0 }}</h4>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 text-center py-4 text-white rounded" style="background-color:#1f2eb4">
                                    <h6 class="mb-1">Total Paid (Bookings)</h6>
                                    <h4 class="fw-bold">R{{ number_format($customer->total_booking_price ?? 0, 2) }}</h4>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 text-center py-4 bg-warning text-dark rounded">
                                    <h6 class="mb-1">Total Deposits</h6>
                                    <h4 class="fw-bold">R{{ number_format($customer->total_purchase_deposit ?? 0, 2) }}
                                    </h4>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 text-center py-4 bg-success text-white rounded">
                                    <h6 class="mb-1">Total Payable (Purchases)</h6>
                                    <h4 class="fw-bold">R{{ number_format($customer->total_purchase_price ?? 0, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> {{-- row --}}
            </div>
        </div>
    </div>

    {{-- Booking History --}}
    <div class="container mt-4">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white border-0">
                <h3 class="card-title text-bold">Booking History</h3>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    @forelse($bookings as $booking)
                        @php
                            // Other bookings for the same equipment (excluding this booking)
                            $bookedRanges = \App\Models\Booking::where('equipment_id', $booking->equipment_id)
                                ->where('id', '!=', $booking->id)
                                ->get()
                                ->map(fn($b) => ['from' => $b->start_date, 'to' => $b->end_date])
                                ->values();
                        @endphp


                        <div class="col-md-12">
                            <div class="p-3 border rounded-3 bg-white shadow-sm h-100">
                                <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
                                    <h6 class="text-bold text-black mb-0">
                                        {{ optional($booking->equipment)->name ?? 'N/A' }}
                                    </h6>


                                    {{-- Status dropdown --}}
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge booking-status-badge" data-badge-for="{{ $booking->id }}"
                                            @class([
                                                'bg-success' => $booking->status === 'completed',
                                                'bg-warning text-dark' => $booking->status === 'pending',
                                                'bg-danger' => $booking->status === 'canceled',
                                                'bg-primary' => $booking->status === 'confirmed',
                                                'bg-info' => $booking->status === 'ongoing',
                                            ])>
                                            {{ ucfirst($booking->status) }}
                                        </span>

                                        <form class="booking-status-form" data-booking-id="{{ $booking->id }}"
                                            data-url="{{ route('customers.bookings.updateStatus', $booking->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <select name="status"
                                                class="form-select form-select-sm border-0 bg-light rounded-2 shadow-sm px-2 py-1">
                                                @foreach (['pending', 'confirmed', 'completed', 'canceled'] as $st)
                                                    <option value="{{ $st }}" @selected($booking->status === $st)>
                                                        {{ ucfirst($st) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </form>
                                    </div>
                                </div>

                                {{-- Dates form --}}
                                {{-- Dates form --}}
                                <form class="booking-dates-form" data-booking-id="{{ $booking->id }}"
                                    data-url="{{ route('customers.bookings.updateDates', $booking->id) }}"
                                    data-disabled-dates='@json($bookedRanges)'
                                    data-daily-rate="{{ optional($booking->equipment)->daily_price ?? 0 }}"
                                    data-stock="{{ optional($booking->equipment)->stock ?? 0 }}">

                                    @csrf
                                    @method('PATCH')

                                    <div class="row g-2 align-items-center">
                                        <div class="col-md-6">
                                            <label class="form-label mb-1"><strong>Start Date:</strong></label>
                                            <input type="text" name="start_date" value="{{ $booking->start_date }}"
                                                class="form-control form-control-sm booking-start-date"
                                                placeholder="Select start date" autocomplete="off" readonly>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label mb-1"><strong>End Date:</strong></label>
                                            <input type="text" name="end_date" value="{{ $booking->end_date }}"
                                                class="form-control form-control-sm booking-end-date"
                                                placeholder="Select end date" autocomplete="off" readonly>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label mb-1 mt-1"><strong>Admin Note For
                                                    Booking:</strong></label>
                                            <input type="text" name="admin_note" class="form-control form-control-sm"
                                                placeholder="Enter a note about this booking"
                                                value="{{ old('admin_note', $booking->admin_note) }}">
                                            @error('admin_note')
                                                <span class="text-danger small">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label mb-1 mt-1"><strong>Total Price:</strong></label>
                                            <input type="text" class="form-control form-control-sm booking-total-price"
                                                disabled value="R{{ number_format($booking->total_price, 2) }}">
                                        </div>

                                      @php
    $availableStock = \App\Models\EquipmentStock::where('equipment_id', $booking->equipment_id)
        ->where('location_id', $booking->location_id)
        ->value('stock') ?? 0;
@endphp

<div class="col-md-3">
    <label class="form-label mb-1 mt-1"><strong>Available Stock:</strong></label>
    <input type="text" class="form-control form-control-sm" disabled value="{{ $availableStock }}">
</div>

                                    </div>

                                    <div class="d-flex justify-content-end mt-2">
                                        <button type="submit" class="btn btn-sm py-2 btn-success">
                                            <i class="fas fa-save"></i> Update Dates
                                        </button>
                                    </div>
                                </form>

                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center text-muted">No booking history available.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Purchase History --}}
    <div class="container mt-4">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white border-0">
                <h3 class="card-title text-bold">Purchase History</h3>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    @forelse($purchases as $purchase)
                        <div class="col-md-12">
                            <div class="p-3 border rounded-3 bg-white shadow-sm h-100">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="text-bold text-black mb-0">
                                        {{ optional($booking->equipment)->name ?? 'N/A' }}
                                    </h6>
                                    <span class="badge bg-primary">Purchase</span>
                                </div>

                                <p class="mb-1 text-muted"><strong>Purchased On:</strong>
                                    {{ $purchase->created_at->format('Y-m-d') }}</p>
                                <p class="mb-1 text-muted"><strong>Total Price:</strong>
                                    R{{ number_format($purchase->total_price, 2) }}</p>
                                <p class="mb-1 text-muted"><strong>Deposit Paid:</strong>
                                    R{{ number_format($purchase->deposit_paid ?? 0, 2) }}</p>
                                <p class="mb-0 text-muted"><strong>Payment Method:</strong>
                                    {{ ucfirst($purchase->payment_method ?? 'N/A') }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center text-muted">No purchase history available.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Email Log History --}}
    <div class="container mt-4">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <h3 class="card-title text-bold mb-0">Email Log History</h3>
                {{-- <span class="badge bg-dark">{{ count($emailLogs) }} total</span> --}}
            </div>

            <div class="card-body  email-log-scroll">
                {{-- Email log entries --}}
                @if ($emailLogs->isEmpty())
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p class="mb-0">No emails sent to this customer please.</p>
                    </div>
                @else
                    @foreach ($emailLogs as $log)
                        @php $isSender = optional($log->sender)->id === auth()->id(); @endphp

                        <div class="d-flex {{ $isSender ? 'justify-content-end' : 'justify-content-start' }} mb-3">
                            <div class="d-flex {{ $isSender ? 'flex-row-reverse' : 'flex-row' }} align-items-start"
                                style="max-width: 80%;">
                                {{-- Avatar --}}
                                <div class="mx-2">
                                    <div class="rounded-circle d-flex justify-content-center align-items-center text-white"
                                        style="width: 40px; height: 40px;
                                         background-color: {{ $isSender ? '#007bff' : '#6c757d' }};
                                         font-weight: bold;">
                                        {{ strtoupper(substr(optional($log->sender)->name ?? 'S', 0, 1)) }}
                                    </div>
                                </div>

                                {{-- Message bubble --}}
                                <div class="p-3 rounded-3 shadow-sm"
                                    style="background-color: {{ $isSender ? '#f2f2f2' : '#ffffff' }};
                                            border: 1px solid #dee2e6; min-width: 250px;">
                                    <div class="d-flex justify-content-between mb-1">
                                        <strong class="text-dark">{{ $log->subject }}</strong>
                                        <small class="text-muted">
                                            {{ $log->sent_at ? \Carbon\Carbon::parse($log->sent_at)->format('Y-m-d H:i') : '-' }}
                                        </small>
                                    </div>

                                    <div class="text-secondary" style="font-size: 0.9rem;">
                                        {!! $log->body !!}
                                    </div>

                                    <div class="mt-2 text-end">
                                        <small class="text-muted">— {{ optional($log->sender)->name ?? 'System' }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
@stop

@section('css')

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        table.table-hover tbody tr:hover {
            background-color: rgba(255, 193, 7, 0.1);
            transition: background-color 0.2s ease-in-out;
        }


        .email-log-scroll {
            max-height: 500px;
            overflow-y: auto;
            background-color: #f9fafb;
        }

        .email-log-scroll::-webkit-scrollbar {
            width: 8px;
        }

        .email-log-scroll::-webkit-scrollbar-thumb {
            background-color: #c7c9cc;
            border-radius: 4px;
        }

        .email-log-scroll::-webkit-scrollbar-thumb:hover {
            background-color: #a6a8ab;
        }

        .booking-start-date,
        .booking-end-date {
            background-color: #ffffff !important;
            color: #000 !important;
        }

        .flatpickr-input[readonly] {
            background-color: #ffffff !important;
            color: #000 !important;
            cursor: pointer;
        }

        /* Gray out disabled (booked) dates */
        .flatpickr-day.disabled,
        .flatpickr-day.disabled:hover {
            background: #e0e0e0 !important;
            color: #999 !important;
            cursor: not-allowed !important;
        }


        .flatpickr-day.selected,
        .flatpickr-day.startRange,
        .flatpickr-day.endRange {
            background: #ffffff !important;
            border: 2px solid #007bff !important;
            color: #000 !important;
        }

        .flatpickr-day:not(.disabled):hover {
            background: #cce5ff !important;
            color: #000 !important;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps.key') }}&libraries=places&callback=initCustomerAutocomplete"
        defer></script>

    <script>
        (function() {
            document.addEventListener('focusin', (e) => {
                const sel = e.target.closest('.booking-status-form select[name="status"]');
                if (!sel) return;
                sel.dataset.prev = sel.value;
            });

            document.addEventListener('change', async (e) => {
                const sel = e.target.closest('.booking-status-form select[name="status"]');
                if (!sel) return;

                const form = sel.closest('.booking-status-form');
                const url = form.getAttribute('data-url');
                const bookingId = form.getAttribute('data-booking-id');
                const newStatus = sel.value;
                const prevValue = sel.dataset.prev ?? sel.value;

                const revertSelect = (reason = null) => {
                    sel.value = prevValue;
                    if (window.Swal && reason) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Not updated',
                            text: reason,
                            timer: 1600,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false
                        });
                    }
                };

                const nice = (s) => s.charAt(0).toUpperCase() + s.slice(1);
                const result = await Swal.fire({
                    title: 'Are you sure?',
                    text: `Change booking status to "${nice(newStatus)}"?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, update',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                });

                if (!result.isConfirmed) {
                    revertSelect('Update canceled.');
                    return;
                }

                try {
                    const res = await fetch(url, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            status: newStatus
                        }),
                    });

                    const data = await res.json();
                    if (!res.ok || !data.success) throw new Error(data.message || 'Update failed');

                    const badge = document.querySelector(
                        `.booking-status-badge[data-badge-for="${bookingId}"]`);
                    if (badge) {
                        const clsMap = {
                            completed: 'bg-success',
                            pending: 'bg-warning text-dark',
                            canceled: 'bg-danger',
                            confirmed: 'bg-primary',
                            ongoing: 'bg-info',
                        };
                        badge.textContent = (data.status || newStatus).replace(/^./, c => c.toUpperCase());
                        badge.className = 'badge booking-status-badge';
                        (clsMap[data.status] || 'bg-secondary').split(' ').forEach(c => badge.classList.add(
                            c));
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Updated',
                        text: 'Booking status changed successfully.',
                        toast: true,
                        position: 'top-end',
                        timer: 1400,
                        showConfirmButton: false
                    });

                } catch (err) {
                    revertSelect(err.message || 'Unable to update status.');
                }
            });
        })();

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('#customer-update-form');
            if (!form) return;

            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                const data = Object.fromEntries(new FormData(form).entries());

                const result = await Swal.fire({
                    title: 'Are you sure?',
                    text: 'Do you want to update the customer details?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, update',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                });
                if (!result.isConfirmed) return;

                try {
                    const res = await fetch(form.action, {
                        method: 'PATCH', // <- fixed to PATCH
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(data)
                    });

                    const resData = await res.json();
                    if (!res.ok || !resData.success) throw new Error(resData.message ||
                        'Update failed');

                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: 'Customer details updated successfully.',
                        toast: true,
                        position: 'top-end',
                        timer: 1500,
                        showConfirmButton: false
                    });

                } catch (err) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: err.message || 'Something went wrong.',
                        toast: true,
                        position: 'top-end',
                        timer: 2500,
                        showConfirmButton: false
                    });
                }
            });
        });

        /* =====================================================
           BOOKING DATES UPDATE (flatpickr + price + overlap)
        ===================================================== */
        document.addEventListener('DOMContentLoaded', function() {
          document.querySelectorAll('.booking-dates-form').forEach(form => {
    const startInput = form.querySelector('.booking-start-date');
    const endInput = form.querySelector('.booking-end-date');
    const priceField = form.querySelector('.booking-total-price');
    const stockField = form.querySelector('.booking-available-stock');
    const disabledRanges = JSON.parse(form.dataset.disabledDates || '[]');
    const dailyRate = parseFloat(form.dataset.dailyRate || 0);
    const bookingId = form.dataset.bookingId;
    let availableStock = parseInt(form.dataset.stock || 0);

    // Build array of fully booked (stock=0) dates
    const fullyBookedDates = disabledRanges.map(range => ({
        from: range.from,
        to: range.to
    }));

    const startPicker = flatpickr(startInput, {
        dateFormat: "Y-m-d",
        disable: fullyBookedDates,
        onChange: updatePrice
    });

    const endPicker = flatpickr(endInput, {
        dateFormat: "Y-m-d",
        disable: fullyBookedDates,
        onChange: updatePrice
    });

    function updatePrice() {
        if (!startInput.value || !endInput.value) return;
        const s = new Date(startInput.value);
        const e = new Date(endInput.value);
        const days = Math.max(1, (e - s) / (1000 * 60 * 60 * 24) + 1);
        priceField.value = "R" + (days * dailyRate).toFixed(2);
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const payload = {
            start_date: startInput.value,
            end_date: endInput.value,
            admin_note: form.querySelector('[name="admin_note"]').value,
            booked_stock: 1 // you can make this dynamic if selecting quantity
        };

        try {
            const res = await fetch(form.dataset.url, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const json = await res.json();

            if (!json.success) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: json.message
                });
                return;
            }

            // Update live stock
            if (stockField) stockField.value = json.available;

            Swal.fire({
                icon: 'success',
                title: 'Updated!',
                text: `Booking updated successfully. Remaining stock: ${json.available}`,
                toast: true,
                position: 'top-end',
                timer: 1800,
                showConfirmButton: false
            });
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message
            });
        }
    });
});

        });


        window.initCustomerAutocomplete = function() {
            const input = document.getElementById("customer_country");
            if (!input) return;
            const autocomplete = new google.maps.places.Autocomplete(input, {
                fields: ["formatted_address"],
                componentRestrictions: {
                    country: "ZA"
                } // 🇿🇦 change/remove as needed
            });
            autocomplete.addListener("place_changed", () => {
                const place = autocomplete.getPlace();
                if (place.formatted_address) {
                    input.value = place.formatted_address;
                }
            });
        };





        document.querySelectorAll('.booking-dates-form').forEach(form => {
    const startInput = form.querySelector('.booking-start-date');
    const endInput = form.querySelector('.booking-end-date');
    const priceField = form.querySelector('.booking-total-price');
    const stockField = form.querySelector('.booking-available-stock');
    const disabledRanges = JSON.parse(form.dataset.disabledDates || '[]');
    const dailyRate = parseFloat(form.dataset.dailyRate || 0);
    let availableStock = parseInt(form.dataset.stock || 0);

    const startPicker = flatpickr(startInput, {
        dateFormat: "Y-m-d",
        disable: disabledRanges,
        onChange: updatePrice,
    });

    const endPicker = flatpickr(endInput, {
        dateFormat: "Y-m-d",
        disable: disabledRanges,
        onChange: updatePrice,
    });

    function updatePrice() {
        if (!startInput.value || !endInput.value) return;
        const s = new Date(startInput.value);
        const e = new Date(endInput.value);
        const days = (e - s) / (1000 * 60 * 60 * 24) + 1;
        if (days < 1) return;
        priceField.value = "R" + (days * dailyRate).toFixed(2);
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const data = {
            start_date: startInput.value,
            end_date: endInput.value,
            admin_note: form.querySelector('[name="admin_note"]').value
        };

        const res = await fetch(form.dataset.url, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(data)
        });

        const json = await res.json();
        if (json.success) {
            stockField.value = json.available; // ✅ update stock live
            Swal.fire({
                icon: 'success',
                title: 'Updated',
                text: `Stock left: ${json.available}`,
                timer: 1500,
                toast: true,
                showConfirmButton: false,
            });
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: json.message });
        }
    });
});

    </script>
@stop
