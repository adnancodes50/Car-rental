@extends('adminlte::page')

@section('title', 'View Add-On')

@section('content_header')
    <h1 class="text-bold container">Add-On Booking Details</h1>
@stop

@section('content')
    <div class="container-fluid">

        <!-- Card -->
        <div class="card shadow-sm border-0 rounded-4">


            <div class="card-body">
                @if ($reservations->isNotEmpty())
                    <div class="table-responsive">
                        <table id="addonBookingsTable" class="table table-striped table-hover align-middle text-sm w-100">
                            <thead class="table-light text-uppercase text-muted">
                                <tr>
                                    <th>Customer</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Qty</th>
                                    <th>Total Price</th>
                                    <th>Dates</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reservations as $res)
                                    <tr>
                                        <td class="fw-semibold">{{ $res->booking->customer->name ?? 'Unknown' }}</td>
                                        <td>{{ $res->booking->customer->email ?? '-' }}</td>
                                        <td>{{ $res->booking->customer->phone ?? '-' }}</td>
                                        <td>{{ $res->qty }}</td>
                                        <td>R{{ number_format($res->price_total, 2) }}</td>
                                        <td>{{ $res->booking->start_date }} → {{ $res->booking->end_date }}</td>
                                    </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">No reservations yet for this add-on.</p>
                @endif
            </div>
        </div>
    </div>
@stop

@section('css')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
@stop

@section('js')
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#addonBookingsTable').DataTable({
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                order: [
                    [0, 'asc']
                ],
                language: {
                    emptyTable: "No reservations found."
                }
            });
        });
    </script>
@stop
