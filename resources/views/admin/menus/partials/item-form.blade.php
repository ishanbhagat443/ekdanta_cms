@php $prefix = $prefix ?? ''; @endphp
{{--
    Shared field set for both "Add Item" (plain form, id=gsm-add-item-form)
    and "Edit Item" (modal, id=gsm-edit-item-form). JS reads/writes these
    inputs by `name` — the modal duplicates names, scoped by its own form.
--}}
<div class="form-row">
    <div class="col-12 col-md-6">
        <div class="form-group">
            <label><small>Title <span class="text-danger">*</span></small></label>
            <input type="text" name="label" required class="form-control form-control-sm" placeholder="Admin-facing title">
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="form-group">
            <label><small>Navigation Label <span class="text-muted">(shown to visitors)</span></small></label>
            <input type="text" name="nav_label" class="form-control form-control-sm" placeholder="Defaults to Title">
        </div>
    </div>
</div>

<div class="form-group">
    <label><small>Link Type</small></label>
    <select name="link_type" class="form-control form-control-sm gsm-link-type">
        @foreach ($linkTypes as $key => $label)
            <option value="{{ $key }}">{{ $label }}</option>
        @endforeach
    </select>
</div>

{{-- URL: shown for url/custom/anchor/phone/email --}}
<div class="form-group gsm-field-url">
    <label><small class="gsm-url-label">URL</small></label>
    <input type="text" name="url" class="form-control form-control-sm" placeholder="/about or https://…">
</div>

{{-- Reference picker: shown for page/blog/blog_category --}}
<div class="form-group gsm-field-reference" style="display:none;">
    <label><small class="gsm-reference-label">Select</small></label>
    <select name="reference_id" class="form-control form-control-sm gsm-reference-select">
        <option value="">Type to search…</option>
    </select>
</div>

<div class="form-group">
    <label><small>Parent</small></label>
    <select name="parent_id" class="form-control form-control-sm gsm-parent-select">
        <option value="">— Top level —</option>
        @foreach ($menu->items->whereNull('parent_id') as $rootItem)
            <option value="{{ $rootItem->id }}">{{ $rootItem->label }}</option>
            @foreach ($menu->items->where('parent_id', $rootItem->id) as $childItem)
                <option value="{{ $childItem->id }}">&nbsp;&nbsp;— {{ $childItem->label }}</option>
            @endforeach
        @endforeach
    </select>
</div>

