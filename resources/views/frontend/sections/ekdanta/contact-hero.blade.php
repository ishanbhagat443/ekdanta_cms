{{--
    Ekdanta · Contact Hero (Phone Dial). The phone types the business number
    key by key, then shows the call screen (CSS loops from the Motion
    Library, one per digit); scrolling tilts it away. Drawn as the first
    frame: number typed, keypad showing. The number: Settings → Business.
--}}
@php
    $d = $section->dataFor('desktop');
    $m = $section->dataFor('mobile');
    $u = fn ($n) => "calc({$n} * var(--u))";
    $number = (string) (business_phones()[0]['number'] ?? '');
    $chars = preg_split('//u', trim(preg_replace('/[^0-9 ]/', '', $number)), -1, PREG_SPLIT_NO_EMPTY);
    // Where each digit sits on the keypad: 1–9 in rows of three, 0 under 8.
    $key = fn (int $digit) => $digit === 0 ? [1, 3] : [($digit - 1) % 3, intdiv($digit - 1, 3)];
    $keys = [['1', ''], ['2', 'ABC'], ['3', 'DEF'], ['4', 'GHI'], ['5', 'JKL'], ['6', 'MNO'], ['7', 'PQRS'], ['8', 'TUV'], ['9', 'WXYZ'], ['*', ''], ['0', '+'], ['#', '']];
    $handset = '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"></path>';
    $chat = '<path d="M21 12a8 8 0 0 1-11.6 7.1L4 20.5l1.4-5A8 8 0 1 1 21 12z"></path>';
    $mark = media_ref($d['mark'] ?? null);
