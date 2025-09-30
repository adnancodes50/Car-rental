{{-- resources/views/admin/emailtemplete/edit.blade.php --}}
@extends('adminlte::page')

@section('title', 'Edit Email Template')

@section('content_header')
    <h1 class="container mr-5">Edit Email Template</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 email-template-card">
        <div class="card-body p-4">
            <form action="{{ route('email.update', $template->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label>Trigger</label>
                        <select name="trigger" class="form-control" required>
                            <option value="purchase" @selected(old('trigger', $template->trigger) === 'purchase')>Purchase</option>
                            <option value="booking"  @selected(old('trigger', $template->trigger) === 'booking')>Booking</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-2">
                        <label>Recipient</label>
                        <select name="recipient" class="form-control" required>
                            <option value="customer" @selected(old('recipient', $template->recipient) === 'customer')>Customer</option>
                            <option value="admin"    @selected(old('recipient', $template->recipient) === 'admin')>Admin</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-2">
                        <label>Name</label>
                        <input type="text" class="form-control" name="name" value="{{ old('name', $template->name) }}" required>
                    </div>

                    <div class="col-md-6 mb-2">
                        <label>Subject</label>
                        <input type="text" class="form-control" name="subject" value="{{ old('subject', $template->subject) }}" required>
                    </div>

                    <div class="col-md-12 mb-2">
                        <label>Body</label>
                        <textarea id="summernote" name="body" required>{{ old('body', $template->body) }}</textarea>
                    </div>

                    <div class="col-md-6 mb-2">
                        <label>Enabled</label>
                        <select name="enabled" class="form-control" required>
                            <option value="1" @selected(old('enabled', (string)$template->enabled) === '1')>Enabled</option>
                            <option value="0" @selected(old('enabled', (string)$template->enabled) === '0')>Disabled</option>
                        </select>
                    </div>

                    <div class="col-md-12 mb-1 mt-1 d-flex justify-content-between">
                        <a href="{{ route('email.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                        <button type="submit" class="btn btn-dark">
                            <i class="fas fa-save"></i> Update Template
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs4.min.css" rel="stylesheet">
<style>
    .email-template-card { background-color:#fff; border-radius:.75rem; }
    .note-editor.note-frame { border:1px solid #ced4da; }
    .note-editor.note-frame .note-editable { background-color:#fff; }
    .note-editor .dropdown-menu { z-index:2050; }
</style>
@endsection

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs4.min.js"></script>

<script>
$(function () {
    $('#summernote').summernote({
        height: 300,
        toolbar: [
            ['style', ['bold','italic','underline','clear']],
            ['font', ['strikethrough','superscript','subscript']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul','ol','paragraph']],
            ['insert', ['link','picture','video']],
            ['view', ['fullscreen','codeview','help']]
        ]
    });
});

</script>
@endpush
