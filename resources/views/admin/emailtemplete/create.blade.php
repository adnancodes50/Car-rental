{{-- resources/views/admin/emailtemplete/create.blade.php --}}
@extends('adminlte::page')

@section('title', 'Create Email Template')

@section('content_header')
    <h1 class=" container mr-5">Create New Email Template</h1>
@stop

@section('content')
<div class="container-fluid">
    {{-- Error / Success messages --}}
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0 email-template-card">
        <div class="card-body p-4">
            <form action="{{ route('email.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label>Trigger</label>
                        <select name="trigger" class="form-control" required>
                            <option value="purchase" {{ old('trigger') == 'purchase' ? 'selected' : '' }}>Purchase</option>
                            <option value="booking" {{ old('trigger') == 'booking' ? 'selected' : '' }}>Booking</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-2">
                        <label>Recipient</label>
                        <select name="recipient" class="form-control" required>
                            <option value="customer" {{ old('recipient') == 'customer' ? 'selected' : '' }}>Customer</option>
                            <option value="admin" {{ old('recipient') == 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-2">
                        <label>Name</label>
                        <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                    </div>

                    <div class="col-md-6 mb-2">
                        <label>Subject</label>
                        <input type="text" class="form-control" name="subject" value="{{ old('subject') }}" required>
                    </div>

                    {{-- Summernote Editor for Body --}}
                    <div class="col-md-12 mb-2">
                        <label>Body</label>
                        <textarea id="summernote" name="body" required>{{ old('body') }}</textarea>
                    </div>

                    <div class="col-md-6 mb-2">
                        <label>Enabled</label>
                        <select name="enabled" class="form-control" required>
                            <option value="1" {{ old('enabled') == '1' ? 'selected' : '' }}>Enabled</option>
                            <option value="0" {{ old('enabled') == '0' ? 'selected' : '' }}>Disabled</option>
                        </select>
                    </div>

                    <div class="col-md-12 mb-4 d-flex justify-content-between">
                        <a href="{{ route('email.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>

                        <button type="submit" class="btn btn-dark">
                            <i class="fas fa-plus-circle"></i> Create Template
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
    {{-- Summernote CSS --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs4.min.css" rel="stylesheet">
    <style>
        .email-template-card {
            background-color: #fff;
            border-radius: .75rem;
        }

        .note-editor.note-frame {
            border: 1px solid #ced4da;
        }

        .note-editor.note-frame .note-editable {
            background-color: #fff;
        }

        .note-editor .dropdown-menu {
            z-index: 2050;
        }
    </style>
@endsection

@section('js')
    {{-- jQuery, Popper, Bootstrap, Summernote JS --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs4.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#summernote').summernote({
                height: 300,
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['font', ['strikethrough', 'superscript', 'subscript']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        });
    </script>
@endsection
