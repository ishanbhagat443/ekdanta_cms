{{-- Motion Library preview: the real engine running one preset on a sample. --}}
@php
    $keyframes = (array) $preset->keyframes;
    $sample = match (true) {
        isset($keyframes['$split']) => 'text',
        isset($keyframes['$count']) => 'count',
        isset($keyframes['draw']) => 'path',
        str_starts_with($preset->slug, 'marquee') => 'marquee',
        $preset->stagger > 0 => 'row',
        default => 'box',
    };
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Preview: {{ $preset->name }}</title>
    <script>document.documentElement.classList.add('js-enabled');</script>
    @vite(['resources/scss/frontend.scss', 'resources/js/frontend/app.js'])
    <style>
        body { margin: 0; background: #f4f6f1; color: #0d1410; font-family: system-ui, sans-serif; }
        .mp-stage { height: 100svh; display: grid; place-items: center; overflow: hidden; }
        .mp-box { width: 120px; height: 120px; border-radius: 22px; background: #0d1410; }
        .mp-row { display: flex; gap: 12px; padding: 0; margin: 0; list-style: none; }
        .mp-row li { width: 56px; height: 56px; border-radius: 14px; background: #0d1410; }
        .mp-text { max-width: 22ch; font-size: 28px; font-weight: 700; line-height: 1.2; text-align: center; }
        .mp-count { font-size: 64px; font-weight: 800; }
        .mp-marquee-wrap { width: 100%; overflow: hidden; }
        .mp-marquee { display: flex; width: max-content; gap: 40px; font-size: 32px; font-weight: 700; white-space: nowrap; }
    </style>
</head>
<body>
<section id="gsm-s0" class="ek-scene @foreach (array_keys($motion['lengths'] ?? []) as $pinDevice) ek-pin-{{ $pinDevice[0] }} @endforeach" data-ek-motion
         style="@foreach ($motion['lengths'] ?? [] as $pinDevice => $pinLength)--ek-len-{{ $pinDevice[0] }}: {{ $pinLength }};@endforeach">
    @if ($motion)
        <script type="application/json" data-ek-config>@json($motion['config'])</script>
        @if ($motion['css'] !== '')<style>{!! style_safe($motion['css']) !!}</style>@endif
    @endif
    <div class="mp-stage" data-ek-stage>
        @switch($sample)
            @case('text')
                <p class="mp-text" data-m="demo">This sentence shows the motion one piece at a time.</p>
                @break
            @case('count')
                <strong class="mp-count" data-m="demo">10,000+</strong>
                @break
            @case('path')
                <svg viewBox="0 0 200 100" width="260" aria-hidden="true"><path data-m="demo" d="M10 80 C 60 0, 140 0, 190 80" fill="none" stroke="#0d1410" stroke-width="4" stroke-linecap="round"/></svg>
                @break
            @case('marquee')
                <div class="mp-marquee-wrap"><div class="mp-marquee" data-m="demo">@for ($i = 0; $i < 8; $i++)<span>Motion ●</span>@endfor</div></div>
                @break
            @case('row')
                <ul class="mp-row" data-m="demo">@for ($i = 0; $i < 5; $i++)<li></li>@endfor</ul>
                @break
            @default
                <div class="mp-box" data-m="demo" tabindex="0" aria-label="Sample"></div>
        @endswitch
    </div>
</section>
</body>
</html>
