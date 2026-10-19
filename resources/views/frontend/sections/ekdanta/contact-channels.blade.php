{{--
    Ekdanta · Contact Channels. The cards are the steps (one per channel);
    the steps' index (--ek-f) moves the green band, lights the list and rolls
    each card's value in, in CSS (_contact-channels.scss). Tapping a channel
    jumps to its card. Drawn with the first card showing.
--}}
@php
    $d = $section->viewData('desktop');
    $m = $section->viewData('mobile');
    $channels = array_values(array_filter((array) ($d['channels'] ?? []), fn ($c) => ($c['label'] ?? '') !== ''));
    $count = count($channels);
    $icons = [
        'wa' => 'M21 12a8 8 0 0 1-11.6 7.1L4 20.5l1.4-5A8 8 0 1 1 21 12z',
        'phone' => 'M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z',
        'sales' => 'M3 8h18v11H3z M8 8V5h8v3 M3 13h18',
        'mail' => 'M4 6h16v12H4z M4 7l8 6 8-6',
        'pin' => 'M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11z M12 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z',
        'ig' => 'M7 3h10a4 4 0 0 1 4 4v10a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V7a4 4 0 0 1 4-4z M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z M17.5 6.5h.01',
    ];
    $mark = media_ref($d['mark'] ?? null);
