{{--
    Chrome-less frontend preview for the Global Block editor's live-preview
    iframe — loads the real frontend assets (Theme Customizer CSS, fonts,
    Bootstrap) and renders the block through the SAME section-wrapper +
    section-type Blade a live page uses, so what you see here is exactly
    what visitors would see.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Block Preview</title>
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    @vite(['resources/scss/frontend.scss', 'resources/js/frontend/app.js'])
    @foreach (app(\App\Services\ThemeService::class)->googleFontsUrls() as $googleFontsUrl)
        <link rel="stylesheet" href="{{ $googleFontsUrl }}">
    @endforeach
    <style>{!! theme_css() !!}</style>
    <style>body { padding: 1.5rem 0; }</style>
</head>
<body>
    @include('frontend.partials.section-wrapper', [
        'section' => $section,
        'menu' => $menu,
        'mediaMap' => $mediaMap,
    ])

    @stack('scripts')
</body>
</html>
