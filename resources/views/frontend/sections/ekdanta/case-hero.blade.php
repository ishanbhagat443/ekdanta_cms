{{--
    Ekdanta · Case Hero. The case comes from the template page
    ($templateItem) or the section's "case" field. Load tracks bring the
    text and the profile card in; loops pan its grid and beat the heart;
    scrolling tips the card back. Previous / next are links to the
    neighbouring cases. Phones (no design): text, then a smaller card.
--}}
@php
    $d = $section->viewData('desktop');
    $m = $section->viewData('mobile');
    $all = \App\Models\CollectionItem::query()->of('case-studies')->published()->get();
    $case = $templateItem ?? null;
    $case = $case instanceof \App\Models\CollectionItem ? $case : (\App\Models\CollectionItem::pick('case-studies', $d['case'] ?? null)->first() ?? $all->first());
@endphp
@if ($case)
@php
    $at = max(0, $all->search(fn ($c) => $c->id === $case->id));
    $count = max(1, $all->count());
    $prev = $all[($at - 1 + $count) % $count] ?? null;
    $next = $all[($at + 1) % $count] ?? null;
    $num = fn (int $i) => str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT);
    $hex = ltrim((string) $case->get('colour'), '#');
    $bg = strlen($hex) === 6 ? '#'.$hex : 'var(--tk-ink)';
    $made = array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $case->get('made')))));
    $logo = media_ref($case->get('logo'))?->url;
    $img = media_ref($case->get('image'))?->url;
    $len = mb_strlen($case->title);
    $nameSize = $len <= 11 ? [104, 52] : ($len <= 16 ? [96, 46] : [76, 38]);
    $back = '<path d="M19 12H5M11 6l-6 6 6 6"></path>';
