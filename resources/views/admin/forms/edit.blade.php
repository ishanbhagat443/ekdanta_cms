@extends('admin.layouts.app')

@section('page_title', 'Edit Form — ' . $form->name)

@section('page_actions')
    <div>
        <a href="{{ route('admin.forms.submissions', $form) }}" class="btn btn-info btn-sm">
            <i class="fas fa-inbox mr-1"></i> Submissions
        </a>
        <a href="{{ route('admin.forms.index') }}" class="btn btn-default btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> All Forms
        </a>
    </div>
@stop

@section('page_content')
    <form action="{{ route('admin.forms.update', $form) }}" method="POST">
        @csrf @method('PUT')
        <div class="row">
            {{-- ── Settings ─────────────────────────────────────── --}}
            <div class="col-lg-4">
                <div class="card card-primary card-outline">
                    <div class="card-header"><h3 class="card-title">Form Settings</h3></div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $form->name) }}" required>
                            @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label for="slug">Slug</label>
                            <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror"
                                   value="{{ old('slug', $form->slug) }}">
                            @error('slug')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            <small class="text-muted">Submits to <code>POST /forms/{{ $form->slug }}</code></small>
                        </div>
                        <div class="form-group">
                            <label for="heading">Heading (shown above form)</label>
                            <input type="text" name="heading" id="heading" class="form-control"
                                   value="{{ old('heading', $form->heading) }}" placeholder="e.g. Get In Touch">
                        </div>
                        <div class="form-group">
                            <label for="button_label">Submit Button Label</label>
                            <input type="text" name="button_label" id="button_label" class="form-control"
                                   value="{{ old('button_label', $form->button_label) }}">
                        </div>
                        <div class="form-group">
                            <label for="success_message">Success Message</label>
                            <input type="text" name="success_message" id="success_message" class="form-control"
                                   value="{{ old('success_message', $form->success_message) }}">
                        </div>
                        <div class="form-group">
                            <label for="notify_emails_csv">Notification Emails</label>
                            <input type="text" name="notify_emails_csv" id="notify_emails_csv"
                                   class="form-control @error('notify_emails_csv') is-invalid @enderror"
                                   value="{{ old('notify_emails_csv', implode(', ', $form->notify_emails ?? [])) }}"
                                   placeholder="sales@example.com, info@example.com">
                            @error('notify_emails_csv')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            <small class="text-muted">Comma-separated. Each address gets an email per submission.</small>
                        </div>
                        <div class="form-group">
                            <label for="enquiry_source">Store Submissions As Enquiry</label>
                            <select name="enquiry_source" id="enquiry_source" class="form-control">
                                <option value="">— No (keep in Form Submissions only) —</option>
                                @foreach (\App\Models\Enquiry::SOURCES as $key => $label)
                                    <option value="{{ $key }}" @selected(old('enquiry_source', $form->enquiry_source) === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Submissions also land in the central <a href="{{ route('admin.enquiries.index') }}">Enquiries</a> inbox under this source.</small>
                        </div>
                        <div class="custom-control custom-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                                   @checked(old('is_active', $form->is_active))>
                            <label class="custom-control-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Fields builder ───────────────────────────────── --}}
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Fields</h3>
                        <button type="button" class="btn btn-sm btn-success" id="add-field">
                            <i class="fas fa-plus mr-1"></i> Add Field
                        </button>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            Drag <i class="fas fa-grip-vertical"></i> to reorder. Options textarea (one per line) applies to
                            Dropdown / Radio / Checkbox. Extra rules use Laravel pipe syntax, e.g. <code>min:3|max:50</code>.
                        </p>
                        <div id="field-rows">
                            @foreach ($form->fields as $i => $field)
                                <div class="border rounded p-2 mb-2 field-row bg-light">
                                    <div class="form-row align-items-center">
                                        <div class="col-auto text-muted drag-handle" style="cursor: grab;"><i class="fas fa-grip-vertical"></i></div>
                                        <div class="col-md-3">
                                            <input type="text" name="fields[{{ $i }}][label]" class="form-control form-control-sm"
                                                   placeholder="Label" value="{{ $field->label }}">
                                        </div>
                                        <div class="col-md-2">
                                            <select name="fields[{{ $i }}][type]" class="form-control form-control-sm field-type">
                                                @foreach ($fieldTypes as $type)
                                                    <option value="{{ $type }}" @selected($field->type === $type)>{{ ucfirst($type) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" name="fields[{{ $i }}][placeholder]" class="form-control form-control-sm"
                                                   placeholder="Placeholder" value="{{ $field->placeholder }}">
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" name="fields[{{ $i }}][validation_rules]" class="form-control form-control-sm"
                                                   placeholder="Extra rules" value="{{ $field->validation_rules }}">
                                        </div>
                                        <div class="col-auto">
                                            <div class="custom-control custom-checkbox">
                                                <input type="hidden" name="fields[{{ $i }}][is_required]" value="0">
                                                <input type="checkbox" class="custom-control-input" id="req_{{ $i }}"
                                                       name="fields[{{ $i }}][is_required]" value="1" @checked($field->is_required)>
                                                <label class="custom-control-label small" for="req_{{ $i }}">Req.</label>
                                            </div>
                                        </div>
                                        <div class="col-auto ml-auto">
                                            <button type="button" class="btn btn-xs btn-outline-danger remove-field"><i class="fas fa-times"></i></button>
                                        </div>
                                    </div>
                                    <div class="form-row mt-1 options-wrap" @unless($field->hasOptions()) style="display:none" @endunless>
                                        <div class="col-11 offset-1">
                                            <textarea name="fields[{{ $i }}][options]" rows="2" class="form-control form-control-sm"
                                                      placeholder="One option per line">{{ implode("\n", $field->options ?? []) }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <p class="text-muted small mb-0 @if($form->fields->isNotEmpty()) d-none @endif" id="no-fields-hint">
                            No fields yet — click “Add Field”.
                        </p>
                    </div>
                    <div class="card-footer text-right">
                        <button class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save Form</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- JS template for new rows --}}
    <template id="field-row-template">
        <div class="border rounded p-2 mb-2 field-row bg-light">
            <div class="form-row align-items-center">
                <div class="col-auto text-muted drag-handle" style="cursor: grab;"><i class="fas fa-grip-vertical"></i></div>
                <div class="col-md-3">
                    <input type="text" name="fields[__i__][label]" class="form-control form-control-sm" placeholder="Label">
                </div>
                <div class="col-md-2">
                    <select name="fields[__i__][type]" class="form-control form-control-sm field-type">
                        @foreach ($fieldTypes as $type)
                            <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="fields[__i__][placeholder]" class="form-control form-control-sm" placeholder="Placeholder">
                </div>
                <div class="col-md-2">
                    <input type="text" name="fields[__i__][validation_rules]" class="form-control form-control-sm" placeholder="Extra rules">
                </div>
                <div class="col-auto">
                    <div class="custom-control custom-checkbox">
                        <input type="hidden" name="fields[__i__][is_required]" value="0">
                        <input type="checkbox" class="custom-control-input" id="req___i__" name="fields[__i__][is_required]" value="1">
                        <label class="custom-control-label small" for="req___i__">Req.</label>
                    </div>
                </div>
                <div class="col-auto ml-auto">
                    <button type="button" class="btn btn-xs btn-outline-danger remove-field"><i class="fas fa-times"></i></button>
                </div>
            </div>
            <div class="form-row mt-1 options-wrap" style="display:none">
                <div class="col-11 offset-1">
                    <textarea name="fields[__i__][options]" rows="2" class="form-control form-control-sm" placeholder="One option per line"></textarea>
                </div>
            </div>
        </div>
    </template>
@stop

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script src="{{ asset('js/admin/form-builder.js') }}"></script>
@endpush
