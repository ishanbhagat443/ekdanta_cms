<!DOCTYPE html>
{{-- data-motion="off": every visitor gets the reduced-motion page (Theme
     Customizer → Animations off), or a preview asked for it (?reduce-motion=1).
     "forced" hides the visitor's own Pause button: the choice is already made. --}}
@php $motionForced = ! theme('animations.enabled', true) || request()->boolean('reduce-motion'); @endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @if ($motionForced) data-motion="off" data-motion-forced @endif>
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
    {{-- Before first paint: JS is on; the visitor's "Pause animations" choice;
         and if the motion script never arrives, show everything after 2.5 s. --}}
    <script>
        (function (root) {
            root.classList.add('js-enabled');
            try { if (!root.hasAttribute('data-motion') && localStorage.getItem('ek-motion-paused') === '1') root.setAttribute('data-motion', 'off'); } catch (e) {}
            setTimeout(function () { if (!window.EkMotion) root.classList.add('ek-reveal'); }, 2500);
        })(document.documentElement);
    </script>

    @stack('head')
</head>
<body class="@yield('bodyClass')" data-gsm-theme="{{ theme('dark_mode.enabled', false) ? 'light' : '' }}">
    @include('frontend.partials.topbar')
    @include('frontend.partials.header')

    <main>
        @yield('content')
    </main>

    {{-- Hidden by the page's settings, or by a section with its own footer. --}}
    @unless ($hideFooter ?? false)
        @include('frontend.partials.footer')
    @endunless


    @if (theme('custom_code.js_global'))
        <script>{!! theme('custom_code.js_global') !!}</script>
    @endif
    @if (theme('custom_code.js_footer'))
        <script>{!! theme('custom_code.js_footer') !!}</script>
    @endif

    {{-- Builder preview only: click-to-edit on the page itself. Never sent
         to a public visitor — see gsm_inline_editing(). --}}
    @if (gsm_inline_editing())
        <link href="{{ asset('css/inline-edit.css') }}?v={{ gsm_version() }}" rel="stylesheet">
        <script src="{{ asset('js/gsm-inline-edit.js') }}?v={{ gsm_version() }}" defer></script>
    @endif

    @stack('scripts')
</body>
</html>
