{{--
    Ekdanta · Our Story. Scroll tracks bring the founders together; the
    five beats are the engine's steps. Their fade, the hundred dots, the
    highlight, the badge swap and the side dots follow --ek-f in CSS
    (_our-story.scss); about-story.js counts the numbers and pops the logo.
    Phones (no design): founders above, beats below.
--}}
@php
    $d = $section->viewData('desktop');
    $m = $section->viewData('mobile');
    $br = fn (string $text) => implode('<br>', array_map('e', array_values(array_filter(array_map('trim', preg_split('/\R/', $text))))));
    $mark = media_ref($d['mark'] ?? null)?->web_url;
    $logo = media_ref($d['logo'] ?? null)?->web_url;
    // Indian grouping (1,00,000), as the counters show it.
    $fmt = fn ($n) => preg_replace('/\B(?=(\d{2})+\d{3}$)|\B(?=\d{3}$)/', ',', (string) (int) $n);
@endphp
<div class="ek ek-our-story" id="{{ $d['anchor'] ?: 'a-story' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            $cardW = $v([250, 140]);
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: var(--tk-ink); color: #FFFFFF">
                <div class="ek-art">
                    <div aria-hidden="true" style="position: absolute; left: {{ ek_u($v([60, -125])) }}; top: {{ ek_u($v([150, -90])) }}; width: {{ ek_u(640) }}; height: {{ ek_u(640) }}; border-radius: 999px; background: radial-gradient(circle, rgba(46,180,93,0.18), rgba(46,180,93,0) 65%)"></div>

                    @foreach ([1 => [$v([140, 40]), $v([220, 120]), -5, '#1E2A24', 2], 2 => [$v([372, 210]), $v([250, 136]), 5, '#24322B', 3]] as $n => [$x, $y, $tilt, $fill, $z])
                        @php $photo = media_ref($d["founder_{$n}_photo"] ?? null); @endphp
                        <figure data-m="ab-founder-{{ $n }}" style="position: absolute; left: {{ ek_u($x) }}; top: {{ ek_u($y) }}; width: {{ ek_u($cardW) }}; margin: 0; transform: rotate({{ $tilt }}deg); z-index: {{ $z }}">
                            <div class="ek-story-photo" style="height: {{ ek_u($v([330, 190])) }}; border-radius: {{ ek_u($cardW / 2) }} {{ ek_u($cardW / 2) }} {{ ek_u($v([18, 12])) }} {{ ek_u($v([18, 12])) }}; background: {{ $fill }}; overflow: hidden; display: flex; align-items: flex-end; justify-content: center; padding-bottom: {{ ek_u($v([18, 12])) }}; box-sizing: border-box; font-size: max({{ ek_u($v([15, 12])) }}, 0.75rem); color: #8A968F; text-align: center">
                                @if ($photo)<img src="{{ $photo->web_url }}" alt="{{ $photo->alt ?? '' }}" loading="lazy" style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; object-position: center 22%; border-radius: inherit">@else{{ $d["founder_{$n}_placeholder"] }}@endif
                            </div>
                            <figcaption style="text-align: center">
                                <p style="margin: {{ ek_u($v([14, 8])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u($v([18, 15])) }}, 0.8125rem)">{{ $d["founder_{$n}_name"] }}</p>
                                <p style="margin: {{ ek_u(2) }} 0 0; font-size: max({{ ek_u($v([15, 13])) }}, 0.8125rem); color: var(--tk-mint)">{{ $d["founder_{$n}_role"] }}</p>
                            </figcaption>
                        </figure>
                    @endforeach
                    <div aria-hidden="true" data-m="ab-join" style="position: absolute; left: {{ ek_u($v([352, 171])) }}; top: {{ ek_u($v([268, 150])) }}; width: {{ ek_u($v([64, 48])) }}; height: {{ ek_u($v([64, 48])) }}; z-index: 4">
                        <span class="ek-story-plus" style="position: absolute; inset: 0; border-radius: 999px; background: var(--tk-green); color: var(--tk-ink); font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u($v([34, 26])) }}; display: flex; align-items: center; justify-content: center">+</span>
                        <span class="ek-story-mark" data-m="ab-pulse" style="position: absolute; left: {{ ek_u($v([-8, -6])) }}; top: {{ ek_u($v([-8, -6])) }}; width: {{ ek_u($v([80, 60])) }}; height: {{ ek_u($v([80, 60])) }}; border-radius: 999px; background: #FFFFFF; display: flex; align-items: center; justify-content: center">@if ($mark)<img loading="lazy" decoding="async" src="{{ $mark }}" alt="" style="width: {{ ek_u($v([50, 38])) }}; height: auto; display: block">@endif</span>
                    </div>

                    <div data-m="ab-beats" style="position: absolute; left: {{ ek_u($v([760, 24])) }}; top: {{ ek_u($v([0, 410])) }}; width: {{ ek_u($v([580, 342])) }}; height: {{ ek_u($v([900, 434])) }}">
                        <div class="ek-story-beat" style="--i: 0; position: absolute; left: 0; top: {{ ek_u($v([230, 20])) }}; width: 100%"><div data-m="ab-beat-in">
                            <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([14, 12])) }}, {{ $v(['0.8125rem', '0.75rem']) }}); letter-spacing: 0.12em; color: var(--tk-mint)">{{ $t['eyebrow'] }}</span>
                            <h2 style="margin: {{ ek_u($v([10, 6])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u($v([64, 36])) }}, 0.8125rem); line-height: 1.04; letter-spacing: -0.045em">{!! $br($t['heading']) !!}</h2>
                            <p style="margin: {{ ek_u($v([18, 10])) }} 0 0; font-size: max({{ ek_u($v([22, 16])) }}, 0.8125rem); line-height: 1.5; color: #BFD2C6">{{ $t['text'] }}</p>
                        </div></div>
                        <div class="ek-story-beat" style="--i: 1; position: absolute; left: 0; top: {{ ek_u($v([250, 30])) }}; width: 100%">
                            <span aria-hidden="true" data-story-count data-to="{{ (int) $d['years_number'] }}" data-at="0.4" data-span="0.6" data-suffix="{{ $d['years_suffix'] }}" style="display: block; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u($v([170, 110])) }}, 0.8125rem); line-height: 0.95; letter-spacing: -0.06em; color: var(--tk-green)">{{ $fmt($d['years_number']) }}{{ $d['years_suffix'] }}</span>
                            <p style="margin: {{ ek_u($v([14, 10])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([30, 22])) }}, 0.8125rem); line-height: 1.25; letter-spacing: -0.02em"><span class="visually-hidden">{{ $fmt($d['years_number']) }}{{ $d['years_suffix'] }} </span>{!! $br($t['years_text']) !!}</p>
                        </div>
                        <div class="ek-story-beat" style="--i: 2; position: absolute; left: 0; top: {{ ek_u($v([210, 10])) }}; width: 100%">
                            <span aria-hidden="true" data-story-count data-to="{{ (int) $d['owners_number'] }}" data-at="1.3" data-span="0.7" data-suffix="{{ $d['owners_suffix'] }}" style="display: block; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u($v([120, 76])) }}, 0.8125rem); line-height: 0.95; letter-spacing: -0.06em; color: var(--tk-green)">{{ $fmt($d['owners_number']) }}{{ $d['owners_suffix'] }}</span>
                            <p style="margin: {{ ek_u($v([12, 8])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([30, 22])) }}, 0.8125rem); letter-spacing: -0.02em"><span class="visually-hidden">{{ $fmt($d['owners_number']) }}{{ $d['owners_suffix'] }} </span>{{ $t['owners_text'] }}</p>
                            <div aria-hidden="true" style="margin-top: {{ ek_u($v([26, 18])) }}; width: {{ ek_u($v([496, 322])) }}; display: grid; grid-template-columns: repeat(25, {{ ek_u($v([12, 10])) }}); gap: {{ ek_u($v([8, 3])) }}">
                                @for ($i = 0; $i < 100; $i++)<span class="ek-story-dot" style="--i: {{ $i }}; width: {{ ek_u($v([12, 10])) }}; height: {{ ek_u($v([12, 10])) }}; border-radius: 999px"></span>@endfor
                            </div>
                            <p style="margin: {{ ek_u($v([14, 10])) }} 0 0; font-size: max({{ ek_u($v([15, 13])) }}, 0.8125rem); color: #8A968F">{{ $t['dots_caption'] }}</p>
                        </div>
                        <div class="ek-story-beat" style="--i: 3; position: absolute; left: 0; top: {{ ek_u($v([230, 10])) }}; width: 100%">
                            <span aria-hidden="true" style="display: block; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u($v([120, 80])) }}; line-height: 0.6; color: var(--tk-green)">“</span>
                            <p style="margin: {{ ek_u($v([10, 6])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u($v([38, 24])) }}, 0.8125rem); line-height: 1.25; letter-spacing: -0.03em">{{ $t['quote'] }} <span class="ek-story-hl" style="background-image: linear-gradient(transparent 58%, rgba(46,180,93,0.6) 58%); background-repeat: no-repeat">{{ $t['quote_highlight'] }}</span></p>
                        </div>
                        <div class="ek-story-beat" style="--i: 4; position: absolute; left: 0; top: {{ ek_u($v([240, 10])) }}; width: 100%">
                            <p style="margin: 0; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([32, 22])) }}, 0.8125rem); letter-spacing: -0.02em; color: #BFD2C6">{{ $t['became'] }}</p>
                            @if ($logo)<img loading="lazy" decoding="async" data-story-logo src="{{ $logo }}" alt="{{ $d['logo_alt'] }}" style="margin-top: {{ ek_u($v([18, 12])) }}; height: {{ ek_u($v([96, 64])) }}; width: auto; display: block; transform-origin: left center">@endif
                            <p style="margin: {{ ek_u($v([34, 20])) }} 0 0; padding-left: {{ ek_u($v([18, 14])) }}; border-left: {{ ek_u(3) }} solid var(--tk-green); font-size: max({{ ek_u($v([21, 16])) }}, 0.8125rem); line-height: 1.5; font-style: italic; color: #DCE7E0">{{ $t['founders_quote'] }}</p>
                            <p style="margin: {{ ek_u(12) }} 0 0; padding-left: {{ ek_u($v([21, 17])) }}; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([15, 13])) }}, 0.8125rem); color: var(--tk-mint)">{{ $t['founders_credit'] }}</p>
                        </div>
                    </div>

                    <div aria-hidden="true" style="position: absolute; right: {{ ek_u($v([44, 10])) }}; top: {{ ek_u($v([360, 430])) }}; display: flex; flex-direction: column; gap: {{ ek_u($v([10, 8])) }}">
                        @for ($i = 0; $i < 5; $i++)<span class="ek-story-side" style="--i: {{ $i }}; width: {{ ek_u($v([8, 6])) }}; border-radius: 999px"></span>@endfor
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    @include('frontend.sections.ekdanta.partials.noscript-list', ['items' => [
        ['', $d['text']],
        [$fmt($d['years_number']).$d['years_suffix'], preg_replace('/\s+/', ' ', $d['years_text'])],
        [$fmt($d['owners_number']).$d['owners_suffix'], $d['owners_text'].' '.$d['dots_caption']],
        ['', $d['quote']],
        [$d['became'], $d['logo_alt']],
        ['', $d['founders_quote'].' '.$d['founders_credit']],
    ]])
</div>
