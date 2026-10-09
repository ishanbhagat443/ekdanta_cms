@extends('frontend.layouts.app')

@php
    $seoMeta = $post->seoMeta;
    $ogImage = $seoMeta?->ogMedia?->url ?? $post->featuredMedia?->url;
@endphp

@section('title', $seoMeta?->meta_title ?: site_title($post->title))

@include('frontend.partials.seo-head', ['seo' => [
    'title' => $seoMeta?->meta_title ?? $post->title,
    'description' => $seoMeta?->meta_description ?? $post->autoExcerpt(30),
    'keywords' => $seoMeta?->meta_keywords,
    'canonical' => $seoMeta?->canonical_url ?? route('front.blog.show', $post->slug),
    'robots' => $seoMeta?->robots,
    'og_title' => $seoMeta?->og_title ?? $post->title,
    'og_description' => $seoMeta?->og_description ?? $post->autoExcerpt(30),
    'og_image' => $ogImage,
    'og_type' => 'article',
    'twitter_card' => $seoMeta?->twitter_card,
    'twitter_title' => $seoMeta?->twitter_title,
    'twitter_description' => $seoMeta?->twitter_description,
    'twitter_image' => $seoMeta?->twitterMedia?->url,
    'schema' => [
        [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $post->title,
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => $post->updated_at?->toIso8601String(),
            'author' => ['@type' => 'Organization', 'name' => $post->displayAuthor()],
            'image' => $ogImage,
            'mainEntityOfPage' => route('front.blog.show', $post->slug),
        ],
        [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('front.home')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'The Journal', 'item' => route('front.blog.index')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $post->title],
            ],
        ],
    ],
]])

@section('content')
    {{-- Hero (Blog Detail CMS): reuses the same Hero component as Home/About/
         Collection/Contact/PDP/Blog Listing — editable via Admin → Pages →
         Blog Detail. The controller overrides the heading with the post's
         own title and restores the exact 3-level breadcrumb (Home / The
         Journal / Post Title) via the Hero's optional breadcrumb-parent
         fields. Falls back to today's exact static banner only if that
         page/section hasn't been seeded yet or has been disabled. --}}
    @if ($hero)
        @include('frontend.partials.section-wrapper', [
            'section' => $hero['section'],
            'menu' => $hero['menu'],
            'mediaMap' => $hero['media'],
        ])
    @else
        <section class="gsm-page-banner text-white text-center d-flex align-items-center"
                 style="min-height: 200px; background: #101828;">
            <div class="container py-5">
                <h1 class="h2 gsm-serif">{{ $post->title }}</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center mb-0 small">
                        <li class="breadcrumb-item"><a href="{{ route('front.home') }}" class="text-white-50">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('front.blog.index') }}" class="text-white-50">The Journal</a></li>
                        <li class="breadcrumb-item active text-white">{{ \Illuminate\Support\Str::limit($post->title, 40) }}</li>
                    </ol>
                </nav>
            </div>
        </section>
    @endif

    <div class="container py-5">
        <div class="row g-4">
            {{-- Article --}}
            <div class="col-12 col-lg-8">
                <article>
                    @if ($post->featuredMedia)
                        <img loading="lazy" decoding="async" src="{{ $post->featuredMedia->url }}" alt="{{ $post->title }}"
                             class="img-fluid rounded mb-3 w-100" style="max-height: 480px; object-fit: cover;">
                    @endif

                    <div class="mb-3 small">
                        <span class="badge rounded-pill px-2" style="background:#f3f4f6; color:#4b5563;">{{ $post->displayAuthor() }}</span>
                        <span class="badge rounded-pill px-2" style="background:#f3f4f6; color:#4b5563;">
                            <i class="far fa-calendar-alt me-1"></i>{{ $post->published_at?->format('F d, Y') }}
                        </span>
                        @if ($postContent['show_reading_time'] ?? false)
                            {{-- Computed (Post::readingTimeMinutes()), not stored — opt-in badge, off by default. --}}
                            <span class="badge rounded-pill px-2" style="background:#f3f4f6; color:#4b5563;">
                                <i class="far fa-clock me-1"></i>{{ $post->readingTimeMinutes() }} min read
                            </span>
                        @endif
                        @foreach ($post->categories as $category)
                            <a href="{{ route('front.blog.index', ['category' => $category->slug]) }}"
                               class="badge rounded-pill px-2 text-decoration-none"
                               style="background:#eee; color:#555;">{{ $category->name }}</a>
                        @endforeach
                    </div>

                    <div class="gsm-richtext">{!! $post->body !!}</div>

                    {{-- Gallery --}}
                    @if ($post->galleryMedia->isNotEmpty())
                        <div class="row g-2 mt-3">
                            @foreach ($post->galleryMedia as $image)
                                <div class="col-6 col-md-4">
                                    <a href="{{ $image->url }}" target="_blank" rel="noopener">
                                        <img loading="lazy" decoding="async" src="{{ $image->variantUrl('card') ?? $image->url }}" alt="{{ $image->alt }}"
                                             class="img-fluid rounded w-100" style="aspect-ratio: 1; object-fit: cover;">
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Tags --}}
                    @if ($post->tags->isNotEmpty())
                        <div class="mt-3">
                            @foreach ($post->tags as $tag)
                                <a href="{{ route('front.blog.index', ['tag' => $tag->slug]) }}"
                                   class="badge rounded-pill text-decoration-none px-2 py-1 me-1"
                                   style="background:#f3f4f6; color:#4b5563;">#{{ $tag->name }}</a>
                            @endforeach
                        </div>
                    @endif

                    {{-- Share row (design BD2). Each icon is CMS-toggleable (Blog
                         Detail → Social Share); all four default ON, matching
                         today's exact output. Copy Link is a new, opt-in
                         addition (off by default — see app.js for its handler). --}}
                    @php $shareUrl = urlencode(route('front.blog.show', $post->slug)); @endphp
                    <div class="d-flex gap-2 mt-4 border-top pt-3">
                        @if ($socialShare['facebook'] ?? true)
                            <a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener"
                               href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}"><i class="fab fa-facebook-f"></i></a>
                        @endif
                        @if ($socialShare['twitter'] ?? true)
                            <a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener"
                               href="https://twitter.com/intent/tweet?url={{ $shareUrl }}"><i class="fab fa-x-twitter"></i></a>
                        @endif
                        @if ($socialShare['linkedin'] ?? true)
                            <a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener"
                               href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}"><i class="fab fa-linkedin-in"></i></a>
                        @endif
                        @if ($socialShare['whatsapp'] ?? true)
                            <a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener"
                               href="https://wa.me/?text={{ $shareUrl }}"><i class="fab fa-whatsapp"></i></a>
                        @endif
                        @if ($socialShare['copy_link'] ?? false)
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-gsm-copy-link="{{ route('front.blog.show', $post->slug) }}">
                                <i class="fas fa-link"></i>
                            </button>
                        @endif
                        <a href="{{ route('front.blog.index') }}" class="ms-auto small align-self-center text-decoration-none"
                           style="color:#4b5563;">← Back to blog</a>
                    </div>
                </article>

                {{-- Related --}}
                @if ($related->isNotEmpty())
                    <h4 class="gsm-serif mt-5 mb-3">You May Also Like</h4>
                    <div class="row g-4">
                        @foreach ($related as $relatedPost)
                            <div class="col-12 col-md-4">
                                @include('frontend.blog.partials.card', ['post' => $relatedPost])
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="col-12 col-lg-4">
                @include('frontend.blog.partials.sidebar', ['filters' => []])
            </div>
        </div>
    </div>
@stop
