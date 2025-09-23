@extends('adminlte::page')

@section('title', 'Add-On Inventory')

@section('content_header')
<h1 class="text-bold container">Add-On Inventory</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Card -->
    <div class=" card shadow-sm border-0 rounded-4">
      <div class="card-header border-0">
  <div class="row align-items-center">
    <div class="col">
      <h3 class="card-title mb-0 text-bold">Manage Add-Ons</h3>
    </div>
    <div class="col-auto text-end">
      <a href="{{ route('inventry.create') }}" class="btn btn-dark btn-sm">
        <i class="fas fa-plus-circle me-1"></i> Add-On
      </a>
    </div>
  </div>
</div>


        <hr>

        <div class="card-body">
            <div class="table-responsive">
                <table id="addonsTable" class="table table-striped table-hover align-middle text-sm w-100">
                    <thead class="table-light text-uppercase text-muted">
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Qty Total</th>
                            <th>Price (Day)</th>
                            <th>Price (Week)</th>
                            <th>Price (Month)</th>
                            <th class="text-center" style="width:120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                      @foreach($addOns as $addOn)
   <tr>
    <td><img src="{{ asset($addOn->image_url) }}" alt="{{ $addOn->name }}" class="rounded border" style="width:60px; height:60px; object-fit:cover;"></td>
      <td class="fw-semibold">{{ $addOn->name }}</td>
      <td>{{ Str::limit($addOn->description, 50) }}</td>
      <td>{{ $addOn->qty_total }}</td>
      <td>R{{ number_format($addOn->price_day, 2) }}</td>
      <td>R{{ number_format($addOn->price_week, 2) }}</td>
      <td>R{{ number_format($addOn->price_month, 2) }}</td>
      <td class="text-center">
         <div class="d-flex justify-content-center gap-2">
            <a href="{{ route('inventry.edit', $addOn->id) }}" class="btn btn-outline-warning btn-sm action-btn mr-1" title="Edit">
               <i class="fas fa-edit"></i>
            </a>
            <form action="{{ route('inventry.destroy', $addOn->id) }}" method="POST" class="delete-form d-inline-block">
               @csrf
               @method('DELETE')
               <button type="submit" class="btn btn-outline-danger btn-sm action-btn" title="Delete">
                  <i class="fas fa-trash-alt"></i>
               </button>
            </form>
         </div>
      </td>
   </tr>
@endforeach

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    table.table-hover tbody tr:hover {
        background-color: rgba(255, 193, 7, 0.1);
        transition: background-color 0.2s ease-in-out;
    }

    /* Action button styling */
    .action-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 6px;
        padding: 0;
    }

    .action-btn:hover {
        background-color: #fff !important;
    }

    .btn-outline-warning:hover i {
        color: #ffc107;
    }

    .btn-outline-danger:hover i {
        color: #dc3545;
    }

    .action-btn i {
        font-size: 16px;
    }
</style>
@stop

@section('css')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
@stop

@section('js')
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function () {
        // Init DataTable
      $('#addonsTable').DataTable({
    responsive: true,
    autoWidth: false,
    pageLength: 10,
    order: [[0, 'asc']],
    columnDefs: [
        { orderable: false, targets: [6] },
        { searchable: false, targets: [6] },
        { targets: 0, responsivePriority: 1 },
        { targets: 6, responsivePriority: 2 }
    ],
    language: {
        emptyTable: "No add-ons found." // ✅ handles empty table gracefully
    }
});


        // Success / Error Alerts
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: @json(session('success')),
                confirmButtonText: 'OK'
            });
        @endif
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: @json(session('error')),
                confirmButtonText: 'OK'
            });
        @endif

        // Delete confirmation
        $(document).on('submit', 'form.delete-form', function (e) {
            e.preventDefault();
            let form = this;
            Swal.fire({
                title: 'Are you sure?',
                text: "This add-on will be permanently deleted.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@stop
