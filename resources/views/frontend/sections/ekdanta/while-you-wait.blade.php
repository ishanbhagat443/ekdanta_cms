{{--
    Ekdanta · While You Wait. Three link cards (work, Instagram, blog).
    Scroll tracks deal the cards in and fan the work photos out; loops
    scroll the phone feed and roll the notebook titles. Drawn dealt in.
--}}
@php
    $d = $section->viewData('desktop');
    $m = $section->viewData('mobile');
    $img = fn ($key) => media_ref($d[$key] ?? null)?->web_url;
    $bg = fn ($key) => ($url = $img($key)) ? '#FFFFFF url('.e($url).') center top / cover no-repeat' : '#FFFFFF';
    $arrow = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: '.ek_u(16).'; height: '.ek_u(16).'"><path d="M5 12h14M13 6l6 6-6 6"></path></svg>';
    // The loops move through exactly three items, then back to the first.
    $titles = array_values(array_filter(array_map('trim', preg_split('/\R/', (string) ($d['notebook_titles'] ?? '')))));
    $titles = $titles ? [$titles[0], $titles[1 % count($titles)], $titles[2 % count($titles)], $titles[0]] : [];
    $feed = ['feed_1', 'feed_2', 'feed_3', 'feed_1'];
    $cards = [
        ['key' => 'work', 'bg' => 'var(--tk-dark-4)', 'color' => '#FFFFFF', 'label' => 'var(--tk-mint)', 'text' => '#BFD2C6', 'link' => 'var(--tk-mint)'],
        ['key' => 'insta', 'bg' => 'var(--tk-orange)', 'color' => 'var(--tk-ink)', 'label' => '#633800', 'text' => '#3B2A10', 'link' => 'inherit'],
        ['key' => 'blog', 'bg' => 'var(--tk-paper)', 'color' => 'var(--tk-ink)', 'label' => 'var(--tk-green-text-sm)', 'text' => 'var(--tk-muted)', 'link' => 'var(--tk-green-deep)'],
    ];
