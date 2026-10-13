{{--
    Ekdanta · Work Hero (Wall). Four columns of case study and reel
    pictures, each list drawn twice so its loop (up or down by half) is
    seamless; load tracks bring the columns and the text in; scrolling pulls
    the wall closer and fades the text. Phones (no design): text on top,
    the wall smaller underneath.
--}}
@php
    $d = $section->viewData('desktop');
    $m = $section->viewData('mobile');
    $cases = \App\Models\CollectionItem::pick('case-studies', $d['cases'] ?? null)->map(fn ($c) => media_ref($c->get('image'))?->web_url)->filter()->values();
    $reels = \App\Models\CollectionItem::pick('reels', $d['reels'] ?? null)->map(fn ($r) => media_ref($r->get('cover'))?->web_url)->filter()->values();
    // Columns alternate case (300 tall) and reel (500 tall), starting with a case on odd columns.
    $columns = [];
    [$ci, $ri] = [0, 0];
    for ($c = 0; $c < 4; $c++) {
        for ($k = 0; $k < 4; $k++) {
            $reel = ($c + $k) % 2 === 1;
            $list = $reel ? $reels : $cases;
            if ($list->isEmpty()) {
                continue;
            }
            $columns[$c][] = [$list[($reel ? $ri++ : $ci++) % $list->count()], $reel ? 500 : 300];
        }
    }
    $chips = array_values(array_filter((array) ($d['chips'] ?? []), fn ($c) => ($c['label'] ?? '') !== ''));
