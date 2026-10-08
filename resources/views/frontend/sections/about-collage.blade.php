@php
    $data = $section->dataFor('desktop');
    $bg = $mediaMap[$data['bg_media_id'] ?? 0] ?? null;
    $arch = $mediaMap[$data['image_arch_media_id'] ?? 0] ?? null;
    $oval = $mediaMap[$data['image_oval_media_id'] ?? 0] ?? null;
    $rect = $mediaMap[$data['image_rect_media_id'] ?? 0] ?? null;
@endphp

{{-- Desktop: fixed-aspect canvas (same ratio as the background artwork,
     1920:1090) so the %-positioned photos and captions register exactly
     onto the frames + arrows painted in the artwork. --}}
<div class="gsm-about d-none d-lg-block"
     @if ($bg) style="background-image: url('{{ $bg->url }}');" @endif>
    <div class="gsm-about-canvas">
        <div class="gsm-about-text">
            @if (!empty($data['eyebrow']))
                <div class="text-uppercase fw-bold gsm-about-eyebrow mb-3">
                    @include('frontend.partials.device-text', ['section' => $section, 'key' => 'eyebrow'])
                </div>
            @endif
            @if (!empty($data['heading']))
                <h2 class="gsm-serif gsm-text-section-heading gsm-about-heading mb-2">
                    @include('frontend.partials.device-text', ['section' => $section, 'key' => 'heading'])
                </h2>
                <span class="gsm-heading-rule gsm-heading-rule-start mb-3" aria-hidden="true"></span>
            @endif
            @if (!empty($data['body']))
                <div class="gsm-richtext gsm-text-paragraph gsm-about-body">{!! $data['body'] !!}</div>
            @endif
            @if (!empty($data['button_label']))
                <x-gsm-button :url="$data['button_url'] ?? '#'" :label="$data['button_label']"
                              :style="$data['button_style'] ?? null" class="mt-3"
                              fallback-class="btn gsm-btn-gradient text-white rounded-pill px-4 gsm-text-buttons" />
            @endif
        </div>

        @if ($rect)
            <img src="{{ $rect->url }}" alt="{{ $rect->alt }}" class="gsm-about-photo gsm-about-rect" loading="lazy" decoding="async">
        @endif
        @if ($oval)
            <img src="{{ $oval->url }}" alt="{{ $oval->alt }}" class="gsm-about-photo gsm-about-oval" loading="lazy" decoding="async">
        @endif
        @if ($arch)
            <img src="{{ $arch->url }}" alt="{{ $arch->alt }}" class="gsm-about-photo gsm-about-arch" loading="lazy" decoding="async">
        @endif

        @if (!empty($data['caption_top']))
            <div class="gsm-about-caption gsm-about-caption-top">{{ $data['caption_top'] }}</div>
        @endif
        @if (!empty($data['caption_left']))
            <div class="gsm-about-caption gsm-about-caption-left">{{ $data['caption_left'] }}</div>
        @endif
        @if (!empty($data['caption_bottom']))
            <div class="gsm-about-caption gsm-about-caption-bottom">{{ $data['caption_bottom'] }}</div>
        @endif
    </div>
</div>

{{-- Tablet / mobile: stacked flow — text first, then photos, captions as labels. --}}
<div class="gsm-about-stacked d-lg-none py-5">
    <div class="container">
        @if (!empty($data['eyebrow']))
            <div class="text-uppercase fw-bold gsm-about-eyebrow mb-2">{{ $data['eyebrow'] }}</div>
        @endif
        @if (!empty($data['heading']))
            <h2 class="gsm-serif gsm-text-section-heading mb-2">{{ $data['heading'] }}</h2>
            <span class="gsm-heading-rule gsm-heading-rule-start mb-3" aria-hidden="true"></span>
        @endif
        @if (!empty($data['body']))
            <div class="gsm-richtext gsm-text-paragraph">{!! $data['body'] !!}</div>
        @endif
        @if (!empty($data['button_label']))
            <x-gsm-button :url="$data['button_url'] ?? '#'" :label="$data['button_label']"
                          :style="$data['button_style'] ?? null" class="mt-2 mb-4"
                          fallback-class="btn gsm-btn-gradient text-white rounded-pill px-4 gsm-text-buttons" />
        @endif

        @if ($arch)
            <img src="{{ $arch->url }}" alt="{{ $arch->alt }}" class="img-fluid w-100 gsm-about-stacked-arch mb-2" loading="lazy" decoding="async">
        @endif
        @if (!empty($data['caption_top']))
            <div class="gsm-about-caption text-center mb-3">{{ $data['caption_top'] }}</div>
        @endif
        <div class="row g-2">
            @if ($rect)
                <div class="col-6"><img src="{{ $rect->url }}" alt="{{ $rect->alt }}" class="img-fluid w-100 rounded" style="aspect-ratio: 3/4; object-fit: cover;" loading="lazy" decoding="async"></div>
            @endif
            @if ($oval)
                <div class="col-6"><img src="{{ $oval->url }}" alt="{{ $oval->alt }}" class="img-fluid w-100 gsm-about-stacked-oval" style="aspect-ratio: 3/4; object-fit: cover;" loading="lazy" decoding="async"></div>
            @endif
        </div>
        @if (!empty($data['caption_bottom']))
            <div class="gsm-about-caption text-center mt-3">{{ $data['caption_bottom'] }}</div>
        @endif
    </div>
</div>
