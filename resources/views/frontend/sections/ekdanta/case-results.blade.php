{{--
    Ekdanta · Case Results. Scroll tracks flip the result cards down, swing
    the quote card in and raise the call to action; the cards' dashed rule
    runs (loop). Numbers, quote and names are the case's own fields; the
    "[ … ]" defaults show until they are filled in. Phones (no design):
    three small cards, then the quote and the band.
--}}
@php
    $d = $section->viewData('desktop');
    $m = $section->viewData('mobile');
    $case = $templateItem ?? null;
    $case = $case instanceof \App\Models\CollectionItem ? $case : \App\Models\CollectionItem::pick('case-studies', $d['case'] ?? null)->first();
    $placeholder = fn ($text) => str_starts_with(trim((string) $text), '[');
@endphp
@if ($case)
@php
    $results = array_slice(array_values(array_filter((array) $case->get('results', []), fn ($r) => ($r['label'] ?? '') !== '')), 0, 3);
    $hex = ltrim((string) $case->get('colour'), '#');
    $bg = strlen($hex) === 6 ? '#'.$hex : 'var(--tk-ink)';
    $logo = media_ref($case->get('logo'))?->web_url;
    $short = $case->get('short_name') ?: $case->title;
@endphp
<div class="ek ek-case-results" id="{{ $d['anchor'] ?: 'c-result' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: #FFFFFF">
                <div class="ek-art">
                    <div data-m="cr-head" style="position: absolute; left: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([116, 84])) }}; width: {{ ek_u($v([760, 342])) }}">
                        <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([14, 12])) }}, {{ $v(['0.8125rem', '0.75rem']) }}); letter-spacing: 0.12em; color: var(--tk-green-text-sm)">{{ $t['eyebrow'] }}</span>
                        <h2 style="margin: {{ ek_u($v([8, 4])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u($v([56, 30])) }}, 0.8125rem); line-height: 1.04; letter-spacing: -0.045em">{{ str_replace('{name}', $short, $t['heading']) }}</h2>
                        <p style="margin: {{ ek_u($v([14, 8])) }} 0 0; font-size: max({{ ek_u($v([20, 15])) }}, 0.8125rem); line-height: 1.5; color: var(--tk-muted)">{{ $case->get('result_line') }}</p>
                    </div>

                    <ul style="position: absolute; left: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([330, 244])) }}; width: {{ ek_u($v([760, 342])) }}; margin: 0; padding: 0; list-style: none; display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: {{ ek_u($v([20, 8])) }}; perspective: {{ ek_u(1200) }}">
                        @foreach ($results as $result)
                            <li data-m="cr-card" style="height: {{ ek_u($v([220, 140])) }}; box-sizing: border-box; padding: {{ ek_u($v([24, 12])) }}; border-radius: {{ ek_u($v([22, 16])) }}; background: var(--tk-paper); display: flex; flex-direction: column; justify-content: space-between; transform-origin: 50% 0">
                                <span aria-hidden="true" data-m="cr-dash" style="display: block; height: {{ ek_u(3) }}; border-radius: {{ ek_u(2) }}; background-image: linear-gradient(90deg, var(--tk-green) 50%, transparent 50%); background-size: {{ ek_u(24) }} {{ ek_u(3) }}"></span>
                                <span class="{{ $placeholder($result['value'] ?? '') ? 'ek-result-empty' : '' }}" style="font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u($v([46, 26])) }}, 0.8125rem); line-height: 1; letter-spacing: -0.04em; color: var(--tk-ink)">{{ $result['value'] ?? '' }}</span>
                                <span style="display: flex; flex-direction: column; gap: {{ ek_u(2) }}"><span style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u($v([17, 13])) }}, 0.8125rem)">{{ $result['label'] }}</span><span style="font-size: max({{ ek_u($v([14, 12])) }}, 0.75rem); line-height: 1.25; color: var(--tk-muted)">{{ $result['note'] ?? '' }}</span></span>
                            </li>
                        @endforeach
                    </ul>

                    <figure data-m="cr-quote" style="position: absolute; left: {{ ek_u($v([920, 24])) }}; top: {{ ek_u($v([116, 408])) }}; width: {{ ek_u($v([424, 342])) }}; margin: 0; box-sizing: border-box; padding: {{ ek_u($v([34, 20])) }}; border-radius: {{ ek_u($v([28, 22])) }}; background: {{ $bg }}; color: #FFFFFF; box-shadow: 0 {{ ek_u(40) }} {{ ek_u(80) }} {{ ek_u(-40) }} rgba(13,20,16,0.6)">
                        <span aria-hidden="true" style="display: block; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u($v([110, 64])) }}; line-height: 0.6; color: var(--tk-green)">“</span>
                        <blockquote style="margin: {{ ek_u($v([18, 8])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([24, 16])) }}, 0.8125rem); line-height: 1.4; letter-spacing: -0.02em">{{ $case->get('quote') }}</blockquote>
                        <figcaption style="margin-top: {{ ek_u($v([28, 14])) }}; padding-top: {{ ek_u($v([22, 12])) }}; border-top: {{ ek_u(1) }} solid rgba(255,255,255,0.22); display: flex; align-items: center; gap: {{ ek_u($v([14, 10])) }}">
                            @if ($logo)<span style="width: {{ ek_u($v([110, 84])) }}; height: {{ ek_u($v([52, 40])) }}; border-radius: {{ ek_u(12) }}; background: #FFFFFF; display: flex; align-items: center; justify-content: center; flex: none"><img loading="lazy" decoding="async" src="{{ $logo }}" alt="{{ str_replace('{name}', $case->title, (string) $d['logo_alt']) }}" style="width: {{ ek_u($v([81, 62])) }}; height: {{ ek_u($v([29, 22])) }}; object-fit: contain; display: block"></span>@endif
                            <span style="display: flex; flex-direction: column; gap: {{ ek_u(2) }}"><span style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u($v([16, 14])) }}, 0.8125rem)">{{ $case->get('quote_name') }}</span><span style="font-size: max({{ ek_u($v([14, 13])) }}, 0.8125rem); color: rgba(255,255,255,0.75)">{{ $case->get('quote_role') }}, {{ $case->title }}</span></span>
                        </figcaption>
                    </figure>

                    <div data-m="cr-cta" style="position: absolute; left: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([600, 720])) }}; width: {{ ek_u($v([760, 342])) }}; box-sizing: border-box; display: flex; align-items: center; {{ $mob ? 'flex-wrap: wrap;' : '' }} gap: {{ ek_u($v([18, 10])) }}; padding: {{ ek_u($v([22, 14])) }} {{ ek_u($v([26, 18])) }}; border-radius: {{ ek_u($v([22, 18])) }}; background: var(--tk-ink); color: #FFFFFF">
                        <span style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u($v([21, 15])) }}, 0.8125rem); letter-spacing: -0.02em">{{ $t['cta_text'] }}</span>
                        <a class="ek-press" href="{{ link_href($d['cta_link'] ?? '') }}" style="margin-left: auto; height: {{ ek_u($v([48, 42])) }}; box-sizing: border-box; padding: 0 {{ ek_u($v([20, 16])) }}; display: inline-flex; align-items: center; background: var(--tk-green); color: var(--tk-ink); border-radius: 999px; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([15, 14])) }}, 0.8125rem); text-decoration: none">{{ $t['cta_button'] }}</a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endif
