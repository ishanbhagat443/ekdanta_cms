{{--
    Legal Hero picture, Privacy (design px, 560 × 600): the brief card,
    the shield drawn round it and its lock shutting, the chips. Decoration:
    the hero hides it from screen readers. $chips (up to three), $chipBg, $chipFg.
--}}
@php
    $shield = 'M130 14 L236 54 V140 C236 210 190 258 130 286 C70 258 24 210 24 140 V54 Z';
    $fields = [['Name', 6], ['Phone', 10], ['Company (optional)', 7]];
    $label = 'font-family: \'Poppins\', sans-serif; font-weight: 600; font-size: '.ek_u(13).'; color: #3B4640';
@endphp
@foreach ([1, 2] as $ring) {{-- two rings; the motion staggers them --}}
    <span data-m="lg-ring" style="position: absolute; left: {{ ek_u(150) }}; top: {{ ek_u(160) }}; width: {{ ek_u(300) }}; height: {{ ek_u(300) }}; box-sizing: border-box; border-radius: 999px; border: {{ ek_u(2) }} solid rgba(143,227,174,0.45)"></span>
@endforeach
<div data-m="lg-card" style="position: absolute; left: {{ ek_u(40) }}; top: {{ ek_u(110) }}; width: {{ ek_u(340) }}; transform: rotate(-4deg); transform-origin: 50% 60%">
    <div data-m="lg-pop" style="box-sizing: border-box; padding: {{ ek_u(24) }}; border-radius: {{ ek_u(24) }}; background: #FFFFFF; color: var(--tk-ink); box-shadow: 0 {{ ek_u(40) }} {{ ek_u(70) }} {{ ek_u(-30) }} rgba(0,0,0,0.6); display: flex; flex-direction: column; gap: {{ ek_u(14) }}">
        <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: {{ ek_u(12) }}; letter-spacing: 0.12em; color: var(--tk-green-text)">YOUR BRIEF</span>
        @foreach ($fields as [$name, $dots])
            <span style="display: flex; flex-direction: column; gap: {{ ek_u(6) }}"><span style="{{ $label }}">{{ $name }}</span><span style="position: relative; display: block; height: {{ ek_u(40) }}; border-radius: 999px; background: var(--tk-paper); box-shadow: inset 0 0 0 {{ ek_u(1.5) }} var(--tk-line-2)"><span data-m="lg-dots" style="position: absolute; left: {{ ek_u(16) }}; top: {{ ek_u(12) }}; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: {{ ek_u(13) }}; line-height: 1; letter-spacing: {{ ek_u(3) }}; color: var(--tk-green-deep)">{{ str_repeat('●', $dots) }}</span></span></span>
        @endforeach
        <span style="align-self: flex-start; height: {{ ek_u(44) }}; box-sizing: border-box; padding: 0 {{ ek_u(18) }}; display: inline-flex; align-items: center; border-radius: 999px; background: var(--tk-green-deep); color: #FFFFFF; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: {{ ek_u(14) }}">Send on WhatsApp</span>
    </div>
</div>
<div data-m="lg-shield" style="position: absolute; left: {{ ek_u(236) }}; top: {{ ek_u(150) }}; width: {{ ek_u(260) }}; height: {{ ek_u(300) }}; transform-origin: 50% 50%">
    <svg viewBox="0 0 260 300" style="position: absolute; left: 0; top: 0; width: {{ ek_u(260) }}; height: {{ ek_u(300) }}; overflow: visible">
        <path data-m="lg-fill" d="{{ $shield }}" fill="rgba(46,180,93,0.22)"></path>
        <path data-m="lg-draw" pathLength="1" stroke-dasharray="1" style="stroke-dashoffset: 0" d="{{ $shield }}" fill="none" stroke="#2EB45D" stroke-width="7" stroke-linejoin="round"></path>
    </svg>
    <div style="position: absolute; left: {{ ek_u(90) }}; top: {{ ek_u(110) }}; width: {{ ek_u(80) }}; height: {{ ek_u(96) }}">
        <svg data-m="lg-shackle" viewBox="0 0 80 50" style="position: absolute; left: 0; top: 0; width: {{ ek_u(80) }}; height: {{ ek_u(50) }}"><path d="M20 50 V28 a20 20 0 0 1 40 0 V50" fill="none" stroke="#FFFFFF" stroke-width="9" stroke-linecap="round"></path></svg>
        <span style="position: absolute; left: {{ ek_u(4) }}; top: {{ ek_u(42) }}; width: {{ ek_u(72) }}; height: {{ ek_u(54) }}; border-radius: {{ ek_u(14) }}; background: #FFFFFF; box-shadow: 0 {{ ek_u(14) }} {{ ek_u(30) }} {{ ek_u(-14) }} rgba(0,0,0,0.6)"></span>
        <span style="position: absolute; left: {{ ek_u(34) }}; top: {{ ek_u(58) }}; width: {{ ek_u(12) }}; height: {{ ek_u(20) }}; border-radius: {{ ek_u(6) }}; background: var(--tk-green-deep)"></span>
    </div>
</div>
@include('frontend.sections.ekdanta.partials.legal-chips', ['chips' => $chips, 'at' => [[0, 40], [372, 196], [96, 520]], 'shadow' => 'rgba(0,0,0,0.6)', 'part' => 'lg-chip'])
