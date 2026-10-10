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
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="store_as_enquiry" value="0">
                                <input type="checkbox" class="custom-control-input" id="store_as_enquiry" name="store_as_enquiry" value="1"
                                       @checked(old('store_as_enquiry', $form->enquiry_source !== null))>
                                <label class="custom-control-label" for="store_as_enquiry">Also save submissions as enquiries</label>
                            </div>
                            <small class="text-muted">They land in the <a href="{{ route('admin.enquiries.index') }}">Enquiries</a> inbox with this form's name as the source.</small>
                        </div>
                        <fieldset class="border-top pt-3 mb-3">
                            <legend class="h6 mb-2">After sending</legend>
                            <div class="form-group">
                                <label for="success_action">Then</label>
                                <select name="success_action" id="success_action" class="form-control @error('success_action') is-invalid @enderror">
                                    @foreach (\App\Models\Form::SUCCESS_ACTIONS as $key => $label)
                                        <option value="{{ $key }}" @selected(old('success_action', $form->successAction()) === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="success_page">Page to go to</label>
                                <input type="text" name="success_page" id="success_page" class="form-control @error('success_page') is-invalid @enderror"
                                       value="{{ old('success_page', $form->setting('success_page')) }}" placeholder="/thank-you">
                                @error('success_page')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                <small class="text-muted">For "Go to a page".</small>
                            </div>
                            <div class="form-group">
                                <label for="whatsapp_intro">WhatsApp message: first line</label>
                                <input type="text" name="whatsapp_intro" id="whatsapp_intro" class="form-control"
                                       value="{{ old('whatsapp_intro', $form->setting('whatsapp_intro')) }}" placeholder="Hi! I would like to know more.">
                                <small class="text-muted">For "Open WhatsApp". Each field's WhatsApp sentence follows, with the visitor's answer. It goes to the WhatsApp number in Settings → Business.</small>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-6">
                                    <label for="then_label">Then offer: link text</label>
                                    <input type="text" name="then_label" id="then_label" class="form-control" value="{{ old('then_label', $form->setting('then_label')) }}">
                                </div>
                                <div class="form-group col-6">
                                    <label for="then_page">Then offer: page</label>
                                    <input type="text" name="then_page" id="then_page" class="form-control @error('then_page') is-invalid @enderror"
                                           value="{{ old('then_page', $form->setting('then_page')) }}" placeholder="/thank-you">
                                    @error('then_page')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <small class="text-muted d-block mt-n2">A link shown after WhatsApp opens, e.g. to the thank-you page.</small>
                        </fieldset>
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
                            Drag <i class="fas fa-grip-vertical" aria-hidden="true"></i> to reorder. Options (one per line) apply to
                            Dropdown / Radio / Checkbox. Extra rules use Laravel pipe syntax, e.g. <code>min:3|max:50</code>.
                            <strong>Step</strong> splits the form into steps (fields with the same number show together).
                            A <strong>Hidden</strong> field is not shown and can fill itself with the page address, UTM tags or the referrer.
                        </p>
                        <div id="field-rows">
                            @foreach ($form->fields as $i => $field)
                                @include('admin.forms.partials.field-row', ['i' => $i, 'field' => $field])
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
        @include('admin.forms.partials.field-row', ['i' => '__i__', 'field' => null])
    </template>
@stop

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script src="{{ asset('js/admin/form-builder.js') }}"></script>
@endpush
