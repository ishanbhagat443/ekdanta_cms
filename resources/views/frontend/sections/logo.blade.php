@php
    $data = $section->dataFor('desktop');
    $classes = $section->visibilityClasses();
    $link = $data['link'] ?? '/';
    $alt = $data['alt'] ?? setting('business.company_name', '');
    // Max height per device (Header Builder: a smaller logo on phones).
    $maxH = fn (string $device) => (int) ($section->dataFor($device)['max_height'] ?? 56);

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

<a href="{{ $link }}" class="navbar-brand gsm-logo-link order-0 {{ $classes }}" style="--logo-d: {{ $maxH('desktop') }}px; --logo-t: {{ $maxH('tablet') }}px; --logo-m: {{ $maxH('mobile') }}px">
    @if ($lightMedia)
        {{-- Shown only while an overlay header is transparent (see .gsm-header-overlay CSS). --}}
        <img src="{{ $lightMedia->web_url }}" alt="{{ $alt }}" class="gsm-logo-light">
    @endif
    @if ($desktopMedia)
        @if ($tabletMedia || $mobileMedia)
            <img src="{{ $desktopMedia->web_url }}" alt="{{ $alt }}" class="gsm-logo-dark d-none d-lg-inline-block gsm-t-none">
            <img src="{{ ($tabletMedia ?? $desktopMedia)->web_url }}" alt="{{ $alt }}" class="gsm-logo-dark d-none d-md-inline-block d-lg-none gsm-t-inline-block">
            <img src="{{ ($mobileMedia ?? $tabletMedia ?? $desktopMedia)->web_url }}" alt="{{ $alt }}" class="gsm-logo-dark d-inline-block d-md-none">
        @else
            <img src="{{ $desktopMedia->web_url }}" alt="{{ $alt }}" class="gsm-logo-dark">
        @endif
    @else
        <span class="fw-bold gsm-logo-dark">{{ setting('business.company_name', '') }}</span>
    @endif
</a>
