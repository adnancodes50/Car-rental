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
                        {{-- Trigger --}}
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Trigger <span class="text-danger">*</span></label>
                            <select name="trigger_disabled" class="form-control" id="trigger" disabled>
                                <optgroup label="Booking">
                                    <option value="booking_receipt" @selected(old('trigger', $template->trigger) === 'booking_receipt')>booking_receipt</option>
                                    <option value="booking_confirmation" @selected(old('trigger', $template->trigger) === 'booking_confirmation')>booking_confirmation
                                    </option>
                                </optgroup>
                                <optgroup label="Purchase">
                                    <option value="purchase_receipt" @selected(old('trigger', $template->trigger) === 'purchase_receipt')>purchase_receipt</option>
                                    <option value="purchase_deposit" @selected(old('trigger', $template->trigger) === 'purchase_deposit')>purchase_deposit</option>
                                </optgroup>
                            </select>
                            <input type="hidden" name="trigger" value="{{ old('trigger', $template->trigger) }}">
                            @error('trigger')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Recipient --}}
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Recipient <span class="text-danger">*</span></label>
                            <select name="recipient_disabled" class="form-control" id="recipient" disabled>
                                <option value="customer" @selected(old('recipient', $template->recipient) === 'customer')>customer</option>
                                <option value="admin" @selected(old('recipient', $template->recipient) === 'admin')>admin</option>
                            </select>
                            <input type="hidden" name="recipient" value="{{ old('recipient', $template->recipient) }}">
                            @error('recipient')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Name --}}
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Booking Purpose <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name"
                                value="{{ old('name', $template->name) }}" required>
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Subject --}}
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="subject"
                                value="{{ old('subject', $template->subject) }}" required>
                            @error('subject')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>



                        {{-- Placeholders helper --}}
                        <div class="col-md-12 mb-1 mt-1">
                            <div class="alert alert-secondary" id="ph-box" style="white-space:normal;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong class="mb-2">Available placeholders</strong>
                                    <button type="button" class="btn btn-sm btn-outline-dark" id="copy-ph">Copy
                                        list</button>
                                </div>
                                <code id="ph-list" style="display:block; white-space:pre-wrap;"></code>
                            </div>
                        </div>


                        {{-- Body --}}
                        <div class="col-md-12 mb-2">
                            <label class="form-label">Body <span class="text-danger">*</span></label>
                            <textarea id="summernote" name="body" required>{{ old('body', $template->body) }}</textarea>
                            @error('body')
                                <small class="text-danger d-block">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Enabled --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Enabled <span class="text-danger">*</span></label>
                            <select name="enabled" class="form-control" required>
                                @php
    $enabledValue = old('enabled', $template->enabled);
@endphp

<option value="1" @selected($enabledValue == 1)>Enabled</option>
<option value="0" @selected($enabledValue == 0)>Disabled</option>

                            </select>
                            @error('enabled')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>


                        {{-- Actions --}}
                        <div class="col-md-12 d-flex justify-content-between">
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
    .email-template-card {
        background-color: #fff;
        border-radius: .75rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    /* Summernote frame */
    .note-editor.note-frame {
        border: 1px solid #d1d5db;
        border-radius: .5rem;
        overflow: hidden;
    }
    .note-editor.note-frame .note-editable {
        background-color: #fff;
    }
    .note-editor .dropdown-menu {
        z-index: 2050;
    }

    /* ✨ Improved Placeholder Box */
    #ph-box {
        background: linear-gradient(135deg, #f9fafb 0%, #eef2ff 100%);
        border: 1px solid #cbd5e1;
        border-radius: .75rem;
        padding: 1rem 1.25rem;
        font-family: 'Inter', sans-serif;
    }

    #ph-box strong {
        font-size: 1rem;
        color: #1e293b;
    }

    #ph-box code {
        display: block;
        white-space: pre-wrap;
        font-size: .9rem;
        color: #0f172a;
        margin-top: .5rem;
    }

    #ph-box code kbd,
    #ph-box code span {
        background: #e0e7ff;
        color: #1e3a8a;
        font-weight: 600;
        padding: .25rem .5rem;
        border-radius: .375rem;
        margin: .2rem;
        display: inline-block;
    }

    /* ✨ Copy Button */
    #copy-ph {
        background-color: #1e3a8a;
        color: #fff;
        border: none;
        font-size: .8rem;
        padding: .4rem .75rem;
        border-radius: .5rem;
        transition: background-color .2s;
    }

    #copy-ph:hover {
        background-color: #312e81;
    }
</style>

@endsection

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs4.min.js"></script>
    <script>
        (function() {
            // ---- Summernote
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
                ],
                codeviewFilter: false,
                codeviewIframeFilter: false
            });

            // ---- Placeholder presets
            const COMMON = [
                'app_name', 'year', 'customer_name', 'vehicle_row', 'receipt_button'
            ];
            const BOOKING_EXTRA = [
                'booking_id', 'booking_reference', 'booking_reference_paren',
                'start_date', 'end_date', 'status', 'paid_now', 'total_amount'
            ];
            const PURCHASE_EXTRA = [
                'purchase_id', 'paid_now', 'deposit_paid'
            ];

            function listFor(trigger, recipient) {
                let set = [...COMMON];
                if (trigger === 'booking_receipt' || trigger === 'booking_confirmation') {
                    set = set.concat(BOOKING_EXTRA);
                } else if (trigger === 'purchase_receipt' || trigger === 'purchase_deposit') {
                    set = set.concat(PURCHASE_EXTRA);
                }
                return set;
            }

            function renderPH() {
    const t = document.getElementById('trigger').value;
    const r = document.getElementById('recipient').value;
    const arr = listFor(t, r);
    const phList = document.getElementById('ph-list');
    phList.innerHTML = arr
        .map(k => `<span>@{{${k}}}</span>`)
        .join(' ');
}


            document.getElementById('trigger').addEventListener('change', renderPH);
            document.getElementById('recipient').addEventListener('change', renderPH);
            renderPH();

            document.getElementById('copy-ph').addEventListener('click', function() {
                const txt = document.getElementById('ph-list').textContent;
                navigator.clipboard.writeText(txt).then(() => {
                    this.textContent = 'Copied!';
                    setTimeout(() => this.textContent = 'Copy list', 1200);
                });
            });
        })();
    </script>
@endpush