@endphp
<div class="ek ek-case-hero" id="{{ $d['anchor'] ?: 'c-top' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            $button = 'box-sizing: border-box; display: inline-flex; align-items: center; border-radius: 999px; font-family: \'Poppins\', sans-serif; font-weight: 600; font-size: max('.ek_u($v([16, 14])).', 0.8125rem); text-decoration: none; height: '.ek_u($v([54, 46]));
            $round = 'width: '.ek_u($v([44, 38])).'; height: '.ek_u($v([44, 38])).'; border-radius: 999px; display: flex; align-items: center; justify-content: center; text-decoration: none';
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: {{ $bg }}; color: #FFFFFF">
                <div class="ek-art">
                    <div aria-hidden="true" style="position: absolute; inset: 0; background: radial-gradient(circle at {{ $v(['78% 45%', '50% 75%']) }}, rgba(255,255,255,0.14), rgba(255,255,255,0) 55%)"></div>

                    <div data-m="ch-text" style="position: absolute; left: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([136, 84])) }}; width: {{ ek_u($v([660, 342])) }}">
                        <p data-m="ch-crumb" style="margin: 0; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([15, 13])) }}, 0.8125rem); color: rgba(255,255,255,0.75)"><a href="{{ link_href($d['crumb_home_link'] ?? '/work') }}" style="text-decoration: none">{{ $t['crumb_home'] }}</a><span aria-hidden="true">  ›  </span>{{ str_replace(['{n}', '{total}'], [$num($at), $num($count - 1)], $t['crumb_current']) }}</p>
                        <span data-m="ch-chip" style="display: inline-block; margin-top: {{ ek_u($v([18, 12])) }}; padding: {{ ek_u($v([6, 5])) }} {{ ek_u($v([12, 10])) }}; border-radius: 999px; background: var(--tk-green); font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u($v([12, 11])) }}, 0.75rem); letter-spacing: 0.06em; color: var(--tk-ink)">{{ $case->get('sector') }}</span>
                        <h1 style="margin: {{ ek_u($v([14, 8])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u($v($nameSize)) }}, 0.8125rem); line-height: 0.98; letter-spacing: -0.05em"><span style="display: block; overflow: hidden; padding-bottom: {{ ek_u($v([8, 4])) }}"><span data-m="ch-name" style="display: block">{{ $case->title }}</span></span></h1>
                        <p data-m="ch-desc" style="margin: {{ ek_u($v([16, 10])) }} 0 0; {{ $mob ? '' : 'width: '.ek_u(560).';' }} font-size: max({{ ek_u($v([20, 15])) }}, 0.8125rem); line-height: {{ $v([1.55, 1.5]) }}; color: rgba(255,255,255,0.86)">{{ $case->get('description') }}</p>
                        <div data-m="ch-facts" style="margin-top: {{ ek_u($v([26, 16])) }}; display: flex; gap: {{ ek_u($v([40, 20])) }}; padding-top: {{ ek_u($v([18, 12])) }}; border-top: {{ ek_u(1) }} solid rgba(255,255,255,0.22)">
                            <span style="display: flex; flex-direction: column; gap: {{ ek_u(4) }}; {{ $mob ? 'flex: none; max-width: '.ek_u(120) : '' }}"><span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u(12) }}, 0.75rem); letter-spacing: 0.1em; color: rgba(255,255,255,0.6)">{{ $t['industry_label'] }}</span><span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([16, 14])) }}, 0.8125rem)">{{ $case->get('industry') }}</span></span>
                            <span style="display: flex; flex-direction: column; gap: {{ ek_u(4) }}"><span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u(12) }}, 0.75rem); letter-spacing: 0.1em; color: rgba(255,255,255,0.6)">{{ $t['made_label'] }}</span><span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([16, 14])) }}, 0.8125rem)">{{ implode(' · ', $made) }}</span></span>
                        </div>
                        <div data-m="ch-buttons" style="margin-top: {{ ek_u($v([30, 18])) }}; display: flex; gap: {{ ek_u($v([12, 8])) }}">
                            <a class="ek-press" href="{{ link_href($d['button_link'] ?? '') }}" style="{{ $button }}; padding: 0 {{ ek_u($v([24, 18])) }}; background: #FFFFFF; color: var(--tk-ink)">{{ $t['button_label'] }}</a>
                            <a class="ek-press" href="{{ link_href($d['button_2_link'] ?? '') }}" style="{{ $button }}; padding: 0 {{ ek_u($v([22, 16])) }}; gap: {{ ek_u(8) }}; border: {{ ek_u(1.5) }} solid rgba(255,255,255,0.7)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: {{ ek_u(16) }}; height: {{ ek_u(16) }}">{!! $back !!}</svg>{{ $t['button_2_label'] }}</a>
                        </div>
                    </div>

                    <div data-m="ch-card-tip" style="position: absolute; left: {{ ek_u($v([820, 65])) }}; top: {{ ek_u($v([120, 500])) }}; width: {{ ek_u($v([520, 260])) }}; height: {{ ek_u($v([660, 330])) }}; transform-origin: 50% 100%">
                        <div style="position: absolute; left: 0; top: 0; width: {{ ek_u(520) }}; height: {{ ek_u(660) }}; {{ $mob ? 'transform: scale(0.5); transform-origin: 0 0' : '' }}">
                            <div data-m="ch-card" style="position: absolute; inset: 0; border-radius: {{ ek_u(28) }}; background: #FFFFFF; color: var(--tk-ink); box-shadow: 0 {{ ek_u(60) }} {{ ek_u(100) }} {{ ek_u(-40) }} rgba(0,0,0,0.7); overflow: hidden">
                                <div aria-hidden="true" style="position: absolute; left: {{ ek_u(24) }}; right: {{ ek_u(24) }}; top: {{ ek_u(22) }}; display: flex; align-items: center; gap: {{ ek_u(14) }}">
                                    <span style="width: {{ ek_u(70) }}; height: {{ ek_u(70) }}; flex-shrink: 0; border-radius: 999px; background: #FFFFFF; box-shadow: 0 0 0 {{ ek_u(3) }} #FFFFFF, 0 0 0 {{ ek_u(5) }} #E1306C; display: flex; align-items: center; justify-content: center; overflow: hidden">@if ($logo)<img src="{{ $logo }}" alt="" style="width: {{ ek_u(62) }}; height: {{ ek_u(22) }}; object-fit: contain; display: block">@endif</span>
                                    <span style="display: flex; flex-direction: column; gap: {{ ek_u(2) }}; min-width: 0"><span style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: {{ ek_u(17) }}; white-space: nowrap; overflow: hidden; text-overflow: ellipsis">{{ $case->title }}</span><span style="font-size: {{ ek_u(14) }}; color: var(--tk-muted)">{{ $d['card_line'] }}</span></span>
                                    <span style="margin-left: auto; padding: {{ ek_u(8) }} {{ ek_u(16) }}; border-radius: {{ ek_u(10) }}; background: #0095F6; color: #FFFFFF; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: {{ ek_u(13) }}">{{ $d['card_follow'] }}</span>
                                </div>
                                <div aria-hidden="true" style="position: absolute; left: 0; right: 0; top: {{ ek_u(112) }}; height: {{ ek_u(42) }}; border-top: {{ ek_u(1) }} solid var(--tk-line-1); display: flex; justify-content: center; gap: {{ ek_u(40) }}; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: {{ ek_u(11) }}; letter-spacing: 0.1em; color: var(--tk-suffix)"><span style="padding-top: {{ ek_u(13) }}; border-top: {{ ek_u(1.5) }} solid var(--tk-ink); margin-top: {{ ek_u(-1) }}; color: var(--tk-ink)">POSTS</span><span style="padding-top: {{ ek_u(13) }}">REELS</span><span style="padding-top: {{ ek_u(13) }}">TAGGED</span></div>
                                <div style="position: absolute; left: 0; right: 0; top: {{ ek_u(154) }}; bottom: 0; overflow: hidden; background: #FFFFFF">@if ($img)<img data-m="ch-pan" src="{{ $img }}" alt="{{ str_replace('{name}', $case->title, (string) $d['image_alt']) }}" style="width: {{ ek_u(520) }}; height: auto; display: block">@endif</div>
                                <span aria-hidden="true" data-m="ch-like" style="position: absolute; right: {{ ek_u(22) }}; bottom: {{ ek_u(22) }}; width: {{ ek_u(52) }}; height: {{ ek_u(52) }}; border-radius: 999px; background: #FFFFFF; box-shadow: 0 {{ ek_u(10) }} {{ ek_u(24) }} {{ ek_u(-10) }} rgba(0,0,0,0.45); display: flex; align-items: center; justify-content: center"><svg viewBox="0 0 24 24" style="width: {{ ek_u(24) }}; height: {{ ek_u(24) }}"><path d="M12 21s-7-4.4-9.3-9A5.2 5.2 0 0 1 12 6.6 5.2 5.2 0 0 1 21.3 12C19 16.6 12 21 12 21z" fill="#E1306C"></path></svg></span>
                            </div>
                        </div>
                    </div>

                    @if ($count > 1)
                        <div data-m="ch-nav" style="position: absolute; right: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([806, 76])) }}; display: flex; align-items: center; gap: {{ ek_u($v([10, 6])) }}">
                            <a class="ek-press" href="{{ $prev?->url() ?? '#' }}" aria-label="{{ $d['prev_label'] }}: {{ $prev?->title }}" style="{{ $round }}; border: {{ ek_u(1.5) }} solid rgba(255,255,255,0.6); color: #FFFFFF"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: {{ ek_u(18) }}; height: {{ ek_u(18) }}"><path d="M15 6l-6 6 6 6"></path></svg></a>
                            <span aria-hidden="true" style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u($v([15, 13])) }}, 0.8125rem); min-width: {{ ek_u($v([64, 52])) }}; text-align: center">{{ $num($at) }} / {{ $num($count - 1) }}</span>
                            <a class="ek-press" href="{{ $next?->url() ?? '#' }}" aria-label="{{ $d['next_label'] }}: {{ $next?->title }}" style="{{ $round }}; background: #FFFFFF; color: var(--tk-ink)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: {{ ek_u(18) }}; height: {{ ek_u(18) }}"><path d="M9 6l6 6-6 6"></path></svg></a>
                        </div>
                    @endif
                    @unless ($mob)
                        <div aria-hidden="true" data-m="ch-hint" style="position: absolute; left: {{ ek_u(96) }}; bottom: {{ ek_u(30) }}; font-family: 'Poppins', sans-serif; font-weight: 500; font-size: max({{ ek_u(13) }}, 0.8125rem); letter-spacing: 0.14em; color: rgba(255,255,255,0.7)">{{ $t['hint'] }}</div>
                    @endunless
                </div>
            </div>
        </div>
    @endforeach
</div>
@endif
