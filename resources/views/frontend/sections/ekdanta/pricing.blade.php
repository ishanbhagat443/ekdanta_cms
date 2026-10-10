{{--
    Ekdanta · Pricing (No Fixed Packages). Scroll tracks bring the generic
    packages in and drop them (gravity: ease inQuad), stamp and drop the
    "no fixed packages" stamp, raise the plan card and pop its chips. Drawn
    in the final state: packages and stamp gone, the plan in place.
--}}
@php
    $d = $section->dataFor('desktop');
    $m = $section->dataFor('mobile');
    $u = fn ($n) => "calc({$n} * var(--u))";
    $lines = fn ($text) => array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $text)), fn ($l) => $l !== ''));
    $factors = array_values(array_filter((array) ($d['factors'] ?? []), fn ($f) => ($f['text'] ?? '') !== ''));
    $icons = [
        'goal' => '<circle cx="12" cy="12" r="8"></circle><circle cx="12" cy="12" r="3"></circle>',
        'calendar' => '<rect x="4" y="5" width="16" height="15" rx="2"></rect><path d="M4 10h16M9 3v4M15 3v4"></path>',
        'budget' => '<path d="M7 5h10M7 9h10M7 5c5 0 7 2 7 4s-2 4-7 4l7 7"></path>',
        'tools' => '<path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L4 17v3h3l5.3-5.3a4 4 0 0 0 5.4-5.4l-2.5 2.5-2.4-.6-.6-2.4z"></path>',
    ];
    $chat = '<path d="M21 12a8 8 0 0 1-11.6 7.1L4 20.5l1.4-5A8 8 0 1 1 21 12z"></path>';
