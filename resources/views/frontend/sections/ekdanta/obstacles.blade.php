{{--
    Ekdanta · Obstacles (Flip Cards). Each card turns from the obstacle to its
    fix as the visitor scrolls, the counter counts them, and the mark walks
    the track. Drawn finished (cards turned, mark at the end); the motion
    starts it from the beginning.
--}}
@php
    $d = $section->viewData('desktop');
    $m = $section->viewData('mobile');
    $cards = array_values(array_filter((array) ($d['cards'] ?? []), fn ($c) => ($c['obstacle'] ?? '') !== ''));
@endphp
<div class="ek ek-obstacles" id="{{ $d['anchor'] ?: 'j-obstacles' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            // [desktop, mobile] design measurements
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            $cardW = $v([360, 165]);
            $cardH = $v([220, 162]);
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: var(--tk-paper)">
                <div class="ek-art">
                    @if ($mob)
                        <h2 style="position: absolute; left: {{ ek_u(24) }}; top: {{ ek_u(88) }}; width: {{ ek_u(342) }}; margin: 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u(31) }}, 0.8125rem); line-height: 1.06; letter-spacing: -0.045em">{!! nl2br(e($t['heading'])) !!}</h2>
                        <div style="position: absolute; left: {{ ek_u(24) }}; top: {{ ek_u(214) }}; display: flex; align-items: baseline; gap: {{ ek_u(10) }}">
                            <span style="font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u(34) }}; line-height: 1; letter-spacing: -0.04em"><span data-m="removed">{{ count($cards) }}</span><span style="color: var(--tk-suffix)">/{{ count($cards) }}</span></span>
                            <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u(11) }}, 0.75rem); letter-spacing: 0.1em; color: var(--tk-muted)">{{ $t['counter_label'] }}</span>
                        </div>
                    @else
                        <div style="position: absolute; left: {{ ek_u(156) }}; right: {{ ek_u(156) }}; top: {{ ek_u(116) }}; display: flex; justify-content: space-between; align-items: flex-end">
                            <h2 style="margin: 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u(54) }}; line-height: 1.02; letter-spacing: -0.045em">{!! nl2br(e($t['heading'])) !!}</h2>
                            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: {{ ek_u(2) }}">
                                <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u(13) }}, 0.8125rem); letter-spacing: 0.1em; color: var(--tk-muted)">{{ $t['counter_label'] }}</span>
                                <span style="font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u(64) }}; line-height: 1; letter-spacing: -0.04em"><span data-m="removed">{{ count($cards) }}</span><span style="color: var(--tk-suffix)">/{{ count($cards) }}</span></span>
                            </div>
                        </div>
                    @endif

                    <ul style="position: absolute; left: {{ ek_u($v([156, 24])) }}; top: {{ ek_u($v([300, 268])) }}; width: {{ ek_u($v([1128, 342])) }}; margin: 0; padding: 0; list-style: none; display: grid; grid-template-columns: repeat({{ $v([3, 2]) }}, {{ ek_u($cardW) }}); gap: {{ $mob ? ek_u(10).' '.ek_u(12) : ek_u(24) }}">
                        @foreach ($cards as $card)
                            <li style="height: {{ ek_u($cardH) }}; perspective: {{ ek_u($v([1200, 900])) }}">
                                <div data-m="card" style="position: relative; width: {{ ek_u($cardW) }}; height: {{ ek_u($cardH) }}; transform-style: preserve-3d; transform: rotateY(180deg)">
                                    <div style="position: absolute; inset: 0; box-sizing: border-box; padding: {{ ek_u($v([26, 16])) }}; border-radius: {{ ek_u($v([22, 18])) }}; background: var(--tk-line-2); backface-visibility: hidden; -webkit-backface-visibility: hidden; display: flex; flex-direction: column; justify-content: space-between">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="#56605A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: {{ ek_u($v([40, 30])) }}; height: {{ ek_u($v([40, 30])) }}"><rect x="3" y="8" width="18" height="8" rx="1"></rect><path d="M7 8l4 8M13 8l4 8M5 16v4M19 16v4"></path></svg>
                                        <span style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u($v([26, 17])) }}, 0.8125rem); line-height: 1.2; letter-spacing: -0.03em; color: #3B4640">{{ $card['obstacle'] }}</span>
                                    </div>
                                    <div style="position: absolute; inset: 0; box-sizing: border-box; padding: {{ $mob ? ek_u(14).' '.ek_u(14).' '.ek_u(12) : ek_u(26) }}; border-radius: {{ ek_u($v([22, 18])) }}; background: var(--tk-green); transform: rotateY(180deg); backface-visibility: hidden; -webkit-backface-visibility: hidden; display: flex; flex-direction: column; justify-content: space-between">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="#0D1410" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: {{ ek_u($v([40, 26])) }}; height: {{ ek_u($v([40, 26])) }}"><circle cx="12" cy="12" r="9"></circle><path d="M8 12.5l3 3 5-6"></path></svg>
                                        <span style="display: flex; flex-direction: column; gap: {{ ek_u($v([6, 4])) }}">
                                            <span style="font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u($v([24, 15])) }}, 0.8125rem); {{ $mob ? 'line-height: 1.15; letter-spacing: -0.02em' : 'letter-spacing: -0.03em' }}">{{ $card['fix'] }}</span>
                                            <span style="font-size: max({{ ek_u($v([16, 12.5])) }}, {{ $mob ? '0.78125rem' : '0.8125rem' }}); line-height: {{ $v([1.45, 1.35]) }}; color: var(--tk-dark-4)">{{ $card['fix_text'] }}</span>
                                        </span>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    @php $side = $v([156, 24]); $trackY = $v([820, 800]); $walker = $v([60, 44]); @endphp
                    <div aria-hidden="true" style="position: absolute; left: {{ ek_u($side) }}; right: {{ ek_u($side) }}; top: {{ ek_u($trackY) }}; border-top: {{ ek_u(2) }} dashed #C5CCC7"></div>
                    <div aria-hidden="true" data-m="trackfill" style="position: absolute; left: {{ ek_u($side) }}; top: {{ ek_u($trackY) }}; height: {{ ek_u(2) }}; width: {{ ek_u($v([1128, 342])) }}; background: var(--tk-green); transform-origin: 0 50%"></div>
                    <div aria-hidden="true" data-m="walker" style="position: absolute; left: {{ ek_u($v([1224, 322])) }}; top: {{ ek_u($v([790, 778])) }}; width: {{ ek_u($walker) }}; height: {{ ek_u($walker) }}; border-radius: 50%; background: #FFFFFF; box-shadow: 0 {{ ek_u(8) }} {{ ek_u(20) }} {{ ek_u(-8) }} rgba(13,20,16,0.4); display: flex; align-items: center; justify-content: center"><img loading="lazy" decoding="async" src="{{ media_ref($t['walker_image'] ?? null)?->web_url }}" alt="" style="width: {{ ek_u($v([38, 28])) }}; height: auto"></div>
                </div>
            </div>
        </div>
    @endforeach
</div>
