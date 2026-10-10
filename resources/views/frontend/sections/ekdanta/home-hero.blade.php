{{--
    Ekdanta · Home Hero (Torch). The headline sits in the torch beam: a
    faded copy behind (decorative) and the real text inside the beam, which
    the torch-beam scene opens over 10 s or on scroll (scene settings in the
    Motion tab). Without JS or with reduced motion the beam is fully open.
--}}
@php
    $d = $section->dataFor('desktop');
    $m = $section->dataFor('mobile');
    $u = fn ($n) => "calc({$n} * var(--u))";
@endphp
<div class="ek ek-home-hero" id="{{ $d['anchor'] ?: 'j-top' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mobile = $comp === 'mobile';
            $chips = array_filter(array_map('trim', explode("\n", (string) ($t['chips'] ?? ''))));
            $beam = $mobile
                ? 'polygon(calc(var(--mx) + '.$u(7).') calc(var(--my) - '.$u(12).'), calc((var(--mx) - var(--my) * 1.6 + '.$u(250).') * var(--bk) + var(--spb) * var(--u)) '.$u(-10).', calc((var(--mx) - var(--my) * 1.6 - '.$u(300).') * var(--bk) + var(--spa) * var(--u)) '.$u(-10).', calc(var(--mx) - '.$u(7).') calc(var(--my) + '.$u(12).'))'
                : 'polygon(var(--ax) calc(var(--ay) - '.$u(16).'), 0 calc((var(--ay) - '.$u(110).') * var(--bk) + var(--spa) * var(--u)), 0 calc((var(--ay) + '.$u(60).') * var(--bk) + var(--spb) * var(--u)), var(--ax) calc(var(--ay) + '.$u(16).'))';
            $beamBase = $mobile ? '--mx: '.$u(150).'; --my: '.$u(590) : '--ax: '.$u(892).'; --ay: '.$u(490);
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: var(--tk-green-deep)">
                <div class="ek-art">
                    @include('frontend.sections.ekdanta.partials.home-hero-text', ['t' => $t, 'chips' => $chips, 'ghost' => true, 'mobile' => $mobile])

                    <div aria-hidden="true" data-m="beam" style="position: absolute; inset: 0; background: var(--tk-green); clip-path: {{ $beam }}; {{ $beamBase }}"></div>
                    <div data-m="beam" style="position: absolute; inset: 0; clip-path: {{ $beam }}; {{ $beamBase }}">
                        @include('frontend.sections.ekdanta.partials.home-hero-text', ['t' => $t, 'chips' => $chips, 'ghost' => false, 'mobile' => $mobile])
                    </div>

                    <div class="ek-hh-cta" style="position: absolute; left: {{ $u($mobile ? 24 : 96) }}; top: {{ $u($mobile ? 470 : 700) }}; display: flex; gap: {{ $u($mobile ? 8 : 12) }}">
                        <a href="{{ link_href($t['button_link'] ?? '#contact') }}" style="height: {{ $u($mobile ? 52 : 56) }}; box-sizing: border-box; padding: 0 {{ $u($mobile ? 22 : 26) }}; display: inline-flex; align-items: center; background: var(--tk-ink); color: #FFFFFF; border-radius: 999px; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ $u($mobile ? 15 : 16) }}, 0.8125rem); text-decoration: none">{{ $t['button_label'] }}</a>
                        <a href="{{ link_href($t['whatsapp_link'] ?? 'wa:') }}" target="_blank" rel="noopener" style="height: {{ $u($mobile ? 52 : 56) }}; box-sizing: border-box; padding: 0 {{ $u($mobile ? 18 : 24) }}; display: inline-flex; align-items: center; gap: {{ $u(8) }}; box-shadow: inset 0 0 0 {{ $u(1.5) }} var(--tk-ink); color: var(--tk-ink); border-radius: 999px; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ $u($mobile ? 15 : 16) }}, 0.8125rem); text-decoration: none"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: {{ $u($mobile ? 17 : 18) }}; height: {{ $u($mobile ? 17 : 18) }}"><path d="M21 12a8 8 0 0 1-11.6 7.1L4 20.5l1.4-5A8 8 0 1 1 21 12z"></path></svg>{{ $t['whatsapp_label'] }}</a>
                    </div>

                    <div aria-hidden="true" style="position: absolute; {{ $mobile ? 'left: 0; top: 0; transform-origin: 0 0; transform: translate('.$u(161.8).', '.$u(494.5).') rotate(22deg) scale(0.62)' : 'left: '.$u(852).'; top: '.$u(340) }}; width: {{ $u(588) }}; height: {{ $u(560) }}">
                        <img src="{{ media_ref($t['head_image'] ?? null)?->url }}" alt="" style="position: absolute; left: 0; top: 0; width: {{ $u(588) }}; height: {{ $u(560) }}; display: block">
                        <div data-m="rig" style="position: absolute; left: 0; top: 0; width: {{ $u(588) }}; height: {{ $u(560) }}; transform-origin: {{ $u(326) }} {{ $u(284) }}">
                            <img src="{{ media_ref($t['trunk_image'] ?? null)?->url }}" alt="" style="position: absolute; left: 0; top: 0; width: {{ $u(588) }}; height: {{ $u(560) }}; display: block">
                            <span style="position: absolute; left: {{ $u(40) }}; top: {{ $u(150) }}; width: {{ $u(26) }}; height: {{ $u(26) }}; margin: {{ $u(-13) }} 0 0 {{ $u(-13) }}; border-radius: 50%; background: #FFF6C9; box-shadow: 0 0 {{ $u(40) }} {{ $u(18) }} rgba(255,246,201,0.55); opacity: var(--glow)"></span>
                        </div>
                    </div>

                    <div aria-hidden="true" class="ek-hh-hint" style="position: absolute; {{ $mobile ? 'left: '.$u(24).'; top: '.$u(540) : 'left: 0; right: 0; bottom: '.$u(30).'; text-align: center' }}; font-family: 'Poppins', sans-serif; font-weight: 500; font-size: {{ $mobile ? $u(11) : 'max('.$u(13).', 0.8125rem)' }}; letter-spacing: 0.14em; color: {{ $mobile ? 'var(--tk-dark-4)' : 'var(--tk-ink)' }}">{{ $t['hint'] }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>
