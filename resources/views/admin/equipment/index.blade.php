@extends('adminlte::page')

@section('title', 'Equipment')

@section('content_header')
    <h1 class="container text-bold">Equipment</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex w-100 align-items-center justify-content-between">
                <h3 class="card-title mb-0">All Equipment</h3>

                <!-- Category Filter aligned to right -->
                <div class="ml-auto" style="width: 200px;">
                    <select id="categoryFilter" class="form-control form-control-sm">
                        <option value="">All Categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}"
                                {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="card-body p-2">
            <div class="table-responsive">
                <table id="equipmentTable" class="table table-striped mb-0 w-100">
                    <thead>
                        <tr>
                            <th style="width: 80px;">Image</th>
                            <th>Category</th>
                            <th>Name</th>

                            @foreach ($locations as $location)
                                <th>{{ $location->name }} Stock</th>
                            @endforeach
                            <th>Total Stock</th>
                            <th>Status</th>
                            <th style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($equipment as $item)
                            <tr data-category-id="{{ $item->category_id }}">
                                <td>
                                    @if ($item->image)
                                        <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" width="50">
                                    @endif
                                </td>
                                <td>{{ $item->category->name ?? '-' }}</td>
                                <td>{{ $item->name }}</td>


                                {{-- Stock per location --}}
                                @foreach ($locations as $location)
                                    @php
                                        $stockRecord   = $item->stocks->firstWhere('location_id', $location->id);
                                        $available     = $stockRecord ? $stockRecord->stock : 0;

                                        $bookings      = $item->bookings->where('location_id', $location->id);
                                        $bookedTotal   = $bookings->sum('booked_stock');

                                        $purchases     = $item->purchases->where('location_id', $location->id);
                                        $purchasedTotal = $purchases->sum('quantity');
                                    @endphp

                                    <td class="text-center">
                                        <div><strong>Available:</strong> {{ $available }}</div>
                                        <div class="text-primary"><strong>Booked:</strong> {{ $bookedTotal }}</div>
                                        <div class="text-success"><strong>Purchased:</strong> {{ $purchasedTotal }}</div>

                                        <a href="javascript:void(0);"
                                           class="btn btn-sm btn-outline-info mt-1 view-location-details"
                                           data-equipment="{{ $item->name }}"
                                           data-location="{{ $location->name }}"
                                           data-available="{{ $available }}"
                                           data-bookings='@json($bookings->values())'
                                           data-purchases='@json($purchases->values())'>
                                           <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                @endforeach

                                <td>{{ $item->stocks->sum('stock') }}</td>

                                <td>
                                    @if ($item->status === 'active')
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-secondary">Inactive</span>
                                    @endif
                                </td>

                                <td>
                                    <a href="{{ route('equipment.edit', $item) }}" class="btn btn-outline-primary btn-sm ml-1">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <form action="{{ route('equipment.destroy', $item) }}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-outline-danger btn-sm ml-1 delete-btn">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 3 + $locations->count() + 3 }}" class="text-center">
                                    No equipment found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="locationDetailsModal" tabindex="-1" role="dialog" aria-labelledby="locationDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header  text-white" style="background-color: grey">
        <h5 class="modal-title" id="locationDetailsModalLabel">Equipment Details</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <div id="locationDetailsContent">
          <div class="text-center py-3 text-muted">
            <i class="fas fa-info-circle"></i> Select a location to view details.
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@stop


@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<style>
    #equipmentTable td, #equipmentTable th {
        padding: 12px 15px;
        vertical-align: middle;
    }
    .detail-card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 10px;
        background: #f8f9fa;
    }
</style>
@stop


@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {

    if ($('#equipmentTable tbody tr').length) {
        $('#equipmentTable').DataTable({
            "columnDefs": [
                { "orderable": false, "targets": {{ 3 + $locations->count() + 2 }} }
            ],
            "pageLength": 10
        });
    }

    $('#categoryFilter').on('change', function() {
        var selectedId = $(this).val();
        var baseUrl = "{{ route('equipment.index') }}";
        window.location.href = selectedId ? baseUrl + '?category_id=' + selectedId : baseUrl;
    });

    $('.delete-btn').on('click', function(e) {
        e.preventDefault();
        let form = $(this).closest('form');
        Swal.fire({
            title: 'Are you sure?',
            text: "This equipment will be deleted permanently.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => { if (result.isConfirmed) form.submit(); });
    });

    // ✅ Show modal with booking & purchase details
    $(document).on('click', '.view-location-details', function() {
        const equipment = $(this).data('equipment');
        const location = $(this).data('location');
        const available = $(this).data('available');
        const bookings = $(this).data('bookings');
        const purchases = $(this).data('purchases');

        $('#locationDetailsModalLabel').text(`${equipment} — ${location} Details`);

        let html = `
            <div class="row text-center mb-3">
                <div class="col-md-4"><div class="detail-card"><strong>Available</strong><div class="h5 text-success">${available}</div></div></div>
                <div class="col-md-4"><div class="detail-card"><strong>Bookings</strong><div class="h5 text-primary">${bookings.length}</div></div></div>
                <div class="col-md-4"><div class="detail-card"><strong>Purchases</strong><div class="h5 text-warning">${purchases.length}</div></div></div>
            </div>
            <hr>
        `;

        // Bookings table
        if (bookings.length > 0) {
            html += `
            <h5 class="mb-2 text-primary">📅 Booking Details</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th>Customer</th>
                            <th>Qty</th>
                            <th>Total Price</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Duration</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

           bookings.forEach(b => {
    const start = new Date(b.start_date);
    const end = new Date(b.end_date);
    const diffDays = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1; // ✅ +1 for inclusive days
    html += `
        <tr>
            <td>${b.customer?.name ?? '—'}</td>
            <td>${b.booked_stock}</td>
            <td>R ${parseFloat(b.total_price).toFixed(2)}</td>
            <td>${b.start_date}</td>
            <td>${b.end_date}</td>
            <td>${diffDays} days</td>
        </tr>`;
});


            html += `</tbody></table></div>`;
        } else {
            html += `<div class="text-muted mb-3">No bookings found for this location.</div>`;
        }

        // Purchases table
        if (purchases.length > 0) {
            html += `
            <h5 class="mt-4 mb-2 text-success">🛒 Purchase Details</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th>Customer</th>
                            <th>Quantity</th>
                            <th>Total Price</th>
                            <th>Purchase Deposit Paid   </th>

                        </tr>
                    </thead>
                    <tbody>
            `;

            purchases.forEach(p => {
                html += `
                    <tr>
                        <td>${p.customer?.name ?? '—'}</td>
                        <td>${p.quantity}</td>
                        <td>R ${parseFloat(p.total_price).toFixed(2)}</td>
                        <td>${p.deposit_paid}</td>


                    </tr>`;
            });

            html += `</tbody></table></div>`;
        } else {
            html += `<div class="text-muted">No purchases found for this location.</div>`;
        }

        $('#locationDetailsContent').html(html);
        $('#locationDetailsModal').modal('show');
    });
});
</script>
@stop
