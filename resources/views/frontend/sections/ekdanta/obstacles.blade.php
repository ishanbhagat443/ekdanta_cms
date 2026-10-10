{{--
    Ekdanta · Obstacles (Flip Cards). Each card turns from the obstacle to its
    fix as the visitor scrolls, the counter counts them, and the mark walks
    the track. Drawn finished (cards turned, mark at the end); the motion
    starts it from the beginning.
--}}
@php
    $d = $section->dataFor('desktop');
    $m = $section->dataFor('mobile');
    $u = fn ($n) => "calc({$n} * var(--u))";
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
                        <h2 style="position: absolute; left: {{ $u(24) }}; top: {{ $u(88) }}; width: {{ $u(342) }}; margin: 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ $u(31) }}, 0.8125rem); line-height: 1.06; letter-spacing: -0.045em">{!! nl2br(e($t['heading'])) !!}</h2>
                        <div style="position: absolute; left: {{ $u(24) }}; top: {{ $u(214) }}; display: flex; align-items: baseline; gap: {{ $u(10) }}">
                            <span style="font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ $u(34) }}; line-height: 1; letter-spacing: -0.04em"><span data-m="removed">{{ count($cards) }}</span><span style="color: var(--tk-suffix)">/{{ count($cards) }}</span></span>
                            <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ $u(11) }}, 0.75rem); letter-spacing: 0.1em; color: var(--tk-muted)">{{ $t['counter_label'] }}</span>
                        </div>
                    @else
                        <div style="position: absolute; left: {{ $u(156) }}; right: {{ $u(156) }}; top: {{ $u(116) }}; display: flex; justify-content: space-between; align-items: flex-end">
                            <h2 style="margin: 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ $u(54) }}; line-height: 1.02; letter-spacing: -0.045em">{!! nl2br(e($t['heading'])) !!}</h2>
                            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: {{ $u(2) }}">
                                <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ $u(13) }}, 0.8125rem); letter-spacing: 0.1em; color: var(--tk-muted)">{{ $t['counter_label'] }}</span>
                                <span style="font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ $u(64) }}; line-height: 1; letter-spacing: -0.04em"><span data-m="removed">{{ count($cards) }}</span><span style="color: var(--tk-suffix)">/{{ count($cards) }}</span></span>
                            </div>
                        </div>
                    @endif

                    <ul style="position: absolute; left: {{ $u($v([156, 24])) }}; top: {{ $u($v([300, 268])) }}; width: {{ $u($v([1128, 342])) }}; margin: 0; padding: 0; list-style: none; display: grid; grid-template-columns: repeat({{ $v([3, 2]) }}, {{ $u($cardW) }}); gap: {{ $mob ? $u(10).' '.$u(12) : $u(24) }}">
                        @foreach ($cards as $card)
                            <li style="height: {{ $u($cardH) }}; perspective: {{ $u($v([1200, 900])) }}">
                                <div data-m="card" style="position: relative; width: {{ $u($cardW) }}; height: {{ $u($cardH) }}; transform-style: preserve-3d; transform: rotateY(180deg)">
                                    <div style="position: absolute; inset: 0; box-sizing: border-box; padding: {{ $u($v([26, 16])) }}; border-radius: {{ $u($v([22, 18])) }}; background: var(--tk-line-2); backface-visibility: hidden; -webkit-backface-visibility: hidden; display: flex; flex-direction: column; justify-content: space-between">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="#56605A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: {{ $u($v([40, 30])) }}; height: {{ $u($v([40, 30])) }}"><rect x="3" y="8" width="18" height="8" rx="1"></rect><path d="M7 8l4 8M13 8l4 8M5 16v4M19 16v4"></path></svg>
                                        <span style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ $u($v([26, 17])) }}, 0.8125rem); line-height: 1.2; letter-spacing: -0.03em; color: #3B4640">{{ $card['obstacle'] }}</span>
                                    </div>
                                    <div style="position: absolute; inset: 0; box-sizing: border-box; padding: {{ $mob ? $u(14).' '.$u(14).' '.$u(12) : $u(26) }}; border-radius: {{ $u($v([22, 18])) }}; background: var(--tk-green); transform: rotateY(180deg); backface-visibility: hidden; -webkit-backface-visibility: hidden; display: flex; flex-direction: column; justify-content: space-between">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="#0D1410" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: {{ $u($v([40, 26])) }}; height: {{ $u($v([40, 26])) }}"><circle cx="12" cy="12" r="9"></circle><path d="M8 12.5l3 3 5-6"></path></svg>
                                        <span style="display: flex; flex-direction: column; gap: {{ $u($v([6, 4])) }}">
                                            <span style="font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ $u($v([24, 15])) }}, 0.8125rem); {{ $mob ? 'line-height: 1.15; letter-spacing: -0.02em' : 'letter-spacing: -0.03em' }}">{{ $card['fix'] }}</span>
                                            <span style="font-size: max({{ $u($v([16, 12.5])) }}, {{ $mob ? '0.78125rem' : '0.8125rem' }}); line-height: {{ $v([1.45, 1.35]) }}; color: var(--tk-dark-4)">{{ $card['fix_text'] }}</span>
                                        </span>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    @php $side = $v([156, 24]); $trackY = $v([820, 800]); $walker = $v([60, 44]); @endphp
                    <div aria-hidden="true" style="position: absolute; left: {{ $u($side) }}; right: {{ $u($side) }}; top: {{ $u($trackY) }}; border-top: {{ $u(2) }} dashed #C5CCC7"></div>
                    <div aria-hidden="true" data-m="trackfill" style="position: absolute; left: {{ $u($side) }}; top: {{ $u($trackY) }}; height: {{ $u(2) }}; width: {{ $u($v([1128, 342])) }}; background: var(--tk-green); transform-origin: 0 50%"></div>
                    <div aria-hidden="true" data-m="walker" style="position: absolute; left: {{ $u($v([1224, 322])) }}; top: {{ $u($v([790, 778])) }}; width: {{ $u($walker) }}; height: {{ $u($walker) }}; border-radius: 50%; background: #FFFFFF; box-shadow: 0 {{ $u(8) }} {{ $u(20) }} {{ $u(-8) }} rgba(13,20,16,0.4); display: flex; align-items: center; justify-content: center"><img src="{{ media_ref($t['walker_image'] ?? null)?->url }}" alt="" style="width: {{ $u($v([38, 28])) }}; height: auto"></div>
                </div>
            </div>
        </div>
    @endforeach
</div>