@endphp
<div class="ek ek-pricing" id="{{ $d['anchor'] ?: 's-price' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            $button = 'height: '.$u($v([54, 50])).'; box-sizing: border-box; display: inline-flex; align-items: center; '.($mob ? 'justify-content: center; flex: 1;' : '').' gap: '.$u(8).'; border-radius: 999px; font-family: \'Poppins\', sans-serif; font-weight: 600; font-size: max('.$u($v([16, 14])).', 0.8125rem); text-decoration: none';
            [$cw, $ch, $cgap, $ctop] = $v([[168, 300, 184, 240], [106, 190, 118, 34]]);
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: var(--tk-paper)">
                <div class="ek-art">
                    <div data-m="price-head" style="position: absolute; left: {{ $u($v([96, 24])) }}; top: {{ $u($v([120, 82])) }}; width: {{ $u($v([620, 342])) }}">
                        <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ $u($v([14, 12])) }}, 0.75rem); letter-spacing: 0.12em; color: var(--tk-green-text-sm)">{{ $t['eyebrow'] }}</span>
                        <h2 style="margin: {{ $u($v([8, 4])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ $u($v([64, 32])) }}; line-height: {{ $v([1.02, 1.04]) }}; letter-spacing: -0.045em">{{ $t['heading'] }}<br><span style="color: var(--tk-green-text)">{{ $t['heading_2'] }}</span></h2>
                        <p style="margin: {{ $u($v([18, 8])) }} 0 0; {{ $mob ? '' : 'width: '.$u(560).';' }} font-size: max({{ $u($v([19, 14])) }}, 0.8125rem); line-height: {{ $v([1.5, 1.45]) }}; color: var(--tk-muted)">{{ $t['text'] }}</p>
                    </div>

                    <div style="position: absolute; left: {{ $u($v([96, 24])) }}; top: {{ $u($v([440, 548])) }}; width: {{ $u($v([600, 342])) }}">
                        <span data-m="price-label" style="display: block; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ $u($v([13, 11])) }}, 0.75rem); letter-spacing: 0.1em; color: var(--tk-muted)">{{ $t['factors_label'] }}</span>
                        <ul style="margin: {{ $u($v([14, 8])) }} 0 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: {{ $u($v([12, 8])) }}">
                            @foreach ($factors as $factor)
                                <li data-m="price-factor" style="display: flex; align-items: center; gap: {{ $u($v([14, 10])) }}"><span style="width: {{ $u($v([40, 32])) }}; height: {{ $u($v([40, 32])) }}; flex-shrink: 0; border-radius: {{ $u($v([12, 10])) }}; background: #FFFFFF; box-shadow: 0 {{ $u(6) }} {{ $u(14) }} {{ $u(-10) }} rgba(13,20,16,0.5); display: flex; align-items: center; justify-content: center"><svg viewBox="0 0 24 24" fill="none" stroke="#1E8E47" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: {{ $u($v([20, 16])) }}; height: {{ $u($v([20, 16])) }}">{!! $icons[$factor['icon'] ?? 'goal'] ?? $icons['goal'] !!}</svg></span><span style="font-size: max({{ $u($v([18, 14])) }}, 0.8125rem); line-height: {{ $v([1.35, 1.3]) }}">{{ $factor['text'] }}</span></li>
                            @endforeach
                        </ul>
                        @unless ($mob)
                            <div data-m="price-actions" style="margin-top: {{ $u(26) }}; display: flex; gap: {{ $u(12) }}">
                                <a class="ek-press" href="{{ link_href($d['button_link'] ?? '') }}" @if (\App\Support\Link::external($d['button_link'] ?? '')) target="_blank" rel="noopener" @endif style="{{ $button }}; padding: 0 {{ $u(24) }}; background: var(--tk-ink); color: #FFFFFF"><svg viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: {{ $u(18) }}; height: {{ $u(18) }}">{!! $chat !!}</svg>{{ $t['button_label'] }}</a>
                                <a class="ek-press" href="{{ link_href($d['button_2_link'] ?? '') }}" style="{{ $button }}; padding: 0 {{ $u(22) }}; box-shadow: inset 0 0 0 {{ $u(1.5) }} var(--tk-ink)">{{ $t['button_2_label'] }}</a>
                            </div>
                        @endunless
                    </div>

                    <div style="position: absolute; {{ $mob ? 'left: '.$u(20).'; top: '.$u(248).'; width: '.$u(350).'; height: '.$u(270) : 'left: '.$u(790).'; top: 0; width: '.$u(560).'; height: '.$u(900) }}">
                        <div aria-hidden="true">
                            @foreach ($lines($d['packages']) as $j => $name)
                                <div data-m="pack-{{ $j }}" class="ek-pack" style="position: absolute; left: {{ $u($j * $cgap) }}; top: {{ $u($ctop) }}; width: {{ $u($cw) }}; height: {{ $u($ch) }}; box-sizing: border-box; padding: {{ $u($v([20, 12])) }}; border-radius: {{ $u($v([20, 16])) }}; background: #FFFFFF; box-shadow: 0 {{ $u($v([20, 16])) }} {{ $u($v([40, 32])) }} {{ $u($v([-26, -22])) }} rgba(13,20,16,0.45); opacity: 0">
                                    <span style="display: block; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: {{ $u($v([18, 13])) }}; color: #3B4640">{{ $name }}</span>
                                    <span style="display: block; margin-top: {{ $u($v([10, 6])) }}; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ $u($v([26, 16])) }}; {{ $mob ? '' : 'letter-spacing: -0.03em;' }} color: var(--tk-suffix)">{{ $d['package_price'] }}</span>
                                    @foreach ($v([[110, 90, 120, 70], [70, 56, 76]]) as $b => $bw)
                                        <span style="display: block; margin-top: {{ $u($b === 0 ? $v([18, 12]) : $v([12, 9])) }}; height: {{ $u($v([6, 5])) }}; width: {{ $u($bw) }}; border-radius: {{ $u(3) }}; background: var(--tk-line-1)"></span>
                                    @endforeach
                                    <span style="position: absolute; left: {{ $u($v([20, 12])) }}; right: {{ $u($v([20, 12])) }}; bottom: {{ $u($v([20, 12])) }}; height: {{ $u($v([38, 26])) }}; border-radius: 999px; background: var(--tk-line-1)"></span>
                                </div>
                            @endforeach
                            <div data-m="price-stamp" style="position: absolute; {{ $mob ? 'left: 0; right: 0; top: '.$u(100) : 'left: '.$u(70).'; top: '.$u(330).'; width: '.$u(420) }}; text-align: center; opacity: 0">
                                <span style="display: inline-block; transform: rotate(-11deg); padding: {{ $u($v([12, 8])) }} {{ $u($v([22, 14])) }}; border-radius: {{ $u($v([14, 10])) }}; border: {{ $u($v([5, 4])) }} solid #E0662B; color: #E0662B; background: rgba(244,246,241,0.85); font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ $u($v([34, 22])) }}; line-height: 1; letter-spacing: 0.02em; white-space: nowrap">{{ $d['stamp'] }}</span>
                            </div>
                        </div>
                        <div data-m="price-plan" style="position: absolute; {{ $mob ? 'left: 0; right: 0; top: 0; padding: '.$u(18).'; border-radius: '.$u(22) : 'left: '.$u(50).'; top: '.$u(170).'; width: '.$u(460).'; padding: '.$u(30).'; border-radius: '.$u(28) }}; box-sizing: border-box; background: var(--tk-green-deep); color: #FFFFFF; box-shadow: 0 {{ $u($v([40, 30])) }} {{ $u($v([80, 60])) }} {{ $u($v([-40, -30])) }} rgba(13,20,16,0.7)">
                            <span style="display: flex; justify-content: space-between; align-items: center; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ $u($v([13, 11])) }}, 0.75rem); letter-spacing: 0.1em; color: var(--tk-mint)"><span>{{ $d['plan_label'] }}</span><span style="padding: {{ $u($v([4, 3])) }} {{ $u($v([10, 8])) }}; border-radius: 999px; background: rgba(255,255,255,0.12); letter-spacing: 0.04em; color: #FFFFFF">{{ $d['plan_tag'] }}</span></span>
                            <span style="display: block; margin-top: {{ $u($v([14, 6])) }}; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ $u($v([32, 20])) }}; line-height: {{ $v([1.1, 1.12]) }}; letter-spacing: -0.035em">{!! nl2br(e($t['plan_title'])) !!}</span>
                            <ul style="margin: {{ $u($v([22, 12])) }} 0 0; padding: 0; list-style: none; display: flex; flex-wrap: wrap; gap: {{ $u($v([10, 6])) }}">
                                @foreach ($lines($d['plan_items']) as $item)
                                    <li data-m="price-chip" style="padding: {{ $u($v([9, 5])) }} {{ $u($v([15, 10])) }}; border-radius: 999px; background: #FFFFFF; color: var(--tk-ink); font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ $u($v([15, 12.5])) }}, {{ $mob ? '0.78125rem' : '0.8125rem' }})">{{ $item }}</li>
                                @endforeach
                            </ul>
                            <span style="margin-top: {{ $u($v([26, 12])) }}; padding-top: {{ $u($v([18, 10])) }}; border-top: {{ $u(1) }} solid rgba(255,255,255,0.18); display: flex; align-items: center; gap: {{ $u($v([14, 10])) }}">
                                <svg viewBox="0 0 40 40" aria-hidden="true" style="width: {{ $u($v([40, 32])) }}; height: {{ $u($v([40, 32])) }}; flex-shrink: 0"><circle cx="20" cy="20" r="18" fill="#2EB45D"></circle><path data-m="price-check" d="M12 20.5l5.5 5.5L28.5 14" fill="none" stroke="#0D1410" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round" pathLength="1" stroke-dasharray="1 1" style="stroke-dashoffset: 0"></path></svg>
                                <span data-m="price-quote" style="display: flex; flex-direction: column; gap: {{ $u($v([2, 1])) }}"><span style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ $u($v([18, 14.5])) }}, 0.8125rem)">{{ $d['quote_title'] }}</span><span style="font-size: max({{ $u($v([15, 12.5])) }}, {{ $mob ? '0.78125rem' : '0.8125rem' }}); color: var(--tk-mint-2)">{{ $d['quote_note'] }}</span></span>
                            </span>
                        </div>
                    </div>

                    @if ($mob)
                        <div data-m="price-actions" style="position: absolute; left: {{ $u(24) }}; right: {{ $u(24) }}; top: {{ $u(772) }}; display: flex; gap: {{ $u(8) }}">
                            <a class="ek-press" href="{{ link_href($d['button_link'] ?? '') }}" @if (\App\Support\Link::external($d['button_link'] ?? '')) target="_blank" rel="noopener" @endif style="{{ $button }}; background: var(--tk-ink); color: #FFFFFF"><svg viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: {{ $u(16) }}; height: {{ $u(16) }}">{!! $chat !!}</svg>{{ $t['button_label'] }}</a>
                            <a class="ek-press" href="{{ link_href($d['button_2_link'] ?? '') }}" style="{{ $button }}; box-shadow: inset 0 0 0 {{ $u(1.5) }} var(--tk-ink)">{{ $t['button_2_label'] }}</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