@endphp
<div class="ek ek-while-you-wait" id="{{ $d['anchor'] ?: 't-wait' }}">
    @foreach ($section->comps($d, $m) as $comp => $t)
        @php
            $mob = $comp !== 'desktop';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: #FFFFFF">
                <div class="ek-art">
                    @if ($mob)
                        <div data-m="wy-head" style="position: absolute; left: {{ ek_u(24) }}; top: {{ ek_u(86) }}; width: {{ ek_u(342) }}">
                            <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u(12) }}, 0.75rem); letter-spacing: 0.12em; color: var(--tk-green-text-sm)">{{ $t['eyebrow'] }}</span>
                            <h2 style="margin: {{ ek_u(4) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u(29) }}, 0.8125rem); line-height: 1.04; letter-spacing: -0.05em; white-space: nowrap">{{ $t['heading'] }}</h2>
                        </div>
                    @else
                        <div data-m="wy-head" style="position: absolute; left: {{ ek_u(96) }}; top: {{ ek_u(118) }}; width: {{ ek_u(1248) }}; display: flex; align-items: flex-end; justify-content: space-between">
                            <div><span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u(14) }}, 0.8125rem); letter-spacing: 0.12em; color: var(--tk-green-text-sm)">{{ $t['eyebrow'] }}</span>
                                <h2 style="margin: {{ ek_u(8) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u(64) }}, 0.8125rem); line-height: 1.02; letter-spacing: -0.05em">{{ $t['heading'] }}</h2></div>
                            <p style="margin: 0 0 {{ ek_u(8) }}; width: {{ ek_u(380) }}; font-size: max({{ ek_u(19) }}, 0.8125rem); line-height: 1.55; color: #3B4640">{{ $t['text'] }}</p>
                        </div>
                    @endif

                    @foreach ($cards as $i => $c)
                        @php
                            $k = $c['key'];
                            $href = $d[$k.'_link'] ?? '';
                            $place = $mob
                                ? 'left: '.ek_u(24).'; top: '.ek_u([196, 396, 596][$i]).'; width: '.ek_u(342).'; height: '.ek_u(186).'; border-radius: '.ek_u(24)
                                : 'left: '.ek_u([96, 520, 944][$i]).'; top: '.ek_u(270).'; width: '.ek_u(400).'; height: '.ek_u(560).'; border-radius: '.ek_u(28).'; flex-direction: column';
                        @endphp
                        <a class="ek-press" data-m="wy-card-{{ $i }}" href="{{ link_href($href) }}" @if (\App\Support\Link::external($href)) target="_blank" rel="noopener" @endif style="position: absolute; {{ $place }}; box-sizing: border-box; overflow: hidden; text-decoration: none; display: flex; background: {{ $c['bg'] }}; color: {{ $c['color'] }}">
                            @if ($k === 'work')
                                <span aria-hidden="true" style="position: relative; {{ $mob ? 'width: '.ek_u(140).'; flex: none' : 'height: '.ek_u(320) }}; display: block">
                                    @foreach (['wy-fan-l' => 'work_photo_1', 'wy-fan-r' => 'work_photo_2', 'wy-fan-m' => 'work_photo_3'] as $part => $photo)
                                        <span data-m="{{ $part }}" style="position: absolute; left: {{ ek_u($v([115, 34])) }}; top: {{ ek_u($v([46, 36])) }}; width: {{ ek_u($v([170, 74])) }}; height: {{ ek_u($v([220, 96])) }}; border-radius: {{ ek_u($v([14, 10])) }}; background: {{ $bg($photo) }}; box-shadow: {{ $part === 'wy-fan-m' ? '0 '.ek_u($v([24, 16])).' '.ek_u($v([44, 28])).' '.ek_u($v([-18, -14])).' rgba(0,0,0,0.8)' : '0 '.ek_u($v([20, 14])).' '.ek_u($v([40, 26])).' '.ek_u($v([-20, -14])).' rgba(0,0,0,0.7)' }}; transform-origin: 50% 100%"></span>
                                    @endforeach
                                </span>
                            @elseif ($k === 'insta')
                                <span aria-hidden="true" style="position: relative; {{ $mob ? 'width: '.ek_u(140).'; flex: none' : 'height: '.ek_u(320) }}; display: block">
                                    <span style="position: absolute; left: {{ ek_u($v([115, 30])) }}; top: {{ ek_u($v([34, 22])) }}; width: {{ ek_u($v([170, 90])) }}; height: {{ ek_u($v([330, 180])) }}; box-sizing: border-box; border-radius: {{ ek_u($v([28, 18])) }}; border: {{ ek_u($v([8, 6])) }} solid var(--tk-ink); background: #FFFFFF; overflow: hidden">
                                        <span style="position: absolute; left: 0; right: 0; top: 0; height: {{ ek_u($v([30, 20])) }}; background: #FFFFFF; z-index: 1; display: flex; align-items: center; gap: {{ ek_u($v([6, 4])) }}; padding: 0 {{ ek_u($v([8, 6])) }}; box-sizing: border-box; border-bottom: {{ ek_u(1) }} solid var(--tk-line-2)">@if ($mark = $img('feed_mark'))<img loading="lazy" decoding="async" src="{{ $mark }}" alt="" style="width: {{ ek_u($v([14, 10])) }}; height: auto">@endif<span style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: {{ ek_u($v([10, 7.5])) }}">{{ $d['feed_name'] }}</span></span>
                                        <span data-m="wy-feed" style="position: absolute; left: 0; top: {{ ek_u($v([30, 20])) }}; width: {{ ek_u($v([154, 78])) }}; display: block">@foreach ($feed as $photo)<span style="display: block; width: {{ ek_u($v([154, 74])) }}; height: {{ ek_u($v([169, 81])) }}; background: {{ $bg($photo) }}"></span>@endforeach</span>
                                    </span>
                                </span>
                            @else
                                <span aria-hidden="true" style="position: relative; {{ $mob ? 'width: '.ek_u(140).'; flex: none' : 'height: '.ek_u(320) }}; display: block; background-image: linear-gradient(rgba(13,20,16,0.06) {{ ek_u(1) }}, transparent {{ ek_u(1) }}); background-size: 100% {{ ek_u($v([32, 24])) }}; background-position: 0 {{ ek_u($v([10, 8])) }}">
                                    <span style="position: absolute; left: {{ ek_u($v([50, 14])) }}; top: {{ ek_u($v([70, 40])) }}; width: {{ ek_u($v([300, 116])) }}; height: {{ ek_u($v([190, 104])) }}; box-sizing: border-box; padding: {{ ek_u($v([20, 10])) }} {{ ek_u($v([22, 10])) }}; border-radius: {{ ek_u($v([18, 12])) }}; background: #FFFFFF; box-shadow: 0 {{ ek_u($v([24, 16])) }} {{ ek_u($v([44, 30])) }} {{ ek_u($v([-26, -20])) }} rgba(13,20,16,0.5); transform: rotate(-3deg); display: flex; flex-direction: column; gap: {{ ek_u($v([8, 4])) }}">
                                        <span style="align-self: flex-start; padding: {{ ek_u($v([4, 2])) }} {{ ek_u($v([9, 6])) }}; border-radius: 999px; background: #D9F2E1; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: {{ ek_u($v([10, 7])) }}; letter-spacing: 0.06em; color: var(--tk-green-deep)">{{ $t['notebook_label'] }}</span>
                                        <span style="height: {{ ek_u($v([86, 58])) }}; overflow: hidden; display: block"><span data-m="wy-titles" style="display: block">@foreach ($titles as $title)<span style="display: flex; align-items: center; height: {{ ek_u($v([86, 58])) }}; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u($v([20, 13])) }}, 0.8125rem); line-height: 1.2; letter-spacing: -0.02em; color: var(--tk-ink)">{{ $title }}</span>@endforeach</span></span>
                                        @if (! $mob && ($t['notebook_byline'] ?? ''))
                                            <span style="font-size: max({{ ek_u(13) }}, 0.8125rem); color: var(--tk-muted)">{{ $t['notebook_byline'] }}</span>
                                        @endif
                                    </span>
                                </span>
                            @endif
                            <span style="{{ $mob ? 'padding: '.ek_u(18).' '.ek_u(18).' '.ek_u(16).' '.ek_u(4).'; gap: '.ek_u(4) : 'padding: 0 '.ek_u(30).' '.ek_u(30).'; gap: '.ek_u(10) }}; display: flex; flex-direction: column; flex: 1{{ $k === 'insta' && ! $mob ? '; position: relative; background: var(--tk-orange)' : '' }}">
                                <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: {{ $mob ? ek_u(11) : 'max('.ek_u(13).', 0.8125rem)' }}; letter-spacing: 0.12em; color: {{ $c['label'] }}">{{ $t[$k.'_label'] }}</span>
                                <span style="font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u($v([28, 19])) }}, 0.8125rem); line-height: {{ $v([1.1, 1.12]) }}; letter-spacing: {{ $v(['-0.035em', '-0.03em']) }}">{{ $t[$k.'_title'] }}</span>
                                @if (($t[$k.'_text'] ?? '') && ! ($mob && $k === 'work'))
                                    <span style="font-size: max({{ ek_u($v([16.5, 13.5])) }}, 0.8125rem); line-height: {{ $v([1.5, 1.4]) }}; color: {{ $c['text'] }}">{{ $t[$k.'_text'] }}</span>
                                @endif
                                <span style="margin-top: auto; display: inline-flex; align-items: center; gap: {{ ek_u($v([8, 6])) }}; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([15, 13.5])) }}, 0.8125rem); color: {{ $c['link'] }}">{{ $t[$k.'_link_label'] }}{!! $arrow !!}</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>
