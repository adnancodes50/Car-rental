@extends('adminlte::page')

@section('title', 'View Vehicle - Calendar Management')

@section('content_header')
<h1>Vehicle Details - Calendar Management</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-5">
        <!-- Vehicle Details Card -->
        <div class="card">
            <div class="card-header">
                <h3>{{ $vehicle->name }} ({{ $vehicle->model ?? 'N/A' }})</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        @if($vehicle->main_image_url)
                            <img src="{{ asset($vehicle->main_image_url) }}" alt="{{ $vehicle->name }}"
                                class="img-fluid img-thumbnail mb-3">
                        @else
                            <p>No main image</p>
                        @endif
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <p><strong>Year:</strong> {{ $vehicle->year ?? '-' }}</p>
                        <p><strong>Type:</strong> {{ $vehicle->type ?? '-' }}</p>
                        <p><strong>Location:</strong> {{ $vehicle->location ?? '-' }}</p>
                        <p><strong>Description:</strong> {{ $vehicle->description ?? '-' }}</p>
                        <p><strong>Status:</strong>
                            <span class="badge badge-{{ $vehicle->status == 'available' ? 'success' : 'danger' }}">
                                {{ ucfirst($vehicle->status) }}
                            </span>
                        </p>
                        <p><strong>Daily Price:</strong> ${{ number_format($vehicle->rental_price_day ?? 0, 2) }}</p>
                        <p><strong>Weekly Price:</strong> ${{ number_format($vehicle->rental_price_week ?? 0, 2) }}</p>
                        <p><strong>Monthly Price:</strong> ${{ number_format($vehicle->rental_price_month ?? 0, 2) }}
                        </p>
                        @if($vehicle->is_for_sale)
                            <p><strong>For Sale:</strong> <span class="badge badge-success">Yes</span></p>
                            <p><strong>Purchase Price:</strong> ${{ number_format($vehicle->purchase_price ?? 0, 2) }}</p>
                            <p><strong>Deposit:</strong> ${{ number_format($vehicle->deposit_amount ?? 0, 2) }}</p>
                        @else
                            <p><strong>For Sale:</strong> <span class="badge badge-secondary">No</span></p>
                        @endif
                    </div>
                </div>
                <hr>
                <h5>Additional Photos:</h5>
                <div class="d-flex flex-wrap">
                    @if($vehicle->images && count($vehicle->images))
                        @foreach($vehicle->images as $img)
                            <img src="{{ asset($img->url) }}" alt="Image" class="img-thumbnail m-1" width="120">
                        @endforeach
                    @else
                        <p>No additional images</p>
                    @endif
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('vehicles.index') }}" class="btn btn-secondary">Back to List</a>
                <a href="{{ route('vehicles.edit', $vehicle->id) }}" class="btn btn-info">Edit</a>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Calendar Management</h3>
                <!-- Button to open Booking Modal -->
                <button class="btn btn-dark ml-auto" data-toggle="modal" data-target="#bookingModal">
                    <i class="fas fa-plus mr-2"></i> Add Booking/Block
                </button>
            </div>

            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>

        <!-- Current Bookings Card -->
        <div class="card mt-4">
            <div class="card-header">
                <h3 class="card-title">Current Bookings & Blocks</h3>
            </div>
            <div class="card-body">
                <div class="booking-list">
                    @forelse($bookings as $booking)
                                    <div class="border rounded p-3 mb-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="badge
                                                {{ $booking->type === 'maintenance' ? 'badge-danger' :
                        ($booking->type === 'internal' ? 'badge-warning' : 'badge-primary') }}">
                                                {{ $booking->type }}
                                            </span>
                                            <form method="POST"
                                                action="{{ route('vehicles.bookings.destroy', [$vehicle->id, $booking->id]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-link text-danger p-0">Remove</button>
                                            </form>
                                        </div>
                                        <div class="text-sm">
                                            <div class="d-flex align-items-center mb-1">
                                                <i class="fas fa-calendar-alt mr-2 text-muted"></i>
                                                <span>
                                                    {{ \Carbon\Carbon::parse($booking->start_date)->format('M d, Y') }}
                                                    -
                                                    {{ \Carbon\Carbon::parse($booking->end_date)->format('M d, Y') }}
                                                </span>
                                            </div>
                                            @if($booking->customer_reference)
                                                <div class="text-muted">{{ $booking->customer_reference }}</div>
                                            @endif
                                            @if($booking->notes)
                                                <div class="text-muted small">{{ $booking->notes }}</div>
                                            @endif
                                        </div>
                                    </div>
                    @empty
                        <p class="text-muted">No bookings found for this vehicle.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Booking Modal --}}
{{-- Booking Modal --}}
<div class="modal fade rounded-5" id="bookingModal" tabindex="-1" role="dialog" aria-labelledby="bookingModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bookingModalLabel">Add Booking/Block</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('vehicles.bookings.store', $vehicle->id) }}">
                    @csrf

                    <!-- Row 1: Start & End Date -->
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="startDate">Start Date</label>
                            <input type="date" class="form-control" id="startDate" name="start_date" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="endDate">End Date</label>
                            <input type="date" class="form-control" id="endDate" name="end_date" required>
                        </div>
                    </div>

                    <!-- Row 2: Type -->
                    <div class="form-group">
                        <label for="bookingType">Type</label>
                        <select class="form-control" id="bookingType" name="type" required>
                            <option value="maintenance">Maintenance</option>
                            <option value="internal">Internal Use</option>
                            <option value="purchaser">Marked as Purchaser</option>
                        </select>
                    </div>

                    <!-- Row 3: Customer Reference (Optional) -->
                    <div class="form-group">
                        <label for="customerReference">Customer Reference <small
                                class="text-muted">(optional)</small></label>
                        <input type="text" class="form-control" id="customerReference" name="customer_reference"
                            placeholder="Enter reference">
                    </div>

                    <!-- Row 4: Notes -->
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"
                            placeholder="Add any notes here"></textarea>
                    </div>

                    <!-- ✅ Move buttons inside the form -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Booking</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@stop

