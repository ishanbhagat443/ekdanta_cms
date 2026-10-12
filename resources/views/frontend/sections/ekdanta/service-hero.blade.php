{{--
    Ekdanta · Service Hero (Live Scene). The service comes from the template
    page ($templateItem) or the section's "service" field; its colours and
    scene are its own fields (Collections → Services). Load tracks bring the
    text in; the scene's loops are tracks too (sd-<scene>-…); scrolling sets
    the scene down. Phones (no design): text, the scene smaller, then the
    other services in a scrolling row.
--}}
@php
    $d = $section->dataFor('desktop');
    $m = $section->dataFor('mobile');
    $u = fn ($n) => "calc({$n} * var(--u))";
    $pages = \App\Models\CollectionItem::query()->of('services')->published()->where('data->has_page', true)->get();
    $service = $templateItem ?? null;
    $service = $service instanceof \App\Models\CollectionItem ? $service : ($pages->firstWhere('slug', $d['service'] ?? '') ?? $pages->first());
@endphp
@if ($service)
@php
    $c = fn (string $key, string $fallback) => $service->get($key) ?: $fallback;
    $bg = $c('hero_bg', '#0D1410');
    $fg = $c('hero_fg', '#FFFFFF');
    $glow = $c('hero_glow', 'rgba(46,180,93,0.28)');
    $line = $c('hero_line', 'rgba(255,255,255,0.35)');
    $accent = $c('hero_accent', '#2EB45D');
    $accentFg = $c('hero_accent_fg', '#0D1410');
    $lower = $service->get('lower_name') ?: mb_strtolower($service->title);
    $goodFor = array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $service->get('good_for')))));
    $scene = array_key_exists($service->get('scene'), \App\Collections\Services::SCENES) ? $service->get('scene') : 'social';
    $len = mb_strlen($service->title);
    $titleSize = $len <= 10 ? [120, 60] : ($len <= 19 ? [88, 46] : [78, 40]);
    $chat = '<path d="M21 12a8 8 0 0 1-11.6 7.1L4 20.5l1.4-5A8 8 0 1 1 21 12z"></path>';
    $wa = str_replace('{service}', $lower, (string) ($d['button_link'] ?? ''));
