@php
    $data = $section->dataFor('desktop');
    $classes = $section->visibilityClasses();
    $link = $data['link'] ?? '/';
    $alt = $data['alt'] ?? setting('business.company_name', '');
    $maxH = (int) ($data['max_height'] ?? 56);

    $desktopMedia = $mediaMap[$section->dataFor('desktop')['media_id'] ?? 0] ?? null;
    $tabletMedia = $mediaMap[$section->dataFor('tablet')['media_id'] ?? 0] ?? null;
    $mobileMedia = $mediaMap[$section->dataFor('mobile')['media_id'] ?? 0] ?? null;

    // Per-device variants only when they actually differ — identical
    // selections collapse to a single <img>.
    if ($tabletMedia && $desktopMedia && $tabletMedia->id === $desktopMedia->id) {
        $tabletMedia = null;
    }
    if ($mobileMedia && (($desktopMedia && $mobileMedia->id === $desktopMedia->id) || ($tabletMedia && $mobileMedia->id === $tabletMedia->id))) {
        $mobileMedia = null;
    }
@endphp

@php $lightMedia = $mediaMap[$data['light_media_id'] ?? 0] ?? null; @endphp

<a href="{{ $link }}" class="navbar-brand order-0 {{ $classes }}">
    @if ($lightMedia)
        {{-- Shown only while an overlay header is transparent (see .gsm-header-overlay CSS). --}}
        <img src="{{ $lightMedia->url }}" alt="{{ $alt }}" class="gsm-logo-light" style="max-height: {{ $maxH }}px;">
    @endif
    @if ($desktopMedia)
        @if ($tabletMedia || $mobileMedia)
            <img src="{{ $desktopMedia->url }}" alt="{{ $alt }}" class="gsm-logo-dark d-none d-lg-inline-block" style="max-height: {{ $maxH }}px;">
            <img src="{{ ($tabletMedia ?? $desktopMedia)->url }}" alt="{{ $alt }}" class="gsm-logo-dark d-none d-md-inline-block d-lg-none" style="max-height: {{ $maxH }}px;">
            <img src="{{ ($mobileMedia ?? $tabletMedia ?? $desktopMedia)->url }}" alt="{{ $alt }}" class="gsm-logo-dark d-inline-block d-md-none" style="max-height: {{ $maxH }}px;">
        @else
            <img src="{{ $desktopMedia->url }}" alt="{{ $alt }}" class="gsm-logo-dark" style="max-height: {{ $maxH }}px;">
        @endif
    @else
        <span class="fw-bold gsm-logo-dark">{{ setting('business.company_name', '') }}</span>
    @endif
</a>
