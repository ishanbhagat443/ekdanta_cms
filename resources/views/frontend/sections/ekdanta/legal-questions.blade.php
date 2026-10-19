{{--
    Ekdanta · Legal Questions (Privacy l-ask, Terms t-ask). A paper plane
    flies the dotted path to the card (scrub keyframes sampled along the
    drawn curve), the card swings in and its rows follow. Drawn finished;
    under reduced motion the plane stays hidden.
--}}
@php
    $d = $section->viewData('desktop');
    $m = $section->viewData('mobile');
    $rows = array_values(array_filter((array) $d['rows'], fn ($r) => ($r['label'] ?? '') !== '' || ($r['value'] ?? '') !== ''));
@endphp
<div class="ek ek-legal-questions" id="{{ $d['anchor'] ?: 'l-ask' }}">
    @foreach ($section->comps($d, $m) as $comp => $t)
        @php
            $mob = $comp !== 'desktop';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            $path = $mob ? 'M30 754 C 225 794, 86 574, 320 374' : 'M116 780 C 436 860, 594 380, 854 160';
            $button = 'height: '.ek_u($v([54, 46])).'; box-sizing: border-box; padding: 0 '.ek_u($v([22, 16])).'; display: inline-flex; align-items: center; border-radius: 999px; font-family: \'Poppins\', sans-serif; font-weight: 600; font-size: max('.ek_u($v([16, 14])).', 0.8125rem); text-decoration: none';
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: {{ $d['bg'] }}; color: #FFFFFF">
                <div class="ek-art" style="perspective: {{ ek_u(1200) }}">
                    <svg aria-hidden="true" viewBox="0 0 {{ $v([1440, 390]) }} {{ $v([900, 844]) }}" style="position: absolute; left: 0; top: 0; width: {{ ek_x($v([1440, 390])) }}; height: {{ ek_u($v([900, 844])) }}"><path d="{{ $path }}" fill="none" stroke="rgba(143,227,174,0.45)" stroke-width="2.5" stroke-dasharray="2 10" stroke-linecap="round"></path></svg>
                    <div aria-hidden="true" data-m="lq-plane" style="position: absolute; left: 0; top: 0; opacity: 0; transform-origin: 0 0"><div style="transform: translate({{ ek_u(-22) }}, {{ ek_u(-22) }})"><svg viewBox="0 0 24 24" fill="#FFFFFF" stroke="var(--tk-ink)" stroke-width="1.2" stroke-linejoin="round" style="width: {{ ek_u(44) }}; height: {{ ek_u(44) }}"><path d="M22 2L2 10.5l7.5 2.5L12 21l3-6.5z"></path><path d="M9.5 13L22 2" fill="none"></path></svg></div></div>

                    <div data-m="lq-head" style="position: absolute; left: {{ ek_x($v([96, 24])) }}; top: {{ ek_u($v([170, 100])) }}; width: {{ ek_x($v([648, 342])) }}">
                        <p style="margin: 0; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([14, 12])) }}, 0.75rem); letter-spacing: 0.12em; color: var(--tk-mint)">{{ $t['eyebrow'] }}</p>
                        <h2 style="margin: {{ ek_u(10) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u($v([54, 36])) }}; line-height: 1.02; letter-spacing: -0.045em; color: inherit">{{ $t['title'] }}</h2>
                        <p style="margin: {{ ek_u(14) }} 0 0; font-size: max({{ ek_u($v([20, 16])) }}, 0.8125rem); line-height: 1.5; color: var(--tk-mint-2)">{{ $t['text'] }}</p>
                    </div>

                    <div data-m="lq-card" style="position: absolute; left: {{ ek_x($v([824, 24])) }}; top: {{ ek_u($v([190, 400])) }}; width: {{ ek_x($v([520, 342])) }}; box-sizing: border-box; padding: {{ ek_u($v([32, 20])) }}; border-radius: {{ ek_u($v([28, 22])) }}; background: #FFFFFF; color: var(--tk-ink); box-shadow: 0 {{ ek_u(40) }} {{ ek_u(80) }} {{ ek_u(-40) }} rgba(0,0,0,0.7); transform-origin: right center">
                        <dl style="margin: 0">
                            @foreach ($rows as $row)
                                <div data-m="lq-row" style="display: flex; flex-direction: column; gap: {{ ek_u(3) }}; padding: {{ ek_u($v([14, 10])) }} 0; border-top: {{ ek_u(1) }} solid var(--tk-line-1)">
                                    <dt style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([12, 11])) }}, 0.75rem); letter-spacing: 0.1em; color: var(--tk-muted)">{{ $row['label'] ?? '' }}</dt>
                                    <dd style="margin: 0; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u($v([19, 17])) }}, 0.8125rem); line-height: 1.4">{{ $row['value'] ?? '' }}</dd>
                                </div>
                            @endforeach
                        </dl>
                        <div style="margin-top: {{ ek_u($v([22, 14])) }}; display: flex; gap: {{ ek_u(10) }}; flex-wrap: wrap">
                            @foreach ([1, 2] as $n)
                                @continue(($t['button_'.$n.'_label'] ?? '') === '')
                                @php $link = $d['button_'.$n.'_link'] ?? ''; $external = \App\Support\Link::external($link); @endphp
                                <a class="ek-press" href="{{ link_href($link) }}" @if ($external) target="_blank" rel="noopener" @endif style="{{ $button }}; {{ $n === 1 ? 'background: var(--tk-green-deep); color: #FFFFFF' : 'box-shadow: inset 0 0 0 '.ek_u(1.5).' var(--tk-ink); color: var(--tk-ink)' }}">{{ $t['button_'.$n.'_label'] }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
