{{--
    Ekdanta · Work Showcase. The work-intro scene moves the cards (gather,
    first one forward, then a carousel with each brand's colour); the steps
    mark the current case (is-active, aria-current). Drawn on the first
    case, so it reads without JS. Cases: Collections → Case studies.
--}}
@php
    $d = $section->viewData('desktop');
    $m = $section->viewData('mobile');
    $cases = \App\Models\CollectionItem::pick('case-studies', $d['cases'] ?? null);
@endphp
<div class="ek ek-work-showcase" id="{{ $d['anchor'] ?: 'j-work' }}" data-base-colour="{{ $d['base_colour'] ?: '#0A5A30' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            $hero = $mob ? [0, 0] : [-320, 0];
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: {{ $cases->first()?->get('colour') ?: ($d['base_colour'] ?: '#0A5A30') }}; color: #FFFFFF">
                <div class="ek-art">
                    <div data-m="work-intro" style="position: absolute; left: {{ $mob ? ek_u(20) : 0 }}; right: {{ $mob ? ek_u(20) : 0 }}; top: {{ ek_u($v([118, 96])) }}; text-align: center; opacity: 0">
                        <h2 style="margin: 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u($v([84, 36])) }}; line-height: {{ $v([1.25, 1.3]) }}">{{ $t['heading'] }}</h2>
                        <p style="margin: {{ ek_u($v([6, 8])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([24, 15])) }}, 0.8125rem); {{ $mob ? 'line-height: 1.45;' : '' }} color: #BFE3CC">{{ $t['subheading'] }}</p>
                    </div>

                    @foreach ($cases as $i => $case)
                        <div aria-hidden="true" data-m="work-card" data-colour="{{ $case->get('colour') ?: '#0A5A30' }}"
                             style="position: absolute; left: {{ ek_u($v([500, 80])) }}; top: {{ ek_u($v([208, 100])) }}; width: {{ ek_u($v([440, 230])) }}; height: {{ ek_u($v([483, 252])) }}; border-radius: {{ ek_u($v([26, 20])) }}; overflow: hidden; background: #FFFFFF; box-shadow: 0 {{ ek_u($v([40, 30])) }} {{ ek_u($v([80, 60])) }} {{ ek_u($v([-30, -24])) }} rgba(0,0,0,0.7); transform: translate({{ ek_u($hero[0]) }}, {{ ek_u($hero[1]) }}) rotate(-3deg); opacity: {{ $i === 0 ? 1 : 0 }}; z-index: {{ 20 - $i }}">
                            <img loading="lazy" decoding="async" src="{{ media_ref($case->get('image'))?->web_url }}" alt="" style="width: 100%; height: 100%; object-fit: cover; object-position: top; display: block">
                        </div>
                    @endforeach

                    <div data-m="work-panels">
                        @foreach ($cases as $i => $case)
                            @php $made = array_filter(array_map('trim', explode("\n", (string) $case->get('made')))); @endphp
                            <article style="position: absolute; left: {{ ek_u($v([700, 24])) }}; top: {{ ek_u($v([210, 382])) }}; width: {{ ek_u($v([620, 342])) }}; display: flex; flex-direction: column; gap: {{ ek_u($v([18, 12])) }}; opacity: {{ $i === 0 ? 1 : 0 }}">
                                <img loading="lazy" decoding="async" src="{{ media_ref($case->get('image'))?->web_url }}" alt="{{ str_replace('{name}', $case->title, (string) $t['image_alt']) }}" class="visually-hidden">
                                <span style="align-self: flex-start; padding: {{ ek_u($v([6, 5])) }} {{ ek_u($v([12, 10])) }}; border-radius: 999px; background: var(--tk-green); font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u($v([13, 11])) }}, 0.75rem); letter-spacing: 0.06em; color: var(--tk-ink)">{{ $case->get('sector') }}</span>
                                <h3 style="margin: 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u($v([60, 31])) }}; line-height: {{ $v([1, 1.02]) }}; letter-spacing: -0.045em">@if ($url = $case->url())<a href="{{ $url }}" style="color: inherit; text-decoration: none">{{ $case->title }}</a>@else{{ $case->title }}@endif</h3>
                                <p style="margin: 0; font-size: max({{ ek_u($v([20, 15.5])) }}, 0.8125rem); line-height: {{ $v([1.55, 1.5]) }}; color: #DCE7E0">{{ $case->get('description') }}</p>
                                <div style="display: flex; flex-direction: column; gap: {{ ek_u($v([10, 8])) }}">
                                    <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([13, 11])) }}, 0.75rem); letter-spacing: 0.1em; color: #9FC7AE">{{ $t['made_label'] }}</span>
                                    <ul style="margin: 0; padding: 0; list-style: none; display: flex; flex-wrap: wrap; gap: {{ ek_u($v([8, 6])) }}">
                                        @foreach ($made as $item)
                                            <li style="padding: {{ ek_u($v([7, 5])) }} {{ ek_u($v([14, 11])) }}; border-radius: 999px; box-shadow: inset 0 0 0 {{ ek_u(1) }} rgba(255,255,255,0.4); font-size: max({{ ek_u($v([15, 13.5])) }}, 0.8125rem); font-weight: 500">{{ $item }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div style="{{ $mob ? '' : 'margin-top: '.ek_u(6).';' }} padding-top: {{ ek_u($v([18, 12])) }}; border-top: {{ ek_u(1) }} solid rgba(255,255,255,0.2); display: flex; align-items: baseline; gap: {{ ek_u($v([14, 10])) }}">
                                    <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([13, 11])) }}, 0.75rem); letter-spacing: 0.1em; color: #9FC7AE">{{ $t['result_label'] }}</span>
                                    <span style="font-size: max({{ ek_u($v([18, 14])) }}, 0.8125rem); color: #DCE7E0">{{ $case->get('home_result') }}</span>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div aria-hidden="true" data-m="work-dots" style="position: absolute; right: {{ ek_u($v([40, 12])) }}; top: {{ ek_u($v([360, 140])) }}; display: flex; flex-direction: column; gap: {{ ek_u($v([10, 7])) }}">
                        @foreach ($cases as $i => $case)
                            <span style="width: {{ ek_u($v([10, 7])) }}; height: {{ ek_u($i === 0 ? $v([34, 26]) : $v([10, 7])) }}; border-radius: 999px; background: {{ $i === 0 ? 'var(--tk-green)' : 'rgba(255,255,255,0.3)' }}"></span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    @include('frontend.sections.ekdanta.partials.noscript-list', ['heading' => $d['heading'].' '.$d['subheading'], 'items' => $cases->map(fn ($c) => [$c->title, (string) $c->get('description')])->all()])
</div>
