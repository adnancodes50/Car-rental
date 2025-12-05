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
                                <optgroup label="Booking Receipts">
                                    <option value="booking_receipt" @selected(old('trigger', $template->trigger) === 'booking_receipt')>booking_receipt</option>
                                </optgroup>
                                <optgroup label="Purchase Receipts">
                                    <option value="purchase_receipt" @selected(old('trigger', $template->trigger) === 'purchase_receipt')>purchase_receipt</option>
                                </optgroup>
                                <optgroup label="Booking Status">
                                    <option value="booking_pending" @selected(old('trigger', $template->trigger) === 'booking_pending')>booking_pending</option>
                                    <option value="booking_confirmed" @selected(old('trigger', $template->trigger) === 'booking_confirmed')>booking_confirmed</option>
                                    <option value="booking_complete" @selected(old('trigger', $template->trigger) === 'booking_complete')>booking_complete</option>
                                    <option value="booking_cancelled" @selected(old('trigger', $template->trigger) === 'booking_cancelled')>booking_cancelled</option>
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
                            <label class="form-label">Template Name <span class="text-danger">*</span></label>
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
                                    <strong class="mb-2">Available Placeholders</strong>
                                    <button type="button" class="btn btn-sm btn-outline-dark" id="copy-ph">Copy All</button>
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

        .note-editor.note-frame { border: 1px solid #d1d5db; border-radius: .5rem; overflow: hidden; }
        .note-editor.note-frame .note-editable { background-color: #fff; }
        .note-editor .dropdown-menu { z-index: 2050; }

        #ph-box { background: linear-gradient(135deg, #f9fafb 0%, #eef2ff 100%); border: 1px solid #cbd5e1; border-radius: .75rem; padding: 1rem 1.25rem; font-family: 'Inter', sans-serif; }
        #ph-box strong { font-size: 1rem; color: #1e293b; }
        #ph-box code { display: block; white-space: pre-wrap; font-size: .9rem; color: #0f172a; margin-top: .5rem; line-height: 1.6; }
        #ph-box code .ph-item { background: #e0e7ff; color: #1e3a8a; font-weight: 600; padding: .25rem .5rem; border-radius: .375rem; margin: .2rem; display: inline-block; font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace; font-size: 0.85rem; }

        #copy-ph { background-color: #1e3a8a; color: #fff; border: none; font-size: .8rem; padding: .4rem .75rem; border-radius: .5rem; transition: background-color .2s; }
        #copy-ph:hover { background-color: #312e81; }
    </style>
@stop

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs4.min.js"></script>
    <script>
        (function() {
            $('#summernote').summernote({
                height: 400,
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

            const COMMON_PLACEHOLDERS = ['app_name', 'year', 'customer_name', 'logo_url', 'header_text', 'lead_title', 'lead_line', 'summary_title', 'footer_message', 'receipt_url'];
            const BOOKING_PLACEHOLDERS = ['booking_reference', 'start_date', 'end_date', 'status', 'paid_now', 'total_amount', 'equipment_name', 'location_name'];
            const PURCHASE_PLACEHOLDERS = ['purchase_id', 'paid_now', 'deposit_paid', 'total_amount', 'equipment_name', 'location_name', 'quantity'];

            function getPlaceholdersForTrigger(trigger, recipient) {
                let placeholders = [...COMMON_PLACEHOLDERS];
                if (trigger.startsWith('booking_')) placeholders = placeholders.concat(BOOKING_PLACEHOLDERS);
                else if (trigger.startsWith('purchase_')) placeholders = placeholders.concat(PURCHASE_PLACEHOLDERS);
                return placeholders;
            }

            function renderPlaceholders() {
                const trigger = document.getElementById('trigger').value;
                const recipient = document.getElementById('recipient').value;
                const placeholders = getPlaceholdersForTrigger(trigger, recipient);
                const phList = document.getElementById('ph-list');
                phList.innerHTML = placeholders.map(ph => `<span class="ph-item">${ph}</span>`).join(' ');
            }

            document.getElementById('trigger').addEventListener('change', renderPlaceholders);
            document.getElementById('recipient').addEventListener('change', renderPlaceholders);
            renderPlaceholders();

            document.getElementById('copy-ph').addEventListener('click', function() {
                const placeholders = document.getElementById('ph-list').textContent;
                navigator.clipboard.writeText(placeholders).then(() => {
                    const originalText = this.textContent;
                    this.textContent = 'Copied!';
                    this.classList.add('btn-success');
                    setTimeout(() => { this.textContent = originalText; this.classList.remove('btn-success'); }, 1500);
                }).catch(err => {
                    console.error('Failed to copy: ', err);
                    this.textContent = 'Failed!';
                    setTimeout(() => { this.textContent = 'Copy All'; }, 1500);
                });
            });

            document.getElementById('trigger').addEventListener('change', function() {
                document.querySelector('input[name="trigger"]').value = this.value;
            });
            document.getElementById('recipient').addEventListener('change', function() {
                document.querySelector('input[name="recipient"]').value = this.value;
            });
        })();
    </script>
@endpush
