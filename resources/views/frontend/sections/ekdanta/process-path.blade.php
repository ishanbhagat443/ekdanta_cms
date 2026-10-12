{{--
    Ekdanta · Process Path. Scroll tracks draw the path (dash offset), move
    the mark along it (offset-distance on the path) and pop each step in.
    The path is drawn in design px; the mark's frame is scaled by --u-n (the
    unit as a number, set by the motion engine). Drawn complete.
--}}
@php
    $d = $section->dataFor('desktop');
    $m = $section->dataFor('mobile');
    $steps = array_values(array_filter((array) ($d['steps'] ?? []), fn ($s) => ($s['title'] ?? '') !== ''));
    $mark = media_ref($d['mark'] ?? null);
    $paths = [
        'desktop' => 'M96 560 C115.0 570.0, 148.5 631.7, 210 620 C271.5 608.3, 380.0 490.0, 465 490 C550.0 490.0, 635.0 620.0, 720 620 C805.0 620.0, 890.0 490.0, 975 490 C1060.0 490.0, 1168.5 608.3, 1230 620 C1291.5 631.7, 1325.0 570.0, 1344 560',
        'mobile' => 'M94 226 C94.0 254.0, 118.0 254.0, 118 282 C118.0 338.0, 70.0 338.0, 70 394 C70.0 450.0, 118.0 450.0, 118 506 C118.0 562.0, 70.0 562.0, 70 618 C70.0 674.0, 118.0 674.0, 118 730 C118.0 765.0, 94.0 765.0, 94 800',
    ];
    $nodes = ['desktop' => [[210, 620], [465, 490], [720, 620], [975, 490], [1230, 620]], 'mobile' => [[118, 282], [70, 394], [118, 506], [70, 618], [118, 730]]];
@endphp
<div class="ek ek-process-path" id="{{ $d['anchor'] ?: 's-process' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            [$w, $h] = $v([[1440, 900], [390, 844]]);
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: #FFFFFF">
                <div class="ek-art">
                    <div data-m="proc-head" style="position: absolute; left: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([112, 82])) }}; {{ $mob ? 'width: '.ek_u(342) : '' }}">
                        <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([14, 12])) }}, 0.75rem); letter-spacing: 0.12em; color: var(--tk-green-text-sm)">{{ $t['eyebrow'] }}</span>
                        <h2 style="margin: {{ ek_u($v([8, 4])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u($v([54, 31])) }}; line-height: 1.04; letter-spacing: -0.045em">{{ $t['heading'] }}<br><span style="color: var(--tk-green-text)">{{ $t['heading_2'] }}</span></h2>
                    </div>
                    @unless ($mob)
                        <p data-m="proc-text" style="position: absolute; right: {{ ek_u(96) }}; top: {{ ek_u(136) }}; width: {{ ek_u(400) }}; margin: 0; font-size: max({{ ek_u(19) }}, 0.8125rem); line-height: 1.5; color: var(--tk-muted)">{{ $t['text'] }}</p>
                    @endunless

                    <svg viewBox="0 0 {{ $w }} {{ $h }}" aria-hidden="true" style="position: absolute; left: 0; top: 0; width: {{ ek_u($w) }}; height: {{ ek_u($h) }}">
                        <path d="{{ $paths[$comp] }}" fill="none" stroke="#DDE2DA" stroke-width="3" stroke-dasharray="{{ $v(['2 10', '2 9']) }}" stroke-linecap="round"></path>
                        <path data-m="proc-path" d="{{ $paths[$comp] }}" fill="none" stroke="#2EB45D" stroke-width="{{ $v([6, 5]) }}" stroke-linecap="round" pathLength="1" stroke-dasharray="1 1" style="stroke-dashoffset: 0"></path>
                    </svg>

                    <ol style="margin: 0; padding: 0; list-style: none">
                        @foreach ($steps as $i => $step)
                            @php
                                [$nx, $ny] = $nodes[$comp][$i] ?? end($nodes[$comp]);
                                $below = $ny > 550;
                                $side = $mob ? 'card' : ($below ? 'card-below' : 'card-above');
                            @endphp
                            <li>
                                <div aria-hidden="true" style="position: absolute; left: {{ ek_u($nx) }}; top: {{ ek_u($ny) }}; width: 0; height: 0">
                                    <span style="position: absolute; left: {{ ek_u(-$v([15, 12])) }}; top: {{ ek_u(-$v([15, 12])) }}; width: {{ ek_u($v([30, 24])) }}; height: {{ ek_u($v([30, 24])) }}; border-radius: 50%; background: #FFFFFF; box-shadow: inset 0 0 0 {{ ek_u(3) }} var(--tk-line-2)"></span>
                                    <span data-m="proc-node" style="position: absolute; left: {{ ek_u(-$v([20, 16])) }}; top: {{ ek_u(-$v([20, 16])) }}; width: {{ ek_u($v([40, 32])) }}; height: {{ ek_u($v([40, 32])) }}; border-radius: 50%; background: var(--tk-green); color: var(--tk-ink); font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u($v([15, 13])) }}, 0.8125rem); display: flex; align-items: center; justify-content: center; box-shadow: 0 0 0 {{ ek_u($v([6, 5])) }} rgba(46,180,93,0.18)">{{ $i + 1 }}</span>
                                </div>
                                <div data-m="proc-{{ $side }}" style="position: absolute; {{ $mob ? 'left: '.ek_u(152).'; top: '.ek_u($ny - 44).'; width: '.ek_u(222).'; padding: '.ek_u(10).' '.ek_u(12).'; border-radius: '.ek_u(14) : 'left: '.ek_u($nx - 120).'; '.($below ? 'top: '.ek_u($ny + 40) : 'bottom: '.ek_u(900 - $ny + 40)).'; width: '.ek_u(240).'; padding: '.ek_u(18).' '.ek_u(20).'; border-radius: '.ek_u(18) }}; box-sizing: border-box; background: var(--tk-paper)">
                                    <span style="display: block; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u($v([19, 14.5])) }}, 0.8125rem); letter-spacing: -0.02em">{{ $step['title'] }}</span>
                                    <span style="display: block; margin-top: {{ ek_u($v([6, 3])) }}; font-size: max({{ ek_u($v([15, 12.5])) }}, {{ $mob ? '0.78125rem' : '0.8125rem' }}); line-height: {{ $v([1.45, 1.38]) }}; color: var(--tk-muted)">{{ $step['text'] ?? '' }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ol>

                    <div aria-hidden="true" style="position: absolute; left: 0; top: 0; width: {{ $w }}px; height: {{ $h }}px; transform-origin: 0 0; transform: scale(var(--u-n, 1)); pointer-events: none">
                        <div data-m="proc-marker" style="position: absolute; left: 0; top: 0; width: {{ $v([56, 44]) }}px; height: {{ $v([56, 44]) }}px; offset-path: path('{{ $paths[$comp] }}'); offset-distance: 100%; offset-rotate: 0deg">
                            <span data-m="proc-bob" style="display: flex; width: 100%; height: 100%; border-radius: 50%; background: #FFFFFF; box-shadow: 0 {{ $v([12, 10]) }}px {{ $v([26, 22]) }}px -10px rgba(13,20,16,0.45), 0 0 0 {{ $v([4, 3]) }}px var(--tk-green); align-items: center; justify-content: center">@if ($mark)<img src="{{ $mark->url }}" alt="" style="width: {{ $v([34, 27]) }}px; height: auto; display: block">@endif</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
