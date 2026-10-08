@php
    $data = $section->dataFor('desktop');
    $bg = $mediaMap[$data['bg_media_id'] ?? 0] ?? null;
    $video = $mediaMap[$data['bg_video_media_id'] ?? 0] ?? null;
    $overlay = min(90, max(0, (int) ($data['overlay_opacity'] ?? 50))) / 100;
    $overlayGradient = $data['overlay_gradient'] ?? null;
    $minHeight = ['banner' => '200px', 'small' => '320px', 'medium' => '460px', 'large' => '600px', 'half' => '50svh', 'full' => '100svh'][$data['height'] ?? 'full'] ?? '100svh';
    $align = $data['alignment'] ?? 'center';
    $contentWidth = (int) ($data['content_width'] ?? 780);
    $entrance = $data['entrance'] ?? 'fade-up';
    $showScroll = (bool) ($data['scroll_indicator'] ?? true);
    $showBreadcrumb = (bool) ($data['show_breadcrumb'] ?? false);
    $breadcrumbLabel = $data['breadcrumb_label'] ?? $data['heading'] ?? null;
    $breadcrumbParentLabel = $data['breadcrumb_parent_label'] ?? null;
    $breadcrumbParentUrl = $data['breadcrumb_parent_url'] ?? null;
@endphp

<div class="gsm-hero position-relative overflow-hidden d-flex align-items-center text-white"
     style="min-height: {{ $minHeight }};
            background-color: #101828;
            {{ $bg && ! $video ? "background: url('{$bg->url}') center/cover no-repeat #101828;" : '' }}">

    {{-- Background video layer (design 02 Hero.png): decorative only —
         muted+playsinline satisfy mobile autoplay policies, pointer-events
         are disabled in CSS so it can never be paused or interacted with. --}}
    @if ($video)
        <video class="gsm-hero-video"
               autoplay muted loop playsinline
               preload="metadata"
               @if ($bg) poster="{{ $bg->url }}" @endif
               aria-hidden="true" tabindex="-1"
               disablepictureinpicture disableremoteplayback
               controlslist="nodownload nofullscreen noremoteplayback">
            <source src="{{ $video->url }}" type="{{ $video->mime ?: 'video/mp4' }}">
        </video>
    @endif

    {{-- Overlay above the video, below the content. A Gradient Library
         slug takes over from the flat dark overlay when configured. --}}
    @if ($overlayGradient)
        <div class="gsm-hero-overlay" style="background-image: var(--gsm-gradient-{{ $overlayGradient }});"></div>
    @else
        <div class="gsm-hero-overlay" style="background: rgba(10, 14, 26, {{ $overlay }});"></div>
    @endif

    <div style="max-width: {{ $contentWidth }}px;"
         class="container py-5 text-{{ $align }} position-relative gsm-hero-content {{ $entrance !== 'none' ? 'gsm-hero-enter gsm-hero-enter-'.$entrance : '' }}">
        @if (!empty($data['eyebrow']))
            <div class="text-uppercase gsm-eyebrow gsm-hero-eyebrow">
                @include('frontend.partials.device-text', ['section' => $section, 'key' => 'eyebrow'])
            </div>
        @endif
        @if (!empty($data['heading']))
            <h1 class="gsm-text-main-heading gsm-serif gsm-hero-heading">
                @include('frontend.partials.device-text', ['section' => $section, 'key' => 'heading'])
            </h1>
        @endif
        @if ($showBreadcrumb && $breadcrumbLabel)
            {{-- Design 01 Hero.png (About page): "Home / About us" beneath the H1.
                 An optional middle trail item (e.g. Home / The Journal / Post
                 Title) renders only when explicitly configured — every other
                 page's breadcrumb is unaffected. --}}
            <nav aria-label="breadcrumb" class="gsm-hero-breadcrumb">
                <ol class="breadcrumb justify-content-{{ $align === 'start' ? 'start' : ($align === 'end' ? 'end' : 'center') }} mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('front.home') }}">Home</a></li>
                    @if ($breadcrumbParentLabel)
                        <li class="breadcrumb-item">
                            @if ($breadcrumbParentUrl)
                                <a href="{{ $breadcrumbParentUrl }}">{{ $breadcrumbParentLabel }}</a>
                            @else
                                {{ $breadcrumbParentLabel }}
                            @endif
                        </li>
                    @endif
                    <li class="breadcrumb-item active" aria-current="page">{{ $breadcrumbLabel }}</li>
                </ol>
            </nav>
        @endif
        @if (!empty($data['subheading']))
            <p class="gsm-text-sub-heading gsm-hero-sub">@include('frontend.partials.device-text', ['section' => $section, 'key' => 'subheading'])</p>
        @endif
        @if (!empty($data['text']))
            <div class="mx-auto" style="max-width: {{ $contentWidth }}px;">{!! $section->dataFor('desktop')['text'] !!}</div>
        @endif
        @include('frontend.partials.buttons', ['buttons' => $data['buttons'] ?? [], 'alignment' => $align])
    </div>

    @if ($showScroll)
        {{-- Right-edge scroll indicator (design 02 Hero.png) --}}
        <span class="gsm-hero-scroll d-none d-lg-block" aria-hidden="true"></span>
    @endif
</div>
