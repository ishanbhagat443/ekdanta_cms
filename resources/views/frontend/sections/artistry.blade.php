@php
    $data = $section->dataFor('desktop');
    $bg = $mediaMap[$data['bg_media_id'] ?? 0] ?? null;
    $gallery = collect((array) ($data['gallery'] ?? []))->filter(fn ($g) => isset($mediaMap[$g['media_id'] ?? 0]))->values();
    $features = collect((array) ($data['features'] ?? []))->filter(fn ($f) => !empty($f['title']));
    $showFlourish = (bool) ($data['show_flourish'] ?? true);
    $galleryId = 'gsm-artistry-gallery-'.$section->id;
@endphp

<div class="gsm-floral-border" aria-hidden="true"></div>

<div class="gsm-artistry"
     @if ($bg) style="background-image: url('{{ $bg->url }}'); background-size: cover;" @endif>
    <div class="container py-5">
        @include('frontend.partials.section-heading', ['section' => $section])

        {{-- Accordion-style gallery: collapsed cards show only the title;
             selecting one reveals its description. More than 3 cards
             scroll horizontally instead of wrapping to new rows, so the
             CMS can add any number of cards without breaking the layout. --}}
        @if ($gallery->isNotEmpty())
            <div class="gsm-artistry-slider {{ $gallery->count() > 3 ? 'gsm-artistry-slider-scroll' : '' }} mt-2">
                <div class="gsm-artistry-track" id="{{ $galleryId }}">
                    @foreach ($gallery as $index => $item)
                        @php $media = $mediaMap[$item['media_id']]; @endphp
                        <div class="gsm-artistry-slot">
                            <button type="button" class="gsm-artistry-card {{ $index === 0 ? 'gsm-artistry-card-active' : '' }}"
                                    data-gsm-artistry-card aria-expanded="{{ $index === 0 ? 'true' : 'false' }}">
                                <img loading="lazy" decoding="async" src="{{ $media->variantUrl('card') ?? $media->url }}"
                                     alt="{{ $item['title'] }}" class="gsm-artistry-photo">
                                <div class="gsm-artistry-overlay">
                                    <h3 class="gsm-serif gsm-artistry-title">{{ $item['title'] }}</h3>
                                    @if (!empty($item['text']))
                                        <p class="gsm-artistry-text mb-0">{{ $item['text'] }}</p>
                                    @endif
                                </div>
                            </button>
                        </div>
                    @endforeach
                </div>
                @if ($gallery->count() > 3)
                    <div class="gsm-artistry-dots" aria-hidden="true"></div>
                @endif
            </div>
        @endif
    </div>

    @if ($features->isNotEmpty())
        <div class="container gsm-artistry-features-wrap">
            <div class="d-flex align-items-center justify-content-center gsm-artistry-features">
                @if ($showFlourish)
                    <img src="{{ asset('images/design/flourish.svg') }}" alt="" aria-hidden="true"
                         class="gsm-artistry-flourish d-none d-md-block">
                @endif

                <div class="row g-4 gsm-artistry-features-row">
                    @foreach ($features as $item)
                        <div class="col-12 col-sm-6 col-lg-4">
                            <div class="gsm-artistry-feature-card">
                                @if (!empty($item['icon']))
                                    <span class="gsm-artistry-feature-icon">
                                        <i class="{{ $item['icon'] }}"></i>
                                    </span>
                                @endif
                                <div class="gsm-artistry-feature-body">
                                    <h4 class="gsm-serif mb-1">{{ $item['title'] }}</h4>
                                    @if (!empty($item['text']))
                                        <p class="gsm-text-paragraph mb-0">{{ $item['text'] }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($showFlourish)
                    <img src="{{ asset('images/design/flourish.svg') }}" alt="" aria-hidden="true"
                         class="gsm-artistry-flourish gsm-artistry-flourish-right d-none d-md-block">
                @endif
            </div>
        </div>
    @endif
</div>