<div class="form-row">
    <div class="col-6 col-md-4">
        <div class="form-group">
            <label><small>Icon</small></label>
            <input type="text" name="icon" placeholder="fas fa-star" class="form-control form-control-sm">
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="form-group">
            <label><small>Badge Text</small></label>
            <input type="text" name="badge_text" placeholder="New" class="form-control form-control-sm">
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="form-group">
            <label><small>Badge Color</small></label>
            <select name="badge_color" class="form-control form-control-sm">
                <option value="">Default</option>
                @foreach ($badgeColors as $color)
                    <option value="{{ $color }}">{{ ucfirst($color) }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="form-row">
    <div class="col-6">
        <div class="form-group">
            <label><small>CSS Class</small></label>
            <input type="text" name="css_class" class="form-control form-control-sm" placeholder="my-custom-class">
        </div>
    </div>
    <div class="col-6">
        <div class="form-group">
            <label><small>ID Attribute</small></label>
            <input type="text" name="element_id" class="form-control form-control-sm" placeholder="my-anchor-id">
        </div>
    </div>
</div>

<div class="form-group">
    <label><small>Visibility — Permission Required</small></label>
    <select name="permission" class="form-control form-control-sm">
        <option value="">— Everyone (per auth rules below) —</option>
        @foreach ($permissions as $permission)
            <option value="{{ $permission }}">{{ $permission }}</option>
        @endforeach
    </select>
</div>

<div class="form-row">
    <div class="col-4">
        <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="{{ $prefix }}new_tab" name="new_tab">
            <label class="custom-control-label" for="{{ $prefix }}new_tab"><small>New tab</small></label>
        </div>
    </div>
    <div class="col-4">
        <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="{{ $prefix }}auth_only" name="auth_only">
            <label class="custom-control-label" for="{{ $prefix }}auth_only"><small>Logged-in only</small></label>
        </div>
    </div>
    <div class="col-4">
        <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="{{ $prefix }}guest_only" name="guest_only">
            <label class="custom-control-label" for="{{ $prefix }}guest_only"><small>Guests only</small></label>
        </div>
    </div>
</div>

<div class="form-row">
    <div class="col-6">
        <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input gsm-is-mega" id="{{ $prefix }}is_mega" name="is_mega">
            <label class="custom-control-label" for="{{ $prefix }}is_mega"><small>Mega menu (children become columns)</small></label>
        </div>
    </div>
    <div class="col-6">
        <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="{{ $prefix }}is_active" name="is_active" checked>
            <label class="custom-control-label" for="{{ $prefix }}is_active"><small>Active</small></label>
        </div>
    </div>
</div>

{{-- Mega menu settings: column count --}}
<div class="form-group gsm-field-mega" style="display:none;">
    <label><small>Mega Menu Columns</small></label>
    <input type="number" name="mega_columns" min="1" max="6" value="4" class="form-control form-control-sm">
</div>

<hr class="gsm-field-column-header" style="display:none;">
<div class="gsm-field-column-header" style="display:none;">
    <p class="text-muted small mb-2"><i class="fas fa-columns mr-1"></i> Mega Menu Column Content
        <span class="text-muted">(only applies when this item's parent is a Mega menu)</span></p>
</div>

<div class="form-group gsm-field-column-type" style="display:none;">
    <label><small>Column Type</small></label>
    <select name="column_type" class="form-control form-control-sm gsm-column-type">
        <option value="links">Link List (default — this item's children)</option>
        @foreach ($columnTypes as $key => $label)
            @unless ($key === 'links')
                <option value="{{ $key }}">{{ $label }}</option>
            @endunless
        @endforeach
    </select>
</div>

<div class="form-group gsm-field-image" style="display:none;">
    <label><small>Image</small></label>
    <div class="input-group input-group-sm">
        <input type="number" name="image_media_id" id="{{ $prefix }}image_media_id" class="form-control">
        <div class="input-group-append">
            <button type="button" class="btn btn-outline-primary gsm-bf-media-pick"
                    data-target="{{ $prefix }}image_media_id"><i class="fas fa-photo-video"></i></button>
        </div>
    </div>
</div>

<div class="form-group gsm-field-html" style="display:none;">
    <label><small>Custom HTML</small></label>
    <textarea name="custom_html" rows="3" class="form-control form-control-sm text-monospace" spellcheck="false"></textarea>
</div>

<div class="gsm-field-cta" style="display:none;">
    <div class="form-row">
        <div class="col-6">
            <div class="form-group">
                <label><small>CTA Label</small></label>
                <input type="text" name="cta_label" class="form-control form-control-sm">
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                <label><small>CTA Style</small></label>
                <select name="cta_style" class="form-control form-control-sm">
                    <option value="primary">Filled</option>
                    <option value="outline">Outline</option>
                    <option value="link">Link</option>
                </select>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label><small>CTA URL</small></label>
        <input type="text" name="cta_url" class="form-control form-control-sm" placeholder="/enquiry">
    </div>
</div>

<div class="form-group gsm-field-featured" style="display:none;">
    <label><small>Number to Show</small></label>
    <input type="number" name="featured_limit" min="1" max="12" value="4" class="form-control form-control-sm">
</div>

<div class="form-group mb-0">
    <label><small>Responsive — Show On</small></label>
    <div class="d-flex gap-3">
        @foreach (['desktop' => 'Desktop', 'tablet' => 'Tablet', 'mobile' => 'Mobile'] as $device => $label)
            <div class="custom-control custom-checkbox custom-control-inline">
                <input type="checkbox" class="custom-control-input gsm-visible-device" id="{{ $prefix }}device_{{ $device }}"
                       name="visible_devices[]" value="{{ $device }}" checked>
                <label class="custom-control-label" for="{{ $prefix }}device_{{ $device }}"><small>{{ $label }}</small></label>
            </div>
        @endforeach
    </div>
</div>
