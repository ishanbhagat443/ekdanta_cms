{{--
    Ekdanta · WhatsApp Tips. Scroll tracks fly the plane along its path
    (--plane → offset-distance), raise the phone and bring the tips in one
    by one. The plane's path is in design px, so it sits in a 1440 × 900
    layer scaled by --u-n (set by the engine). Phones (no design): text,
    then a smaller phone; no plane.
--}}
@php
    $d = $section->viewData('desktop');
    $m = $section->viewData('mobile');
    $tips = array_slice(array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $d['tips'])))), 0, 4);
    $mark = media_ref($d['mark'] ?? null)?->url;
    $chat = '<path d="M21 12a8 8 0 0 1-11.6 7.1L4 20.5l1.4-5A8 8 0 1 1 21 12z"></path>';
    $path = 'M120 820 C 420 700, 560 860, 760 640 S 980 380, 960 300';
    $href = link_href($d['button_link'] ?? '');
@endphp
<div class="ek ek-whatsapp-tips" id="{{ $d['anchor'] ?: 'b-sub' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: var(--tk-ink); color: #FFFFFF">
                <div class="ek-art">
                    <div data-m="tw-head" style="position: absolute; left: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([200, 84])) }}; width: {{ ek_u($v([560, 342])) }}">
                        <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([14, 12])) }}, {{ $v(['0.8125rem', '0.75rem']) }}); letter-spacing: 0.12em; color: var(--tk-mint)">{{ $t['eyebrow'] }}</span>
                        <h2 style="margin: {{ ek_u($v([10, 6])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u($v([76, 46])) }}, 0.8125rem); line-height: 1; letter-spacing: -0.05em">{{ $t['heading'] }}<br><span style="color: var(--tk-green)">{{ $t['heading_2'] }}</span></h2>
                        <p style="margin: {{ ek_u($v([20, 12])) }} 0 0; font-size: max({{ ek_u($v([20, 15])) }}, 0.8125rem); line-height: 1.55; color: #BFD2C6">{{ $t['text'] }}</p>
                        <a class="ek-press" href="{{ $href }}" @if (\App\Support\Link::external($d['button_link'] ?? '')) target="_blank" rel="noopener" @endif style="margin-top: {{ ek_u($v([30, 18])) }}; height: {{ ek_u($v([56, 48])) }}; box-sizing: border-box; padding: 0 {{ ek_u($v([24, 20])) }}; display: inline-flex; align-items: center; gap: {{ ek_u(10) }}; background: var(--tk-green); color: var(--tk-ink); border-radius: 999px; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([16, 15])) }}, 0.8125rem); text-decoration: none"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: {{ ek_u(18) }}; height: {{ ek_u(18) }}">{!! $chat !!}</svg>{{ $t['button_label'] }}</a>
                        <p style="margin: {{ ek_u($v([14, 10])) }} 0 0; font-size: max({{ ek_u($v([14, 13])) }}, 0.8125rem); color: #8A968F">{{ $t['note'] }}</p>
                    </div>

                    @unless ($mob)
                        <svg aria-hidden="true" viewBox="0 0 1440 900" style="width: {{ ek_u(1440) }}; height: {{ ek_u(900) }}; position: absolute; left: 0; top: 0"><path d="{{ $path }}" fill="none" stroke="rgba(143,227,174,0.35)" stroke-width="2" pathLength="1" style="stroke-dasharray: 0.012 0.012"></path></svg>
                        <div aria-hidden="true" style="position: absolute; left: 0; top: 0; width: 1440px; height: 900px; transform: scale(var(--u-n, 1)); transform-origin: 0 0; pointer-events: none">
                            <div data-m="tw-plane" class="ek-tw-plane" style="position: absolute; left: 0; top: 0; width: 46px; height: 46px; offset-path: path('{{ $path }}'); offset-rotate: auto; opacity: 0"><svg viewBox="0 0 24 24" style="width: 46px; height: 46px"><path d="M2 11.5L21 3l-6.5 18-3.5-7.5L2 11.5z" fill="#F2A230"></path><path d="M11 13.5L21 3" stroke="#0D1410" stroke-width="1.2"></path></svg></div>
                        </div>
                    @endunless

                    <div data-m="tw-phone" style="position: absolute; left: {{ ek_u($v([960, 107])) }}; top: {{ ek_u($v([150, 476])) }}; width: {{ ek_u($v([320, 176])) }}; height: {{ ek_u($v([620, 341])) }}; transform: rotate(-2deg)">
                        <div style="position: absolute; left: 0; top: 0; width: {{ ek_u(320) }}; height: {{ ek_u(620) }}; box-sizing: border-box; border-radius: {{ ek_u(44) }}; border: {{ ek_u(10) }} solid #1E2622; background: #ECE5DD; overflow: hidden; box-shadow: 0 {{ ek_u(60) }} {{ ek_u(100) }} {{ ek_u(-40) }} rgba(0,0,0,0.8); {{ $mob ? 'transform: scale(0.55); transform-origin: 0 0' : '' }}">
                            <div aria-hidden="true" style="position: absolute; left: 0; right: 0; top: 0; height: {{ ek_u(70) }}; background: var(--tk-green-deep); display: flex; align-items: center; gap: {{ ek_u(10) }}; padding: {{ ek_u(18) }} {{ ek_u(14) }} 0; box-sizing: border-box"><span style="width: {{ ek_u(36) }}; height: {{ ek_u(36) }}; border-radius: 999px; background: #FFFFFF; display: flex; align-items: center; justify-content: center">@if ($mark)<img src="{{ $mark }}" alt="" style="width: {{ ek_u(22) }}; height: auto">@endif</span><span style="display: flex; flex-direction: column"><span style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: {{ ek_u(15) }}; color: #FFFFFF">{{ $d['phone_name'] }}</span><span style="font-size: {{ ek_u(12) }}; color: var(--tk-mint-2)">{{ $d['phone_status'] }}</span></span></div>
                            <ol style="position: absolute; left: {{ ek_u(12) }}; right: {{ ek_u(12) }}; top: {{ ek_u(86) }}; margin: 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: {{ ek_u(10) }}">
                                {{-- Phones draw the phone at 55 %: 1.48rem is 13 px on screen, and three tips fit. --}}
                                @foreach ($mob ? array_slice($tips, 0, 3) : $tips as $k => $tip)
                                    <li data-m="tw-tip" style="align-self: flex-start; max-width: {{ ek_u(250) }}; padding: {{ ek_u(9) }} {{ ek_u(12) }} {{ ek_u(8) }}; border-radius: {{ ek_u(4) }} {{ ek_u(14) }} {{ ek_u(14) }} {{ ek_u(14) }}; background: #FFFFFF; box-shadow: 0 {{ ek_u(1) }} {{ ek_u(1) }} rgba(0,0,0,0.12); font-size: {{ $mob ? 'max('.ek_u(14.5).', 1.48rem)' : 'max('.ek_u(14.5).', 0.8125rem)' }}; line-height: 1.4; color: var(--tk-ink); transform-origin: 0 0"><b style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u(12) }}, {{ $mob ? '1.36rem' : '0.75rem' }}); color: var(--tk-green-text-sm); display: block; margin-bottom: {{ ek_u(2) }}">{{ str_replace('{n}', (string) ($k + 1), $d['tip_label']) }}</b>{{ $tip }}</li>
                                @endforeach
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
