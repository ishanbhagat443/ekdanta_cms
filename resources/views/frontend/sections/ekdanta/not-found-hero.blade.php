{{--
    Ekdanta · 404 Hero. Load tracks drop the 4s, roll the "e" in and bring
    the text up; loops make the "e" look around and blink, float the paper
    sheets and walk the trail to the pin. Scroll tracks pull the 4s apart,
    roll the "e" away and fade the hero out. Drawn arrived.
--}}
@php
    $d = $section->dataFor('desktop');
    $m = $section->dataFor('mobile');
    $u = fn ($n) => "calc({$n} * var(--u))";
    $code = (string) ($d['code'] ?: '404');
    $mark = media_ref($d['mark'] ?? null)?->url;
    $home = '<path d="M3 11l9-7 9 7v9H3z"></path><path d="M10 20v-5h4v5"></path>';
    $glass = '<circle cx="11" cy="11" r="7"></circle><path d="M20 20l-4-4"></path>';
    // Paper sheets, in the order they fade in: left, top, tilt.
    $sheets = [
        'desktop' => [[120, 168, -10], [1250, 150, 9], [240, 520, 8], [1318, 420, -7], [96, 700, -4], [1190, 680, 5]],
        'mobile' => [[10, 78, -10], [336, 76, 9], [330, 668, -7], [14, 672, -4]],
    ];
