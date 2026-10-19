{{--
    Ekdanta · Home Hero (Torch). The headline sits in the torch beam: a
    faded copy behind (decorative) and the real text inside the beam, which
    the torch-beam scene opens over 10 s or on scroll (scene settings in the
    Motion tab). Without JS or with reduced motion the beam is fully open.
--}}
@php
    $d = $section->viewData('desktop');
    $m = $section->viewData('mobile');
@endphp
<div class="ek ek-home-hero" id="{{ $d['anchor'] ?: 'j-top' }}">
    @foreach ($section->comps($d, $m) as $comp => $t)
        @php
            $mobile = $comp !== 'desktop';
            $chips = array_filter(array_map('trim', explode("\n", (string) ($t['chips'] ?? ''))));
            $beam = $mobile
                ? 'polygon(calc(var(--mx) + '.ek_u(7).') calc(var(--my) - '.ek_u(12).'), calc((var(--mx) - var(--my) * 1.6 + '.ek_u(250).') * var(--bk) + var(--spb) * var(--u)) '.ek_u(-10).', calc((var(--mx) - var(--my) * 1.6 - '.ek_u(300).') * var(--bk) + var(--spa) * var(--u)) '.ek_u(-10).', calc(var(--mx) - '.ek_u(7).') calc(var(--my) + '.ek_u(12).'))'
                : 'polygon(var(--ax) calc(var(--ay) - '.ek_u(16).'), 0 calc((var(--ay) - '.ek_u(110).') * var(--bk) + var(--spa) * var(--u)), 0 calc((var(--ay) + '.ek_u(60).') * var(--bk) + var(--spb) * var(--u)), var(--ax) calc(var(--ay) + '.ek_u(16).'))';
            $beamBase = $mobile ? '--mx: '.ek_u(150).'; --my: '.ek_u(590) : '--ax: '.ek_u(892).'; --ay: '.ek_u(490);
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: var(--tk-green-deep)">
                <div class="ek-art">
                    @include('frontend.sections.ekdanta.partials.home-hero-text', ['t' => $t, 'chips' => $chips, 'ghost' => true, 'mobile' => $mobile])

                    <div aria-hidden="true" data-m="beam" style="position: absolute; inset: 0; background: var(--tk-green); clip-path: {{ $beam }}; {{ $beamBase }}"></div>
                    <div data-m="beam" style="position: absolute; inset: 0; clip-path: {{ $beam }}; {{ $beamBase }}">
                        @include('frontend.sections.ekdanta.partials.home-hero-text', ['t' => $t, 'chips' => $chips, 'ghost' => false, 'mobile' => $mobile, 'after' => $mobile])
                    </div>

                    @unless ($mobile)
                        @include('frontend.sections.ekdanta.partials.home-hero-cta', ['t' => $t, 'mobile' => false])
                    @endunless

                    <div aria-hidden="true" style="position: absolute; {{ $mobile ? 'left: 0; top: 0; transform-origin: 0 0; transform: translate('.ek_u(161.8).', '.ek_u(494.5).') rotate(22deg) scale(0.62)' : 'left: '.ek_u(852).'; top: '.ek_u(340) }}; width: {{ ek_u(588) }}; height: {{ ek_u(560) }}">
                        <img src="{{ media_ref($t['head_image'] ?? null)?->web_url }}" alt="" fetchpriority="high" style="position: absolute; left: 0; top: 0; width: {{ ek_u(588) }}; height: {{ ek_u(560) }}; display: block">
                        <div data-m="rig" style="position: absolute; left: 0; top: 0; width: {{ ek_u(588) }}; height: {{ ek_u(560) }}; transform-origin: {{ ek_u(326) }} {{ ek_u(284) }}">
                            <img src="{{ media_ref($t['trunk_image'] ?? null)?->web_url }}" alt="" style="position: absolute; left: 0; top: 0; width: {{ ek_u(588) }}; height: {{ ek_u(560) }}; display: block">
                            <span style="position: absolute; left: {{ ek_u(40) }}; top: {{ ek_u(150) }}; width: {{ ek_u(26) }}; height: {{ ek_u(26) }}; margin: {{ ek_u(-13) }} 0 0 {{ ek_u(-13) }}; border-radius: 50%; background: #FFF6C9; box-shadow: 0 0 {{ ek_u(40) }} {{ ek_u(18) }} rgba(255,246,201,0.55); opacity: var(--glow)"></span>
                        </div>
                    </div>

                    @unless ($mobile)
                        <div aria-hidden="true" class="ek-hh-hint" style="position: absolute; left: 0; right: 0; bottom: {{ ek_u(30) }}; text-align: center; font-family: 'Poppins', sans-serif; font-weight: 500; font-size: max({{ ek_u(13) }}, 0.8125rem); letter-spacing: 0.14em; color: var(--tk-ink)">{{ $t['hint'] }}</div>
                    @endunless
                </div>
            </div>
        </div>
    @endforeach
</div>
