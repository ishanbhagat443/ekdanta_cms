{{--
    Ekdanta · Four Promises. The tabs are the engine's steps; the block's
    turn, the tab bars and the icon pop follow --ek-f in CSS
    (_four-promises.scss), the current tab's colours follow is-active.
    Phones (no design): heading, a smaller block, then the tabs.
--}}
@php
    $d = $section->dataFor('desktop');
    $m = $section->dataFor('mobile');
    $u = fn ($n) => "calc({$n} * var(--u))";
    $promises = array_slice(array_values(array_filter((array) ($d['promises'] ?? []), fn ($p) => ($p['title'] ?? '') !== '')), 0, 4);
    $icons = [
        'building' => '<path d="M3 21h18M5 21V8l7-5 7 5v13M9 21v-6h6v6"></path>',
        'growth' => '<path d="M3 17l6-6 4 4 8-8M15 7h6v6"></path>',
        'sliders' => '<path d="M4 6h10M18 6h2M4 12h4M12 12h8M4 18h12M20 18h0"></path><circle cx="16" cy="6" r="2"></circle><circle cx="10" cy="12" r="2"></circle><circle cx="18" cy="18" r="2"></circle>',
        'pin' => '<path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11z"></path><circle cx="12" cy="10" r="2.5"></circle>',
    ];
    // Face colours: background, text, icon tile.
    $colours = [
        'green' => ['var(--tk-green)', 'var(--tk-ink)', 'rgba(13,20,16,0.1)'],
        'ink' => ['var(--tk-ink)', '#FFFFFF', 'rgba(255,255,255,0.18)'],
        'orange' => ['var(--tk-orange)', 'var(--tk-ink)', 'rgba(13,20,16,0.1)'],
        'deep' => ['var(--tk-green-deep)', '#FFFFFF', 'rgba(255,255,255,0.18)'],
    ];
