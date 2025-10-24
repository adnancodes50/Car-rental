@extends('adminlte::page')

@section('title', 'View Location')

@section('content_header')
    <h1 class="container text-bold">View Location: {{ $location->name }}</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm rounded-4">

        <!-- Card Header -->
        <div class="card-header bg-white">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">Location Details</h5>
                </div>
                <div class="col-auto">
                    <a href="{{ route('locations.index') }}" class="btn btn-secondary btn-sm">Back</a>
                </div>
            </div>
        </div>

        <div class="card-body">

            <!-- Outgoing Prices -->
            <h5 class="mt-4">Outgoing Transfer Prices</h5>
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Transfer Fee</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($location->outgoingPrices as $price)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $price->fromLocation->name }}</td>
                            <td>{{ $price->toLocation->name }}</td>
                            <td>{{ number_format($price->transfer_fee, 2) }}</td>
                            <td>{{ ucfirst($price->status) }}</td>
                            <td>
                                <button class="btn btn-sm btn-warning edit-price-btn"
                                    data-id="{{ $price->id }}"
                                    data-from="{{ $price->fromLocation->name }}"
                                    data-to="{{ $price->toLocation->name }}"
                                    data-fee="{{ $price->transfer_fee }}"
                                    data-status="{{ $price->status }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">No outgoing pricing found.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Incoming Prices -->
            <h5 class="mt-4">Incoming Transfer Prices</h5>
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Transfer Fee</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($location->incomingPrices as $price)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $price->fromLocation->name }}</td>
                            <td>{{ $price->toLocation->name }}</td>
                            <td>{{ number_format($price->transfer_fee, 2) }}</td>
                            <td>{{ ucfirst($price->status) }}</td>
                            <td>
                                <button class="btn btn-sm btn-warning edit-price-btn"
                                    data-id="{{ $price->id }}"
                                    data-from="{{ $price->fromLocation->name }}"
                                    data-to="{{ $price->toLocation->name }}"
                                    data-fee="{{ $price->transfer_fee }}"
                                    data-status="{{ $price->status }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">No incoming pricing found.</td></tr>
                    @endforelse
                </tbody>
            </table>

        </div>
    </div>
</div>

<!-- Edit Price Modal -->
<div class="modal fade" id="editPriceModal" tabindex="-1" aria-labelledby="editPriceModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="editPriceForm">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editPriceModalLabel">Edit Transfer Price</h5>
        </div>
        <div class="modal-body">
          <input type="hidden" name="pricing_id" id="pricing_id">
          <div class="mb-3">
            <label>From</label>
            <input type="text" id="from_location" class="form-control" disabled>
          </div>
          <div class="mb-3">
            <label>To</label>
            <input type="text" id="to_location" class="form-control" disabled>
          </div>
          <div class="mb-3">
            <label>Transfer Fee</label>
            <input type="number" name="transfer_fee" id="transfer_fee" class="form-control" min="0" required>
          </div>
          <div class="mb-3">
            <label>Status</label>
            <select name="status" id="status" class="form-control">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
         
          <button type="submit" class="btn btn-dark">Update Price</button>
        </div>
      </div>
    </form>
  </div>
</div>

@stop

@section('js')
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Open modal and fill data
    $('.edit-price-btn').click(function() {
        let id = $(this).data('id');
        $('#pricing_id').val(id);
        $('#from_location').val($(this).data('from'));
        $('#to_location').val($(this).data('to'));
        $('#transfer_fee').val($(this).data('fee'));
        $('#status').val($(this).data('status'));

        $('#editPriceModal').modal('show');
    });

    // Submit AJAX request to update price
    $('#editPriceForm').submit(function(e) {
        e.preventDefault();

        let pricingId = $('#pricing_id').val();
        let formData = {
            transfer_fee: $('#transfer_fee').val(),
            status: $('#status').val(),
            _token: '{{ csrf_token() }}',
            _method: 'PUT'
        };

        $.ajax({
            url: '/locations/location-pricings/' + pricingId,
            type: 'PUT',
            data: formData,
            success: function(res) {
                Swal.fire('Success', 'Price updated successfully!', 'success')
                    .then(() => location.reload());
            },
            error: function(err) {
                Swal.fire('Error', 'Something went wrong!', 'error');
            }
        });
    });
});
</script>
@endsection

