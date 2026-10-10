{{--
    Ekdanta · Thank-you Hero. Load tracks play the opening (the pill morphs
    into the tick, the tick draws, chips and text arrive); loops keep the
    rings, chips and live dot going; scroll tracks slide the chips apart and
    fade the message. "Received" gets the visitor's local time (data-ek-now).
--}}
@php
    $d = $section->dataFor('desktop');
    $m = $section->dataFor('mobile');
    $u = fn ($n) => "calc({$n} * var(--u))";
    $chat = '<path d="M21 12a8 8 0 0 1-11.6 7.1L4 20.5l1.4-5A8 8 0 1 1 21 12z"></path>';
@endphp
<div class="ek ek-thank-you-hero" id="{{ $d['anchor'] ?: 't-top' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            $size = $v([128, 112]);
            $chip = 'height: '.$u($v([56, 44])).'; box-sizing: border-box; padding: 0 '.$u($v([18, 14])).' 0 '.$u($v([8, 6])).'; display: inline-flex; align-items: center; gap: '.$u($v([10, 8])).'; border-radius: 999px; background: #FFFFFF; box-shadow: 0 '.$u($v([18, 14])).' '.$u($v([40, 30])).' '.$u($v([-22, -18])).' rgba(13,20,16,0.45), inset 0 0 0 '.$u(1).' #E3E8E0; font-family: \'Poppins\', sans-serif; font-weight: 600; font-size: max('.$u($v([15, 13])).', 0.8125rem); white-space: nowrap';
            $dot = 'width: '.$u($v([40, 32])).'; height: '.$u($v([40, 32])).'; border-radius: 50%; display: flex; align-items: center; justify-content: center';
            $button = 'height: '.$u($v([52, 52])).'; box-sizing: border-box; padding: 0 '.$u(22).'; display: '.($mob ? 'flex; justify-content: center' : 'inline-flex').'; align-items: center; gap: '.$u(10).'; border-radius: 999px; font-family: \'Poppins\', sans-serif; font-weight: 600; font-size: max('.$u($v([16, 15])).', 0.8125rem); text-decoration: none';
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: #FFFFFF">
                <div class="ek-art">
                    <div aria-hidden="true" style="position: absolute; inset: 0; background-image: radial-gradient(var(--tk-line-2) {{ $u($v([1.4, 1.3])) }}, transparent {{ $u($v([1.4, 1.3])) }}); background-size: {{ $u($v([28, 24])) }} {{ $u($v([28, 24])) }}; -webkit-mask-image: radial-gradient(ellipse {{ $v(['60% 55% at 50% 40%', '80% 45% at 50% 28%']) }}, #000 20%, transparent 75%); mask-image: radial-gradient(ellipse {{ $v(['60% 55% at 50% 40%', '80% 45% at 50% 28%']) }}, #000 20%, transparent 75%)"></div>
                    <div aria-hidden="true" style="position: absolute; inset: 0; background: radial-gradient(ellipse {{ $v(['45% 40% at 50% 30%', '70% 35% at 50% 26%']) }}, rgba(46,180,93,{{ $v([0.12, 0.13]) }}), transparent 70%)"></div>

                    <div aria-hidden="true" data-m="ty-waves" style="position: absolute; left: {{ $u($v([656, 139])) }}; top: {{ $u($v([196, 166])) }}; width: {{ $u($size) }}; height: {{ $u($size) }}">
                        @for ($i = 0; $i < 3; $i++)
                            <span data-m="ty-wave" style="position: absolute; inset: 0; border-radius: 50%; box-shadow: inset 0 0 0 {{ $u(2) }} var(--tk-green); opacity: 0"></span>
                        @endfor
                    </div>

                    <div aria-hidden="true" data-m="ty-chip-left" style="position: absolute; left: {{ $u($v([286, 16])) }}; top: {{ $u($v([226, 104])) }}">
                        <span data-m="ty-chip" style="display: block"><span data-m="ty-float" style="{{ $chip }}"><span style="{{ $dot }}; background: #D9F2E1"><svg viewBox="0 0 24 24" fill="none" stroke="#1E8E47" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" style="width: {{ $u($v([20, 16])) }}; height: {{ $u($v([20, 16])) }}"><path d="M3 6h18v12H3z"></path><path d="M3 7l9 6 9-6"></path></svg></span>{{ $t['chip_1'] }}</span></span>
                    </div>
                    <div aria-hidden="true" data-m="ty-chip-right" style="position: absolute; {{ $mob ? 'right: '.$u(16).'; top: '.$u(288) : 'left: '.$u(950).'; top: '.$u(226) }}">
                        <span data-m="ty-chip" style="display: block"><span data-m="ty-float-2" style="{{ $chip }}"><span style="{{ $dot }}; background: var(--tk-ink)"><svg viewBox="0 0 24 24" fill="none" stroke="#8FE3AE" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width: {{ $u($v([20, 16])) }}; height: {{ $u($v([20, 16])) }}"><path d="M6 16V11a6 6 0 0 1 12 0v5l2 2H4z"></path><path d="M10 21h4"></path></svg></span>{{ $t['chip_2'] }}</span></span>
                    </div>

                    <div data-m="ty-message" style="position: absolute; left: 0; right: 0; top: {{ $u($v([196, 166])) }}; display: flex; flex-direction: column; align-items: center; text-align: center">
                        <span aria-hidden="true" style="height: {{ $u($size) }}; display: flex; align-items: center; justify-content: center">
                            <span data-m="ty-morph" style="position: relative; width: {{ $u($size) }}; height: {{ $u($size) }}; border-radius: 999px; background: var(--tk-green); box-shadow: 0 {{ $u(24) }} {{ $u(50) }} {{ $u(-20) }} rgba(10,90,48,0.6); display: flex; align-items: center; justify-content: center; overflow: hidden">
                                <span data-m="ty-send" style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; gap: {{ $u($v([12, 10])) }}; color: #FFFFFF; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: {{ $u($v([17, 15])) }}; white-space: nowrap; opacity: 0"><span data-m="ty-spin" style="width: {{ $u($v([18, 16])) }}; height: {{ $u($v([18, 16])) }}; box-sizing: border-box; border-radius: 50%; border: {{ $u(2.5) }} solid rgba(255,255,255,0.3); border-top-color: #FFFFFF"></span>{{ $d['sending'] }}</span>
                                <svg viewBox="0 0 24 24" style="width: {{ $u($v([64, 56])) }}; height: {{ $u($v([64, 56])) }}; position: relative"><path data-m="ty-check" pathLength="1" stroke-dasharray="1" style="stroke-dashoffset: 0" d="M5 12.5l4.5 4.5L19 7.5" fill="none" stroke="#0D1410" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                            </span>
                        </span>
                        <h1 style="margin: {{ $u($v([30, 76])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ $u($v([150, 56])) }}; line-height: 0.95; letter-spacing: -0.06em"><span style="display: block; overflow: hidden; padding: 0 {{ $u($v([10, 8])) }} {{ $u($v([10, 8])) }}"><span data-m="ty-title" style="display: block">{{ $t['heading'] }}</span></span></h1>
                        <p data-m="ty-line" style="margin: {{ $u(8) }} 0 0; {{ $mob ? 'width: '.$u(330).'; line-height: 1.25;' : '' }} font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ $u($v([30, 20])) }}, 0.8125rem); letter-spacing: -0.025em">{{ $t['line'] }}</p>
                        <p data-m="ty-text" style="margin: {{ $u($v([12, 10])) }} 0 0; width: {{ $u($v([640, 330])) }}; font-size: max({{ $u($v([20, 16])) }}, 0.8125rem); line-height: {{ $v([1.55, 1.5]) }}; color: var(--tk-muted)">{{ $t['text'] }}</p>
                        @php
                            $received = '<span role="status" style="'.($mob ? 'margin-top: '.$u(18).'; ' : '').'height: '.$u($v([52, 40])).'; box-sizing: border-box; padding: 0 '.$u($v([20, 16])).'; display: inline-flex; align-items: center; gap: '.$u($v([10, 8])).'; border-radius: 999px; background: var(--tk-paper); font-size: max('.$u($v([16, 14])).', 0.8125rem); color: #3B4640"><span data-m="ty-live" style="width: '.$u($v([10, 9])).'; height: '.$u($v([10, 9])).'; border-radius: 50%; background: var(--tk-green)"></span><span>'.e($t['received']).' <span data-ek-now="'.e($t['received_time']).'">'.e($t['received_fallback']).'</span></span></span>';
                            $wa = '<a class="ek-press" href="'.e(link_href($d['whatsapp_link'] ?? 'wa:')).'"'.(\App\Support\Link::external($d['whatsapp_link'] ?? 'wa:') ? ' target="_blank" rel="noopener"' : '').' style="'.$button.'; background: var(--tk-green); color: var(--tk-ink)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: '.$u(18).'; height: '.$u(18).'">'.$chat.'</svg>'.e($t['whatsapp_label']).'</a>';
                            $home = '<a class="ek-press" href="'.e(link_href($d['home_link'] ?? '/')).'" style="'.$button.'; '.($mob ? 'height: '.$u(48).';' : '').' box-shadow: inset 0 0 0 '.$u(1.5).' var(--tk-ink)">'.e($t['home_label']).'</a>';
                        @endphp
                        @if ($mob)
                            <span data-m="ty-received">{!! $received !!}</span>
                            <div data-m="ty-actions" style="margin-top: {{ $u(16) }}; width: {{ $u(342) }}; display: flex; flex-direction: column; gap: {{ $u(10) }}">{!! $wa !!}{!! $home !!}</div>
                        @else
                            <div data-m="ty-actions" style="margin-top: {{ $u(30) }}; display: flex; align-items: center; gap: {{ $u(12) }}">{!! $received !!}{!! $wa !!}{!! $home !!}</div>
                        @endif
                    </div>
                    @unless ($mob)
                        <div data-m="ty-next" style="position: absolute; left: 0; right: 0; bottom: {{ $u(34) }}; display: flex; justify-content: center"><a href="{{ link_href($d['next_link'] ?? '#t-next') }}" style="display: flex; flex-direction: column; align-items: center; gap: {{ $u(4) }}; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ $u(14) }}, 0.8125rem); color: var(--tk-muted); text-decoration: none">{{ $t['next_label'] }}<svg data-m="ty-bob" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: {{ $u(20) }}; height: {{ $u(20) }}"><path d="M6 9l6 6 6-6"></path></svg></a></div>
                    @endunless
                </div>
            </div>
        </div>
    @endforeach
</div>
