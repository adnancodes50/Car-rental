@extends('adminlte::page')

@section('title', 'Email Templates')

@section('content_header')
<h1 class="text-bold container">Email Templates</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Card -->
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header border-0">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="card-title mb-0 text-bold">All Email Templates</h3>
                </div>
                <div class="col-auto text-end">
                    <a href="{{ route('email.create') }}" class="btn btn-dark btn-sm">
                        <i class="fas fa-plus-circle me-1"></i> New Template
                    </a>
                </div>
            </div>
        </div>
        <hr>
        <div class="card-body">
            <!-- Responsive Table -->
            <div class="table-responsive">
                <table id="templatesTable" class="table table-striped table-hover align-middle text-sm w-100">
                    <thead class="table-light text-uppercase text-muted">
                        <tr>
                            <th>ID</th>
                            <th>Trigger</th>
                            <th>Recipient</th>
                            <th>Name</th>
                            <th>Subject</th>
                            <th>Body</th>
                            <th>Enabled</th>
                            <th class="text-center" style="width:80px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($templates as $template)
                        <tr>
                            <td>{{ $template->id }}</td>
                            <td>{{ ucfirst($template->trigger) }}</td>
                            <td>{{ ucfirst($template->recipient) }}</td>
                            <td>{{ $template->name }}</td>
                            <td>{{ $template->subject }}</td>
                            <td>{{ Str::limit($template->body, 50) }}</td>
                            <td class="text-center align-middle">
                                @if ($template->enabled)
                                    <span class="badge py-1 text-white" style="background-color: rgb(18, 158, 151); font-size: 0.85rem;">Enabled</span>
                                @else
                                    <span class="badge bg-danger">Disabled</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('email.edit', $template->id) }}" class="btn btn-outline-warning btn-sm action-btn" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
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
.action-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    padding: 0;
}
.action-btn:hover { background-color: #fff !important; }
.btn-outline-warning:hover i { color: #ffc107; }
.action-btn i { font-size: 16px; }
</style>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    $('#templatesTable').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [7] },
            { searchable: false, targets: [7] },
            { targets: 0, responsivePriority: 1 },
            { targets: 6, responsivePriority: 2 }
        ]
    });

    // SweetAlert for flash messages
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: @json(session('success')),
            confirmButtonColor: '#198754'
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: @json(session('error')),
            confirmButtonColor: '#dc3545'
        });
    @endif
});
</script>
@stop





{{-- https://adnanali2233.app.n8n.cloud/workflows --}}
