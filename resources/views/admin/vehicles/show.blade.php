@extends('adminlte::page')

@section('title', 'View Vehicle - Calendar Management')

@section('content_header')
    <h1>Vehicle Details - Calendar Management</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-5">
            <!-- Vehicle Details -->
            <div class="card">
                <div class="card-header">
                    <h3>{{ $vehicle->name }} ({{ $vehicle->model ?? 'N/A' }})</h3>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-12">
                            @if ($vehicle->main_image_url)
                                <img src="{{ asset($vehicle->main_image_url) }}" alt="{{ $vehicle->name }}"
                                     class="img-fluid img-thumbnail">
                            @else
                                <p>No main image</p>
                            @endif
                        </div>
                    </div>

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

                    @if ($vehicle->is_for_sale)
                        <p><strong>For Sale:</strong> <span class="badge badge-success">Yes</span></p>
                        <p><strong>Purchase Price:</strong> ${{ number_format($vehicle->purchase_price ?? 0, 2) }}</p>
                        <p><strong>Deposit:</strong> ${{ number_format($vehicle->deposit_amount ?? 0, 2) }}</p>
                    @else
                        <p><strong>For Sale:</strong> <span class="badge badge-secondary">No</span></p>
                    @endif

                    <hr>
                    <h5>Additional Photos</h5>
                    <div class="d-flex flex-wrap">
                        @forelse ($vehicle->images as $img)
                            <img src="{{ asset($img->url) }}" alt="Image" class="img-thumbnail m-1" width="120">
                        @empty
                            <p>No additional images</p>
                        @endforelse
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
                    <button class="btn btn-dark ml-auto" data-toggle="modal" data-target="#bookingModal">
                        <i class="fas fa-plus mr-2"></i> Add Booking/Block
                    </button>
                </div>
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>

            <!-- Current Bookings & Blocks -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">Current Bookings & Blocks</h3>
                </div>
                <div class="card-body">
                   <div class="booking-list">
  @forelse($entries as $row)
    @php
      $badgeClass = match($row['type']) {
          'maintenance' => 'badge-danger',
          'internal'    => 'badge-warning',
          'purchaser', 'purchase' => 'badge-secondary',
          'booking'     => 'badge-primary',
          default       => 'badge-light',
      };
      $title = ucfirst($row['type']);
    @endphp

    <div class="border rounded p-3 mb-3">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <span class="badge {{ $badgeClass }}">{{ $title }}</span>

        @if($row['can_delete'])
          <form method="POST" action="{{ route('vehicles.bookings.destroy', [$vehicle->id, $row['id']]) }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-link text-danger p-0">Remove</button>
          </form>
        @else
          <span class="text-muted small">public</span>
        @endif
      </div>

      <div class="text-sm">
        <div class="d-flex align-items-center mb-1">
          <i class="fas fa-calendar-alt mr-2 text-muted"></i>
          <span>
            {{ \Carbon\Carbon::parse($row['start'])->format('M d, Y') }}
            –
            {{ \Carbon\Carbon::parse($row['end'])->format('M d, Y') }}
          </span>
        </div>
        @if(!empty($row['ref']))
          {{-- <div class="text-muted">{{ $row['ref'] }}</div> --}}
        @endif
        @if(!empty($row['notes']))
          {{-- <div class="text-muted small">{{ $row['notes'] }}</div> --}}
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

    {{-- Booking Modal --}}
    <div class="modal fade rounded-5" id="bookingModal" tabindex="-1" role="dialog" aria-labelledby="bookingModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bookingModalLabel">Add Booking/Block</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ route('vehicles.bookings.store', $vehicle->id) }}">
                        @csrf

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

                        <div class="form-group">
                            <label for="bookingType">Type</label>
                            <select class="form-control" id="bookingType" name="type" required>
                                <option value="maintenance">Maintenance</option>
                                <option value="internal">Internal Use</option>
                                <option value="purchaser">Marked as Purchaser</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="customerReference">Customer Reference <small class="text-muted">(optional)</small></label>
                            <input type="text" class="form-control" id="customerReference" name="customer_reference" placeholder="Enter reference">
                        </div>

                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Add any notes here"></textarea>
                        </div>

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
@section('css')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css" rel="stylesheet" />
<style>
  /* Let the JS control height; don't clamp it to 120px */
  #calendar{
    max-width: 100%;
    margin: 0 auto;
    background: #fff;
    padding: 10px;
    border-radius: 8px;
    /* no fixed height here */
  }
  .fc .fc-bg-event { opacity: .35; }
  /* Hide titles on background events */
  .fc .fc-bg-event .fc-event-title { display: none; }
</style>
@stop


{{-- JS --}}
@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const calendarEl = document.getElementById('calendar');

        // Data from PHP:
        const eventsData   = @json($calendarEvents); // end already +1 day (exclusive)
        const bookedRanges = @json($bookedDates);    // inclusive Y-M-D pairs

        // Local Y-M-D helpers (no UTC conversions)
        function fromYMD(s){ const [y,m,d]=(s||'').split('-').map(Number); return (y&&m&&d)? new Date(y, m-1, d) : null; }
        function toYMD(dt){ return [dt.getFullYear(), String(dt.getMonth()+1).padStart(2,'0'), String(dt.getDate()).padStart(2,'0')].join('-'); }

        // Disabled dates (local)
        const disabledSet = new Set();
        bookedRanges.forEach(r => {
            const s = fromYMD(r.start), e = fromYMD(r.end);
            if (!s || !e) return;
            for (let d = new Date(s.getFullYear(), s.getMonth(), s.getDate()); d <= e; d.setDate(d.getDate()+1)) {
                disabledSet.add(toYMD(d));
            }
        });

        // Calendar
        if (calendarEl) {
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: 600,
                contentHeight: 'auto',
                firstDay: 0,
                dayMaxEvents: true,
                eventOrder: 'source,type,title',
                events: eventsData, // background blocks, colored by type
            });
            calendar.render();
        }

        // Disable in modal inputs
        const startInput = document.getElementById('startDate');
        const endInput   = document.getElementById('endDate');

        function wireDisable(input){
            if (!input) return;
            input.addEventListener('input', function(){
                const val = this.value;
                if (disabledSet.has(val)) {
                    Swal.fire({ icon: 'error', title: 'Date not available', text: 'This date is already booked!' });
                    this.value = '';
                }
            });
        }
        wireDisable(startInput);
        wireDisable(endInput);

        // Flash messages
        @if(session('success'))
            Swal.fire({ icon:'success', title:'Success', text:"{{ session('success') }}", timer:2500, showConfirmButton:false });
        @endif
        @if(session('error'))
            Swal.fire({ icon:'error', title:'Error', text:"{{ session('error') }}", timer:3000, showConfirmButton:true });
        @endif

        // Delete confirmation
        document.querySelectorAll('form[action*="destroy"]').forEach(form => {
            form.addEventListener('submit', function(e){
                e.preventDefault();
                Swal.fire({
                    title:'Are you sure?',
                    text:'This booking/block will be permanently removed!',
                    icon:'warning',
                    showCancelButton:true,
                    confirmButtonColor:'#d33',
                    cancelButtonColor:'#3085d6',
                    confirmButtonText:'Yes, delete it!'
                }).then(res => { if (res.isConfirmed) form.submit(); });
            });
        });
    });
    </script>
@stop
