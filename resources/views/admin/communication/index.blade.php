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
                        <button type="submit" class="btn btn-success py-2">
                            <i class="fas fa-paper-plane"></i> Send Email
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Chat Style Email Logs --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white text-black d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Email Conversation Logs</h5>

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


            <div class="card-body chat-box" style="background:white; height: 500px; overflow-y:auto; padding:20px;">
                @forelse($emailLogs as $log)
                    <div class="chat-message {{ $log->sent_by == auth()->id() ? 'sent' : 'received' }}">
                        <div class="message-header">
                            <strong>{{ $log->sender->name ?? 'System' }}</strong>
                            <span class="timestamp">{{ \Carbon\Carbon::parse($log->sent_at)->format('d M Y h:i A') }}</span>
                        </div>

                        <div class="message-body">
                            <p class="subject"><strong>Subject:</strong> {{ $log->subject }}</p>
                            <div class="body-text">{!! $log->body !!}</div>
                        </div>

                        <div class="message-footer">
                            <small>To: {{ $log->customer->name ?? 'Unknown' }}
                                ({{ $log->customer->email ?? 'N/A' }})</small>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted mt-4">
                        <i class="fas fa-envelope-open-text fa-2x mb-2"></i>
                        <p>No email logs found.</p>
                    </div>
                @endforelse
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
        .note-editor.note-frame {
            border: 1px solid #ced4da;
        }

        .note-editor.note-frame .note-editable {
            background-color: #fff;
        }

        .note-editor .dropdown-menu {
            z-index: 2050;
        }
        .chat-box {
            background: white;
            border-radius: 10px;
        }
        .chat-message {
            max-width: 75%;
            padding: 12px 16px;
            margin-bottom: 15px;
            border-radius: 15px;
            position: relative;
            word-wrap: break-word;
        }
        .chat-message.sent {
            background-color: #d1e7dd;
            margin-left: auto;
            text-align: left;
            border-bottom-right-radius: 0;
        }
        .chat-message.received {
            background-color: #ffffff;
            margin-right: auto;
            border-bottom-left-radius: 0;
        }
        .card-header form#filterForm {
            margin-left: auto;
        }
        .message-header {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        .message-header strong {
            color: #2c3e50;
        }
        .timestamp {
            font-size: 0.8rem;
            color: #888;
        }

        .message-body {
            font-size: 0.95rem;
            color: #333;
        }

        .subject {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .body-text {
            background: #f9f9f9;
            padding: 8px 10px;
            border-radius: 10px;
        }

        .message-footer {
            text-align: right;
            font-size: 0.8rem;
            color: #777;
            margin-top: 8px;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