@endphp
<div class="ek ek-four-promises" id="{{ $d['anchor'] ?: 'a-values' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: #FFFFFF">
                <div class="ek-art">
                    <div data-m="vl-head" style="position: absolute; left: {{ $u($v([96, 24])) }}; top: {{ $u($v([124, 84])) }}; width: {{ $u($v([560, 342])) }}">
                        <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ $u($v([14, 12])) }}, {{ $v(['0.8125rem', '0.75rem']) }}); letter-spacing: 0.12em; color: var(--tk-green-text-sm)">{{ $t['eyebrow'] }}</span>
                        <h2 style="margin: {{ $u($v([8, 4])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ $u($v([64, 36])) }}, 0.8125rem); line-height: 1.02; letter-spacing: -0.045em">{{ $t['heading'] }}</h2>
                        <p style="margin: {{ $u($v([14, 8])) }} 0 0; font-size: max({{ $u($v([19, 15])) }}, 0.8125rem); line-height: 1.5; color: var(--tk-muted)">{{ $t['text'] }}</p>
                    </div>

                    <div data-m="vl-tabs" style="position: absolute; left: {{ $u($v([96, 24])) }}; top: {{ $u($v([400, 532])) }}; width: {{ $u($v([520, 342])) }}; display: flex; flex-direction: column; gap: {{ $u($v([6, 2])) }}">
                        @foreach ($promises as $i => $promise)
                            <button type="button" class="ek-promise-tab {{ $i === 0 ? 'is-active' : '' }}" data-ek-step="{{ $i }}" style="--i: {{ $i }}; position: relative; display: flex; align-items: center; gap: {{ $u($v([16, 12])) }}; height: {{ $u($v([60, 46])) }}; padding: 0 0 0 {{ $u($v([22, 18])) }}; border: 0; background: none; text-align: left; cursor: pointer; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ $u($v([22, 17])) }}, 0.8125rem); letter-spacing: -0.02em">
                                <span aria-hidden="true" class="ek-promise-bar" style="position: absolute; left: 0; top: {{ $u($v([12, 9])) }}; width: {{ $u(4) }}; height: {{ $u($v([36, 28])) }}; border-radius: {{ $u(2) }}; background: var(--tk-green)"></span>
                                <span class="ek-promise-num" style="font-size: max({{ $u($v([14, 12])) }}, 0.75rem); letter-spacing: 0.06em">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>{{ $promise['title'] }}
                            </button>
                        @endforeach
                    </div>

                    <div data-m="vl-prism" style="position: absolute; left: {{ $u($v([760, 27])) }}; top: {{ $u($v([220, 258])) }}; width: {{ $u($v([560, 336])) }}; height: {{ $u($v([360, 216])) }}">
                        <div style="position: absolute; left: 0; top: 0; width: {{ $u(560) }}; height: {{ $u(360) }}; {{ $mob ? 'transform: scale(0.6); transform-origin: 0 0' : '' }}">
                            <div aria-hidden="true" style="position: absolute; left: {{ $u(60) }}; right: {{ $u(60) }}; top: {{ $u(420) }}; height: {{ $u(30) }}; border-radius: 999px; background: radial-gradient(ellipse, rgba(13,20,16,0.22), rgba(13,20,16,0) 70%)"></div>
                            <div data-m="vl-bob" style="position: absolute; left: 0; top: 0; width: {{ $u(560) }}; height: {{ $u(360) }}; perspective: {{ $u(1800) }}">
                                <div class="ek-promise-block" style="position: absolute; inset: 0; transform-style: preserve-3d">
                                    @foreach ($promises as $i => $promise)
                                        @php [$bg, $fg, $tile] = $colours[$promise['colour'] ?? 'green'] ?? $colours['green']; @endphp
                                        <div class="ek-promise-face" style="--i: {{ $i }}; position: absolute; inset: 0; box-sizing: border-box; padding: {{ $u(40) }} {{ $u(44) }}; border-radius: {{ $u(28) }}; background: {{ $bg }}; color: {{ $fg }}; transform: rotateX({{ -90 * $i }}deg) translateZ({{ $u(180) }}); backface-visibility: hidden; -webkit-backface-visibility: hidden; display: flex; flex-direction: column; box-shadow: inset 0 0 0 {{ $u(1) }} rgba(13,20,16,0.06)">
                                            <span style="display: flex; justify-content: space-between; align-items: center">
                                                <span class="ek-promise-icon" style="width: {{ $u(64) }}; height: {{ $u(64) }}; border-radius: {{ $u(18) }}; background: {{ $tile }}; display: flex; align-items: center; justify-content: center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: {{ $u(30) }}; height: {{ $u(30) }}">{!! $icons[$promise['icon'] ?? 'building'] ?? $icons['building'] !!}</svg></span>
                                                <span aria-hidden="true" style="font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ $u(64) }}; line-height: 1; letter-spacing: -0.05em; opacity: 0.25">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                            </span>
                                            <h3 style="margin: auto 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ $u(40) }}; line-height: 1.08; letter-spacing: -0.04em">{{ $promise['title'] }}</h3>
                                            {{-- Phones draw the block at 60 %: 1.354rem here is 13 px on screen. --}}
                                            <p style="margin: {{ $u(12) }} 0 0; font-size: max({{ $u(19) }}, {{ $mob ? '1.354rem' : '0.8125rem' }}); line-height: {{ $v([1.5, 1.4]) }}; opacity: 0.88">{{ $promise['text'] }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <p aria-hidden="true" data-m="vl-hint" style="position: absolute; left: {{ $u($v([760, 24])) }}; width: {{ $u($v([560, 342])) }}; top: {{ $u($v([680, 742])) }}; margin: 0; text-align: center; font-family: 'Poppins', sans-serif; font-weight: 500; font-size: max({{ $u($v([15, 13])) }}, 0.8125rem); color: var(--tk-muted)">{{ $t['hint'] }}</p>
                </div>
            </div>
        </div>
    @endforeach
</div>
