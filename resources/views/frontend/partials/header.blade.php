@php
    /** @var array $chrome (view composer) */
    $area = $chrome['header'] ?? null;
    $layout = $area['layout'] ?? null;
    $sticky = $layout?->setting('sticky', false);
    $container = $layout?->setting('container', 'container');

    // Header Builder sections, in the order the admin arranged them. The logo
    // leads, the menu is placed by the navigation style, everything else
    // (CTA button, search, language switcher…) sits on the right.
    $entries = collect($area['sections'] ?? []);
    $logoEntry = $entries->first(fn ($e) => $e['section']->type === 'logo');
    $navEntry = $entries->first(fn ($e) => $e['section']->type === 'nav-menu');
    $extras = $entries->reject(fn ($e) => in_array($e['section']->type, ['logo', 'nav-menu'], true));

    // Page settings → "Header and footer on this page": style, menu and
    // button text/link override the Header Builder defaults for one page.
    $pageHeader = (array) (($currentPage ?? null)?->setting('header', []) ?? []);
    $variant = $pageHeader['variant'] ?? ($layout?->headerVariant() ?? 'normal');
    $navMenu = ! empty($pageHeader['menu_id'])
        ? app(\App\Repositories\Contracts\MenuRepositoryInterface::class)->tree((int) $pageHeader['menu_id'])
        : ($navEntry['menu'] ?? null);
    $ctaOverride = array_filter(['label' => $pageHeader['cta_label'] ?? null, 'url' => $pageHeader['cta_url'] ?? null]);
    $whatsapp = $layout?->setting('whatsapp_button', 'off') !== 'off' ? business_whatsapp() : null;

    // Header Builder → Navigation style, per device: inline links,
    // hamburger dropdown or full-screen overlay.
    $styles = collect(['mobile', 'tablet', 'desktop'])->mapWithKeys(fn ($d) => [$d => $layout?->navStyle($d) ?? 'dropdown'])->all();
    $showFor = fn (string $style, string $display = 'block') => \App\Models\Section::displayClasses(
        $styles['mobile'] === $style, $styles['tablet'] === $style, $styles['desktop'] === $style, $display
    );
    $uses = fn (string $style) => in_array($style, $styles, true);

    // Header-over-hero mode (Header Builder): transparent on the first screen,
    // solid once the visitor scrolls.
    $overlayHero = $variant === 'overlay';
    $overlayVars = '';
    if ($overlayHero) {
        $overlayVars = '--gsm-header-transparent: '.($layout->setting('transparent_color') ?: 'transparent').';'
            .'--gsm-header-scrolled-bg: '.($layout->setting('scrolled_color') ?: '#ffffff').';'
            .'--gsm-header-blur: '.((int) $layout->setting('blur', 0)).'px;'
            .'--gsm-header-menu: '.($layout->setting('menu_color') ?: '#ffffff').';'
            .'--gsm-header-menu-hover: '.($layout->setting('menu_hover_color') ?: 'var(--gsm-primary)').';';
    }

    $brand = fn () => $logoEntry
        ? view('frontend.partials.render-section', [
            'section' => $logoEntry['section'], 'menu' => $logoEntry['menu'], 'mediaMap' => $logoEntry['media'],
        ])
        : '<a href="'.e(url('/')).'" class="navbar-brand fw-bold">'.e(setting('business.company_name', '')).'</a>';
@endphp

@if ($area && $layout?->setting('progress_bar'))
    {{-- Scroll progress across the very top (Header Builder). --}}
    <div class="gsm-scroll-progress" aria-hidden="true" style="--gsm-progress-color: {{ $layout->setting('progress_color') ?: 'var(--gsm-accent)' }}"></div>
@endif

