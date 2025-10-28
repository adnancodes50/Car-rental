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

            <div class="card-body chat-box">
    @forelse($emailLogs as $log)
        @php
            // Handle null sender (system messages)
            $isSentByUser = $log->sent_by == auth()->id();
            $isSystem = is_null($log->sent_by);
            $chatClass = $isSentByUser || $isSystem ? 'sent' : 'received';
            $senderName = $log->sender->name ?? 'Super Admin';
            $senderInitial = strtoupper(substr($senderName, 0, 1));
        @endphp

        <div class="chat-message {{ $chatClass }}">
            <div class="chat-avatar">
                <div class="avatar-circle">{{ $senderInitial }}</div>
            </div>

            <div class="chat-bubble">
                <div class="message-info">
                    <strong>{{ $senderName }}</strong>
                    <small class="text-muted">{{ \Carbon\Carbon::parse($log->sent_at)->format('d M Y, h:i A') }}</small>
                </div>
                <div class="message-body">
                    <p><strong>Subject:</strong> {{ $log->subject }}</p>
                    <div class="message-content">{!! $log->body !!}</div>
                </div>
                <div class="message-footer text-end">
                    <small>
                        To: {{ $log->customer->name ?? 'Unknown' }}
                        ({{ $log->customer->email ?? 'N/A' }})
                    </small>
                </div>
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
