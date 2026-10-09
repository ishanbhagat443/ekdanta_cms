@php
    $data = $section->dataFor('desktop');
    $classes = $section->visibilityClasses();
    $align = $data['alignment'] ?? 'center';
    $alignClass = ['start' => 'me-auto', 'center' => 'mx-auto', 'end' => 'ms-auto'][$align] ?? 'mx-auto';
@endphp

{{-- The inline list only. partials/header decides where and on which
     devices it shows (Header Builder → Navigation style). --}}
@if ($menu)
        <ul class="navbar-nav {{ $alignClass }} gsm-nav {{ $classes }}">
            @foreach ($menu->rootItems as $item)
                @continue(! $item->isVisibleToCurrentUser())
                @php $itemClasses = trim('nav-item '.$item->deviceVisibilityClasses()); @endphp

                @if ($item->children->isEmpty())
                    <li class="{{ $itemClasses }}">
                        @include('frontend.partials.menu-item-link', ['item' => $item, 'linkClass' => 'nav-link'])
                    </li>
                @elseif ($item->is_mega)
                    {{-- Mega menu: children = columns (links/image/featured/html/cta) --}}
                    <li class="{{ $itemClasses }} dropdown gsm-mega position-static">
                        <a class="nav-link dropdown-toggle" href="{{ $item->resolvedUrl() }}"
                           data-bs-toggle="dropdown" role="button">
                            @if ($item->icon)<i class="{{ $item->icon }} me-1"></i>@endif{{ $item->displayLabel() }}
                            @if ($item->badge_text)<span class="{{ $item->badgeClass() }} ms-1">{{ $item->badge_text }}</span>@endif
                        </a>
                        <div class="dropdown-menu w-100 mt-0 border-0 shadow p-4">
                            <div class="container">
                                <div class="row row-cols-{{ min($item->mega_columns ?: 4, $item->children->count()) ?: 1 }}">
                                    @foreach ($item->children as $column)
                                        @include('frontend.partials.mega-menu-column', ['column' => $column])
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </li>
                @else
                    {{-- Standard dropdown --}}
                    <li class="{{ $itemClasses }} dropdown">
                        <a class="nav-link dropdown-toggle" href="{{ $item->resolvedUrl() }}"
                           data-bs-toggle="dropdown" role="button">
                            @if ($item->icon)<i class="{{ $item->icon }} me-1"></i>@endif{{ $item->displayLabel() }}
                            @if ($item->badge_text)<span class="{{ $item->badgeClass() }} ms-1">{{ $item->badge_text }}</span>@endif
                        </a>
                        <ul class="dropdown-menu shadow border-0">
                            @foreach ($item->children as $child)
                                @continue(! $child->isVisibleToCurrentUser())
                                @if ($child->children->isEmpty())
                                    <li>@include('frontend.partials.menu-item-link', ['item' => $child, 'linkClass' => 'dropdown-item'])</li>
                                @else
                                    <li class="dropdown-submenu">
                                        <a class="dropdown-item dropdown-toggle" href="{{ $child->resolvedUrl() }}">{{ $child->displayLabel() }}</a>
                                        <ul class="dropdown-menu shadow border-0">
                                            @foreach ($child->children as $grandchild)
                                                @continue(! $grandchild->isVisibleToCurrentUser())
                                                <li>@include('frontend.partials.menu-item-link', ['item' => $grandchild, 'linkClass' => 'dropdown-item'])</li>
                                            @endforeach
                                        </ul>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </li>
                @endif
            @endforeach
        </ul>
@endif
