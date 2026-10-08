@php
    $item = $node['item'];
    $reference = $item->reference();
    $referenceText = $reference?->title ?? $reference?->name ?? '';
    // Built here (not inline in @json) because Blade's @json directive splits
    // its argument on commas — an inline array literal's commas truncate it
    // and break compilation ("Unclosed '[' … does not match ')'").
    $itemData = $item->only([
        'id', 'label', 'nav_label', 'link_type', 'url', 'reference_id', 'icon', 'target',
        'css_class', 'element_id', 'badge_text', 'badge_color', 'permission', 'auth_only',
        'guest_only', 'is_mega', 'mega_columns', 'column_type', 'image_media_id', 'custom_html',
        'cta_label', 'cta_url', 'cta_style', 'featured_limit', 'visible_devices', 'is_active',
    ]);
@endphp

<li class="gsm-tree-item mb-1" data-id="{{ $item->id }}">
    <div class="d-flex align-items-center border rounded bg-white px-2 py-1 {{ $item->is_active ? '' : 'opacity-50' }}">
        <span class="gsm-tree-handle text-muted mr-2" style="cursor: grab;"><i class="fas fa-grip-vertical"></i></span>
        @if ($item->icon)<i class="{{ $item->icon }} fa-fw mr-1 text-muted"></i>@endif
        <span class="mr-2">{{ $item->label }}</span>
        @if ($item->nav_label && $item->nav_label !== $item->label)
            <small class="text-muted mr-1">({{ $item->nav_label }})</small>
        @endif
        @if ($item->badge_text)
            <span class="{{ $item->badgeClass() }} mr-1">{{ $item->badge_text }}</span>
        @endif
        @if ($item->is_mega)<span class="badge badge-warning mr-1">MEGA</span>@endif
        @if ($item->column_type && $item->column_type !== 'links')<span class="badge badge-purple mr-1" style="background:#6f42c1;color:#fff;">{{ ucfirst(str_replace('_', ' ', $item->column_type)) }}</span>@endif
        @if ($item->auth_only)<span class="badge badge-dark mr-1" title="Logged-in only"><i class="fas fa-lock"></i></span>@endif
        @if ($item->guest_only)<span class="badge badge-dark mr-1" title="Guests only"><i class="fas fa-user-slash"></i></span>@endif
        @if ($item->permission)<span class="badge badge-info mr-1" title="Requires: {{ $item->permission }}"><i class="fas fa-key"></i></span>@endif
        @unless ($item->is_active)<span class="badge badge-secondary mr-1">hidden</span>@endunless
        <small class="text-muted text-truncate" style="max-width: 180px;">{{ $item->url }}</small>
        <div class="ml-auto">
            <button type="button" class="btn btn-xs btn-outline-primary gsm-item-edit"
                    data-item='@json($itemData)'
                    data-reference-text="{{ $referenceText }}">
                <i class="fas fa-pen"></i>
            </button>
            <button type="button" class="btn btn-xs btn-outline-danger gsm-item-delete"><i class="fas fa-trash"></i></button>
        </div>
    </div>
    <ol class="gsm-tree list-unstyled ml-4 mt-1">
        @foreach ($node['children'] as $child)
            @include('admin.menus.partials.tree-item', ['node' => $child])
        @endforeach
    </ol>
</li>