@if ($area && $variant !== 'hidden')
    <header class="gsm-header gsm-site-header {{ match ($variant) {
                'overlay' => 'gsm-header-overlay',
                'pill' => 'gsm-header-pill',
                default => ($sticky ? 'sticky-top' : '').' bg-white shadow-sm',
            } }} {{ $overlayHero && $layout?->setting('scrolled_shadow', true) ? 'gsm-header-shadow-on-scroll' : '' }}"
            @if ($overlayVars !== '') style="{{ $overlayVars }}" @endif>
        <div class="{{ $container }}">
            <div class="gsm-site-header__bar">
                <div class="gsm-site-header__brand">{!! $brand() !!}</div>

                {{-- The inline menu is the Header Builder's menu section (its style), so it needs one. --}}
                @if ($navMenu && $navEntry && $uses('inline'))
                    <nav class="navbar navbar-expand p-0 gsm-site-header__nav {{ $showFor('inline', 'flex') }}" aria-label="Main">
                        @include('frontend.partials.render-section', [
                            'section' => $navEntry['section'], 'menu' => $navMenu, 'mediaMap' => $navEntry['media'],
                        ])
                    </nav>
                @endif

                <div class="gsm-site-header__actions">
                    @foreach ($extras as $entry)
                        @php
                            $extraSection = $entry['section'];
                            // This page's own header button text/link.
                            if ($extraSection->type === 'cta-button' && $ctaOverride !== []) {
                                $extraSection = clone $extraSection;
                                $extraSection->data_desktop = array_replace((array) $extraSection->data_desktop, $ctaOverride);
                                $extraSection->data_tablet = array_diff_key((array) $extraSection->data_tablet, $ctaOverride);
                                $extraSection->data_mobile = array_diff_key((array) $extraSection->data_mobile, $ctaOverride);
                            }
                        @endphp
                        @include('frontend.partials.render-section', [
                            'section' => $extraSection, 'menu' => $entry['menu'], 'mediaMap' => $entry['media'],
                        ])
                    @endforeach

                    @if ($whatsapp)
                        <a href="{{ $whatsapp['wa'] }}" target="_blank" rel="noopener"
                           class="gsm-header-icon gsm-header-whatsapp {{ $layout->setting('whatsapp_button') === 'mobile' ? 'd-inline-flex d-lg-none' : '' }}"
                           aria-label="WhatsApp {{ setting('business.company_name', '') }}">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a8 8 0 0 1-11.6 7.1L4 20.5l1.4-5A8 8 0 1 1 21 12z"></path></svg>
                        </a>
                    @endif

                    @if ($navMenu && $uses('dropdown'))
                        <div class="dropdown gsm-main-menu {{ $showFor('dropdown', 'inline-flex') }}">
                            <button class="gsm-header-icon" type="button" data-bs-toggle="dropdown"
                                    data-bs-display="static" aria-expanded="false" aria-label="Menu">
                                <i class="fas fa-bars" aria-hidden="true"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end gsm-main-menu__list">
                                @foreach ($navMenu->rootItems as $item)
                                    @continue(! $item->isVisibleToCurrentUser())
                                    <li>@include('frontend.partials.menu-item-link', ['item' => $item, 'linkClass' => 'dropdown-item'])</li>
                                    @foreach ($item->children as $child)
                                        @continue(! $child->isVisibleToCurrentUser())
                                        @if ($item->is_mega && $child->children->isNotEmpty())
                                            {{-- Mega column flattened: title as a header, then its links. --}}
                                            <li><h6 class="dropdown-header gsm-mega">{{ $child->displayLabel() }}</h6></li>
                                            @foreach ($child->children as $grandchild)
                                                @continue(! $grandchild->isVisibleToCurrentUser())
                                                <li>@include('frontend.partials.menu-item-link', ['item' => $grandchild, 'linkClass' => 'dropdown-item ps-4 small gsm-mega'])</li>
                                            @endforeach
                                        @else
                                            <li>@include('frontend.partials.menu-item-link', ['item' => $child, 'linkClass' => 'dropdown-item ps-4 small'])</li>
                                        @endif
                                    @endforeach
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if ($navMenu && $uses('overlay'))
                        <button type="button" class="gsm-header-icon gsm-menu-toggle {{ $showFor('overlay', 'inline-flex') }}"
                                aria-controls="gsm-nav-overlay" aria-expanded="false" aria-label="Open menu" data-gsm-menu-open>
                            <span class="gsm-menu-toggle__bar" aria-hidden="true"></span>
                            <span class="gsm-menu-toggle__bar" aria-hidden="true"></span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </header>

    @if ($navMenu && $uses('overlay'))
        {{-- Full-screen menu. A modal <dialog>: the browser traps focus inside,
             closes it on Esc and hands focus back to the menu button. --}}
        <dialog id="gsm-nav-overlay" class="gsm-nav-overlay" aria-label="Menu">
            <div class="{{ $container }}">
                <div class="gsm-site-header__bar">
                    <div class="gsm-site-header__brand">{!! $brand() !!}</div>
                    <div class="gsm-site-header__actions">
                        <button type="button" class="gsm-header-icon gsm-menu-toggle is-open" aria-label="Close menu" data-gsm-menu-close>
                            <span class="gsm-menu-toggle__bar" aria-hidden="true"></span>
                            <span class="gsm-menu-toggle__bar" aria-hidden="true"></span>
                        </button>
                    </div>
                </div>
                <nav aria-label="Main">
                    <ul class="gsm-nav-overlay__list">
                        @foreach ($navMenu->rootItems as $item)
                            @continue(! $item->isVisibleToCurrentUser())
                            <li class="gsm-nav-overlay__item" style="--i: {{ $loop->index }}">
                                @include('frontend.partials.menu-item-link', ['item' => $item, 'linkClass' => 'gsm-nav-overlay__link'])
                                @if ($item->children->isNotEmpty())
                                    <ul class="gsm-nav-overlay__sub">
                                        @foreach ($item->children as $child)
                                            @continue(! $child->isVisibleToCurrentUser())
                                            <li>@include('frontend.partials.menu-item-link', ['item' => $child, 'linkClass' => 'gsm-nav-overlay__sublink'])</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </nav>
                {{-- Header Builder → Full-screen menu: one button and the call line. --}}
                @if ($overlayCta = $layout?->setting('overlay_cta_label'))
                    <a href="{{ $layout->setting('overlay_cta_url') ?: '#' }}" class="gsm-nav-overlay__cta" style="--i: {{ $navMenu->rootItems->count() }}">{{ $overlayCta }}</a>
                @endif
                @if ($pauseLabel = $layout?->setting('overlay_pause_label'))
                    <button type="button" class="ek-pause-toggle gsm-nav-overlay__pause" data-ek-pause aria-pressed="false">{{ $pauseLabel }}</button>
                @endif
                @if ($layout?->setting('overlay_call_line') && ($callPhones = collect(business_phones())->where('menu_label', '!=', ''))->isNotEmpty())
                    <p class="gsm-nav-overlay__call">
                        @foreach ($callPhones as $phone)
                            {{ $phone['menu_label'] }} <a href="{{ $phone['tel'] }}">{{ $phone['number'] }}</a>@if (! $loop->last) · @endif
                        @endforeach
                    </p>
                @endif
            </div>
        </dialog>
    @endif

    @stack('after-header')

    {{-- Admin-defined header CSS/JS --}}
    @if ($layout?->custom_css)
        <style>{!! style_safe($layout->custom_css) !!}</style>
    @endif
    @if ($layout?->custom_js)
        @push('scripts')
            <script>{!! $layout->custom_js !!}</script>
        @endpush
    @endif
@endif
