@php
    /**
     * Universal page-section wrapper: visibility, background (color /
     * image / Gradient Library / video), overlay, spacing, radius,
     * z-index, animation, per-section custom CSS/JS. All admin-managed.
     */
    $style = [];

    if ($bg = $section->styleValue('bg_color')) {
        $style[] = "background-color: {$bg}";
    }
    if ($gradientSlug = $section->styleValue('bg_gradient')) {
        $style[] = "background-image: var(--gsm-gradient-{$gradientSlug})";
    }
    if (($bgId = $section->styleValue('bg_media_id')) && isset($mediaMap[$bgId])) {
        $style[] = "background-image: url('{$mediaMap[$bgId]->url}')";
        $style[] = 'background-size: cover';
        $style[] = 'background-position: center';
    }
    foreach (['pt' => 'padding-top', 'pb' => 'padding-bottom', 'mt' => 'margin-top', 'mb' => 'margin-bottom'] as $key => $cssProp) {
        $v = $section->styleValue($key);
        if ($v !== null && $v !== '') {
            $style[] = "{$cssProp}: {$v}px";
        }
    }
    if ($radius = $section->styleValue('radius')) {
        $style[] = 'border-radius: '.$radius.(preg_match('/(px|%|rem|em)$/', (string) $radius) ? '' : 'px');
        $style[] = 'overflow: hidden';
    }
    if (($z = $section->styleValue('z_index')) !== null && $z !== '') {
        $style[] = 'z-index: '.(int) $z;
    }

    // Video background + overlay need stacking context.
    $video = ($videoId = $section->styleValue('bg_video_media_id')) ? ($mediaMap[$videoId] ?? null) : null;
    $overlayOpacity = (int) ($section->styleValue('overlay_opacity') ?? 0);
    $hasLayers = $video !== null || $overlayOpacity > 0;

    if ($hasLayers || ($z !== null && $z !== '')) {
        $style[] = 'position: relative';
    }

    $domId = 'gsm-s'.$section->id;

    // Motion tab: the engine (resources/js/frontend/motion) reads the JSON
    // below. A pinned stage's height is CSS (--ek-len-*), so nothing jumps
    // when the script starts. No motion: no attributes, nothing to run.
    $motion = \App\Support\SectionMotion::render($section);
    foreach ($motion['lengths'] ?? [] as $pinDevice => $pinLength) {
        $style[] = '--ek-len-'.$pinDevice[0].': '.$pinLength;
    }
@endphp

<section id="{{ $domId }}"
         class="gsm-section gsm-section-{{ $section->type }} {{ $section->visibilityClasses() }} @if ($motion) ek-scene @if ($motion['steps']) ek-steps @endif @foreach (array_keys($motion['lengths']) as $pinDevice) ek-pin-{{ $pinDevice[0] }} @endforeach @endif"
         @if ($motion) data-ek-motion data-ek-type="{{ $section->type }}" @endif
         @if ($style !== []) style="{{ implode('; ', $style) }}" @endif>
    @if ($motion)
        <script type="application/json" data-ek-config>@json($motion['config'])</script>
        @if ($motion['css'] !== '')<style>{!! style_safe($motion['css']) !!}</style>@endif
    @endif

    @if ($video)
        <video class="gsm-section-video" autoplay muted loop playsinline preload="metadata"
               aria-hidden="true" tabindex="-1" disablepictureinpicture
               controlslist="nodownload nofullscreen noremoteplayback">
            <source src="{{ $video->url }}" type="{{ $video->mime }}">
        </video>
    @endif

    @if ($overlayOpacity > 0)
        <div class="gsm-section-overlay" aria-hidden="true"
             style="background-color: {{ $section->styleValue('overlay_color') ?: '#0a0e1a' }}; opacity: {{ $overlayOpacity / 100 }};"></div>
    @endif

    @if ($hasLayers)
        <div class="gsm-section-inner">
            @include('frontend.partials.render-section', [
                'section' => $section,
                'menu' => $menu,
                'mediaMap' => $mediaMap,
            ])
        </div>
    @else
        @include('frontend.partials.render-section', [
            'section' => $section,
            'menu' => $menu,
            'mediaMap' => $mediaMap,
        ])
    @endif
</section>

@if (str_starts_with($section->type, 'ekdanta-'))
    @once
        @push('head')
            @vite(['resources/scss/ekdanta.scss', 'resources/js/frontend/ekdanta.js'])
        @endpush
    @endonce
@endif

@if ($css = $section->styleValue('custom_css'))
    <style>{!! $css !!}</style>
@endif
@if ($js = $section->styleValue('custom_js'))
    @push('scripts')
        <script>{!! $js !!}</script>
    @endpush
@endif