@endphp
<div class="ek ek-contact-channels" id="{{ $d['anchor'] ?: 'c-channels' }}">
    @foreach ($section->comps($d, $m) as $comp => $t)
        @php
            $mob = $comp !== 'desktop';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            $button = 'height: '.ek_u($v([54, 46])).'; box-sizing: border-box; display: inline-flex; align-items: center; border-radius: 999px; font-family: \'Poppins\', sans-serif; font-weight: 600; font-size: max('.ek_u($v([16, 14])).', 0.8125rem); text-decoration: none';
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}" style="--row: {{ $v([84, 48]) }}; --roll: {{ $v([60, 40]) }}; --drop: {{ $v([30, 24]) }}">
            <div class="ek-stage" data-ek-stage style="background: var(--tk-ink); color: #FFFFFF">
                <div class="ek-art">
                    <div data-m="ch-head" style="position: absolute; left: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([112, 84])) }}; width: {{ ek_u($v([900, 342])) }}">
                        <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([14, 12])) }}, 0.75rem); letter-spacing: 0.12em; color: var(--tk-green)">{{ $t['eyebrow'] }}</span>
                        <h2 style="margin: {{ ek_u($v([8, 4])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u($v([52, 28])) }}; line-height: {{ $v([1.05, 1.06]) }}; letter-spacing: -0.045em">{{ $t['heading'] }}</h2>
                    </div>

                    <div aria-hidden="true" data-m="ch-ui" class="ek-ch-band" style="position: absolute; {{ $mob ? 'left: '.ek_u(16).'; right: '.ek_u(16).'; top: '.ek_u(180).'; height: '.ek_u(44).'; border-radius: '.ek_u(14).'; box-shadow: 0 0 '.ek_u(40).' rgba(46,180,93,0.4)' : 'left: '.ek_u(90).'; top: '.ek_u(266).'; width: '.ek_u(560).'; height: '.ek_u(76).'; border-radius: '.ek_u(18).'; box-shadow: 0 0 '.ek_u(60).' rgba(46,180,93,0.45)' }}; background: var(--tk-green)"></div>
                    @unless ($mob)
                        <div aria-hidden="true" data-m="ch-ui" class="ek-ch-band" style="position: absolute; left: {{ ek_u(22) }}; top: {{ ek_u(278) }}; width: {{ ek_u(52) }}; height: {{ ek_u(52) }}; border-radius: 50%; background: #FFFFFF; box-shadow: 0 {{ ek_u(8) }} {{ ek_u(20) }} {{ ek_u(-8) }} rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center">@if ($mark)<img loading="lazy" decoding="async" src="{{ $mark->web_url }}" alt="" style="width: {{ ek_u(32) }}; height: auto">@endif</div>
                    @endunless

                    <div role="group" aria-label="{{ $t['eyebrow'] }}" data-m="ch-ui" style="position: absolute; {{ $mob ? 'left: '.ek_u(16).'; right: '.ek_u(16).'; top: '.ek_u(178) : 'left: '.ek_u(90).'; top: '.ek_u(262).'; width: '.ek_u(560) }}; display: flex; flex-direction: column">
                        @foreach ($channels as $i => $channel)
                            <button type="button" class="ek-ch-row" data-ek-step="{{ $i }}" style="--i: {{ $i }}; height: {{ ek_u($v([84, 48])) }}; width: 100%; box-sizing: border-box; padding: 0 {{ ek_u($v([22, 14])) }}; border: 0; background: transparent; display: flex; justify-content: space-between; align-items: center; gap: {{ ek_u($v([16, 10])) }}; text-align: left; cursor: pointer">
                                <span class="ek-ch-label" style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u($v([34, 19])) }}, 0.8125rem); letter-spacing: -0.03em">{{ $channel['label'] }}</span>
                                <span class="ek-ch-value" style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([15, 12.5])) }}, {{ $mob ? '0.78125rem' : '0.8125rem' }})">{{ $channel['value'] ?? '' }}</span>
                            </button>
                        @endforeach
                    </div>

                    <div data-m="ch-cards">
                        @foreach ($channels as $i => $channel)
                            @php
                                $links = array_filter([[$channel['button'] ?? '', $channel['button_link'] ?? ''], [$channel['button_2'] ?? '', $channel['button_2_link'] ?? '']], fn ($b) => $b[0] !== '');
                                $chars = preg_split('//u', (string) ($channel['value'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
                            @endphp
                            <div class="ek-ch-card {{ $i === 0 ? 'is-active' : '' }}" style="--i: {{ $i }}; position: absolute; {{ $mob ? 'left: '.ek_u(16).'; right: '.ek_u(16).'; top: '.ek_u(482).'; height: '.ek_u(300).'; padding: '.ek_u(20).'; border-radius: '.ek_u(24) : 'left: '.ek_u(720).'; top: '.ek_u(240).'; width: '.ek_u(624).'; height: '.ek_u(520).'; padding: '.ek_u(40).'; border-radius: '.ek_u(28) }}; box-sizing: border-box; background: var(--tk-paper); color: var(--tk-ink); display: flex; flex-direction: column; gap: {{ ek_u($v([18, 10])) }}">
                                <div style="display: flex; align-items: center; gap: {{ ek_u($v([16, 12])) }}">
                                    <span style="width: {{ ek_u($v([64, 44])) }}; height: {{ ek_u($v([64, 44])) }}; border-radius: {{ ek_u($v([18, 14])) }}; background: var(--tk-green); display: flex; align-items: center; justify-content: center"><svg viewBox="0 0 24 24" fill="none" stroke="#0D1410" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: {{ ek_u($v([30, 22])) }}; height: {{ ek_u($v([30, 22])) }}"><path d="{{ $icons[$channel['icon'] ?? 'wa'] ?? $icons['wa'] }}"></path></svg></span>
                                    <span style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u($v([14, 11.5])) }}, 0.75rem); letter-spacing: 0.1em; color: var(--tk-green-text-sm)">{{ sprintf('%02d / %02d', $i + 1, $count) }} · {{ $channel['kicker'] ?? '' }}</span>
                                </div>
                                <div style="display: flex; height: {{ ek_u($v([66, 42])) }}; overflow: hidden; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u($v([52, 32])) }}; line-height: {{ ek_u($v([66, 42])) }}; letter-spacing: -0.035em">
                                    <span class="visually-hidden">{{ $channel['value'] ?? '' }}</span>
                                    @foreach ($chars as $j => $char)
                                        <span aria-hidden="true" class="ek-ch-char" style="--j: {{ $j }}; display: inline-block; white-space: pre">{{ $char }}</span>
                                    @endforeach
                                </div>
                                <p style="margin: 0; font-size: max({{ ek_u($v([20, 15])) }}, 0.8125rem); line-height: {{ $v([1.55, 1.5]) }}; color: #3B4640">{{ $channel['text'] ?? '' }}</p>
                                <div style="margin-top: auto; display: flex; flex-wrap: wrap; gap: {{ ek_u($v([12, 8])) }}">
                                    @foreach (array_values($links) as $b => [$label, $link])
                                        <a class="ek-press" href="{{ link_href($link) }}" @if (\App\Support\Link::external($link)) target="_blank" rel="noopener" @endif style="{{ $button }}; padding: 0 {{ ek_u($b === 0 ? $v([24, 18]) : $v([22, 16])) }}; {{ $b === 0 ? 'background: var(--tk-ink); color: #FFFFFF' : 'box-shadow: inset 0 0 0 '.ek_u(1.5).' var(--tk-ink)' }}">{{ $label }}</a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div aria-hidden="true" data-m="ch-ui" class="ek-ch-dots" style="position: absolute; {{ $mob ? 'left: 0; right: 0; top: '.ek_u(800).'; justify-content: center; gap: '.ek_u(6) : 'right: '.ek_u(40).'; top: '.ek_u(400).'; flex-direction: column; gap: '.ek_u(10) }}; display: flex">
                        @foreach ($channels as $i => $channel)
                            <span style="{{ $mob ? 'width: '.ek_u(7).'; height: '.ek_u(7) : 'width: '.ek_u(10).'; height: '.ek_u(10) }}; border-radius: 999px; background: rgba(255,255,255,0.3)"></span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    @include('frontend.sections.ekdanta.partials.noscript-list', ['items' => array_map(fn ($c) => [($c['kicker'] ?? '').': '.($c['value'] ?? ''), (string) ($c['text'] ?? '')], $channels)])
</div>
