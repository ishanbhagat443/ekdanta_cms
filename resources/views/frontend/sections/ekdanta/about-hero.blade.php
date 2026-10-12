{{--
    Ekdanta · About Hero (Dictionary). Load tracks spell the word, bring the
    meanings in and sweep the highlight; the ledger's cover opens, its lines
    write themselves and get ticked. Scrolling lifts the ledger away. Drawn
    finished: highlight on, ledger open and filled. Phones (no design):
    the entry stacks above a smaller ledger.
--}}
@php
    $d = $section->viewData('desktop');
    $m = $section->viewData('mobile');
    $word = (string) ($d['word'] ?: 'Ekdanta');
    $green = (int) ($d['word_green'] ?? 0);
    $mark = media_ref($d['mark'] ?? null)?->url;
    $ledger = array_values(array_filter((array) ($d['ledger'] ?? []), fn ($r) => ($r['text'] ?? '') !== ''));
    $kalam = "font-family: 'Kalam', 'Hind', cursive";
@endphp
@once
    @push('head')
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Kalam:wght@400;700&display=swap">
    @endpush
@endonce
<div class="ek ek-about-hero" id="{{ $d['anchor'] ?: 'a-top' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            $meanings = array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $t['meanings']))));
            $button = 'box-sizing: border-box; display: inline-flex; align-items: center; border-radius: 999px; font-family: \'Poppins\', sans-serif; font-weight: 600; font-size: max('.ek_u($v([16, 15])).', 0.8125rem); text-decoration: none; height: '.ek_u($v([54, 48]));
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: #F6F2E7">
                <div class="ek-art">
                    <div aria-hidden="true" style="position: absolute; inset: 0; background-image: radial-gradient(rgba(13,20,16,0.06) {{ ek_u(1) }}, transparent {{ ek_u(1) }}); background-size: {{ ek_u(22) }} {{ ek_u(22) }}; opacity: 0.7"></div>

                    <div data-m="ab-text" style="position: absolute; left: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([138, 92])) }}; width: {{ ek_u($v([660, 342])) }}">
                        <p data-m="ab-crumb" style="margin: 0; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([15, 13])) }}, 0.8125rem); color: var(--tk-muted)"><a href="{{ link_href($d['crumb_home_link'] ?? '/') }}" style="text-decoration: none">{{ $t['crumb_home'] }}</a><span aria-hidden="true">  ›  </span>{{ $t['crumb_current'] }}</p>
                        <h1 aria-label="{{ $word }}" style="margin: {{ ek_u($v([10, 8])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u($v([148, 76])) }}, 0.8125rem); line-height: 1; letter-spacing: -0.05em; white-space: nowrap"><span aria-hidden="true">@foreach (mb_str_split($word) as $i => $letter)<span style="display: inline-block; overflow: hidden; padding-bottom: {{ ek_u($v([8, 4])) }}; vertical-align: top"><span data-m="ab-letter" style="display: inline-block{{ $i < $green ? '; color: var(--tk-green-text)' : '' }}">{{ $letter }}</span></span>@endforeach</span></h1>
                        <p data-m="ab-pron" style="margin: {{ ek_u($v([6, 4])) }} 0 0; display: flex; align-items: baseline; gap: {{ ek_u($v([14, 10])) }}; font-size: max({{ ek_u($v([22, 16])) }}, 0.8125rem); color: var(--tk-muted)"><span lang="hi" style="font-weight: 600; color: var(--tk-ink)">{{ $d['script'] }}</span><span aria-hidden="true">·</span><span style="font-style: italic">{{ $d['pronunciation'] }}</span><span aria-hidden="true">·</span><span style="font-style: italic">{{ $d['word_class'] }}</span></p>
                        <ol style="margin: {{ ek_u($v([22, 14])) }} 0 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: {{ ek_u($v([14, 8])) }}; font-size: max({{ ek_u($v([20, 15])) }}, 0.8125rem); line-height: 1.5">
                            @foreach ($meanings as $i => $meaning)
                                <li data-m="ab-meaning" style="display: flex; gap: {{ ek_u($v([14, 10])) }}"><span style="font-family: 'Poppins', sans-serif; font-weight: 700; color: var(--tk-green-text)">{{ $i + 1 }}.</span><span>@if ($i === count($meanings) - 1)<span data-m="ab-hl" style="font-family: 'Poppins', sans-serif; font-weight: 600; background-image: linear-gradient(transparent 56%, rgba(46,180,93,0.42) 56%); background-repeat: no-repeat; background-size: 100% 100%">{{ $meaning }}</span>@else{{ $meaning }}@endif</span></li>
                            @endforeach
                        </ol>
                        <div data-m="ab-buttons" style="margin-top: {{ ek_u($v([30, 20])) }}; display: flex; gap: {{ ek_u($v([12, 10])) }}">
                            <a class="ek-press" href="{{ link_href($d['button_link'] ?? '') }}" style="{{ $button }}; padding: 0 {{ ek_u($v([24, 20])) }}; background: var(--tk-ink); color: #FFFFFF">{{ $t['button_label'] }}</a>
                            <a class="ek-press" href="{{ link_href($d['button_2_link'] ?? '') }}" style="{{ $button }}; padding: 0 {{ ek_u($v([22, 18])) }}; border: {{ ek_u(1.5) }} solid var(--tk-ink)">{{ $t['button_2_label'] }}</a>
                        </div>
                    </div>

                    {{-- The ledger, drawn at desktop size; phones scale it down. --}}
                    <div aria-hidden="true" data-m="ab-book" style="position: absolute; left: {{ ek_u($v([790, 77])) }}; top: {{ ek_u($v([170, 560])) }}; width: {{ ek_u($v([590, 236])) }}; height: {{ ek_u($v([520, 208])) }}">
                        <div style="position: absolute; left: 0; top: 0; width: {{ ek_u(590) }}; height: {{ ek_u(520) }}; {{ $mob ? 'transform: scale(0.4); transform-origin: 0 0' : '' }}">
                            <div data-m="ab-float" style="position: absolute; left: 0; top: 0; width: {{ ek_u(590) }}; height: {{ ek_u(520) }}; perspective: {{ ek_u(2000) }}">
                                <div style="position: absolute; left: {{ ek_u(10) }}; top: {{ ek_u(34) }}; width: {{ ek_u(570) }}; height: {{ ek_u(432) }}; border-radius: {{ ek_u(16) }}; background: #9E1B16; box-shadow: 0 {{ ek_u(50) }} {{ ek_u(90) }} {{ ek_u(-40) }} rgba(13,20,16,0.55), inset 0 0 0 {{ ek_u(3) }} rgba(233,185,73,0.5)"></div>
                                <div style="position: absolute; left: {{ ek_u(22) }}; top: {{ ek_u(46) }}; width: {{ ek_u(273) }}; height: {{ ek_u(408) }}; border-radius: {{ ek_u(10) }} {{ ek_u(4) }} {{ ek_u(4) }} {{ ek_u(10) }}; background: #EFE6CF"></div>
                                <div style="position: absolute; left: {{ ek_u(295) }}; top: {{ ek_u(46) }}; width: {{ ek_u(273) }}; height: {{ ek_u(408) }}; border-radius: {{ ek_u(4) }} {{ ek_u(10) }} {{ ek_u(10) }} {{ ek_u(4) }}; background: #FFF8E6; box-shadow: inset {{ ek_u(14) }} 0 {{ ek_u(18) }} {{ ek_u(-14) }} rgba(0,0,0,0.35); overflow: hidden">
                                    <div style="position: absolute; left: 0; right: 0; top: {{ ek_u(78) }}; bottom: 0; background-image: repeating-linear-gradient(to bottom, transparent 0, transparent {{ ek_u(59) }}, rgba(31,42,68,0.14) {{ ek_u(59) }}, rgba(31,42,68,0.14) {{ ek_u(60) }})"></div>
                                    <span style="position: absolute; left: {{ ek_u(36) }}; top: 0; bottom: 0; width: {{ ek_u(1.5) }}; background: rgba(192,57,43,0.55)"></span><span style="position: absolute; left: {{ ek_u(40) }}; top: 0; bottom: 0; width: {{ ek_u(1.5) }}; background: rgba(192,57,43,0.55)"></span>
                                    <span style="position: absolute; left: {{ ek_u(50) }}; top: {{ ek_u(26) }}; {{ $kalam }}; font-weight: 700; font-size: {{ ek_u(22) }}; color: #C0392B">{{ $d['ledger_title'] }}</span>
                                    <span style="position: absolute; right: {{ ek_u(22) }}; top: {{ ek_u(32) }}; {{ $kalam }}; font-size: {{ ek_u(15) }}; color: #C0392B">{{ $d['ledger_page'] }}</span>
                                    @foreach ($ledger as $i => $row)
                                        <div style="position: absolute; left: {{ ek_u(50) }}; right: {{ ek_u(22) }}; top: {{ ek_u(98 + $i * 60) }}; height: {{ ek_u(40) }}; display: flex; align-items: flex-end; justify-content: space-between; {{ $kalam }}; font-weight: 700; font-size: {{ ek_u(25) }}; line-height: 1; color: #1F2A44"><span data-m="ab-write" style="display: inline-block; overflow: hidden; white-space: nowrap; vertical-align: bottom">{{ $row['text'] }}</span><span data-m="ab-tick" style="display: inline-block; color: {{ ($row['mark'] ?? '') === '✓' ? 'var(--tk-green-text)' : '#C0392B' }}">{{ $row['mark'] ?? '' }}</span></div>
                                    @endforeach
                                </div>
                                <div data-m="ab-cover" style="position: absolute; left: {{ ek_u(295) }}; top: {{ ek_u(46) }}; width: {{ ek_u(273) }}; height: {{ ek_u(408) }}; transform-origin: 0 50%; transform-style: preserve-3d; transform: rotateY(-180deg)">
                                    <div style="position: absolute; inset: 0; border-radius: {{ ek_u(4) }} {{ ek_u(12) }} {{ ek_u(12) }} {{ ek_u(4) }}; background: linear-gradient(135deg, #C62D24, #9E1B16); backface-visibility: hidden; -webkit-backface-visibility: hidden; box-shadow: inset 0 0 0 {{ ek_u(12) }} rgba(0,0,0,0.06), inset 0 0 0 {{ ek_u(14) }} #E9B949, inset 0 0 0 {{ ek_u(18) }} rgba(0,0,0,0.06), inset 0 0 0 {{ ek_u(19) }} #E9B949; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: {{ ek_u(14) }}">
                                        <span style="{{ $kalam }}; font-weight: 700; font-size: {{ ek_u(30) }}; color: #F6D27A">{{ $d['cover_line'] }}</span>
                                        <span style="width: {{ ek_u(90) }}; height: {{ ek_u(90) }}; border-radius: 999px; background: #FFF8E6; display: flex; align-items: center; justify-content: center; box-shadow: 0 0 0 {{ ek_u(4) }} #E9B949">@if ($mark)<img src="{{ $mark }}" alt="" style="width: {{ ek_u(56) }}; height: auto; display: block">@endif</span>
                                    </div>
                                    <div style="position: absolute; inset: 0; border-radius: {{ ek_u(10) }} {{ ek_u(4) }} {{ ek_u(4) }} {{ ek_u(10) }}; background: #FFF8E6; transform: rotateY(180deg); backface-visibility: hidden; -webkit-backface-visibility: hidden; box-shadow: inset {{ ek_u(-14) }} 0 {{ ek_u(18) }} {{ ek_u(-14) }} rgba(0,0,0,0.35); display: flex; flex-direction: column; align-items: center">
                                        <span style="margin-top: {{ ek_u(34) }}; {{ $kalam }}; font-weight: 700; font-size: {{ ek_u(25) }}; color: #C0392B">{{ $d['inside_line'] }}</span>
                                        <span style="margin-top: {{ ek_u(10) }}; width: {{ ek_u(210) }}; display: flex; justify-content: space-between; {{ $kalam }}; font-weight: 700; font-size: {{ ek_u(22) }}; color: #C0392B"><span>{{ $d['inside_left'] }}</span><span data-m="ab-twinkle" style="display: inline-block; color: #E9A02B">✦</span><span>{{ $d['inside_right'] }}</span></span>
                                        <span style="margin-top: {{ ek_u(26) }}; width: {{ ek_u(118) }}; height: {{ ek_u(118) }}; border-radius: 999px; background: #FFFFFF; box-shadow: 0 0 0 {{ ek_u(3) }} rgba(192,57,43,0.35), 0 {{ ek_u(12) }} {{ ek_u(24) }} {{ ek_u(-14) }} rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center">@if ($mark)<img src="{{ $mark }}" alt="" style="width: {{ ek_u(74) }}; height: auto; display: block">@endif</span>
                                        <span style="margin-top: {{ ek_u(22) }}; {{ $kalam }}; font-weight: 700; font-size: {{ ek_u(30) }}; color: #1F2A44">{{ $d['inside_name'] }}</span>
                                        <span style="margin-top: {{ ek_u(2) }}; font-size: {{ ek_u(15) }}; color: var(--tk-muted)">{{ $d['inside_place'] }}</span>
                                    </div>
                                </div>
                                <div style="position: absolute; left: {{ ek_u(289) }}; top: {{ ek_u(40) }}; width: {{ ek_u(12) }}; height: {{ ek_u(420) }}; border-radius: {{ ek_u(6) }}; background: linear-gradient(90deg, #7E140F, #B3261E, #7E140F)"></div>
                            </div>
                        </div>
                    </div>

                    <div aria-hidden="true" data-m="ab-hint" style="position: absolute; left: {{ ek_u($v([96, 24])) }}; bottom: {{ ek_u($v([30, 20])) }}; font-family: 'Poppins', sans-serif; font-weight: 500; font-size: max({{ ek_u(13) }}, 0.8125rem); letter-spacing: 0.14em; color: var(--tk-muted)">{{ $t['hint'] }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>
