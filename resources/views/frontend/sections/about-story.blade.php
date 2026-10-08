@php
    $data = $section->dataFor('desktop');
    $bg = $mediaMap[$data['bg_media_id'] ?? 0] ?? null;
    $photo = $mediaMap[$data['photo_media_id'] ?? 0] ?? null;
    $highlights = collect((array) ($data['highlights'] ?? []))->filter(fn ($h) => filled($h['text'] ?? null));
    $showGarland = (bool) ($data['show_garland'] ?? true);
@endphp

{{-- Gold garland border along the top edge (design 02 About Section.png,
     same ornament asset as the Home Counter section). --}}
@if ($showGarland)
    <div class="gsm-garland-border" aria-hidden="true"></div>
@endif

<div class="gsm-about-story"
     @if ($bg) style="background-image: url('{{ $bg->url }}');" @endif>
    <div class="container py-5">
        <div class="row g-4">
            <div class="col-12 col-lg-6">
                @if (!empty($data['eyebrow']))
                    <div class="text-uppercase fw-bold gsm-about-eyebrow mb-2">
                        @include('frontend.partials.device-text', ['section' => $section, 'key' => 'eyebrow'])
                    </div>
                @endif
                @if (!empty($data['heading']))
                    <h2 class="gsm-serif gsm-text-section-heading gsm-about-story-heading mb-3">
                        @include('frontend.partials.device-text', ['section' => $section, 'key' => 'heading'])
                    </h2>
                @endif
                @if (!empty($data['intro']))
                    <div class="gsm-richtext gsm-text-paragraph">{!! $data['intro'] !!}</div>
                @endif
            </div>
            <div class="col-12 col-lg-6 gsm-about-story-lead">
                @if (!empty($data['lead']))
                    <div class="gsm-richtext gsm-text-paragraph">{!! $data['lead'] !!}</div>
                @endif
            </div>
        </div>

        @if ($photo)
            <div class="gsm-about-story-photo-wrap">
                <img src="{{ $photo->variantUrl('banner') ?? $photo->url }}"
                     alt="{{ $photo->alt ?: ($data['heading'] ?? '') }}"
                     class="gsm-about-story-photo" loading="lazy" decoding="async">
            </div>
        @endif

        @if (!empty($data['body_left']) || !empty($data['body_right']))
            <div class="row g-4 mt-1">
                <div class="col-12 col-lg-6">
                    @if (!empty($data['body_left']))
                        <div class="gsm-richtext gsm-text-paragraph">{!! $data['body_left'] !!}</div>
                    @endif
                </div>
                <div class="col-12 col-lg-6">
                    @if (!empty($data['body_right']))
                        <div class="gsm-richtext gsm-text-paragraph">{!! $data['body_right'] !!}</div>
                    @endif
                </div>
            </div>
        @endif

        @if ($highlights->isNotEmpty())
            <div class="gsm-about-story-highlights mt-4">
                @foreach ($highlights as $item)
                    <span class="gsm-about-story-highlight gsm-serif">{{ $item['text'] }}</span>
                @endforeach
            </div>
        @endif
    </div>
</div>
