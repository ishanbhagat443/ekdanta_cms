@props([
    'url' => '#',
    'label' => '',
    'style' => null,        // ButtonStyle slug from the Button Builder
    'fallbackClass' => 'btn gsm-btn-gradient text-white rounded-pill px-4 gsm-text-buttons',
])

@php
    /** Button Builder component: renders a styled <a> for a ButtonStyle slug.
     *  Unknown/inactive slugs fall back to the legacy theme button so links
     *  never break when a style is deleted. */
    $buttonStyle = app(\App\Services\ButtonStyleService::class)->findBySlug($style);

    $videoId = $buttonStyle?->get('background.mode') === 'video' ? $buttonStyle->get('background.video_media_id') : null;
    $video = $videoId ? \App\Models\Media::query()->find((int) $videoId) : null;
    $isRipple = $buttonStyle?->get('animation.hover_effect') === 'ripple';
@endphp

<a href="{{ $url }}"
   {{ $attributes->merge(['class' => $buttonStyle ? $buttonStyle->cssClass() : $fallbackClass]) }}
   @if ($isRipple) data-gsm-ripple @endif>
    @if ($video)
        <video class="gsm-btn-video" autoplay muted loop playsinline preload="metadata"
               aria-hidden="true" tabindex="-1" disablepictureinpicture
               controlslist="nodownload nofullscreen noremoteplayback">
            <source src="{{ $video->url }}" type="{{ $video->mime }}">
        </video>
    @endif

    @if ($icon = $buttonStyle?->get('icon.left'))
        <i class="{{ $icon }}" aria-hidden="true"></i>
    @endif

    <span class="gsm-btn-label">{{ $label }}</span>

    @if ($icon = $buttonStyle?->get('icon.right'))
        <i class="{{ $icon }}" aria-hidden="true"></i>
    @endif
</a>
