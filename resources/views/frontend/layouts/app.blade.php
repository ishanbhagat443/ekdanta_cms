<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @unless (theme('animations.enabled', true)) data-motion="off" @endunless>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', setting('business.company_name', ''))</title>

    @if (setting_image('branding.favicon'))
        <link rel="icon" href="{{ setting_image('branding.favicon') }}">
    @endif

    {{-- Font Awesome 7 (CDN). The copy bundled under public/vendor is AdminLTE's
         v5 and lacks the v6+ names the storefront uses (fa-bag-shopping,
         fa-magnifying-glass, fa-house, fa-shirt, fa-x-twitter …), which render
         as empty boxes. v7 also keeps the legacy v5 aliases, so both work. --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.0/css/all.min.css"
          integrity="sha512-ApSLB1Pd3/bZN8fWB/RG9YhN/7bd9Hkf3AGaE2mPfebjrxagjuBtx2GcgdqIlJkUzwylBo61r9Xa9NmgBI0swA=="
          crossorigin="anonymous" referrerpolicy="no-referrer">

    {{-- Bootstrap 5 + site styles --}}
    @vite(['resources/scss/frontend.scss', 'resources/js/frontend/app.js'])

    {{-- Theme Customizer: Google Fonts + local @font-face + generated CSS variables --}}
    @if ($googleFontsUrls = app(\App\Services\ThemeService::class)->googleFontsUrls())
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        @foreach ($googleFontsUrls as $googleFontsUrl)
            <link rel="stylesheet" href="{{ $googleFontsUrl }}">
        @endforeach
    @endif
    <style id="gsm-theme-vars">{!! theme_css() !!}</style>

    @if (theme('custom_code.js_header'))
        <script>{!! theme('custom_code.js_header') !!}</script>
    @endif

    {{-- Set before first paint: the animation CSS only hides content when
         this class is present, so a visitor without JS (or with a blocked
         CDN) sees everything instead of a blank page. --}}
    <script>document.documentElement.classList.add('js-enabled');</script>

    @stack('head')
</head>
<body class="@yield('bodyClass')" data-gsm-theme="{{ theme('dark_mode.enabled', false) ? 'light' : '' }}">
    @include('frontend.partials.topbar')
    @include('frontend.partials.header')

    <main>
        @yield('content')
    </main>

    @include('frontend.partials.footer')


    @if (theme('custom_code.js_global'))
        <script>{!! theme('custom_code.js_global') !!}</script>
    @endif
    @if (theme('custom_code.js_footer'))
        <script>{!! theme('custom_code.js_footer') !!}</script>
    @endif

    {{-- Scroll animations: Anime.js v4 (UMD global `anime`) plus the CMS's
         attribute reader. Deferred so they never block first paint. --}}
    {{-- Order matters: deferred scripts execute in document order, so the
         local reveal script goes FIRST. If the CDN stalls, our file has
         already run and can un-hide the content instead of waiting behind it. --}}
    <script src="{{ asset('js/gsm-animations.js') }}?v={{ gsm_version() }}" defer></script>
    <script src="{{ config('gsm.anime.url') }}"
            integrity="{{ config('gsm.anime.sri') }}" crossorigin="anonymous"
            referrerpolicy="no-referrer" defer></script>

    {{-- Builder preview only: click-to-edit on the page itself. Never sent
         to a public visitor — see gsm_inline_editing(). --}}
    @if (gsm_inline_editing())
        <link href="{{ asset('css/inline-edit.css') }}?v={{ gsm_version() }}" rel="stylesheet">
        <script src="{{ asset('js/gsm-inline-edit.js') }}?v={{ gsm_version() }}" defer></script>
    @endif

    @stack('scripts')
</body>
</html>