{{-- ✅ CSS --}}
@section('css')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css" rel="stylesheet" />
<style>
    #calendar {
        max-width: 100%;
        margin: 0 auto;
        background: #fff;
        padding: 10px;
        border-radius: 8px;
        height: 120px;
    }
</style>
@stop


@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        let calendarEl = document.getElementById('calendar');

        // Parse booked dates from Blade
        const bookedDates = @json($bookedDates);

        // Generate array of all booked dates
        let disabledDates = [];
        bookedDates.forEach(b => {
            let start = new Date(b.start);
            let end = new Date(b.end);
            for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
                disabledDates.push(new Date(d).toISOString().split('T')[0]);
            }
        });

        // Initialize FullCalendar
        if (calendarEl) {
            let calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: 600,
                events: bookedDates.map(b => ({
                    start: b.start,
                    end: b.end,
                    display: 'background',
                    color: '#ff9f89'
                }))
            });
            calendar.render();
        }

        // Disable booked dates in modal date inputs
        const startInput = document.getElementById('startDate');
        const endInput = document.getElementById('endDate');

        function disableBookedDates(input) {
            input.addEventListener('input', function () {
                if (disabledDates.includes(this.value)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Date not available',
                        text: 'This date is already booked!',
                    });
                    this.value = '';
                }
            });
        }

        disableBookedDates(startInput);
        disableBookedDates(endInput);

        // SweetAlert flash messages
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: "{{ session('success') }}",
                timer: 2500,
                showConfirmButton: false
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: "{{ session('error') }}",
                timer: 3000,
                showConfirmButton: true
            });
        @endif

        // Delete confirmation
        document.querySelectorAll('form[action*="destroy"]').forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This booking/block will be permanently removed!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });
        });
    });
</script>
@stop
