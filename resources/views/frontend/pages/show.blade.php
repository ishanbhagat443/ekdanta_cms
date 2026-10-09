@extends('frontend.layouts.app')

@php
    $seoService = app(\App\Services\SeoService::class);
    $seoMeta = $page?->seoMeta;
    $seo = $page ? $seoService->resolve($seoMeta, [
        'title' => $page->title,
        'canonical' => $seoService->canonicalFor($isHome ? '/' : $page->slug),
    ]) : [];

    // Entity schema (if set) + BreadcrumbList for inner pages.
    $schemas = [];
    if (!empty($seo['schema'])) {
        $decoded = is_string($seo['schema']) ? json_decode($seo['schema'], true) : $seo['schema'];
        if ($decoded) $schemas[] = $decoded;
    }
    if ($page && !$isHome) {
        $schemas[] = $seoService->breadcrumbSchema([
            ['name' => $seoMeta?->breadcrumb_title ?: $page->title],
        ]);
    }
    if ($schemas !== []) $seo['schema'] = count($schemas) === 1 ? $schemas[0] : $schemas;
@endphp

@php
    // <title>: explicit meta title / settings template win; otherwise "Page — Company".
    $titleTag = ($page && ! empty($seo['title']) && ($seoMeta?->meta_title || setting('seo.meta_title')))
        ? $seo['title']
        : implode(' — ', array_filter([$page?->title, setting('business.company_name')]));
@endphp
@section('title', $titleTag)

@if ($page)
    @include('frontend.partials.seo-head', ['seo' => $seo])
@endif

@section('content')
    @if ($page === null)
        {{-- Fresh install: no published home page yet. --}}
        <div class="container py-5 text-center text-muted">
            <p class="mb-0">Publish a page with slug “home” to build this screen.</p>
        </div>
    @else
        {{-- Page title banner (global inner-page pattern, hidden on home) --}}
        @if (!$isHome && $page->show_banner)
            @include('frontend.partials.page-title-banner', ['page' => $page])
        @endif

        @foreach ($sections as $entry)
            @include('frontend.partials.section-wrapper', [
                'section' => $entry['section'],
                'menu' => $entry['menu'],
                'mediaMap' => $entry['media'],
            ])
        @endforeach
    @endif
@stop
