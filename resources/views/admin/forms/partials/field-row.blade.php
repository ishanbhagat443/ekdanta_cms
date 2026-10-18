{{--
    One field in the Form Builder. $i = row index ("__i__" in the template
    for new rows), $field = FormField or null. Every control has a visible
    caption (placeholders alone vanish once a value is typed).
--}}
@php
    $field ??= null;
    $type = $field?->type ?? 'text';
    $id = 'f_'.$i;
@endphp
<div class="border rounded p-2 mb-2 field-row bg-light">
    <div class="form-row align-items-end">
        <div class="col-auto text-muted drag-handle pb-2" style="cursor: grab;" title="Drag to reorder"><i class="fas fa-grip-vertical" aria-hidden="true"></i></div>
        <div class="col-md-3">
            <label class="small mb-0" for="{{ $id }}_label">Label</label>
            <input type="text" name="fields[{{ $i }}][label]" id="{{ $id }}_label" class="form-control form-control-sm" value="{{ $field?->label }}">
        </div>
        <div class="col-md-2">
            <label class="small mb-0" for="{{ $id }}_type">Type</label>
            <select name="fields[{{ $i }}][type]" id="{{ $id }}_type" class="form-control form-control-sm field-type">
                @foreach ($fieldTypes as $option)
                    <option value="{{ $option }}" @selected($type === $option)>{{ ucfirst($option) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="small mb-0" for="{{ $id }}_placeholder">Placeholder or hint</label>
            <input type="text" name="fields[{{ $i }}][placeholder]" id="{{ $id }}_placeholder" class="form-control form-control-sm" value="{{ $field?->placeholder }}">
        </div>
        <div class="col-md-2">
            <label class="small mb-0" for="{{ $id }}_rules">Extra rules</label>
            <input type="text" name="fields[{{ $i }}][validation_rules]" id="{{ $id }}_rules" class="form-control form-control-sm" value="{{ $field?->validation_rules }}">
        </div>
        <div class="col-auto pb-1">
            <div class="custom-control custom-checkbox">
                <input type="hidden" name="fields[{{ $i }}][is_required]" value="0">
                <input type="checkbox" class="custom-control-input" id="{{ $id }}_req" name="fields[{{ $i }}][is_required]" value="1" @checked($field?->is_required)>
                <label class="custom-control-label small" for="{{ $id }}_req">Required</label>
            </div>
        </div>
        <div class="col-auto ml-auto pb-1">
            <button type="button" class="btn btn-xs btn-outline-danger remove-field" aria-label="Remove this field"><i class="fas fa-times" aria-hidden="true"></i></button>
        </div>
    </div>
    <div class="form-row mt-1">
        <div class="col-md-2 ml-md-4">
            <label class="small mb-0" for="{{ $id }}_step">Step</label>
            <input type="number" min="1" max="10" name="fields[{{ $i }}][step]" id="{{ $id }}_step" class="form-control form-control-sm" value="{{ $field?->setting('step') }}" placeholder="1">
        </div>
        <div class="col-md-2 options-only" @unless (in_array($type, ['radio', 'checkbox'], true)) style="display:none" @endunless>
            <label class="small mb-0" for="{{ $id }}_display">Show options as</label>
            <select name="fields[{{ $i }}][display]" id="{{ $id }}_display" class="form-control form-control-sm">
                <option value="list" @selected($field?->setting('display') !== 'chips')>List</option>
                <option value="chips" @selected($field?->setting('display') === 'chips')>Chips</option>
            </select>
        </div>
        <div class="col-md-3 hidden-only" @unless ($type === 'hidden') style="display:none" @endunless>
            <label class="small mb-0" for="{{ $id }}_autofill">Fills itself with</label>
            <select name="fields[{{ $i }}][autofill]" id="{{ $id }}_autofill" class="form-control form-control-sm">
                <option value="">Nothing (empty)</option>
                @foreach (\App\Models\FormField::AUTOFILL as $key => $label)
                    <option value="{{ $key }}" @selected($field?->setting('autofill') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col">
            <label class="small mb-0" for="{{ $id }}_whatsapp">WhatsApp sentence <span class="text-muted">({value} = the answer)</span></label>
            <input type="text" name="fields[{{ $i }}][whatsapp]" id="{{ $id }}_whatsapp" class="form-control form-control-sm" value="{{ $field?->setting('whatsapp') }}" placeholder="Business type: {value}.">
        </div>
    </div>
    <div class="form-row mt-1 consent-only" @unless ($type === 'consent') style="display:none" @endunless>
        <div class="col-12 ml-md-4"><p class="small text-muted mb-1">A single box, unticked to start. The label above is its text; a ticked box is saved with the time and these words.</p></div>
        <div class="col-md-4 ml-md-4">
            <label class="small mb-0" for="{{ $id }}_note">Note under the box</label>
            <input type="text" name="fields[{{ $i }}][note]" id="{{ $id }}_note" class="form-control form-control-sm" value="{{ $field?->setting('note') }}">
        </div>
        <div class="col-md-2">
            <label class="small mb-0" for="{{ $id }}_link_text">Link text</label>
            <input type="text" name="fields[{{ $i }}][link_text]" id="{{ $id }}_link_text" class="form-control form-control-sm" value="{{ $field?->setting('link_text') }}" placeholder="Privacy Policy">
        </div>
        <div class="col-md-3">
            <label class="small mb-0" for="{{ $id }}_link_url">Link address</label>
            <input type="text" name="fields[{{ $i }}][link_url]" id="{{ $id }}_link_url" class="form-control form-control-sm" value="{{ $field?->setting('link_url') }}" placeholder="/privacy-policy">
        </div>
        <div class="col-md-5 ml-md-4 mt-1">
            <label class="small mb-0" for="{{ $id }}_short_label">Shorter text for phones <span class="text-muted">(optional)</span></label>
            <input type="text" name="fields[{{ $i }}][short_label]" id="{{ $id }}_short_label" class="form-control form-control-sm" value="{{ $field?->setting('short_label') }}">
        </div>
        <div class="col-md-4 mt-1">
            <label class="small mb-0" for="{{ $id }}_short_note">Shorter note for phones <span class="text-muted">(optional)</span></label>
            <input type="text" name="fields[{{ $i }}][short_note]" id="{{ $id }}_short_note" class="form-control form-control-sm" value="{{ $field?->setting('short_note') }}">
        </div>
    </div>
    <div class="form-row mt-1 options-wrap" @unless ($field?->hasOptions()) style="display:none" @endunless>
        <div class="col ml-md-4">
            <label class="small mb-0" for="{{ $id }}_options">Options (one per line)</label>
            <textarea name="fields[{{ $i }}][options]" id="{{ $id }}_options" rows="2" class="form-control form-control-sm">{{ implode("\n", $field?->options ?? []) }}</textarea>
        </div>
    </div>
</div>
