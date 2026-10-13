{{--
    Ekdanta · Services Hero (Orbit). Three rings turn around the mark (CSS
    loops: each ring turns one way, its pills turn back so they stay upright)
    and pop in on load; scroll tracks push the rings out and fade the copy.
    The orbit is decoration (aria-hidden); the words around it are fields.
--}}
@php
    $d = $section->viewData('desktop');
    $m = $section->viewData('mobile');
    $lines = fn ($text) => array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $text)), fn ($l) => $l !== ''));
    $inner = array_values(array_filter((array) ($d['inner_ring'] ?? []), fn ($r) => ($r['label'] ?? '') !== ''));
    $outer = $lines($d['outer_ring'] ?? '');
    $dots = ['var(--tk-orange)', 'var(--tk-green)', 'var(--tk-mint)'];
    $mark = media_ref($d['mark'] ?? null);
    // Place n items on a circle of radius r, the first at $start degrees.
    $around = fn (int $n, float $r, float $start) => array_map(fn ($i) => [round($r * cos(deg2rad($start + $i * 360 / max(1, $n))), 1), round($r * sin(deg2rad($start + $i * 360 / max(1, $n))), 1)], range(0, max(0, $n - 1)));
@endphp
<div class="ek ek-services-hero" id="{{ $d['anchor'] ?: 's-top' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            $words = $lines($t['roll_words']);
            $rings = [
                // ring, radius, first angle, items
                3 => [$v([340, 176]), 30, count($dots)],
                2 => [$v([270, 146]), $v([8, 22]), count($outer)],
                1 => [$v([150, 90]), $v([45, 85]), count($inner)],
            ];
            $button = 'height: '.ek_u($v([56, 48])).'; box-sizing: border-box; display: inline-flex; align-items: center; border-radius: 999px; font-family: \'Poppins\', sans-serif; font-weight: 600; font-size: max('.ek_u($v([16, 14.5])).', 0.8125rem); text-decoration: none; white-space: nowrap';
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: var(--tk-ink); color: #FFFFFF">
                <div class="ek-art">
                    <div aria-hidden="true" data-m="glow" style="position: absolute; left: {{ ek_u($v([705, -65])) }}; top: {{ ek_u($v([90, 350])) }}; width: {{ ek_u($v([760, 520])) }}; height: {{ ek_u($v([760, 520])) }}; border-radius: 50%; background: radial-gradient(circle, rgba(46,180,93,0.26) 0%, rgba(46,180,93,0.08) 40%, rgba(46,180,93,0) 68%)"></div>

                    <div aria-hidden="true" style="position: absolute; left: {{ ek_u($v([1085, 195])) }}; top: {{ ek_u($v([470, 610])) }}; width: 0; height: 0">
                        @foreach ($rings as $ring => [$radius, $start, $n])
                            <div data-m="ring-{{ $ring }}" style="position: absolute; left: 0; top: 0; width: 0; height: 0">
                                <div style="position: absolute; left: {{ ek_u(-$radius) }}; top: {{ ek_u(-$radius) }}; width: {{ ek_u(2 * $radius) }}; height: {{ ek_u(2 * $radius) }}; border-radius: 50%; border: {{ ek_u(1.5) }} dashed {{ [3 => 'rgba(255,255,255,0.08)', 2 => 'rgba(255,255,255,0.12)', 1 => 'rgba(143,227,174,0.22)'][$ring] }}"></div>
                                <div data-m="turn-{{ $ring }}" style="position: absolute; left: 0; top: 0; width: 0; height: 0">
                                    @foreach ($around($n, $radius, $start) as $i => [$x, $y])
                                        <div style="position: absolute; left: {{ ek_u($x) }}; top: {{ ek_u($y) }}; width: 0; height: 0">
                                            <div data-m="upright-{{ $ring }}" style="position: absolute; left: 0; top: 0; width: 0; height: 0">
                                                <div data-m="pop-{{ $ring }}" style="position: absolute; left: 0; top: 0; transform: translate(-50%, -50%)">
                                                    @if ($ring === 3)
                                                        <span style="display: block; width: {{ ek_u($v([10, 8])) }}; height: {{ ek_u($v([10, 8])) }}; border-radius: 50%; background: {{ $dots[$i] }}"></span>
                                                    @elseif ($ring === 2)
                                                        <span style="display: block; white-space: nowrap; padding: {{ ek_u($v([8, 5])) }} {{ ek_u($v([14, 9])) }}; border-radius: 999px; background: rgba(46,180,93,{{ $v([0.12, 0.14]) }}); box-shadow: inset 0 0 0 {{ ek_u(1) }} rgba(143,227,174,0.35); color: var(--tk-mint-1); font-family: 'Poppins', sans-serif; font-weight: 600; font-size: {{ ek_u($v([14, 10.5])) }}">{{ $outer[$i] }}</span>
                                                    @else
                                                        <span style="display: block; white-space: nowrap; padding: {{ ek_u($v([12, 8])) }} {{ ek_u($v([20, 13])) }}; border-radius: 999px; background: {{ $inner[$i]['colour'] ?? '#2EB45D' }}; color: var(--tk-ink); font-family: 'Poppins', sans-serif; font-weight: 700; font-size: {{ ek_u($v([17, 13])) }}; {{ $mob ? '' : 'letter-spacing: -0.01em;' }} box-shadow: 0 {{ ek_u($v([14, 10])) }} {{ ek_u($v([30, 22])) }} {{ ek_u($v([-14, -12])) }} rgba(0,0,0,0.6)">{{ $inner[$i]['label'] }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                        <div data-m="core" style="position: absolute; left: 0; top: 0; width: 0; height: 0">
                            @for ($i = 0; $i < 2; $i++)
                                <span data-m="core-ring" style="position: absolute; left: {{ ek_u(-$v([70, 46])) }}; top: {{ ek_u(-$v([70, 46])) }}; width: {{ ek_u($v([140, 92])) }}; height: {{ ek_u($v([140, 92])) }}; border-radius: 50%; border: {{ ek_u(2) }} solid rgba(143,227,174,0.6)"></span>
                            @endfor
                            <span data-m="pop-core" style="position: absolute; left: 0; top: 0; width: {{ ek_u($v([140, 92])) }}; height: {{ ek_u($v([140, 92])) }}; transform: translate(-50%, -50%); border-radius: 50%; background: #FFFFFF; box-shadow: 0 {{ ek_u($v([30, 24])) }} {{ ek_u($v([60, 50])) }} {{ ek_u(-20) }} rgba(0,0,0,0.7), 0 0 0 {{ ek_u($v([10, 8])) }} rgba(46,180,93,0.18); display: flex; align-items: center; justify-content: center">@if ($mark)<img src="{{ $mark->web_url }}" alt="" style="width: {{ ek_u($v([84, 56])) }}; height: auto; display: block">@endif</span>
                        </div>
                    </div>

                    <div data-m="hero-text" style="position: absolute; left: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([150, 84])) }}; width: {{ ek_u($v([720, 342])) }}">
                        <p data-m="crumb" style="margin: 0; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([15, 13])) }}, 0.8125rem); color: #9FC7AE"><a href="{{ link_href($d['crumb_home_link'] ?? '/') }}" style="text-decoration: none">{{ $t['crumb_home'] }}</a><span aria-hidden="true">  ›  </span>{{ $t['crumb_current'] }}</p>
                        <h1 style="margin: {{ ek_u($v([14, 10])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u($v([100, 54])) }}; line-height: 1.02; letter-spacing: -0.045em">
                            @foreach ([$t['heading_1'], $t['heading_2']] as $i => $line)
                                <span style="display: block; overflow: hidden; padding-bottom: {{ ek_u($v([6, 4])) }}"><span data-m="line" style="display: block; {{ $i === 1 ? 'color: var(--tk-green);' : '' }}">{{ $line }}</span></span>
                            @endforeach
                        </h1>
                        <p data-m="roll-line" style="margin: {{ ek_u($v([26, 14])) }} 0 0; display: flex; align-items: center; gap: {{ ek_u($v([12, 8])) }}; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([28, 18])) }}, 0.8125rem); letter-spacing: -0.02em; color: #DCE7E0">
                            {{ $t['roll_before'] }}
                            <span class="visually-hidden">{{ implode(', ', $words) }}</span>
                            <span aria-hidden="true" style="display: inline-block; height: {{ ek_u($v([44, 30])) }}; overflow: hidden; padding: 0 {{ ek_u($v([16, 10])) }}; border-radius: {{ ek_u($v([14, 10])) }}; background: rgba(46,180,93,0.16); box-shadow: inset 0 0 0 {{ ek_u(1) }} rgba(143,227,174,0.3); color: var(--tk-mint)">
                                <span data-m="roller" style="display: flex; flex-direction: column">
                                    @foreach ([...$words, $words[0] ?? ''] as $word)
                                        <span style="display: block; height: {{ ek_u($v([44, 30])) }}; line-height: {{ ek_u($v([44, 30])) }}; white-space: nowrap">{{ $word }}</span>
                                    @endforeach
                                </span>
                            </span>
                        </p>
                        <p data-m="lede" style="margin: {{ ek_u($v([22, 12])) }} 0 0; {{ $mob ? '' : 'width: '.ek_u(580).';' }} font-size: max({{ ek_u($v([21, 15.5])) }}, 0.8125rem); line-height: 1.5; color: #BFD2C6">{{ $t['text'] }}</p>
                        <div data-m="actions" style="margin-top: {{ ek_u($v([30, 16])) }}; display: flex; gap: {{ ek_u($v([12, 8])) }}">
                            <a class="ek-press" href="{{ link_href($d['button_link'] ?? '') }}" style="{{ $button }}; padding: 0 {{ ek_u($v([26, 18])) }}; background: var(--tk-green); color: var(--tk-ink)">{{ $t['button_label'] }}</a>
                            <a class="ek-press" href="{{ link_href($d['button_2_link'] ?? '') }}" style="{{ $button }}; padding: 0 {{ ek_u($v([24, 18])) }}; box-shadow: inset 0 0 0 {{ ek_u(1.5) }} rgba(255,255,255,0.6)">{{ $t['button_2_label'] }}</a>
                        </div>
                    </div>
                    @unless ($mob)
                        <div aria-hidden="true" data-m="hint" style="position: absolute; left: {{ ek_u(96) }}; bottom: {{ ek_u(30) }}; font-family: 'Poppins', sans-serif; font-weight: 500; font-size: {{ ek_u(13) }}; letter-spacing: 0.14em; color: #9FC7AE">{{ $t['hint'] }}</div>
                    @endunless
                </div>
            </div>
        </div>
    @endforeach
</div>
