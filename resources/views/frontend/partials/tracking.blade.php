{{--
    Analytics and ad tags: Settings → SEO Defaults (Google Analytics, Tag
    Manager) and a page's Conversion tags (@section('conversion_tags')).
    The one gate: nothing here is sent before tracking_allowed() (the visitor
    chose "Allow all", or Settings → Cookies is off), nor in the builder's
    preview. Google tags start with Consent Mode v2 at "denied", then
    "granted", the choice the visitor made.
--}}
@php
    $ga = trim((string) setting('seo.google_analytics', ''));
    $gtm = trim((string) setting('seo.google_tag_manager', ''));
@endphp
@if (tracking_allowed() && ! request()->boolean('preview') && ($ga || $gtm || View::hasSection('conversion_tags')))
    <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}@if (cookie_bar())gtag('consent','default',{ad_storage:'denied',ad_user_data:'denied',ad_personalization:'denied',analytics_storage:'denied'});gtag('consent','update',{ad_storage:'granted',ad_user_data:'granted',ad_personalization:'granted',analytics_storage:'granted'});@endif</script>
    @if ($gtm)
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s);j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+encodeURIComponent(i);f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer',@json($gtm));</script>
    @endif
    @if ($ga)
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ urlencode($ga) }}"></script>
        <script>gtag('js',new Date());gtag('config',@json($ga));</script>
    @endif
    @yield('conversion_tags')
@endif
