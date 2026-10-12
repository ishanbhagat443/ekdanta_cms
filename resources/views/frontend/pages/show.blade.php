@extends('frontend.layouts.app')

@php
    $seoService = app(\App\Services\SeoService::class);
    // A template page drawing one item (/work/…, /services/…, /blog/…) speaks for that item.
    $templateItem = $templateItem ?? null;
    $seoMeta = $templateItem ? $templateItem->seoMeta : $page?->seoMeta;
    $seoTitle = $templateItem->title ?? $page?->title;
    $seo = $page ? $seoService->resolve($seoMeta, [
        'title' => $seoTitle,
        'canonical' => $seoService->canonicalFor($templateItem ? request()->path() : ($isHome ? '/' : $page->slug)),
    ]) : [];

    // Entity schema (if set) + BreadcrumbList for inner pages.
    $schemas = [];
    if (!empty($seo['schema'])) {
        $decoded = is_string($seo['schema']) ? json_decode($seo['schema'], true) : $seo['schema'];
        if ($decoded) $schemas[] = $decoded;
    }
    if ($page && !$isHome) {
        $schemas[] = $seoService->breadcrumbSchema([
            ['name' => $seoMeta?->breadcrumb_title ?: $seoTitle],
        ]);
    }
    if ($schemas !== []) $seo['schema'] = count($schemas) === 1 ? $schemas[0] : $schemas;
@endphp

@php
    // <title>: the page's own meta title, else the site's title pattern.
    $titleTag = $seoMeta?->meta_title ?: site_title($seoTitle);
@endphp
@section('title', $titleTag)

@if ($page)
    @include('frontend.partials.seo-head', ['seo' => $seo])

    {{-- Page settings → Conversion tags: on the live page only (not in the builder's preview). --}}
    @php $conversions = request()->boolean('preview') ? [] : array_filter((array) $page->setting('conversions', [])); @endphp
    @if ($conversions !== [])
        @push('head')
            @if (! empty($conversions['meta_pixel']))
                <script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init',@json($conversions['meta_pixel']));fbq('track','PageView');fbq('track','Lead');</script>
            @endif
            @if (! empty($conversions['ads_id']))
                <script async src="https://www.googletagmanager.com/gtag/js?id={{ $conversions['ads_id'] }}"></script>
                <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config',@json($conversions['ads_id']));@if (! empty($conversions['ads_label']))gtag('event','conversion',{send_to:@json($conversions['ads_id'].'/'.$conversions['ads_label'])});@endif</script>
            @endif
        @endpush
    @endif

    {{-- Page settings → Page code (Developer Mode only to write). --}}
    @php $pageCode = (array) $page->setting('code', []); @endphp
    @if (! empty($pageCode['head']) || ! empty($pageCode['css']))
        @push('head')
            {!! $pageCode['head'] ?? '' !!}
            @if (! empty($pageCode['css']))<style>{!! style_safe($pageCode['css']) !!}</style>@endif
        @endpush
    @endif
    @if (! empty($pageCode['js']) || ! empty($pageCode['body']))
        @push('scripts')
            @if (! empty($pageCode['js']))<script>{!! $pageCode['js'] !!}</script>@endif
            {!! $pageCode['body'] ?? '' !!}
        @endpush
    @endif
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
