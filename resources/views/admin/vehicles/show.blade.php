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
                        <p><strong>Monthly Price:</strong> ${{ number_format($vehicle->rental_price_month ?? 0, 2) }}</p>
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
        <!-- Calendar Management Section -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Calendar Management</h3>
                    <button class="btn btn-primary" data-toggle="modal" data-target="#bookingModal">
                        <i class="fas fa-plus mr-2"></i>Add Booking/Block
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Calendar Navigation -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <button class="btn btn-outline-secondary">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <h4 class="mb-0">September 2025</h4>
                    <button class="btn btn-outline-secondary">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>

                <!-- Calendar -->
                <div class="calendar">
                    <div class="row calendar-header">
                        <div class="col text-center p-2">Sun</div>
                        <div class="col text-center p-2">Mon</div>
                        <div class="col text-center p-2">Tue</div>
                        <div class="col text-center p-2">Wed</div>
                        <div class="col text-center p-2">Thu</div>
                        <div class="col text-center p-2">Fri</div>
                        <div class="col text-center p-2">Sat</div>
                    </div>

                    <!-- Calendar weeks -->
                    <div class="row calendar-week">
                        <div class="col day outside-month text-center p-2 text-muted">31</div>
                        <div class="col day text-center p-2">1</div>
                        <div class="col day text-center p-2">2</div>
                        <div class="col day booked text-center p-2">3</div>
                        <div class="col day booked text-center p-2">4</div>
                        <div class="col day booked text-center p-2">5</div>
                        <div class="col day booked text-center p-2">6</div>
                    </div>

                    <div class="row calendar-week">
                        <div class="col day booked text-center p-2">7</div>
                        <div class="col day booked text-center p-2">8</div>
                        <div class="col day booked text-center p-2">9</div>
                        <div class="col day booked text-center p-2">10</div>
                        <div class="col day today booked text-center p-2">11</div>
                        <div class="col day booked text-center p-2">12</div>
                        <div class="col day booked text-center p-2">13</div>
                    </div>

                    <div class="row calendar-week">
                        <div class="col day booked text-center p-2">14</div>
                        <div class="col day booked text-center p-2">15</div>
                        <div class="col day booked text-center p-2">16</div>
                        <div class="col day booked text-center p-2">17</div>
                        <div class="col day booked text-center p-2">18</div>
                        <div class="col day text-center p-2">19</div>
                        <div class="col day text-center p-2">20</div>
                    </div>

                    <div class="row calendar-week">
                        <div class="col day text-center p-2">21</div>
                        <div class="col day text-center p-2">22</div>
                        <div class="col day text-center p-2">23</div>
                        <div class="col day text-center p-2">24</div>
                        <div class="col day text-center p-2">25</div>
                        <div class="col day text-center p-2">26</div>
                        <div class="col day text-center p-2">27</div>
                    </div>

                    <div class="row calendar-week">
                        <div class="col day text-center p-2">28</div>
                        <div class="col day text-center p-2">29</div>
                        <div class="col day text-center p-2">30</div>
                        <div class="col day outside-month text-center p-2 text-muted">1</div>
                        <div class="col day outside-month text-center p-2 text-muted">2</div>
                        <div class="col day outside-month text-center p-2 text-muted">3</div>
                        <div class="col day outside-month text-center p-2 text-muted">4</div>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-danger mr-2" style="width: 20px; height: 20px; border-radius: 3px;"></div>
                        <span>Booked/Blocked dates</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Bookings Card -->
        <div class="card mt-4">
            <div class="card-header">
                <h3 class="card-title">Current Bookings & Blocks</h3>
            </div>
            <div class="card-body">
                <div class="booking-list">
                    <!-- Booking Item -->
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge badge-primary">rental</span>
                            <button class="btn btn-sm btn-link text-danger p-0">Remove</button>
                        </div>
                        <div class="text-sm">
                            <div class="d-flex align-items-center mb-1">
                                <i class="fas fa-calendar-alt mr-2 text-muted"></i>
                                <span>Jun 15 - Jun 22, 2024</span>
                            </div>
                            <div class="text-muted">John Smith</div>
                        </div>
                    </div>

                    <!-- Booking Item -->
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge badge-danger">maintenance</span>
                            <button class="btn btn-sm btn-link text-danger p-0">Remove</button>
                        </div>
                        <div class="text-sm">
                            <div class="d-flex align-items-center mb-1">
                                <i class="fas fa-calendar-alt mr-2 text-muted"></i>
                                <span>Sep 18 - Sep 02, 2025</span>
                            </div>
                            <div class="text-muted">this</div>
                            <div class="text-muted small">jbdsuj</div>
                        </div>
                    </div>

                    <!-- Booking Item -->
                    <div class="border rounded p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge badge-warning">internal</span>
                            <button class="btn btn-sm btn-link text-danger p-0">Remove</button>
                        </div>
                        <div class="text-sm">
                            <div class="d-flex align-items-center mb-1">
                                <i class="fas fa-calendar-alt mr-2 text-muted"></i>
                                <span>Sep 11 - Sep 13, 2025</span>
                            </div>
                            <div class="text-muted">this</div>
                            <div class="text-muted small">thisb</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Booking Modal -->
<div class="modal fade" id="bookingModal" tabindex="-1" role="dialog" aria-labelledby="bookingModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bookingModalLabel">Add Booking/Block</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label for="bookingType">Type</label>
                        <select class="form-control" id="bookingType">
                            <option value="rental">Rental</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="internal">Internal Use</option>
                            <option value="blocked">Blocked</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="customerName">Customer Name</label>
                        <input type="text" class="form-control" id="customerName" placeholder="Enter customer name">
                    </div>
                    <div class="form-group">
                        <label for="dateRange">Date Range</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="dateRange" placeholder="Select date range">
                            <div class="input-group-append">
                                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea class="form-control" id="notes" rows="3" placeholder="Add any notes here"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Save Booking</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .calendar {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
    }

    .calendar-header {
        background-color: #f8f9fa;
        font-weight: bold;
        border-bottom: 1px solid #dee2e6;
    }

    .calendar-week {
        border-bottom: 1px solid #dee2e6;
    }

    .calendar-week:last-child {
        border-bottom: none;
    }

    .day {
        padding: 0.5rem;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .day:hover {
        background-color: #f8f9fa;
    }

    .today {
        background-color: #e9ecef;
        font-weight: bold;
    }

    .booked {
        background-color: #fee2e2;
        color: #991b1b;
    }

    .outside-month {
        color: #6c757d;
    }

    .booking-list .badge {
        font-size: 0.75rem;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Initialize date range picker
        $('#dateRange').daterangepicker({
            opens: 'left',
            drops: 'auto'
        });

        // Handle booking removal
        $('.booking-list .btn-link').on('click', function() {
            if (confirm('Are you sure you want to remove this booking/block?')) {
                $(this).closest('.border').fadeOut(300, function() {
                    $(this).remove();
                });
            }
        });
    });
</script>
@stop
