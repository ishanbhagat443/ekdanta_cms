@php
    $data = $section->dataFor('desktop');
    $items = collect((array) ($data['items'] ?? []))->filter(fn ($i) => filled($i['heading'] ?? null) || filled($i['video_url'] ?? null));
    $autoplay = (bool) ($data['autoplay'] ?? true);
@endphp

@if ($items->isNotEmpty())
    <div class="container py-5">
        <div class="gsm-video-showcase">
            @foreach ($items as $index => $item)
                @php
                    $poster = $mediaMap[$item['poster_media_id'] ?? 0] ?? null;
                    $videoUrl = $item['video_url'] ?? null;
                    $modalId = 'gsm-video-showcase-'.$section->id.'-'.$index;
                    $imageFirst = $index % 2 === 0; // odd rows (1st, 3rd…): photo left, text right
                @endphp
                <div class="row g-4 align-items-center gsm-video-showcase-row">
                    <div class="col-12 col-lg-6 {{ $imageFirst ? 'order-lg-1' : 'order-lg-2' }}">
                        <div class="position-relative rounded overflow-hidden gsm-video-showcase-media">
                            @if ($poster)
                                <img loading="lazy" decoding="async" src="{{ $poster->variantUrl('card') ?? $poster->url }}" alt=""
                                     class="w-100" style="aspect-ratio: 16/9; object-fit: cover;">
                            @else
                                <div class="bg-dark w-100" style="aspect-ratio: 16/9;"></div>
                            @endif

                            @if ($videoUrl)
                                <button type="button"
                                        class="btn btn-light rounded-circle position-absolute top-50 start-50 translate-middle shadow gsm-video-showcase-play"
                                        data-bs-toggle="modal" data-bs-target="#{{ $modalId }}" aria-label="Play video">
                                    <i class="fas fa-play" style="color: #111827;"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="col-12 col-lg-6 {{ $imageFirst ? 'order-lg-2' : 'order-lg-1' }}">
                        @if (!empty($item['heading']))
                            <h3 class="gsm-serif gsm-text-section-heading mb-3">{{ $item['heading'] }}</h3>
                        @endif
                        @if (!empty($item['text']))
                            <p class="gsm-text-paragraph mb-0">{{ $item['text'] }}</p>
                        @endif
                    </div>
                </div>

                @include('frontend.partials.video-modal', ['videoUrl' => $videoUrl, 'modalId' => $modalId, 'autoplay' => $autoplay])
            @endforeach
        </div>
    </div>
@endif
