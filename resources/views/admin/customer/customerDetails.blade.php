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
                            $locationOptions = collect($booking->location_options ?? []);
                            $locationOptionsMap = $locationOptions->mapWithKeys(function ($option) {
                                $id = (string) ($option['id'] ?? '');
                                if ($id === '') {
                                    return [];
                                }
                                return [
                                    $id => [
                                        'name' => $option['name'] ?? 'Location',
                                        'stock' => (int) ($option['stock'] ?? 0),
                                    ],
                                ];
                            });
                            $locationBookingMap = $booking->location_booking_map ?? [];
                            $locationFullyBooked = $booking->location_fully_booked ?? [];
                            $globalFullyBooked = $booking->global_fully_booked ?? [];
                            $initialAvailableStock = $booking->initial_available_stock ?? null;

                            // Get current location stock for initial display
                            $currentLocationStock = 0;
                            if ($booking->location_id) {
                                $currentLocation = $locationOptions->firstWhere('id', $booking->location_id);
                                $currentLocationStock = $currentLocation['stock'] ?? 0;
                            }
                        @endphp

                        <div class="col-md-12">
                            <div class="p-3 border rounded-3 bg-white shadow-sm h-100">
                                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
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
                                <form class="booking-dates-form" data-booking-id="{{ $booking->id }}"
                                    data-url="{{ route('customers.bookings.updateDates', $booking->id) }}"
                                    data-daily-rate="{{ optional($booking->equipment)->daily_price ?? 0 }}"
                                    data-location-options='@json($locationOptionsMap)'
                                    data-location-bookings='@json($locationBookingMap)'
                                    data-location-fully='@json($locationFullyBooked)'
                                    data-global-fully='@json($globalFullyBooked)'
                                    data-current-location="{{ $booking->location_id ?? '' }}"
                                    data-booked-stock="{{ (int) ($booking->booked_stock ?? 1) }}"
                                    data-initial-available="{{ $initialAvailableStock ?? '' }}">

                                    @csrf
                                    @method('PATCH')


                                    {{-- Dates and Price Section --}}
                                    <div class="dates-price-section mb-4 p-3 bg-light rounded-3">

                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="form-label text-dark fw-semibold">Location</label>
                                                    <select name="location_id"
                                                        class="form-control booking-location-select"
                                                        {{ $locationOptions->isEmpty() ? 'disabled' : '' }}>
                                                        @if ($locationOptions->isEmpty())
                                                            <option value="">No locations available</option>
                                                        @else
                                                            <option value="">Select location</option>
                                                            @foreach ($locationOptions as $option)
                                                                <option value="{{ $option['id'] }}"
                                                                    data-stock="{{ $option['stock'] }}"
                                                                    @selected((string) $booking->location_id === (string) $option['id'])>
                                                                    {{ $option['name'] }} ({{ $option['stock'] }} in
                                                                    stock)
                                                                </option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="form-label text-dark fw-semibold">Booked Stock</label>
                                                    <select name="booked_stock" class="form-control booking-stock-select"
                                                        {{ $locationOptions->isEmpty() ? 'disabled' : '' }}>
                                                        <option value="{{ (int) ($booking->booked_stock ?? 1) }}"
                                                            selected>
                                                            {{ (int) ($booking->booked_stock ?? 1) }}
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <label class="form-label text-dark fw-semibold">Available</label>
                                                    <input type="text"
                                                        class="form-control booking-available-stock text-center fw-bold"
                                                        value="{{ $initialAvailableStock ?? '' }}" readonly
                                                        style="background-color: #e8f5e8 !important; color: #2e7d32; border: 1px solid #c8e6c9;">
                                                </div>
                                            </div>

                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label class="form-label text-dark fw-semibold">In Stock</label>
                                                    <input type="text"
                                                        class="form-control booking-total-stock bg-light text-center"
                                                        value="{{ $currentLocationStock }}" readonly
                                                        style="background-color: #f5f5f5 !important;">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="form-label text-dark fw-semibold">Start Date</label>
                                                    <input type="text" name="start_date"
                                                        value="{{ $booking->start_date }}"
                                                        class="form-control booking-start-date"
                                                        placeholder="Select start date" autocomplete="off" readonly>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="form-label text-dark fw-semibold">End Date</label>
                                                    <input type="text" name="end_date"
                                                        value="{{ $booking->end_date }}"
                                                        class="form-control booking-end-date"
                                                        placeholder="Select end date" autocomplete="off" readonly>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="form-label text-dark fw-semibold">Total Price</label>
                                                    <input type="text" class="form-control booking-total-price fw-bold"
                                                        disabled value="R{{ number_format($booking->total_price, 2) }}"
                                                        style="background-color: #e3f2fd !important; color: #1565c0; border: 1px solid #bbdefb;">
                                                </div>
                                            </div>

                                        </div>

                                        <div class="form-group">
                                            <label class="form-label text-dark fw-semibold">Admin Note For Booking</label>
                                            <textarea name="admin_note" class="form-control" rows="3"
                                                placeholder="Enter a note or comment about this booking">{{ old('admin_note', $booking->admin_note) }}</textarea>
                                            @error('admin_note')
                                                <span class="text-danger small">{{ $message }}</span>
                                            @enderror
                                        </div>



                                        <div class="d-flex justify-content-end mt-4">
                                            <button type="submit" class="btn btn-success px-4 py-2">
                                                <i class="fas fa-save me-2"></i> Update Booking
                                            </button>
                                        </div>

                                    </div>




                                </form>

                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center text-muted py-4">
                            <i class="fas fa-calendar-times fa-2x mb-3"></i>
                            <p class="mb-0">No booking history available.</p>
                        </div>
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
                                        {{-- {{ optional($booking->equipment)->name ?? 'N/A' }} --}}
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
            <div class="card-header bg-white text-black d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Email Log History</h5>

            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 30%">Subject</th>
                                <th>Message Body</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($emailLogs as $log)
                                <tr>
                                    {{-- Subject Column --}}
                                    <td class="align-top">
                                        <strong>{{ $log->subject }}</strong>
                                    </td>

                                    {{-- Message Column --}}
                                    <td>
                                        <div class="message-box p-3 rounded border bg-white position-relative">
                                            {{-- Sender & Receiver --}}
                                            <div class="d-flex justify-content-between mb-2">
                                                <div>
                                                    <span class="fw-bold text-primary">From:</span>
                                                    {{ $log->sender->name ?? 'System / Admin' }}

                                                    <span
                                                        class="text-muted small">({{ $log->sender->email ?? 'N/A' }})</span>

                                                </div>
                                                <div>
                                                    <span class="fw-bold text-success">To:</span>
                                                    {{ $log->customer->name ?? 'Unknown' }}
                                                    <span
                                                        class="text-muted small">({{ $log->customer->email ?? 'N/A' }})</span>
                                                </div>
                                            </div>

                                            {{-- Message Body --}}
                                            <div
                                                class="message-content border-top border-bottom py-3 my-2 bg-light px-3 rounded">
                                                {!! $log->body !!}
                                            </div>

                                            {{-- Timestamp Bottom Right --}}
                                            <div class="text-end text-muted small">
                                                <i class="far fa-clock me-1"></i>
                                                {{ \Carbon\Carbon::parse($log->sent_at)->format('d M Y, h:i A') }}
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center py-4">
                                        <i class="fas fa-envelope-open-text fa-2x mb-2 text-muted"></i><br>
                                        No email logs found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
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

        /* Improved form styling */
        .booking-dates-form .form-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: block;
        }

        .booking-dates-form .form-control,
        .booking-dates-form .form-select {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 0.5rem 0.75rem;
            width: 100%;
        }

        .booking-dates-form .form-control:focus,
        .booking-dates-form .form-select:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        /* Section styling */
        .location-stock-section,
        .dates-price-section {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
        }

        .location-stock-section h6,
        .dates-price-section h6 {
            color: #495057;
            font-size: 0.95rem;
        }

        /* Form group for consistent spacing */
        .form-group {
            margin-bottom: 0;
        }

        /* Improved dropdown styling */
        .booking-location-select,
        .booking-stock-select {
            height: auto;
            min-height: 38px;
        }

        .booking-available-stock {
            font-weight: 600;
            text-align: center;
        }

        .booking-total-price {
            font-weight: 600;
            text-align: center;
        }

        /* Better spacing */
        .booking-dates-form .row {
            margin-bottom: 0;
        }

        .booking-dates-form .form-group {
            margin-bottom: 1rem;
        }

        /* Status badge improvements */
        .booking-status-badge {
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
        }

        /* Button improvements */
        .btn-success {
            font-weight: 500;
        }

        /* Ensure all form controls have consistent appearance */
        select.form-control {
            appearance: auto;
            -webkit-appearance: menulist;
            -moz-appearance: menulist;
            appearance: menulist;
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
                        const statusForDisplay = (data.status || newStatus || '').toLowerCase();
                        badge.textContent = statusForDisplay.replace(/^./, c => c.toUpperCase());
                        badge.className = 'badge booking-status-badge';
                        (clsMap[statusForDisplay] || 'bg-secondary').split(' ').forEach(c => badge.classList
                            .add(
                                c));
                    }

                    sel.dataset.prev = data.status || newStatus;

                    Swal.fire({
                        icon: 'success',
                        title: 'Updated',
                        text: data.message || 'Booking status changed successfully.',
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
                        method: 'PATCH',
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
        document.addEventListener('DOMContentLoaded', () => {
            const parseDate = (value) => {
                if (!value) return null;
                const parts = value.split('-').map(Number);
                if (parts.length !== 3 || parts.some(Number.isNaN)) return null;
                return new Date(parts[0], parts[1] - 1, parts[2]);
            };

            const toYMD = (date) => {
                if (!(date instanceof Date) || Number.isNaN(date.getTime())) return '';
                const y = date.getFullYear();
                const m = String(date.getMonth() + 1).padStart(2, '0');
                const d = String(date.getDate()).padStart(2, '0');
                return `${y}-${m}-${d}`;
            };

            const rangesOverlap = (startA, endA, startB, endB) => {
                const aStart = parseDate(startA);
                const aEnd = parseDate(endA);
                const bStart = parseDate(startB);
                const bEnd = parseDate(endB);
                if (!aStart || !aEnd || !bStart || !bEnd) return false;
                return !(aEnd.getTime() < bStart.getTime() || bEnd.getTime() < aStart.getTime());
            };

            const isInRanges = (ranges, ymd) => {
                if (!Array.isArray(ranges) || !ymd) return false;
                return ranges.some(range => range && range.from && range.to && ymd >= range.from && ymd <= range
                    .to);
            };

            document.querySelectorAll('.booking-dates-form').forEach(form => {
                const startInput = form.querySelector('.booking-start-date');
                const endInput = form.querySelector('.booking-end-date');
                const priceField = form.querySelector('.booking-total-price');
                const noteField = form.querySelector('[name="admin_note"]');
                const locationSelect = form.querySelector('.booking-location-select');
                const stockSelect = form.querySelector('.booking-stock-select');
                const availableField = form.querySelector('.booking-available-stock');
                const totalStockField = form.querySelector('.booking-total-stock');
                const dailyRate = parseFloat(form.dataset.dailyRate || '0');
                const locationOptions = JSON.parse(form.dataset.locationOptions || '{}');
                const locationBookingMap = JSON.parse(form.dataset.locationBookings || '{}');
                const locationFully = JSON.parse(form.dataset.locationFully || '{}');
                const globalFully = JSON.parse(form.dataset.globalFully || '[]');
                const initialAvailable = form.dataset.initialAvailable;
                let selectedStock = parseInt(form.dataset.bookedStock || '1', 10) || 1;
                const todayYMD = toYMD(new Date());

                // Function to update total stock field
                function updateTotalStock() {
                    if (!totalStockField || !locationSelect) return;

                    const selectedOption = locationSelect.options[locationSelect.selectedIndex];
                    if (selectedOption && selectedOption.value) {
                        const stock = selectedOption.getAttribute('data-stock');
                        totalStockField.value = stock || '0';
                    } else {
                        totalStockField.value = '0';
                    }
                }

                if (locationSelect && !locationSelect.value && form.dataset.currentLocation) {
                    const desiredOption = locationSelect.querySelector(
                        `option[value="${form.dataset.currentLocation}"]`);
                    if (desiredOption) {
                        desiredOption.selected = true;
                    }
                }

                if (availableField && initialAvailable !== undefined && initialAvailable !== '') {
                    availableField.value = initialAvailable;
                }

                // Initialize total stock on page load
                updateTotalStock();

                const disableFn = (date) => {
                    const ymd = toYMD(date);
                    if (!ymd) return false;
                    if (ymd < todayYMD) return true;
                    if (isInRanges(globalFully, ymd)) return true;
                    const currentLocation = locationSelect?.value || form.dataset.currentLocation || '';
                    if (currentLocation && isInRanges(locationFully[currentLocation] || [], ymd)) {
                        return true;
                    }
                    return false;
                };

                const startPicker = flatpickr(startInput, {
                    dateFormat: 'Y-m-d',
                    disable: [disableFn],
                    onChange: () => {
                        updatePrice();
                        refreshAvailability();
                    },
                });

                const endPicker = flatpickr(endInput, {
                    dateFormat: 'Y-m-d',
                    disable: [disableFn],
                    onChange: () => {
                        updatePrice();
                        refreshAvailability();
                    },
                });

                form.dataset.currentLocation = form.dataset.currentLocation || (locationSelect?.value ||
                    '');

                function updatePrice() {
                    if (!priceField) return;
                    if (!startInput.value || !endInput.value) {
                        priceField.value = '';
                        return;
                    }
                    const start = parseDate(startInput.value);
                    const end = parseDate(endInput.value);
                    if (!start || !end) return;
                    const diff = (end.getTime() - start.getTime()) / (1000 * 60 * 60 * 24);
                    const days = Math.max(1, Math.floor(diff) + 1);
                    const units = selectedStock && selectedStock > 0 ? selectedStock : 0;
                    const total = days * dailyRate * units;
                    priceField.value = `R${total.toFixed(2)}`;
                }

                function computeAvailableUnits(locationId, startStr, endStr) {
                    if (!locationId || !locationOptions[locationId]) {
                        return 0;
                    }
                    const baseStock = parseInt(locationOptions[locationId].stock ?? 0, 10);
                    if (!startStr || !endStr) {
                        return baseStock;
                    }
                    const bookings = locationBookingMap[locationId] || [];
                    let reserved = 0;
                    bookings.forEach(booking => {
                        if (!booking?.from || !booking?.to) return;
                        if (rangesOverlap(startStr, endStr, booking.from, booking.to)) {
                            reserved += parseInt(booking.units ?? 1, 10);
                        }
                    });
                    const available = baseStock - reserved;
                    return available < 0 ? 0 : available;
                }

                function populateStockOptions(limit) {
                    if (!stockSelect) return;
                    stockSelect.innerHTML = '';
                    if (limit <= 0) {
                        const option = document.createElement('option');
                        option.value = '';
                        option.textContent = 'No stock available';
                        stockSelect.appendChild(option);
                        stockSelect.disabled = true;
                        selectedStock = 0;
                        form.dataset.bookedStock = '0';
                        updatePrice();
                        return;
                    }
                    for (let i = 1; i <= limit; i++) {
                        const option = document.createElement('option');
                        option.value = String(i);
                        option.textContent = String(i);
                        stockSelect.appendChild(option);
                    }
                    stockSelect.disabled = false;
                    if (!selectedStock || selectedStock > limit) {
                        selectedStock = Math.min(limit, Math.max(1, selectedStock || limit));
                    }
                    form.dataset.bookedStock = String(selectedStock);
                    stockSelect.value = String(selectedStock);
                    if (!stockSelect.value) {
                        stockSelect.value = '1';
                        selectedStock = 1;
                        form.dataset.bookedStock = '1';
                    }
                    updatePrice();
                }

                function refreshAvailability() {
                    const locationId = locationSelect ? locationSelect.value : '';
                    if (!locationId || !locationOptions[locationId]) {
                        if (stockSelect) {
                            stockSelect.innerHTML = '<option value="">Select location</option>';
                            stockSelect.disabled = true;
                        }
                        if (availableField) {
                            availableField.value = '';
                        }
                        selectedStock = 0;
                        form.dataset.bookedStock = '0';
                        updatePrice();
                        return;
                    }

                    const baseStock = parseInt(locationOptions[locationId].stock ?? 0, 10);
                    const startStr = startInput.value;
                    const endStr = endInput.value || startStr;
                    const limit = startStr && endStr ? computeAvailableUnits(locationId, startStr, endStr) :
                        baseStock;
                    populateStockOptions(limit);

                    if (availableField) {
                        const displayValue = startStr && endStr ? limit : baseStock;
                        availableField.value = displayValue >= 0 ? displayValue : 0;
                    }

                    updatePrice();
                }

                function ensureValidDates() {
                    if (startInput.value) {
                        const date = parseDate(startInput.value);
                        if (date && disableFn(date)) {
                            startPicker.clear();
                        }
                    }
                    if (endInput.value) {
                        const date = parseDate(endInput.value);
                        if (date && disableFn(date)) {
                            endPicker.clear();
                        }
                    }
                }

                if (locationSelect) {
                    locationSelect.addEventListener('change', () => {
                        selectedStock = 1;
                        form.dataset.bookedStock = '1';
                        form.dataset.currentLocation = locationSelect.value;
                        startPicker.set('disable', [disableFn]);
                        endPicker.set('disable', [disableFn]);
                        ensureValidDates();
                        refreshAvailability();
                        updateTotalStock(); // Update the total stock when location changes
                    });
                }

                if (stockSelect) {
                    stockSelect.addEventListener('change', () => {
                        selectedStock = parseInt(stockSelect.value || '1', 10) || 1;
                        form.dataset.bookedStock = selectedStock;
                        updatePrice();
                    });
                }

                refreshAvailability();
                updatePrice();

                form.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    const rawLocation = locationSelect ? (locationSelect.value || '') : '';
                    const rawStock = stockSelect ? (stockSelect.value || '') : '';

                    const payload = {
                        start_date: startInput.value,
                        end_date: endInput.value,
                        admin_note: noteField ? noteField.value : '',
                        booked_stock: rawStock !== '' ? rawStock : null,
                        location_id: rawLocation !== '' ? rawLocation : null,
                    };

                    try {
                        const response = await fetch(form.dataset.url, {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify(payload),
                        });

                        const json = await response.json();

                        if (!json.success) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: json.message || 'Unable to update booking.',
                            });
                            return;
                        }

                        if (typeof json.available !== 'undefined' && availableField) {
                            availableField.value = json.available;
                        }

                        if (json.location_id && locationSelect) {
                            form.dataset.currentLocation = json.location_id;
                        }

                        if (stockSelect && payload.booked_stock) {
                            selectedStock = parseInt(payload.booked_stock, 10) || selectedStock;
                            form.dataset.bookedStock = selectedStock;
                        }

                        refreshAvailability();

                        Swal.fire({
                            icon: 'success',
                            title: 'Updated!',
                            text: json.message || 'Booking updated successfully.',
                            toast: true,
                            position: 'top-end',
                            timer: 1800,
                            showConfirmButton: false,
                        });
                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: error.message || 'Something went wrong.',
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
                }
            });
            autocomplete.addListener("place_changed", () => {
                const place = autocomplete.getPlace();
                if (place.formatted_address) {
                    input.value = place.formatted_address;
                }
            });
        };
    </script>
@stop
