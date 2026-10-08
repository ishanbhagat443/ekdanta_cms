{{-- Decorative separator (Designs/Contact Us Page 02 Garland & 05 Floral).
     A single centered, responsive ornament image from the Media Library —
     width + vertical margins are CMS-driven. Lazy-loaded (decorative). --}}
@php
    $data = $section->dataFor('desktop');
    $img = $mediaMap[$data['media_id'] ?? 0] ?? null;
    $width = (int) ($data['width'] ?? 340);
    $mt = (int) ($data['margin_top'] ?? 24);
    $mb = (int) ($data['margin_bottom'] ?? 24);
@endphp

@if ($img)
    <div class="gsm-separator" style="margin-top: {{ $mt }}px; margin-bottom: {{ $mb }}px;">
        <img src="{{ $img->url }}" alt="{{ $img->alt ?: '' }}" loading="lazy" decoding="async"
             class="gsm-separator-img" style="max-width: {{ $width }}px;">
    </div>
@endif
