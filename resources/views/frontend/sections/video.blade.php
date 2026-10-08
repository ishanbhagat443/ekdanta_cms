@php
    $data = $section->dataFor('desktop');
    $poster = $mediaMap[$data['poster_media_id'] ?? 0] ?? null;
    $videoUrl = $data['video_url'] ?? null;
    $modalId = 'gsm-video-'.$section->id;
    $autoplay = (bool) ($data['autoplay'] ?? true);
@endphp

<div class="container py-5">
    @include('frontend.partials.section-heading', ['section' => $section])

    <div class="position-relative rounded overflow-hidden mx-auto" style="max-width: 960px;">
        @if ($poster)
            <img loading="lazy" decoding="async" src="{{ $poster->variantUrl('card') ?? $poster->url }}" alt="" class="w-100"
                 style="aspect-ratio: 16/9; object-fit: cover;">
        @else
            <div class="bg-dark w-100" style="aspect-ratio: 16/9;"></div>
        @endif

        @if ($videoUrl)
            <button type="button"
                    class="btn btn-light rounded-circle position-absolute top-50 start-50 translate-middle shadow"
                    style="width: 72px; height: 72px;"
                    data-bs-toggle="modal" data-bs-target="#{{ $modalId }}" aria-label="Play video">
                <i class="fas fa-play" style="color: #1b2a4a;"></i>
            </button>
        @endif
    </div>
</div>

@include('frontend.partials.video-modal', ['videoUrl' => $videoUrl, 'modalId' => $modalId, 'autoplay' => $autoplay])
