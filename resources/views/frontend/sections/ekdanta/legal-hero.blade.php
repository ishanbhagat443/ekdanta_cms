{{--
    Ekdanta · Legal Hero (Privacy l-top, Terms t-top). The heading rises line
    by line, then the picture builds itself (load tracks): the brief card
    with a shield and lock, or the agreement signed and stamped. Scrolling
    lifts the text away and drifts the chips apart. Drawn finished. The
    reading time ({minutes}) is worked out from the page's Legal Body.
--}}
@php
    $d = $section->viewData('desktop');
    $m = $section->viewData('mobile');
    // The page's Legal Body (a sibling: same owner), for the reading time.
    $body = \App\Models\Section::query()->where('sectionable_type', $section->sectionable_type)->where('sectionable_id', $section->sectionable_id)->where('type', 'ekdanta-legal-body')->first();
    $minutes = \App\Support\LegalContent::minutes((array) ($body?->data_desktop['blocks'] ?? []));
    $chips = array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $d['chips']))));
    $fg = $d['fg'] ?: '#FFFFFF';
    $light = strtoupper($fg) !== '#FFFFFF';
@endphp
<div class="ek ek-legal-hero" id="{{ $d['anchor'] ?: 'l-top' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            $button = 'height: '.ek_u($v([56, 48])).'; box-sizing: border-box; padding: 0 '.ek_u($v([22, 16])).'; display: inline-flex; align-items: center; border-radius: 999px; font-family: \'Poppins\', sans-serif; font-weight: 600; font-size: max('.ek_u($v([16, 14])).', 0.8125rem); text-decoration: none; white-space: nowrap';
            $meta = str_replace(['{date}', '{minutes}'], [$t['date'], $minutes], $t['meta']);
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: {{ $d['bg'] }}; color: {{ $fg }}">
                <div class="ek-art">
                    <div aria-hidden="true" style="position: absolute; inset: 0; background-image: radial-gradient(color-mix(in srgb, {{ $fg }} 12%, transparent) {{ ek_u(1.4) }}, transparent {{ ek_u(1.4) }}); background-size: {{ ek_u(28) }} {{ ek_u(28) }}; opacity: 0.5"></div>

                    <div data-m="lh-head" style="position: absolute; left: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([200, 96])) }}; width: {{ ek_u($v([640, 342])) }}">
                        <p data-m="lh-eyebrow" style="margin: 0; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([14, 12])) }}, 0.75rem); letter-spacing: 0.12em; color: {{ $d['accent'] }}">{{ $t['eyebrow'] }}</p>
                        <h1 style="margin: {{ ek_u(12) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u($v([96, 50])) }}; line-height: 0.98; letter-spacing: -0.045em; color: inherit">
                            <span style="display: block; overflow: hidden; padding-bottom: {{ ek_u(6) }}"><span data-m="lh-line" style="display: block">{{ $t['heading_1'] }}</span></span>
                            <span style="display: block; overflow: hidden; padding-bottom: {{ ek_u(6) }}"><span data-m="lh-line" style="display: block; color: {{ $d['accent'] }}">{{ $t['heading_2'] }}</span></span>
                        </h1>
                        <p data-m="lh-text" style="margin: {{ ek_u($v([22, 14])) }} 0 0; font-size: max({{ ek_u($v([20, 16])) }}, 0.8125rem); line-height: 1.5; color: {{ $d['sub'] }}">{{ $t['text'] }}</p>
                        <p data-m="lh-meta" style="margin: {{ ek_u(12) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 500; font-size: max({{ ek_u($v([15, 13])) }}, 0.8125rem); color: {{ $d['accent'] }}">{{ $meta }}</p>
                        <div data-m="lh-actions" style="margin-top: {{ ek_u($v([30, 20])) }}; display: flex; gap: {{ ek_u(10) }}; flex-wrap: wrap">
                            @foreach ([1, 2] as $n)
                                @continue(($t['button_'.$n.'_label'] ?? '') === '')
                                @php $link = $d['button_'.$n.'_link'] ?? ''; $external = \App\Support\Link::external($link); @endphp
                                <a class="ek-press" href="{{ link_href($link) }}" @if ($external) target="_blank" rel="noopener" @endif style="{{ $button }}; {{ $n === 1 ? 'background: var(--tk-green); color: var(--tk-ink)' : 'box-shadow: inset 0 0 0 '.ek_u(1.5).' '.$fg.'; color: '.$fg }}">{{ $t['button_'.$n.'_label'] }}</a>
                            @endforeach
                        </div>
                    </div>

                    {{-- The picture: drawn at 560 × 600 design px, smaller on phones. --}}
                    <div aria-hidden="true" style="position: absolute; left: {{ ek_u($v([800, 27])) }}; top: {{ ek_u($v([150, 448])) }}; width: {{ ek_u(560) }}; height: {{ ek_u(600) }}; transform: scale({{ $v([1, 0.6]) }}); transform-origin: 0 0">
                        <div data-m="lh-drift" style="position: absolute; inset: 0">
                            @include('frontend.sections.ekdanta.partials.'.($d['visual'] === 'terms' ? 'legal-terms' : 'legal-privacy'), ['chips' => $chips, 'chipBg' => $fg, 'chipFg' => $light ? '#FFFFFF' : 'var(--tk-ink)'])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