@endphp
<div class="ek ek-not-found-hero" id="{{ $d['anchor'] ?: 'n-top' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            $four = 'display: block; font-family: \'Poppins\', sans-serif; font-weight: 800; font-size: max('.$u($v([360, 150])).', 0.8125rem); line-height: 0.82; letter-spacing: -0.06em; color: var(--tk-ink)';
            $e = $v([262, 108]);
            $button = 'box-sizing: border-box; align-items: center; border-radius: 999px; font-family: \'Poppins\', sans-serif; font-weight: 600; font-size: max('.$u($v([16, 15])).', 0.8125rem); text-decoration: none';
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: var(--tk-paper)">
                <div class="ek-art">
                    <div aria-hidden="true" data-m="nf-trail" style="position: absolute; inset: 0">
                        @foreach ($sheets[$comp] as $i => [$x, $y, $tilt])
                            <span data-m="nf-sheet-in" style="position: absolute; left: {{ $u($x) }}; top: {{ $u($y) }}"><span style="display: block; transform: rotate({{ $tilt }}deg)"><span data-m="nf-sheet-{{ $i }}" style="position: relative; display: block; width: {{ $u($v([66, 44])) }}; height: {{ $u($v([86, 58])) }}; box-sizing: border-box; padding: {{ $u($v([14, 9])) }} {{ $u($v([10, 7])) }}; border-radius: {{ $u($v([6, 5])) }}; background: #FFFFFF; box-shadow: 0 {{ $u($v([16, 12])) }} {{ $u($v([30, 24])) }} {{ $u($v([-18, -14])) }} rgba(13,20,16,0.45), inset 0 0 0 {{ $u(1) }} #E3E8E0">
                                @foreach ([[70, 0, 'var(--tk-line-2)'], [100, $v([8, 5]), '#EDF0EA'], [100, $v([6, 4]), '#EDF0EA'], [60, $v([6, 4]), '#EDF0EA']] as [$w, $gap, $fill])
                                    <span style="display: block; {{ $gap ? 'margin-top: '.$u($gap).';' : '' }} height: {{ $u($v([4, 3])) }}; width: {{ $w }}%; border-radius: {{ $u(2) }}; background: {{ $fill }}"></span>
                                @endforeach
                                <span style="position: absolute; right: {{ $u($v([8, 6])) }}; bottom: {{ $u($v([8, 5])) }}; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ $u($v([11, 9])) }}; color: #C5CCC7">?</span>
                            </span></span></span>
                        @endforeach
                        @if ($mob)
                            <svg viewBox="0 0 390 844" style="width: {{ $u(390) }}; height: {{ $u(844) }}; position: absolute; left: 0; top: 0"><path data-m="nf-trail-path" d="M 10 790 C 70 760, 110 810, 160 790 S 230 760, 260 800 C 280 830, 320 780, 300 760 C 280 740, 250 770, 280 790 C 300 806, 330 800, 352 772" fill="none" stroke="#9AA59E" stroke-width="4" stroke-linecap="round" style="stroke-dasharray: 2 14"></path>
                                <g data-m="nf-pin" style="transform-box: fill-box; transform-origin: 50% 100%"><path d="M352 772 c-13 0 -22 -9 -22 -21 c0 -16 22 -34 22 -34 s22 18 22 34 c0 12 -9 21 -22 21z" fill="#F2A230"></path><circle cx="352" cy="750" r="9.5" fill="#FFFFFF"></circle><text x="352" y="755" text-anchor="middle" style="font-family: 'Poppins', sans-serif; font-weight: 800; font-size: 13px" fill="#0D1410">?</text></g>
                            </svg>
                        @else
                            <svg viewBox="0 0 1440 900" style="width: {{ $u(1440) }}; height: {{ $u(900) }}; position: absolute; left: 0; top: 0"><path data-m="nf-trail-path" d="M 40 836 C 220 776, 320 876, 480 836 S 700 776, 820 836 S 1000 912, 1060 836 C 1110 766, 1010 736, 1000 806 C 990 876, 1150 876, 1262 806" fill="none" stroke="#9AA59E" stroke-width="5" stroke-linecap="round" style="stroke-dasharray: 2 14"></path>
                                <g data-m="nf-pin" style="transform-box: fill-box; transform-origin: 50% 100%"><path d="M1262 806 c-18 0 -30 -13 -30 -29 c0 -22 30 -47 30 -47 s30 25 30 47 c0 16 -12 29 -30 29z" fill="#F2A230"></path><circle cx="1262" cy="776" r="13" fill="#FFFFFF"></circle><text x="1262" y="782" text-anchor="middle" style="font-family: 'Poppins', sans-serif; font-weight: 800; font-size: 17px" fill="#0D1410">?</text></g>
                            </svg>
                        @endif
                    </div>

                    <div data-m="nf-digits" role="img" aria-label="{{ $code }}" style="position: absolute; left: 0; right: 0; top: {{ $u($v([104, 114])) }}; display: flex; justify-content: center; align-items: center; gap: {{ $mob ? 0 : $u(6) }}">
                        <span aria-hidden="true" data-m="nf-four-l"><span data-m="nf-drop" style="{{ $four }}">{{ mb_substr($code, 0, 1) }}</span></span>
                        <span aria-hidden="true" data-m="nf-e" style="margin: 0 {{ $u($v([34, 12])) }} 0 {{ $u($v([40, 16])) }}"><span data-m="nf-roll" style="display: block"><span data-m="nf-look" style="position: relative; display: block; width: {{ $u($e) }}; height: {{ $u($v([271, 112])) }}; transform-origin: 50% 85%">
                            @if ($mark)<img src="{{ $mark }}" alt="" style="width: {{ $u($e) }}; height: auto; display: block">@endif
                            <span data-m="nf-blink" style="position: absolute; left: {{ $u($v([92, 38])) }}; top: {{ $u($v([76, 31])) }}; width: {{ $u($v([26, 11])) }}; height: {{ $u($v([26, 11])) }}; border-radius: 50% 50% 46% 46%; background: #FFFFFF; transform: scaleY(0); transform-origin: 50% 0"></span>
                        </span></span></span>
                        <span aria-hidden="true" data-m="nf-four-r"><span data-m="nf-drop" style="{{ $four }}">{{ mb_substr($code, -1) }}</span></span>
                    </div>

                    <div data-m="nf-text" style="position: absolute; {{ $mob ? 'left: '.$u(24).'; right: '.$u(24).'; top: '.$u(290) : 'left: 0; right: 0; top: '.$u(452) }}; display: flex; flex-direction: column; align-items: center; text-align: center">
                        <span data-m="nf-badge" style="padding: {{ $u($v([7, 6])) }} {{ $u($v([14, 12])) }}; border-radius: 999px; background: #FFFFFF; box-shadow: inset 0 0 0 {{ $u(1) }} var(--tk-line-2); font-family: 'Poppins', sans-serif; font-weight: 700; font-size: {{ $mob ? $u(11) : 'max('.$u(12).', 0.75rem)' }}; letter-spacing: 0.12em; color: var(--tk-muted)">{{ $t['badge'] }}</span>
                        <h1 style="margin: {{ $u($v([16, 14])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ $u($v([64, 40])) }}, 0.8125rem); line-height: {{ $v([1.02, 1.04]) }}; letter-spacing: -0.05em"><span style="display: block; overflow: hidden; padding: 0 {{ $u($v([6, 4])) }} {{ $u(6) }}"><span data-m="nf-title" style="display: block">{{ $t['heading'] }}{!! $mob ? '<br>' : ' ' !!}<span style="color: var(--tk-green-text)">{{ $t['heading_2'] }}</span> {{ $t['heading_3'] }}</span></span></h1>
                        <p data-m="nf-lede" style="margin: {{ $u($v([10, 8])) }} 0 0; {{ $mob ? '' : 'width: '.$u(620).';' }} font-size: max({{ $u($v([20, 16])) }}, 0.8125rem); line-height: {{ $v([1.55, 1.5]) }}; color: var(--tk-muted)">{{ $t['text'] }}</p>
                        <div data-m="nf-buttons" style="margin-top: {{ $u($v([26, 20])) }}; display: flex; {{ $mob ? 'width: '.$u(342).'; flex-direction: column; gap: '.$u(10) : 'align-items: center; gap: '.$u(12) }}">
                            <a class="ek-press" href="{{ link_href($d['button_link'] ?? '') }}" style="{{ $button }}; height: {{ $u($v([54, 52])) }}; {{ $mob ? 'display: flex; justify-content: center' : 'display: inline-flex; padding: 0 '.$u(24) }}; gap: {{ $u(10) }}; background: var(--tk-green); color: var(--tk-ink)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: {{ $u(18) }}; height: {{ $u(18) }}">{!! $home !!}</svg>{{ $t['button_label'] }}</a>
                            <a class="ek-press" href="{{ link_href($d['button_2_link'] ?? '') }}" style="{{ $button }}; height: {{ $u($v([54, 48])) }}; {{ $mob ? 'display: flex; justify-content: center' : 'display: inline-flex; padding: 0 '.$u(24) }}; box-shadow: inset 0 0 0 {{ $u(1.5) }} var(--tk-ink)">{{ $t['button_2_label'] }}</a>
                            <a href="{{ link_href($d['search_link'] ?? '') }}" style="{{ $button }}; height: {{ $u($v([54, 40])) }}; {{ $mob ? 'display: flex; justify-content: center' : 'display: inline-flex; padding: 0 '.$u(12) }}; gap: {{ $u(8) }}; color: var(--tk-green-deep)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true" style="width: {{ $u($v([18, 17])) }}; height: {{ $u($v([18, 17])) }}">{!! $glass !!}</svg>{{ $t['search_label'] }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
