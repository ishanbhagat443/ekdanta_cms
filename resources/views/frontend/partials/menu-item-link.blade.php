{{--
    Renders one <a> for a menu item: label + icon + badge + all the
    per-item attributes (css class, id, target). $item = MenuItem.
    $linkClass: extra classes to merge in (e.g. "nav-link dropdown-toggle").
--}}
@if ($item->isVisibleToCurrentUser())
    <a class="{{ trim($linkClass.' '.$item->css_class.' '.($item->isCurrent() ? 'active' : '')) }}"
       @if ($item->element_id) id="{{ $item->element_id }}" @endif
       href="{{ $item->resolvedUrl() }}" target="{{ $item->target }}"
       @if ($item->target === '_blank') rel="noopener" @endif>
        @if ($item->icon)<i class="{{ $item->icon }} me-1"></i>@endif{{ $item->displayLabel() }}
        @if ($item->badge_text)
            <span class="{{ $item->badgeClass() }} ms-1">{{ $item->badge_text }}</span>
        @endif
    </a>
@endif