@endphp
<div class="ek ek-service-hero" id="{{ $d['anchor'] ?: 'v-top' }}" style="--hero-fg: {{ $fg }}; --hero-line: {{ $line }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            $button = 'height: '.$u($v([56, 46])).'; box-sizing: border-box; display: inline-flex; align-items: center; border-radius: 999px; font-family: \'Poppins\', sans-serif; font-weight: 600; font-size: max('.$u($v([16, 15])).', 0.8125rem); text-decoration: none'.($mob ? '; justify-content: center' : '');
            $label = 'font-family: \'Poppins\', sans-serif; font-weight: 600; font-size: max('.$u(12).', 0.75rem); letter-spacing: 0.1em';
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: {{ $bg }}; color: {{ $fg }}">
                <div class="ek-art">
                    <div aria-hidden="true" style="position: absolute; left: {{ $u($v([760, -10])) }}; top: {{ $u($v([60, 380])) }}; width: {{ $u($v([700, 410])) }}; height: {{ $u($v([700, 410])) }}; border-radius: 999px; background: radial-gradient(circle, {{ $glow }}, rgba(0,0,0,0) 65%)"></div>

                    <div data-m="sv-text" style="position: absolute; left: {{ $u($v([96, 24])) }}; top: {{ $u($v([132, 84])) }}; width: {{ $u($v([640, 342])) }}">
                        <p data-m="sv-crumb" style="margin: 0; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ $u($v([15, 13])) }}, 0.8125rem); opacity: 0.75"><a href="{{ link_href($d['crumb_home_link'] ?? '/services') }}" style="text-decoration: none">{{ $t['crumb_home'] }}</a><span aria-hidden="true">  ›  </span>{{ $service->title }}</p>
                        <h1 style="margin: {{ $u($v([16, 10])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ $u($v($titleSize)) }}, 0.8125rem); line-height: 1; letter-spacing: -0.05em"><span style="display: block; overflow: hidden; padding-bottom: {{ $u($v([8, 4])) }}"><span data-m="sv-title" style="display: block">{{ $service->title }}</span></span></h1>
                        <p data-m="sv-promise" style="margin: {{ $u($v([24, 12])) }} 0 0; {{ $mob ? '' : 'width: '.$u(580).';' }} font-size: max({{ $u($v([21, 16])) }}, 0.8125rem); line-height: 1.5; opacity: 0.88">{{ $service->get('promise') }}</p>
                        @if ($goodFor)
                            <div data-m="sv-good-for" style="margin-top: {{ $u($v([24, 14])) }}; display: flex; align-items: center; gap: {{ $u($v([10, 8])) }}; flex-wrap: wrap">
                                <span class="ek-sv-label" style="{{ $label }}; margin-right: {{ $u(4) }}">{{ $t['good_for_label'] }}</span>
                                <ul style="display: contents; list-style: none">
                                    @foreach ($goodFor as $item)
                                        <li style="padding: {{ $u($v([8, 6])) }} {{ $u($v([14, 12])) }}; border-radius: 999px; box-shadow: inset 0 0 0 {{ $u(1.5) }} {{ $line }}; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ $u($v([14, 13])) }}, 0.8125rem)">{{ $item }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div data-m="sv-buttons" style="margin-top: {{ $u($v([32, 18])) }}; display: flex; {{ $mob ? 'flex-direction: column;' : '' }} gap: {{ $u($v([12, 8])) }}">
                            <a class="ek-press" href="{{ link_href($wa) }}" @if (\App\Support\Link::external($wa)) target="_blank" rel="noopener" @endif style="{{ $button }}; padding: 0 {{ $u($v([24, 20])) }}; gap: {{ $u(8) }}; background: {{ $accent }}; color: {{ $accentFg }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: {{ $u(18) }}; height: {{ $u(18) }}">{!! $chat !!}</svg>{{ str_replace('{service}', $lower, $t['button_label']) }}</a>
                            <a class="ek-press" href="{{ link_href($d['button_2_link'] ?? '') }}" style="{{ $button }}; padding: 0 {{ $u($v([22, 20])) }}; box-shadow: inset 0 0 0 {{ $u(1.5) }} {{ $line }}">{{ $t['button_2_label'] }}</a>
                        </div>
                    </div>

                    <div aria-hidden="true" data-m="sv-scene" style="position: absolute; left: {{ $u($v([790, 55])) }}; top: {{ $u($v([130, 498])) }}; width: {{ $u($v([560, 280])) }}; height: {{ $u($v([560, 280])) }}">
                        <div style="position: absolute; left: 0; top: 0; width: {{ $u(560) }}; height: {{ $u(560) }}; {{ $mob ? 'transform: scale(0.5); transform-origin: 0 0' : '' }}">
                            @include('frontend.sections.ekdanta.service-scenes.'.$scene)
                        </div>
                    </div>

                    @if ($pages->count() > 1)
                        <nav data-m="sv-nav" aria-label="{{ $d['nav_label'] }}" style="position: absolute; left: {{ $u($v([96, 0])) }}; {{ $mob ? 'right: 0; padding: 0 '.$u(24).'; overflow-x: auto; scrollbar-width: none;' : '' }} top: {{ $u($v([800, 794])) }}; display: flex; align-items: center; gap: {{ $u(6) }}">
                            <span class="ek-sv-label" style="{{ $label }}; margin-right: {{ $u(10) }}; flex: none">{{ $t['all_label'] }}</span>
                            @foreach ($pages as $page)
                                @php $here = $page->id === $service->id; @endphp
                                <a class="ek-press" href="{{ $page->url() }}" @if ($here) aria-current="page" @endif style="flex: none; height: {{ $u($v([40, 36])) }}; padding: 0 {{ $u($v([16, 14])) }}; display: inline-flex; align-items: center; border-radius: 999px; background: {{ $here ? $fg : 'transparent' }}; color: {{ $here ? $bg : $fg }}; box-shadow: inset 0 0 0 {{ $u(1) }} {{ $line }}; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ $u($v([14, 13])) }}, 0.8125rem); text-decoration: none; white-space: nowrap">{{ $page->get('short_name') ?: $page->title }}</a>
                            @endforeach
                        </nav>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
@endif
