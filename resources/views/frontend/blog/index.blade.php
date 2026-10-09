@extends('frontend.layouts.app')

{{--
    Blog Listing SEO (Phase 4): reads the blog-listing Page's own SEO tab
    (Admin → Pages → Blog Listing → SEO — the same reusable field set every
    page already has). Every fallback below matches today's exact hardcoded
    values, so nothing changes until an admin fills in a field.
--}}
@php
    $seoMeta = $listingPage?->seoMeta;
    $pageTitle = $seoMeta?->meta_title ?? 'The Journal';
@endphp

@section('title', implode(' — ', array_filter([$pageTitle, setting('business.company_name')])))

@include('frontend.partials.seo-head', ['seo' => [
    'title' => $pageTitle,
    'description' => $seoMeta?->meta_description ?? setting('seo.meta_description'),
    'keywords' => $seoMeta?->meta_keywords,
    'canonical' => $seoMeta?->canonical_url ?? route('front.blog.index'),
    'robots' => $seoMeta?->robots,
    'og_title' => $seoMeta?->og_title ?? $pageTitle,
    'og_description' => $seoMeta?->og_description ?? $seoMeta?->meta_description ?? setting('seo.meta_description'),
    'og_image' => $seoMeta?->ogMedia?->url,
    'og_type' => 'website',
    'twitter_card' => $seoMeta?->twitter_card,
    'twitter_title' => $seoMeta?->twitter_title,
    'twitter_description' => $seoMeta?->twitter_description,
    'twitter_image' => $seoMeta?->twitterMedia?->url,
    'schema' => [
        '@context' => 'https://schema.org',
        '@type' => 'Blog',
        'name' => $pageTitle,
        'publisher' => ['@type' => 'Organization', 'name' => setting('business.company_name', '')],
    ],
]])

@section('content')
    {{-- Hero (Blog Listing CMS): reuses the same Hero component as Home/About/
         Collection/Contact/PDP — editable via Admin → Pages → Blog Listing.
         Falls back to today's exact static banner only if that page/section
         hasn't been seeded yet (fresh install) or has been disabled. --}}
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
                <h1 class="display-5 gsm-serif">The Journal</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center mb-0 small">
                        <li class="breadcrumb-item"><a href="{{ route('front.home') }}" class="text-white-50">Home</a></li>
                        <li class="breadcrumb-item active text-white">The Journal</li>
                    </ol>
                </nav>
            </div>
        </section>
    @endif

    <div class="container py-5">
        {{-- Active filter notice --}}
        @if (array_filter($filters))
            <div class="alert alert-light border d-flex align-items-center py-2 small">
                <span>
                    Showing posts
                    @if ($filters['search']) matching “{{ $filters['search'] }}” @endif
                    @if ($filters['category']) in category “{{ $filters['category'] }}” @endif
                    @if ($filters['tag']) tagged “{{ $filters['tag'] }}” @endif
                    @if ($filters['archive']) from {{ $filters['archive'] }} @endif
                </span>
                <a href="{{ route('front.blog.index') }}" class="ms-auto">Clear</a>
            </div>
        @endif

        <div class="row g-4">
            <div class="col-12 col-lg-8">
                @if ($posts->isEmpty())
                    <p class="text-muted text-center py-5">No posts found.</p>
                @else
                    {{-- Grid Columns (Blog Listing CMS): 2 (default, matches today
                         exactly) or 3 — Bootstrap col-md-6 / col-md-4. --}}
                    @php $colClass = ((int) ($grid['columns'] ?? 2)) === 3 ? 'col-md-4' : 'col-md-6'; @endphp
                    <div class="row g-4">
                        @foreach ($posts as $post)
                            <div class="col-12 {{ $colClass }}">
                                @include('frontend.blog.partials.card', ['post' => $post, 'grid' => $grid])
                            </div>
                        @endforeach
                    </div>

                    @if ($grid['pagination_enabled'] ?? true)
                        <div class="mt-4 d-flex justify-content-center">
                            {{ $posts->links() }}
                        </div>
                    @endif
                @endif
            </div>

            <div class="col-12 col-lg-4">
                @include('frontend.blog.partials.sidebar')
            </div>
        </div>
    </div>
@stop
