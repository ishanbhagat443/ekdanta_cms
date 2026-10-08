{{--
    Generic SEO head block.
    $seo: ['title', 'description', 'keywords', 'canonical', 'robots',
           'og_title', 'og_description', 'og_image', 'og_type', 'schema' (array|string)]
--}}
@php
    $siteName = setting('business.company_name', config('gsm.name'));
    $title = $seo['title'] ?? $siteName;
    $ogTitle = $seo['og_title'] ?? $title;
@endphp

@push('head')
    @if (!empty($seo['description']))
        <meta name="description" content="{{ $seo['description'] }}">
    @endif
    @if (!empty($seo['keywords']))
        <meta name="keywords" content="{{ $seo['keywords'] }}">
    @endif
    @if (!empty($seo['robots']))
        <meta name="robots" content="{{ $seo['robots'] }}">
    @endif
    @if (!empty($seo['canonical']))
        <link rel="canonical" href="{{ $seo['canonical'] }}">
    @endif

    {{-- Open Graph + Twitter --}}
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:type" content="{{ $seo['og_type'] ?? 'website' }}">
    <meta property="og:title" content="{{ $ogTitle }}">
    @if (!empty($seo['og_description'] ?? $seo['description'] ?? null))
        <meta property="og:description" content="{{ $seo['og_description'] ?? $seo['description'] }}">
    @endif
    <meta property="og:url" content="{{ url()->current() }}">
    @if (!empty($seo['og_image']))
        <meta property="og:image" content="{{ $seo['og_image'] }}">
    @endif

    @php
        $twitterImage = $seo['twitter_image'] ?? $seo['og_image'] ?? null;
        $twitterCard = $seo['twitter_card'] ?? ($twitterImage ? 'summary_large_image' : 'summary');
    @endphp
    <meta name="twitter:card" content="{{ $twitterCard }}">
    <meta name="twitter:title" content="{{ $seo['twitter_title'] ?? $ogTitle }}">
    @if (!empty($seo['twitter_description'] ?? $seo['og_description'] ?? $seo['description'] ?? null))
        <meta name="twitter:description" content="{{ $seo['twitter_description'] ?? $seo['og_description'] ?? $seo['description'] }}">
    @endif
    @if (!empty($twitterImage))
        <meta name="twitter:image" content="{{ $twitterImage }}">
    @endif
    @if (setting('seo.twitter_handle'))
        <meta name="twitter:site" content="{{ setting('seo.twitter_handle') }}">
    @endif

    {{-- JSON-LD. Always re-encoded with JSON_HEX_TAG so "</script>" inside
         admin-supplied JSON can never break out of this tag. --}}
    @php
        $schemaData = $seo['schema'] ?? null;
        if (is_string($schemaData)) {
            $schemaData = json_decode($schemaData, true);
        }
    @endphp
    @if (!empty($schemaData))
        <script type="application/ld+json">{!! json_encode($schemaData, JSON_HEX_TAG | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endif
@endpush
