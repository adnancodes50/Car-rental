@extends('adminlte::page')
@section('title', 'Communication Center')

@section('content_header')
    <h1>Communication Center</h1>
@stop

@section('content')
    <div class="container-fluid mb-5">
        {{-- Email Sending Form --}}
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body p-4">
                <form action="{{ route('communication-setting.send') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="customer_ids" class="form-label">Select Customers</label>
                        <select name="customer_ids[]" id="customer_ids" class="form-control" multiple required>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->email }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" name="subject" id="subject" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="body" class="form-label">Body</label>
                        <textarea id="summernote" name="body" required></textarea>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-success py-2 px-4">
                            <i class="fas fa-paper-plane"></i> Send Email
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modern Chat-Style Email Logs --}}
{{-- Email Logs Table --}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-white text-black d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Email Logs</h5>

        <form id="filterForm" method="GET" action="{{ route('communication-setting.index') }}" class="ms-auto">
            <div class="d-flex align-items-center gap-2">
                <label for="filter" class="me-2 mb-0 text-muted small">Filter:</label>
                <select name="filter" id="filter" class="form-select form-select-sm" style="width: 150px;"
                    onchange="this.form.submit()">
                    <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>All</option>
                    <option value="30days" {{ $filter === '30days' ? 'selected' : '' }}>Last 30 Days</option>
                    <option value="7days" {{ $filter === '7days' ? 'selected' : '' }}>Last 7 Days</option>
                </select>
            </div>
        </form>
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
                            <td class="align-top">
                                <strong>{{ $log->subject }}</strong>
                            </td>
                            <td>
                                <div class="message-box p-3 rounded border bg-white position-relative shadow-sm">
                                    {{-- Top row: sender (left) and receiver (right) --}}
                                    <div class="d-flex justify-content-between mb-2">
                                        <div>
                                            <span class="fw-bold text-primary">From:</span>
                                            {{ $log->sender->name ?? 'System / Admin' }}
                                            <span class="text-muted small">({{ $log->sender->email ?? 'N/A' }})</span>
                                        </div>
                                        <div>
                                            <span class="fw-bold text-success">To:</span>
                                            {{ $log->customer->name ?? 'Unknown' }}
                                            <span class="text-muted small">({{ $log->customer->email ?? 'N/A' }})</span>
                                        </div>
                                    </div>

                                    {{-- Message body --}}
                                    <div class="message-content border-top border-bottom py-3 my-2 bg-light px-3 rounded">
                                        {!! $log->body !!}
                                    </div>

                                    {{-- Timestamp bottom right --}}
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

    <div class="card-footer text-center">
        {{ $emailLogs->links() }}
    </div>
</div>


    </div>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs4.min.css" rel="stylesheet">

    <style>
        /* General styling */
        .chat-box {
            background: #f5f7fa;
            border-radius: 10px;
            height: 500px;
            overflow-y: auto;
            padding: 20px;
        }

        .chat-message {
            display: flex;
            margin-bottom: 20px;
        }

        /* Avatar circle */
        .avatar-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #6c757d;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1rem;
        }

        /* Sent messages (right side) */
        .chat-message.sent {
            flex-direction: row-reverse;
        }

        .chat-message.sent .chat-bubble {
            background-color: #d1e7dd;
            border-radius: 15px 15px 0 15px;
            margin-right: 10px;
            text-align: left;
        }

        /* Received messages (left side) */
        .chat-message.received .chat-bubble {
            background-color: #ffffff;
            border-radius: 15px 15px 15px 0;
            margin-left: 10px;
        }

        .chat-bubble {
            padding: 12px 16px;
            max-width: 70%;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .message-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
        }

        .message-content {
            background: #f8f9fa;
            padding: 8px 10px;
            border-radius: 8px;
            margin-top: 5px;
        }

        .message-footer {
            margin-top: 8px;
            font-size: 0.8rem;
            color: #6c757d;
        }

        .card-header form#filterForm {
            margin-left: auto;
        }

        /* Smooth scrollbar */
        .chat-box::-webkit-scrollbar {
            width: 6px;
        }

        .chat-box::-webkit-scrollbar-thumb {
            background-color: rgba(0, 0, 0, 0.2);
            border-radius: 3px;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs4.min.js"></script>

    <script>
        $(function() {
            $('#customer_ids').select2({
                placeholder: "Select one or more customers",
                width: '100%'
            });

            $('#summernote').summernote({
                height: 300,
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['font', ['fontsize', 'color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        });

        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '{{ session('success') }}',
                confirmButtonColor: '#28a745'
            });
        @endif
    </script>
@stop
