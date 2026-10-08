{{--
    One mega-menu column. $column = MenuItem (a direct child of the
    is_mega parent); $column->column_type decides what renders.
    "links" (default) falls back to the classic label-list behaviour.
--}}
@if ($column->isVisibleToCurrentUser())
    <div class="col mb-3 mb-lg-0">
        @if ($column->label)
            <div class="fw-bold text-uppercase small mb-2">{{ $column->displayLabel() }}</div>
        @endif

        @switch($column->column_type ?? 'links')
            @case('image')
                @if ($column->image)
                    <a href="{{ $column->resolvedUrl() }}" target="{{ $column->target }}">
                        <img src="{{ $column->image->variantUrl('card') ?? $column->image->url }}"
                             alt="{{ $column->displayLabel() }}" class="img-fluid rounded" loading="lazy" decoding="async">
                    </a>
                @endif
                @break
            {{-- $column->image is MenuItem::image() → media, not Category::imageMedia() --}}

            @case('html')
                <div class="gsm-mega-html">{!! $column->custom_html !!}</div>
                @break

            @case('cta')
                <a href="{{ $column->cta_url ?: $column->resolvedUrl() }}" target="{{ $column->target }}"
                   class="btn btn-sm {{ ['outline' => 'btn-outline-primary', 'link' => 'btn-link'][$column->cta_style ?? 'primary'] ?? 'btn-primary gsm-btn-gradient text-white' }}">
                    {{ $column->cta_label ?: $column->displayLabel() }}
                </a>
                @break

            @default
                @foreach ($column->children as $link)
                    @include('frontend.partials.menu-item-link', ['item' => $link, 'linkClass' => 'dropdown-item px-0 d-block'])
                @endforeach
        @endswitch
    </div>
@endif
