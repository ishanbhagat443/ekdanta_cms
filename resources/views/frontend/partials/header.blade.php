@php
    /** @var array $chrome (view composer) */
    $area = $chrome['header'] ?? null;
    $layout = $area['layout'] ?? null;
    $sticky = $layout?->setting('sticky', false);
    $container = $layout?->setting('container', 'container');

    // Logo comes from the Header Builder's logo section so it stays CMS-managed.
    $logoEntry = collect($area['sections'] ?? [])->first(fn ($e) => $e['section']->type === 'logo');

    // One menu for every breakpoint: the last icon on the right, opening as a
    // dropdown. Fed by the CMS menu (Header Builder → Nav Menu).
    $navEntry = collect($area['sections'] ?? [])->first(fn ($e) => $e['section']->type === 'nav-menu');
    $navMenu = $navEntry['menu'] ?? null;

    // Header-over-hero mode (Header Builder): transparent on the first screen,
    // solid once the visitor scrolls.
    $overlayHero = (bool) $layout?->setting('overlay_hero', false);
    $overlayVars = '';
    if ($overlayHero) {
        $overlayVars = '--gsm-header-transparent: '.($layout->setting('transparent_color') ?: 'transparent').';'
            .'--gsm-header-scrolled-bg: '.($layout->setting('scrolled_color') ?: '#ffffff').';'
            .'--gsm-header-blur: '.((int) $layout->setting('blur', 0)).'px;'
            .'--gsm-header-menu: '.($layout->setting('menu_color') ?: '#ffffff').';'
            .'--gsm-header-menu-hover: '.($layout->setting('menu_hover_color') ?: '#c29b54').';';
    }

@endphp

@if ($area)
    <header class="gsm-header gsm-shop-header {{ $overlayHero ? 'gsm-header-overlay' : ($sticky ? 'sticky-top' : '').' bg-white shadow-sm' }} {{ $overlayHero && $layout?->setting('scrolled_shadow', true) ? 'gsm-header-shadow-on-scroll' : '' }}"
            @if ($overlayVars !== '') style="{{ $overlayVars }}" @endif>
        <div class="{{ $container }}">
            <div class="gsm-shop-header__bar">
                <div class="gsm-shop-header__brand">
                    @if ($logoEntry)
                        @include('frontend.partials.render-section', [
                            'section' => $logoEntry['section'],
                            'menu' => $logoEntry['menu'],
                            'mediaMap' => $logoEntry['media'],
                        ])
                    @else
                        <a href="{{ url('/') }}" class="navbar-brand fw-bold">
                            {{ setting('business.company_name', config('gsm.name')) }}
                        </a>
                    @endif
                </div>

                {{-- CENTRE: site search --}}
                <div class="gsm-shop-header__search d-none d-lg-block" data-live-search-wrap>
                    <form action="{{ route('front.blog.index') }}" method="GET" role="search">
                        <i class="fas fa-magnifying-glass gsm-search-icon" aria-hidden="true"></i>
                        <input type="search" name="search" class="gsm-search-input" placeholder="Search…"
                               value="{{ request('search') }}" autocomplete="off" data-live-search
                               aria-label="Search">
                    </form>
                    <div class="gsm-search-results" data-live-search-results hidden></div>
                </div>

                {{-- RIGHT: [search on mobile] Menu --}}
                <div class="gsm-shop-header__actions">
                    <button type="button" class="gsm-header-icon d-lg-none" data-bs-toggle="offcanvas"
                            data-bs-target="#gsm-search-sheet" aria-label="Search">
                        <i class="fas fa-magnifying-glass"></i>
                    </button>

                    {{-- Menu: last icon on the right at every breakpoint.
                         Fed by the CMS menu (Header Builder → Nav Menu). --}}
                    <div class="dropdown gsm-main-menu">
                        <button class="gsm-header-icon" type="button" data-bs-toggle="dropdown"
                                data-bs-display="static" aria-expanded="false" aria-label="Menu">
                            <i class="fas fa-bars"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end gsm-main-menu__list">
                            @if ($navMenu)
                                @foreach ($navMenu->rootItems as $item)
                                    @continue(! $item->isVisibleToCurrentUser())
                                    <li>
                                        @include('frontend.partials.menu-item-link', [
                                            'item' => $item,
                                            'linkClass' => 'dropdown-item',
                                        ])
                                    </li>
                                    @foreach ($item->children as $child)
                                        @continue(! $child->isVisibleToCurrentUser())
                                        @if ($item->is_mega && $child->children->isNotEmpty())
                                            {{-- Mega column flattened: title as a header, then its links. --}}
                                            <li><h6 class="dropdown-header gsm-mega">{{ $child->displayLabel() }}</h6></li>
                                            @foreach ($child->children as $grandchild)
                                                @continue(! $grandchild->isVisibleToCurrentUser())
                                                <li>
                                                    @include('frontend.partials.menu-item-link', [
                                                        'item' => $grandchild,
                                                        'linkClass' => 'dropdown-item ps-4 small gsm-mega',
                                                    ])
                                                </li>
                                            @endforeach
                                        @else
                                            <li>
                                                @include('frontend.partials.menu-item-link', [
                                                    'item' => $child,
                                                    'linkClass' => 'dropdown-item ps-4 small',
                                                ])
                                            </li>
                                        @endif
                                    @endforeach
                                @endforeach
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </header>

    {{-- Mobile search sheet --}}
    <div class="offcanvas offcanvas-top gsm-search-sheet d-lg-none" tabindex="-1" id="gsm-search-sheet" style="height: auto;">
        <div class="offcanvas-header pb-1">
            <h5 class="offcanvas-title">Search</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body pt-1" data-live-search-wrap>
            <form action="{{ route('front.blog.index') }}" method="GET" role="search">
                <input type="search" name="search" class="gsm-search-input" placeholder="Search…"
                       autocomplete="off" data-live-search aria-label="Search">
            </form>
            <div class="gsm-search-results mt-2" data-live-search-results hidden></div>
        </div>
    </div>

    {{-- Admin-defined header CSS/JS --}}
    @if ($layout?->custom_css)
        <style>{!! $layout->custom_css !!}</style>
    @endif
    @if ($layout?->custom_js)
        @push('scripts')
            <script>{!! $layout->custom_js !!}</script>
        @endpush
    @endif
@endif
