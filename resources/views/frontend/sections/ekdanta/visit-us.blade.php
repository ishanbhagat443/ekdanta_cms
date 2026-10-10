{{--
    Ekdanta · Visit Us (Map). The map is an illustration (its shapes stay
    here, its words are fields); scroll tracks draw the river and roads,
    run the route and drop the pin. Drawn complete.
--}}
@php
    $d = $section->dataFor('desktop');
    $m = $section->dataFor('mobile');
    $u = fn ($n) => "calc({$n} * var(--u))";
    $roads = ['M40 660 C 140 520 260 470 420 470 S 620 430 680 380', 'M0 405 L 644 504', 'M520 230 C 480 340 440 420 420 470 S 380 600 370 680', 'M120 250 C 220 330 300 360 420 470', 'M260 260 L 300 680'];
    $pin = '<path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11z M12 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z"></path>';
    $lines = fn ($text) => array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $text)), fn ($l) => $l !== ''));
    $mark = media_ref($d['mark'] ?? null);
    $poppins = "font-family=\"'Poppins', sans-serif\"";
@endphp
<div class="ek ek-visit-us" id="{{ $d['anchor'] ?: 'c-visit' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            $button = 'height: '.$u($v([54, 48])).'; box-sizing: border-box; display: inline-flex; align-items: center; gap: '.$u(8).'; border-radius: 999px; font-family: \'Poppins\', sans-serif; font-weight: 600; font-size: max('.$u($v([16, 14.5])).', 0.8125rem); text-decoration: none; white-space: nowrap';
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: var(--tk-green-deep); color: #FFFFFF">
                <div class="ek-art">
                    <div style="position: absolute; left: {{ $u($v([96, 24])) }}; top: {{ $u($v([130, 82])) }}; width: {{ $u($v([520, 342])) }}; display: flex; flex-direction: column; gap: {{ $u($v([16, 8])) }}">
                        <span data-m="visit-eyebrow" style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ $u($v([14, 12])) }}, 0.75rem); letter-spacing: 0.12em; color: var(--tk-orange)">{{ $t['eyebrow'] }}</span>
                        <h2 data-m="visit-heading" style="margin: 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ $u($v([64, 36])) }}; line-height: 1; letter-spacing: -0.045em">{{ $t['heading'] }}</h2>
                        <address style="margin-top: {{ $u($v([14, 6])) }}; font-style: normal; display: flex; flex-direction: column; gap: {{ $u($v([6, 2])) }}">
                            @foreach ($lines($t['address']) as $k => $line)
                                <span data-m="address-line" style="font-size: max({{ $u($v([22, 15.5])) }}, 0.8125rem); line-height: 1.4; color: {{ $k === 0 ? '#FFFFFF' : '#DCEFE2' }}">{{ $line }}</span>
                            @endforeach
                        </address>
                        <div data-m="visit-details" style="display: flex; flex-direction: column; gap: {{ $u($v([4, 2])) }}; {{ $mob ? '' : 'margin-top: '.$u(6).';' }} font-size: max({{ $u($v([17, 13.5])) }}, 0.8125rem); color: var(--tk-mint-2)">
                            @foreach ($lines($t['details']) as $line)
                                <span>{{ $line }}</span>
                            @endforeach
                        </div>
                        <div data-m="visit-actions" style="margin-top: {{ $u($v([14, 6])) }}; display: flex; gap: {{ $u($v([12, 8])) }}">
                            <a class="ek-press" href="{{ link_href($d['directions_link'] ?? '') }}" @if (\App\Support\Link::external($d['directions_link'] ?? '')) target="_blank" rel="noopener" @endif style="{{ $button }}; padding: 0 {{ $u($v([24, 18])) }}; background: #FFFFFF; color: var(--tk-ink)"><svg viewBox="0 0 24 24" fill="none" stroke="#0D1410" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: {{ $u(18) }}; height: {{ $u(18) }}">{!! $pin !!}</svg>{{ $t['directions_label'] }}</a>
                            <a class="ek-press" href="{{ link_href($d['call_link'] ?? '') }}" style="{{ $button }}; padding: 0 {{ $u($v([22, 18])) }}; box-shadow: inset 0 0 0 {{ $u(1.5) }} rgba(255,255,255,0.7)">{{ $t['call_label'] }}</a>
                        </div>
                    </div>

                    <div data-m="map" style="position: absolute; left: {{ $u($v([700, 24])) }}; top: {{ $u($v([120, 438])) }}; width: {{ $u($v([644, 342])) }}; height: {{ $u($v([660, 350])) }}; border-radius: {{ $u($v([28, 22])) }}; overflow: hidden; background: #E9EFE7; box-shadow: 0 {{ $u($v([40, 30])) }} {{ $u($v([80, 60])) }} {{ $u($v([-40, -30])) }} rgba(0,0,0,0.6)">
                        <svg role="img" aria-label="{{ $d['map_description'] }}" viewBox="0 0 644 660" style="width: 100%; height: 100%; display: block">
                            <rect x="60" y="300" width="150" height="90" rx="22" fill="#D7E6D3"></rect>
                            <rect x="500" y="560" width="120" height="80" rx="22" fill="#D7E6D3"></rect>
                            <text x="40" y="300" {!! $poppins !!} font-weight="800" font-size="64" fill="#D3DDD0">{{ $d['map_city'] }}</text>
                            <path data-m="river" d="M-30 150 C 90 90 190 230 320 190 C 440 150 520 70 680 120" fill="none" stroke="#A9D8E8" stroke-width="54" stroke-linecap="round" pathLength="1" stroke-dasharray="1 1" style="stroke-dashoffset: 0"></path>
                            <text data-m="river-label" x="132" y="122" {!! $poppins !!} font-weight="600" font-size="15" fill="#4D8FA6" transform="rotate(-8 132 122)">{{ $d['map_river'] }}</text>
                            @foreach ($roads as $road)
                                {{-- One offset for both strokes of a road: stroke-dashoffset is inherited. --}}
                                <g data-m="road" style="stroke-dashoffset: 0">
                                    <path d="{{ $road }}" fill="none" stroke="#CDD6CB" stroke-width="18" stroke-linecap="round" pathLength="1" stroke-dasharray="1 1"></path>
                                    <path d="{{ $road }}" fill="none" stroke="#FFFFFF" stroke-width="12" stroke-linecap="round" pathLength="1" stroke-dasharray="1 1"></path>
                                </g>
                            @endforeach
                            <g data-m="junction">
                                <circle cx="420" cy="470" r="20" fill="#FFFFFF" stroke="#CDD6CB" stroke-width="3"></circle>
                                <text x="300" y="514" {!! $poppins !!} font-weight="600" font-size="14" fill="#56605A">{{ $d['map_junction'] }}</text>
                            </g>
                            <g data-m="route-mark">
                                <path data-m="route" d="M70 618 C 150 520 260 470 420 470 L 452 444" fill="none" stroke="#F2A230" stroke-width="6" stroke-linecap="round" pathLength="1" stroke-dasharray="1 1" style="stroke-dashoffset: 0"></path>
                                <circle cx="70" cy="618" r="9" fill="#F2A230" stroke="#FFFFFF" stroke-width="3"></circle>
                                <text x="86" y="640" {!! $poppins !!} font-weight="700" font-size="13" fill="#B97612">{{ $d['map_you'] }}</text>
                            </g>
                            <g data-m="pulse"><circle data-m="pulse-ring" cx="452" cy="444" r="16" fill="#2EB45D" style="transform-box: fill-box; transform-origin: center"></circle></g>
                            <g transform="translate(452 444)">
                                <g data-m="pin">
                                    <ellipse cx="0" cy="4" rx="16" ry="5" fill="rgba(0,0,0,0.18)"></ellipse>
                                    <path d="M0 0 C -22 -26 -30 -40 -30 -56 A 30 30 0 1 1 30 -56 C 30 -40 22 -26 0 0 Z" fill="#0A5A30"></path>
                                    <circle cx="0" cy="-56" r="20" fill="#FFFFFF"></circle>
                                    @if ($mark)<image href="{{ $mark->url }}" x="-13" y="-70" width="26" height="27"></image>@endif
                                </g>
                            </g>
                            <g data-m="pin-label">
                                <rect x="478" y="360" width="150" height="54" rx="14" fill="#0D1410"></rect>
                                <text x="492" y="383" {!! $poppins !!} font-weight="700" font-size="15" fill="#FFFFFF">{{ $d['map_pin'] }}</text>
                                <text x="492" y="402" font-family="'Hind', sans-serif" font-size="13" fill="#CFE8D7">{{ $d['map_place'] }}</text>
                            </g>
                            <text x="624" y="646" text-anchor="end" font-family="'Hind', sans-serif" font-size="12" fill="#6A736D">{{ $d['map_note'] }}</text>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
