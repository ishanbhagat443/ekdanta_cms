@php
    /** @var array $chrome (view composer) */
    $area = $chrome['footer'] ?? null;
    $layout = $area['layout'] ?? null;

    $bgColor = $layout?->setting('bg_color') ?: '#101828';
    $textColor = $layout?->setting('text_color') ?: '#e8e8ef';
    $container = $layout?->setting('container', 'container');

    // Design 10 Footer.png: photographic background + dark overlay.
    $bgMedia = $area['bg_media'] ?? null;
    $overlay = max(0, min(95, (int) ($layout?->setting('overlay_opacity') ?? 70)));
    // Right-side model photo, kept as its own transparent layer so it can
    // be swapped independently of the background artwork.
    $sideMedia = $area['side_media'] ?? null;

    $sections = $area['sections'] ?? collect();

    // Instagram strips render full-bleed ABOVE the footer (design 09);
    // copyright renders full-width below the widget grid (design 10).
    $instagram = $sections->filter(fn ($e) => $e['section']->type === 'instagram-feed');
    $gridSections = $sections->filter(fn ($e) => !in_array($e['section']->type, ['instagram-feed', 'copyright'], true));
    $copyright = $sections->firstWhere(fn ($e) => $e['section']->type === 'copyright');
@endphp

@if ($area && $area['sections']->isNotEmpty())
    @foreach ($instagram as $entry)
        <div class="{{ $entry['section']->visibilityClasses() }}">
            @include('frontend.partials.render-section', [
                'section' => $entry['section'],
                'menu' => null,
                'mediaMap' => $entry['media'],
            ])
        </div>
    @endforeach

    <footer class="gsm-footer pt-5 pb-3 mt-auto @if ($bgMedia) gsm-footer-has-bg @endif"
            style="background-color: {{ $bgColor }}; color: {{ $textColor }};">
        @if ($bgMedia)
            <img src="{{ $bgMedia->url }}" alt="" aria-hidden="true"
                 class="gsm-footer-bg" loading="lazy" decoding="async">
            <div class="gsm-footer-overlay" aria-hidden="true"
                 style="background-color: {{ $bgColor }}; opacity: {{ $overlay / 100 }};"></div>
        @endif

        @if ($sideMedia)
            <img src="{{ $sideMedia->url }}" alt="{{ $sideMedia->alt ?: '' }}"
                 class="gsm-footer-side d-none d-lg-block" loading="lazy" decoding="async">
        @endif

        <div class="gsm-footer-content {{ $container }}">
            <div class="row gy-4">
                @foreach ($gridSections as $entry)
                    <div class="{{ $entry['section']->widthClasses() }} {{ $entry['section']->visibilityClasses() }}">
                        @include('frontend.partials.render-section', [
                            'section' => $entry['section'],
                            'menu' => $entry['menu'],
                            'mediaMap' => $entry['media'],
                        ])
                    </div>
                @endforeach
            </div>
        </div>

        @if ($copyright)
            <div class="gsm-footer-content border-top border-secondary mt-4 pt-3 {{ $copyright['section']->visibilityClasses() }}">
                @include('frontend.sections.copyright', [
                    'section' => $copyright['section'],
                    'menu' => null,
                    'mediaMap' => [],
                ])
            </div>
        @endif
    </footer>

    @if ($layout?->custom_css)
        <style>{!! style_safe($layout->custom_css) !!}</style>
    @endif
    @if ($layout?->custom_js)
        @push('scripts')
            <script>{!! $layout->custom_js !!}</script>
        @endpush
    @endif
@endif