@endphp
<div class="ek ek-work-hero" id="{{ $d['anchor'] ?: 'w-top' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            $button = 'box-sizing: border-box; display: inline-flex; align-items: center; border-radius: 999px; font-family: \'Poppins\', sans-serif; font-weight: 600; font-size: max('.ek_u($v([16, 15])).', 0.8125rem); text-decoration: none; height: '.ek_u($v([56, 46])).($mob ? '; justify-content: center' : '');
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: var(--tk-paper)">
                <div class="ek-art">
                    <div aria-hidden="true" style="position: absolute; left: 0; right: 0; {{ $mob ? 'top: '.ek_u(480).'; bottom: 0; overflow: hidden' : 'top: 0; bottom: 0' }}">
                    <div style="position: absolute; {{ $mob ? 'left: '.ek_u(-60).'; top: '.ek_u(-40).'; width: '.ek_u(1440).'; height: '.ek_u(900).'; transform: scale(0.5); transform-origin: 0 0' : 'left: 0; top: 0; width: '.ek_u(1440).'; height: '.ek_u(900) }}">
                        <div data-m="wk-wall" style="position: absolute; inset: 0; transform-origin: {{ ek_u(1100) }} {{ ek_u(450) }}">
                            <div style="position: absolute; left: {{ ek_u($v([620, 300])) }}; top: {{ ek_u(-330) }}; width: {{ ek_u(1250) }}; height: {{ ek_u(1700) }}; display: flex; gap: {{ ek_u(22) }}; transform: rotateX(50deg) rotateZ(-34deg); transform-origin: 50% 50%">
                                @foreach ($columns as $c => $pictures)
                                    <div data-m="wk-col-in" style="width: {{ ek_u(280) }}; flex-shrink: 0"><div data-m="wk-col-{{ $c + 1 }}" style="display: flex; flex-direction: column; gap: {{ ek_u(22) }}">
                                        @foreach ([...$pictures, ...$pictures] as [$src, $h])
                                            <img src="{{ $src }}" alt="" loading="lazy" style="width: {{ ek_u(280) }}; height: {{ ek_u($h) }}; object-fit: cover; object-position: top; display: block; border-radius: {{ ek_u(20) }}; background: #FFFFFF; box-shadow: 0 {{ ek_u(30) }} {{ ek_u(50) }} {{ ek_u(-30) }} rgba(13,20,16,0.55)">
                                        @endforeach
                                    </div></div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    </div>
                    @if ($mob)
                        <div aria-hidden="true" style="position: absolute; left: 0; right: 0; top: {{ ek_u(480) }}; height: {{ ek_u(90) }}; background: linear-gradient(180deg, var(--tk-paper), rgba(244,246,241,0))"></div>
                    @else
                        <div aria-hidden="true" style="position: absolute; left: 0; top: 0; width: {{ ek_u(900) }}; height: {{ ek_u(900) }}; background: linear-gradient(90deg, var(--tk-paper) 0%, var(--tk-paper) 62%, rgba(244,246,241,0) 100%)"></div>
                    @endif
                    <div aria-hidden="true" style="position: absolute; left: 0; right: 0; bottom: 0; height: {{ ek_u($v([160, 90])) }}; background: linear-gradient(0deg, var(--tk-paper), rgba(244,246,241,0))"></div>

                    {{-- Above the wall: on a short phone the text can reach it. --}}
                    <div data-m="wk-text" style="position: absolute; z-index: 1; left: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([150, 92])) }}; width: {{ ek_u($v([640, 342])) }}">
                        <p data-m="wk-crumb" style="margin: 0; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([15, 13])) }}, 0.8125rem); color: var(--tk-muted)"><a href="{{ link_href($d['crumb_home_link'] ?? '/') }}" style="text-decoration: none">{{ $t['crumb_home'] }}</a><span aria-hidden="true">  ›  </span>{{ $t['crumb_current'] }}</p>
                        <h1 style="margin: {{ ek_u($v([14, 8])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u($v([128, 52])) }}, 0.8125rem); line-height: 1; letter-spacing: -0.05em">
                            <span style="display: block; overflow: hidden; padding-bottom: {{ ek_u($v([8, 4])) }}"><span data-m="wk-line" style="display: block">{{ $t['heading'] }}</span></span>
                            <span style="display: block; overflow: hidden; padding-bottom: {{ ek_u($v([8, 4])) }}"><span data-m="wk-line" style="display: block; color: var(--tk-green-text)">{{ $t['heading_2'] }}</span></span>
                        </h1>
                        <p data-m="wk-lede" style="margin: {{ ek_u($v([20, 12])) }} 0 0; {{ $mob ? '' : 'width: '.ek_u(540).';' }} font-size: max({{ ek_u($v([21, 16])) }}, 0.8125rem); line-height: 1.5; color: #3B4640">{{ $t['text'] }}</p>
                        <ul data-m="wk-chips" style="margin: {{ ek_u($v([22, 14])) }} 0 0; padding: 0; list-style: none; display: flex; flex-wrap: wrap; gap: {{ ek_u($v([10, 8])) }}">
                            @foreach ($chips as $chip)
                                <li style="padding: {{ ek_u($v([9, 7])) }} {{ ek_u($v([16, 12])) }}; border-radius: 999px; background: #FFFFFF; box-shadow: inset 0 0 0 {{ ek_u(1) }} rgba(13,20,16,0.1); font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([14, 13])) }}, 0.8125rem)"><b style="color: var(--tk-green-text-sm)">{{ $chip['number'] ?? '' }}</b> {{ $chip['label'] }}</li>
                            @endforeach
                        </ul>
                        <div data-m="wk-buttons" style="margin-top: {{ ek_u($v([30, 16])) }}; display: flex; {{ $mob ? 'flex-direction: column;' : '' }} gap: {{ ek_u($v([12, 8])) }}">
                            <a class="ek-press" href="{{ link_href($d['button_link'] ?? '') }}" style="{{ $button }}; padding: 0 {{ ek_u($v([26, 18])) }}; background: var(--tk-ink); color: #FFFFFF">{{ $t['button_label'] }}</a>
                            <a class="ek-press" href="{{ link_href($d['button_2_link'] ?? '') }}" style="{{ $button }}; padding: 0 {{ ek_u($v([24, 16])) }}; gap: {{ ek_u(8) }}; background: var(--tk-paper); border: {{ ek_u(1.5) }} solid var(--tk-ink)"><svg viewBox="0 0 24 24" aria-hidden="true" style="width: {{ ek_u(16) }}; height: {{ ek_u(16) }}"><path d="M7 4v16l13-8z" fill="#0D1410"></path></svg>{{ $t['button_2_label'] }}</a>
                        </div>
                    </div>
                    <div aria-hidden="true" data-m="wk-hint" style="position: absolute; left: {{ ek_u($v([96, 24])) }}; bottom: {{ ek_u($v([30, 20])) }}; font-family: 'Poppins', sans-serif; font-weight: 500; font-size: max({{ ek_u(13) }}, 0.8125rem); letter-spacing: 0.14em; color: var(--tk-muted)">{{ $t['hint'] }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>
