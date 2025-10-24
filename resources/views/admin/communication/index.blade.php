{{-- resources/views/admin/communication/index.blade.php --}}
@extends('adminlte::page')

@section('title', 'Communication Center')

@section('content_header')
    <h1>Communication Center</h1>
@stop

@section('content')
    <div class="container-fluid">



        {{-- Send Email Card --}}
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body p-4" >
                <form action="{{ route('communication-setting.send') }}" method="POST">
                    @csrf

                    {{-- Customer Selector --}}
                    <div class="mb-1">
                        <label for="customer_ids" class="form-label">Select Customers</label>
                        <select name="customer_ids[]" id="customer_ids" class="form-control" multiple required>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}">
                                     {{ $customer->email }}
                                </option>
                            @endforeach
                        </select>

                    </div>

                    {{-- Subject --}}
                    <div class="mb-1">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" name="subject" id="subject" class="form-control" required>
                    </div>

                    {{-- Body using Summernote (empty by default) --}}
                    <div class="mb-1">
                        <label for="body" class="form-label">Body</label>
                        <textarea id="summernote" name="body" required></textarea>
                    </div>

                  <div class="d-flex justify-content-end">
    <button type="submit" class="btn btn-dark">
        <i class="fas fa-paper-plane"></i> Send Email
    </button>
</div>

                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
    {{-- Select2 --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet">

    {{-- Summernote --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs4.min.css" rel="stylesheet">

    <style>
        .note-editor.note-frame { border: 1px solid #ced4da; }
        .note-editor.note-frame .note-editable { background-color: #fff; }
        .note-editor .dropdown-menu { z-index: 2050; }
    </style>
@stop

@section('js')
    {{-- Select2 --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>

    {{-- Summernote --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs4.min.js"></script>

    <script>
        $(function () {
            // Initialize Select2
            $('#customer_ids').select2({
                placeholder: "Select one or more customers",
                width: '100%'
            });

            // Initialize Summernote
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
    </script>
@stop
