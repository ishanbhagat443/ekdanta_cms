{{--
    Legal Hero picture, Terms (design px, 560 × 600): the agreement, its
    lines written in, two signatures, the AGREED stamp, the chips.
    Decoration: the hero hides it from screen readers. $chips, $chipBg, $chipFg.
--}}
@php
    $signs = [
        ['M4 30 C 14 6, 24 6, 22 30 C 20 42, 34 18, 44 26 C 52 32, 58 14, 66 22 C 74 30, 82 24, 92 18 C 104 12, 116 30, 136 16', 'var(--tk-green-deep)', 'Ekdanta'],
        ['M6 26 C 16 10, 30 34, 40 20 C 48 10, 56 34, 66 24 C 76 14, 90 30, 100 22 C 110 14, 122 26, 134 20', 'var(--tk-ink)', 'You'],
    ];
@endphp
<div data-m="lg-card" style="position: absolute; left: {{ ek_u(80) }}; top: {{ ek_u(50) }}; width: {{ ek_u(380) }}; transform: rotate(-4deg); transform-origin: 50% 70%">
    <div data-m="lg-pop" style="position: relative; box-sizing: border-box; height: {{ ek_u(500) }}; padding: {{ ek_u(30) }} {{ ek_u(32) }}; border-radius: {{ ek_u(18) }}; background: #FFFFFF; color: var(--tk-ink); box-shadow: 0 {{ ek_u(40) }} {{ ek_u(70) }} {{ ek_u(-30) }} rgba(13,20,16,0.45); display: flex; flex-direction: column; gap: {{ ek_u(14) }}">
        <span style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: {{ ek_u(13) }}; letter-spacing: 0.16em; color: var(--tk-muted)">AGREEMENT</span>
        <span style="font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u(26) }}; letter-spacing: -0.03em">Ekdanta × You</span>
        <span style="display: flex; flex-direction: column; gap: {{ ek_u(12) }}; margin-top: {{ ek_u(6) }}">
            @foreach ([300, 260, 288, 210, 276, 238] as $width)
                <span data-m="lg-line" style="display: block; height: {{ ek_u(9) }}; width: {{ ek_u($width) }}; border-radius: {{ ek_u(5) }}; background: var(--tk-line-2); transform-origin: left center"></span>
            @endforeach
        </span>
        <div style="margin-top: auto; display: flex; gap: {{ ek_u(24) }}">
            @foreach ($signs as [$path, $stroke, $who])
                <div style="flex: 1; display: flex; flex-direction: column; gap: {{ ek_u(6) }}">
                    <svg viewBox="0 0 140 44" style="width: {{ ek_u(140) }}; height: {{ ek_u(44) }}"><path data-m="lg-sign" pathLength="1" stroke-dasharray="1" style="stroke-dashoffset: 0" d="{{ $path }}" fill="none" stroke="{{ $stroke }}" stroke-width="2.6" stroke-linecap="round"></path></svg>
                    <span style="height: {{ ek_u(2) }}; background: var(--tk-ink)"></span>
                    <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: {{ ek_u(13) }}; color: var(--tk-muted)">{{ $who }}</span>
                </div>
            @endforeach
        </div>
        <div data-m="lg-stamp-turn" style="position: absolute; right: {{ ek_u(-34) }}; bottom: {{ ek_u(96) }}">
            <div data-m="lg-stamp" style="width: {{ ek_u(136) }}; height: {{ ek_u(136) }}; box-sizing: border-box; border-radius: 999px; border: {{ ek_u(5) }} solid var(--tk-green-text); box-shadow: inset 0 0 0 {{ ek_u(4) }} #FFFFFF, inset 0 0 0 {{ ek_u(6) }} var(--tk-green-text); background: rgba(230,244,234,0.92); display: flex; flex-direction: column; align-items: center; justify-content: center; gap: {{ ek_u(2) }}; color: var(--tk-green-text); transform: rotate(-14deg)">
                <span style="font-weight: 700; font-size: {{ ek_u(15) }}">शुभ आरंभ</span>
                <span style="font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u(20) }}; letter-spacing: 0.08em">AGREED</span>
            </div>
        </div>
    </div>
</div>
@include('frontend.sections.ekdanta.partials.legal-chips', ['chips' => $chips, 'at' => [[0, 24], [382, 236], [20, 528]], 'shadow' => 'rgba(13,20,16,0.6)', 'part' => 'lg-tchip'])
