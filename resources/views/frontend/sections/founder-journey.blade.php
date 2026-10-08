@php
    $data = $section->dataFor('desktop');
    $bg = $mediaMap[$data['bg_media_id'] ?? 0] ?? null;
    $portrait = $mediaMap[$data['image_media_id'] ?? 0] ?? null;
    $ornamentTop = $mediaMap[$data['ornament_top_media_id'] ?? 0] ?? null;
    $ornamentBottom = $mediaMap[$data['ornament_bottom_media_id'] ?? 0] ?? null;
    $showGarland = (bool) ($data['show_garland'] ?? true);
    $hasMissionVision = filled($data['mission_title'] ?? null) || filled($data['vision_title'] ?? null);
@endphp

@if ($showGarland)
    <div class="gsm-floral-border" aria-hidden="true"></div>
@endif

<div class="gsm-founder-journey"
     @if ($bg) style="background-image: url('{{ $bg->url }}');" @endif>

    {{-- Decorative botanical illustrations, bottom-anchored to the section
         (design 03 Founder Journey.png) — hidden below lg to keep the
         mobile layout clean, matching the About collage's convention. --}}
    @if ($ornamentTop)
        <img src="{{ $ornamentTop->url }}" alt="" aria-hidden="true"
             class="gsm-founder-ornament gsm-founder-ornament-top d-none d-lg-block" loading="lazy" decoding="async">
    @endif
    @if ($ornamentBottom)
        <img src="{{ $ornamentBottom->url }}" alt="" aria-hidden="true"
             class="gsm-founder-ornament gsm-founder-ornament-bottom d-none d-lg-block" loading="lazy" decoding="async">
    @endif
    @if ($portrait)
        <img src="{{ $portrait->url }}" alt="{{ $portrait->alt ?: '' }}"
             class="gsm-founder-portrait d-none d-lg-block" loading="lazy" decoding="async">
    @endif

    <div class="container py-5 position-relative">
        <div class="gsm-founder-content mx-auto text-center">
            @if (!empty($data['heading']))
                <h2 class="gsm-serif gsm-text-section-heading mb-4">
                    @include('frontend.partials.device-text', ['section' => $section, 'key' => 'heading'])
                </h2>
            @endif
            @if (!empty($data['body']))
                <div class="gsm-richtext gsm-text-paragraph gsm-founder-story">{!! $data['body'] !!}</div>
            @endif
        </div>

        @if ($hasMissionVision)
            <div class="row g-4 mt-3 gsm-founder-cards">
                @if (filled($data['mission_title'] ?? null))
                    <div class="col-12 col-md-6">
                        <div class="gsm-founder-card">
                            <h3 class="gsm-serif mb-3">{{ $data['mission_title'] }}</h3>
                            @if (filled($data['mission_text'] ?? null))
                                <p class="gsm-text-paragraph mb-0">{{ $data['mission_text'] }}</p>
                            @endif
                        </div>
                    </div>
                @endif
                @if (filled($data['vision_title'] ?? null))
                    <div class="col-12 col-md-6">
                        <div class="gsm-founder-card">
                            <h3 class="gsm-serif mb-3">{{ $data['vision_title'] }}</h3>
                            @if (filled($data['vision_text'] ?? null))
                                <p class="gsm-text-paragraph mb-0">{{ $data['vision_text'] }}</p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
