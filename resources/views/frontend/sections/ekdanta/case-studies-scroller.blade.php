{{--
    Ekdanta · Case Studies Scroller. The index buttons are the engine's
    steps; the background colour, the picture track and its parallax, the
    big number, the story and its chips all follow --ek-f in CSS
    (_case-studies-scroller.scss). Scroll tracks bring the first case in.
    Phones (no design): numbered index on top, picture, then the story.
--}}
@php
    $d = $section->dataFor('desktop');
    $m = $section->dataFor('mobile');
    $cases = \App\Models\CollectionItem::pick('case-studies', $d['cases'] ?? null)->values();
    $total = str_pad((string) $cases->count(), 2, '0', STR_PAD_LEFT);
    $num = fn (int $i) => str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT);
    $arrow = '<path d="M5 12h14M13 6l6 6-6 6"></path>';
    // The brand colour as "r, g, b" for the background layers (ink if unset).
    $rgb = function ($hex) {
        $hex = ltrim((string) $hex, '#');

        return strlen($hex) === 6 ? implode(', ', array_map('hexdec', str_split($hex, 2))) : '13, 20, 16';
    };
@endphp
<div class="ek ek-case-studies-scroller" id="{{ $d['anchor'] ?: 'w-cases' }}" style="--count: {{ max(1, $cases->count()) }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            [$winW, $winH] = $v([[480, 620], [342, 210]]);
            $eyebrow = str_replace(['{n}', '{total}'], ['<span data-ek-step-num="2">01</span>', e($total)], e($t['eyebrow']));
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: var(--tk-ink); color: #FFFFFF">
                <div class="ek-art">
                    @foreach ($cases as $i => $case)
                        <div aria-hidden="true" class="ek-case-bg" @if ($i === 0) data-m="wc-bg" @endif style="--i: {{ $i }}; position: absolute; left: -50vw; right: -50vw; top: -50vh; bottom: -50vh; background: rgb({{ $rgb($case->get('colour')) }})"></div>
                    @endforeach
                    @foreach ($cases as $i => $case)
                        <span aria-hidden="true" class="ek-case-bignum" style="--i: {{ $i }}; position: absolute; right: {{ ek_u($v([40, 10])) }}; bottom: {{ ek_u($v([-90, -40])) }}; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u($v([380, 180])) }}; line-height: 1; letter-spacing: -0.06em; color: transparent; -webkit-text-stroke: {{ ek_u(2) }} rgba(255,255,255,0.12)">{{ $num($i) }}</span>
                    @endforeach

                    <p data-m="wc-eyebrow" style="position: absolute; left: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([112, 80])) }}; margin: 0; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([14, 12])) }}, {{ $v(['0.8125rem', '0.75rem']) }}); letter-spacing: 0.12em; color: rgba(255,255,255,0.7)">{!! $eyebrow !!}</p>

                    <div data-m="wc-index-in" style="position: absolute; left: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([168, 108])) }}">
                        <div data-m="wc-index" style="display: flex; {{ $mob ? 'gap: '.ek_u(6) : 'flex-direction: column; gap: '.ek_u(6).'; width: '.ek_u(280) }}">
                            @foreach ($cases as $i => $case)
                                @if ($mob)
                                    <button type="button" class="ek-case-tab {{ $i === 0 ? 'is-active' : '' }}" data-ek-step="{{ $i }}" style="--i: {{ $i }}; width: {{ ek_u(48) }}; height: {{ ek_u(32) }}; border: 0; border-radius: 999px; background: rgba(255,255,255,0.12); color: #FFFFFF; cursor: pointer; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u(13) }}, 0.8125rem)"><span aria-hidden="true">{{ $num($i) }}</span><span class="visually-hidden">{{ $case->title }}</span></button>
                                @else
                                    <button type="button" class="ek-case-tab {{ $i === 0 ? 'is-active' : '' }}" data-ek-step="{{ $i }}" style="--i: {{ $i }}; display: block; width: {{ ek_u(280) }}; padding: {{ ek_u(12) }} 0; border: 0; border-top: {{ ek_u(1) }} solid rgba(255,255,255,0.14); background: none; text-align: left; cursor: pointer; color: #FFFFFF">
                                        <span style="display: flex; gap: {{ ek_u(12) }}; align-items: baseline"><span style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u(13) }}, 0.8125rem); color: var(--tk-mint)">{{ $num($i) }}</span><span style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u(18) }}, 0.8125rem); letter-spacing: -0.02em">{{ $case->title }}</span></span>
                                        <span style="display: block; margin: {{ ek_u(4) }} 0 0 {{ ek_u(32) }}; font-size: max({{ ek_u(13) }}, 0.8125rem); letter-spacing: 0.04em; color: rgba(255,255,255,0.75)">{{ $case->get('sector') }}</span>
                                        <span aria-hidden="true" style="display: block; margin: {{ ek_u(10) }} 0 0 {{ ek_u(32) }}; height: {{ ek_u(3) }}; border-radius: {{ ek_u(2) }}; background: rgba(255,255,255,0.15); overflow: hidden"><span class="ek-case-tab-bar" style="display: block; height: 100%; background: var(--tk-green); transform-origin: 0 50%"></span></span>
                                    </button>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <div data-m="wc-window" class="ek-case-window" style="position: absolute; left: {{ ek_u($v([420, 24])) }}; top: {{ ek_u($v([150, 156])) }}; width: {{ ek_u($winW) }}; height: {{ ek_u($winH) }}; border-radius: {{ ek_u($v([28, 22])) }}; overflow: hidden; background: #FFFFFF; box-shadow: 0 {{ ek_u(50) }} {{ ek_u(90) }} {{ ek_u(-40) }} rgba(0,0,0,0.75); --win-h: {{ $winH }}">
                        <div class="ek-case-track" style="position: absolute; left: 0; top: 0; width: 100%">
                            @foreach ($cases as $i => $case)
                                <div style="position: relative; width: 100%; height: {{ ek_u($winH) }}; overflow: hidden">@if ($img = media_ref($case->get('image'))?->url)<img class="ek-case-img" src="{{ $img }}" alt="{{ str_replace('{name}', $case->title, (string) $d['image_alt']) }}" loading="lazy" style="--i: {{ $i }}; position: absolute; left: 0; top: {{ ek_u(-70) }}; width: 100%; height: {{ ek_u($winH + 140) }}; object-fit: cover; object-position: top; display: block">@endif</div>
                            @endforeach
                        </div>
                        <span aria-hidden="true" style="position: absolute; left: {{ ek_u($v([18, 12])) }}; top: {{ ek_u($v([18, 12])) }}; padding: {{ ek_u($v([7, 5])) }} {{ ek_u($v([12, 10])) }}; border-radius: 999px; background: rgba(13,20,16,0.82); font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u($v([13, 11])) }}, 0.75rem); letter-spacing: 0.06em; color: #FFFFFF">{!! str_replace('{n}', '<span data-ek-step-num="2">01</span>', e($d['window_label'])) !!}</span>
                    </div>

                    <div data-m="wc-texts">
                        @foreach ($cases as $i => $case)
                            @php $made = array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $case->get('made'))))); @endphp
                            <article class="ek-case-text" style="--i: {{ $i }}; position: absolute; left: {{ ek_u($v([960, 24])) }}; top: {{ ek_u($v([170, 388])) }}; width: {{ ek_u($v([390, 342])) }}; display: flex; flex-direction: column; gap: {{ ek_u($v([16, 8])) }}">
                                <span style="align-self: flex-start; padding: {{ ek_u($v([6, 4])) }} {{ ek_u($v([12, 10])) }}; border-radius: 999px; background: var(--tk-green); font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u($v([12, 11])) }}, 0.75rem); letter-spacing: 0.06em; color: var(--tk-ink)">{{ $case->get('sector') }}</span>
                                <h3 style="margin: 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u($v([50, 28])) }}, 0.8125rem); line-height: 1.02; letter-spacing: -0.045em">{{ $case->title }}</h3>
                                <p style="margin: 0; font-size: max({{ ek_u($v([18, 14])) }}, 0.8125rem); line-height: {{ $v([1.55, 1.45]) }}; color: rgba(255,255,255,0.86)">{{ $case->get('description') }}</p>
                                <span style="margin-top: {{ ek_u($v([6, 2])) }}; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u(12) }}, 0.75rem); letter-spacing: 0.1em; color: rgba(255,255,255,0.6)">{{ $t['made_label'] }}</span>
                                <ul style="margin: 0; padding: 0; list-style: none; display: flex; flex-wrap: wrap; gap: {{ ek_u($v([8, 6])) }}">
                                    @foreach ($made as $k => $item)
                                        <li class="ek-case-chip" style="--k: {{ $k }}; padding: {{ ek_u($v([8, 6])) }} {{ ek_u($v([14, 10])) }}; border-radius: 999px; background: rgba(255,255,255,0.1); box-shadow: inset 0 0 0 {{ ek_u(1) }} rgba(255,255,255,0.3); font-size: max({{ ek_u($v([15, 13])) }}, 0.8125rem); font-weight: 500">{{ $item }}</li>
                                    @endforeach
                                </ul>
                                <span style="margin-top: {{ ek_u($v([8, 2])) }}; padding-top: {{ ek_u($v([16, 10])) }}; border-top: {{ ek_u(1) }} solid rgba(255,255,255,0.2); display: flex; align-items: baseline; gap: {{ ek_u($v([14, 10])) }}"><span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u(12) }}, 0.75rem); letter-spacing: 0.1em; color: rgba(255,255,255,0.6)">{{ $t['result_label'] }}</span><span style="font-size: max({{ ek_u($v([16, 14])) }}, 0.8125rem); color: rgba(255,255,255,0.86)">{{ $case->get('home_result') }}</span></span>
                                @if ($url = $case->url())
                                    <a class="ek-press" href="{{ $url }}" style="align-self: flex-start; margin-top: {{ ek_u($v([4, 2])) }}; height: {{ ek_u($v([48, 44])) }}; box-sizing: border-box; padding: 0 {{ ek_u($v([20, 16])) }}; display: inline-flex; align-items: center; gap: {{ ek_u(8) }}; background: #FFFFFF; color: var(--tk-ink); border-radius: 999px; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([15, 14])) }}, 0.8125rem); text-decoration: none">{{ $t['button_label'] }}<span class="visually-hidden">: {{ $case->title }}</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: {{ ek_u(16) }}; height: {{ ek_u(16) }}">{!! $arrow !!}</svg></a>
                                @endif
                            </article>
                        @endforeach
                    </div>
                    @unless ($mob)
                        <div aria-hidden="true" data-m="wc-progress" style="position: absolute; left: {{ ek_u(960) }}; top: {{ ek_u(800) }}; width: {{ ek_u(384) }}; height: {{ ek_u(4) }}; border-radius: {{ ek_u(2) }}; background: rgba(255,255,255,0.15); overflow: hidden"><div class="ek-case-progress" style="height: 100%; background: var(--tk-orange); transform-origin: 0 50%"></div></div>
                    @endunless
                </div>
            </div>
        </div>
    @endforeach
</div>