@endphp
<div class="ek ek-contact-hero" id="{{ $d['anchor'] ?: 'c-top' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            $button = 'height: '.$u($v([56, 48])).'; box-sizing: border-box; display: inline-flex; align-items: center; gap: '.$u(8).'; border-radius: 999px; font-family: \'Poppins\', sans-serif; font-weight: 600; font-size: max('.$u($v([16, 14.5])).', 0.8125rem); text-decoration: none; white-space: nowrap';
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: var(--tk-green-deep); color: #FFFFFF">
                <div class="ek-art">
                    <div aria-hidden="true" style="position: absolute; left: {{ $u($v([880, 95])) }}; top: {{ $u($v([220, 480])) }}; width: {{ $u($v([460, 320])) }}; height: {{ $u($v([460, 320])) }}">
                        @for ($i = 0; $i < 3; $i++)
                            <span data-m="ring" style="position: absolute; inset: 0; border-radius: 50%; border: {{ $u(2) }} solid rgba(143,227,174,0.5)"></span>
                        @endfor
                    </div>

                    <div data-m="hero-text" style="position: absolute; left: {{ $u($v([96, 24])) }}; top: {{ $u($v([150, 84])) }}; width: {{ $u($v([760, 342])) }}">
                        <p data-m="crumb" style="margin: 0; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ $u($v([15, 13])) }}, 0.8125rem); color: var(--tk-mint-2)"><a href="{{ link_href($d['crumb_home_link'] ?? '/') }}" style="text-decoration: none">{{ $t['crumb_home'] }}</a><span aria-hidden="true">  ›  </span>{{ $t['crumb_current'] }}</p>
                        <h1 style="margin: {{ $u($v([14, 10])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ $u($v([128, 60])) }}; line-height: 1; letter-spacing: -0.045em">
                            @foreach ([$t['heading_1'], $t['heading_2'], $t['heading_3']] as $i => $line)
                                <span style="display: block; overflow: hidden; padding-bottom: {{ $u($v([6, 4])) }}"><span data-m="line" style="display: block; {{ $i === 1 ? 'color: var(--tk-mint);' : '' }}">{{ $line }}</span></span>
                            @endforeach
                        </h1>
                        <p data-m="lede" style="margin: {{ $u($v([18, 12])) }} 0 0; {{ $mob ? '' : 'width: '.$u(560).';' }} font-size: max({{ $u($v([21, 16])) }}, 0.8125rem); line-height: 1.5; color: #DCEFE2">{{ $t['text'] }}</p>
                        <div data-m="actions" style="margin-top: {{ $u($v([28, 16])) }}; display: flex; gap: {{ $u($v([12, 8])) }}">
                            <a class="ek-press" href="{{ link_href($d['whatsapp_link'] ?? 'wa:') }}" @if (\App\Support\Link::external($d['whatsapp_link'] ?? 'wa:')) target="_blank" rel="noopener" @endif style="{{ $button }}; padding: 0 {{ $u($v([24, 18])) }}; background: #FFFFFF; color: var(--tk-ink)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: {{ $u(18) }}; height: {{ $u(18) }}">{!! $chat !!}</svg>{{ $t['whatsapp_label'] }}</a>
                            <a class="ek-press" href="{{ link_href($d['call_link'] ?? '') }}" style="{{ $button }}; padding: 0 {{ $u($v([22, 18])) }}; box-shadow: inset 0 0 0 {{ $u(1.5) }} rgba(255,255,255,0.7)">{{ $t['call_label'] }}</a>
                        </div>
                    </div>

                    <div style="position: absolute; {{ $mob ? 'left: 0; top: 0; transform-origin: 0 0; transform: translate('.$u(150).', '.$u(428).') scale(0.62);' : 'left: '.$u(940).'; top: '.$u(100).';' }} width: {{ $u(340) }}; height: {{ $u(700) }}; perspective: {{ $u(1400) }}">
                        <div data-m="phone" style="width: {{ $u(340) }}; height: {{ $u(700) }}; transform: rotateY(-10deg) rotateX(4deg)">
                            <div data-m="float" style="position: relative; width: {{ $u(340) }}; height: {{ $u(700) }}; box-sizing: border-box; padding: {{ $u(12) }}; border-radius: {{ $u(52) }}; background: var(--tk-ink); box-shadow: 0 {{ $u(50) }} {{ $u(90) }} {{ $u(-40) }} rgba(0,0,0,0.75), inset 0 0 0 {{ $u(2) }} rgba(255,255,255,0.08)">
                                <div role="img" aria-label="A phone dialling {{ $d['caller'] }} on {{ $number }}" style="position: relative; width: {{ $u(316) }}; height: {{ $u(676) }}; border-radius: {{ $u(42) }}; overflow: hidden; background: #111A15">
                                    <div aria-hidden="true" style="position: absolute; left: 0; right: 0; top: {{ $u(56) }}; height: {{ $u(44) }}; display: flex; justify-content: center; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: {{ $u(32) }}; letter-spacing: 0.02em; color: #FFFFFF">
                                        @php $n = 0; @endphp
                                        @foreach ($chars as $char)
                                            @if ($char === ' ')
                                                <span style="display: inline-block; width: {{ $u(12) }}"></span>
                                            @else
                                                <span @if ($n < 10) data-m="digit-{{ $n }}" @endif style="display: inline-block">{{ $char }}</span>
                                                @php $n++; @endphp
                                            @endif
                                        @endforeach
                                    </div>
                                    <span aria-hidden="true" data-m="dial-label" style="position: absolute; left: 0; right: 0; top: {{ $u(104) }}; text-align: center; font-size: {{ $u(14) }}; color: #8FA197">{{ $d['dial_label'] }}</span>
                                    <div aria-hidden="true" data-m="keypad" style="position: absolute; left: 0; top: 0; width: {{ $u(316) }}; height: {{ $u(676) }}">
                                        <div style="position: absolute; left: {{ $u(33) }}; top: {{ $u(150) }}; width: {{ $u(250) }}; display: grid; grid-template-columns: repeat(3, {{ $u(70) }}); column-gap: {{ $u(20) }}; row-gap: {{ $u(16) }}">
                                            @foreach ($keys as [$digit, $letters])
                                                <span style="width: {{ $u(70) }}; height: {{ $u(70) }}; border-radius: 50%; background: var(--tk-dark-1); display: flex; flex-direction: column; align-items: center; justify-content: center"><span style="font-family: 'Poppins', sans-serif; font-weight: 500; font-size: {{ $u(28) }}; line-height: 1">{{ $digit }}</span><span style="height: {{ $u(11) }}; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: {{ $u(9) }}; letter-spacing: 0.12em; color: var(--tk-muted-2-sm)">{{ $letters }}</span></span>
                                            @endforeach
                                        </div>
                                        @php $n = 0; @endphp
                                        @foreach (array_filter($chars, 'ctype_digit') as $char)
                                            @break($n >= 10)
                                            @php [$col, $row] = $key((int) $char); @endphp
                                            <span data-m="press-{{ $n++ }}" style="position: absolute; left: {{ $u(33 + $col * 90) }}; top: {{ $u(150 + $row * 86) }}; width: {{ $u(70) }}; height: {{ $u(70) }}; border-radius: 50%; background: var(--tk-green); color: var(--tk-ink); display: flex; align-items: center; justify-content: center; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: {{ $u(30) }}; opacity: 0">{{ $char }}</span>
                                        @endforeach
                                        <span data-m="call-button" style="position: absolute; left: {{ $u(123) }}; top: {{ $u(506) }}; width: {{ $u(70) }}; height: {{ $u(70) }}; border-radius: 50%; background: var(--tk-green); display: flex; align-items: center; justify-content: center"><svg viewBox="0 0 24 24" fill="none" stroke="#0D1410" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width: {{ $u(28) }}; height: {{ $u(28) }}">{!! $handset !!}</svg></span>
                                    </div>
                                    <div aria-hidden="true" data-m="call-screen" style="position: absolute; left: 0; top: {{ $u(150) }}; width: {{ $u(316) }}; height: {{ $u(460) }}; display: flex; flex-direction: column; align-items: center; gap: {{ $u(14) }}; opacity: 0">
                                        <span data-m="avatar" style="margin-top: {{ $u(20) }}; width: {{ $u(112) }}; height: {{ $u(112) }}; border-radius: 50%; background: #FFFFFF; display: flex; align-items: center; justify-content: center">@if ($mark)<img src="{{ $mark->url }}" alt="" style="width: {{ $u(70) }}; height: auto">@endif</span>
                                        <span style="margin-top: {{ $u(10) }}; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: {{ $u(28) }}">{{ $d['caller'] }}</span>
                                        <span style="font-size: {{ $u(15) }}; color: #8FA197">{{ $d['calling'] }}</span>
                                        <span style="margin-top: auto; margin-bottom: {{ $u(30) }}; width: {{ $u(70) }}; height: {{ $u(70) }}; border-radius: 50%; background: #E5484D; display: flex; align-items: center; justify-content: center"><svg viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width: {{ $u(28) }}; height: {{ $u(28) }}; transform: rotate(135deg)">{!! $handset !!}</svg></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div aria-hidden="true" data-m="bubble" style="position: absolute; left: {{ $u($v([700, 14])) }}; top: {{ $u($v([600, 690])) }}; width: {{ $u(270) }}; {{ $mob ? 'transform-origin: 0 100%; transform: scale(0.74);' : '' }}">
                        <div data-m="bubble-card" style="width: {{ $u(270) }}; box-sizing: border-box; padding: {{ $u(14) }} {{ $u(16) }}; border-radius: {{ $u(20) }} {{ $u(20) }} {{ $u(4) }} {{ $u(20) }}; background: #FFFFFF; color: var(--tk-ink); box-shadow: 0 {{ $u(20) }} {{ $u(40) }} {{ $u(-20) }} rgba(0,0,0,0.6); display: flex; gap: {{ $u(10) }}; align-items: flex-start; transform-origin: 100% 100%">
                            <span style="width: {{ $u(34) }}; height: {{ $u(34) }}; flex-shrink: 0; border-radius: 50%; background: var(--tk-green); display: flex; align-items: center; justify-content: center"><svg viewBox="0 0 24 24" fill="none" stroke="#0D1410" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: {{ $u(18) }}; height: {{ $u(18) }}">{!! $chat !!}</svg></span>
                            <span style="display: flex; flex-direction: column; gap: {{ $u(2) }}"><span style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: {{ $u(14) }}">{{ $d['bubble_name'] }}</span><span style="font-size: {{ $u(15) }}; line-height: 1.35; color: #3B4640">{{ $d['bubble_text'] }}</span></span>
                        </div>
                    </div>
                    <div aria-hidden="true" data-m="hint" style="position: absolute; left: {{ $u($v([96, 24])) }}; {{ $mob ? 'top: '.$u(650) : 'bottom: '.$u(30) }}; font-family: 'Poppins', sans-serif; font-weight: 500; font-size: {{ $u($v([13, 11])) }}; letter-spacing: 0.14em; color: var(--tk-mint-2)">{{ $t['hint'] }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>
