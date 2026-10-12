{{--
    Ekdanta · Page Finder. Scroll tracks deal the cards in; loops animate
    their little icons. The search box (page-finder.js) filters the index
    below as you type; without JS it stays hidden and the cards remain.
--}}
@php
    $d = $section->dataFor('desktop');
    $m = $section->dataFor('mobile');
    $cards = array_slice(array_values(array_filter((array) ($d['cards'] ?? []), fn ($c) => ($c['title'] ?? '') !== '')), 0, 6);
    $arrow = '<path d="M5 12h14M13 6l6 6-6 6"></path>';
    $glass = '<circle cx="11" cy="11" r="7"></circle><path d="M20 20l-4-4"></path>';
    $icons = [
        'home' => ['0 0 24 24', 'fill="none" stroke="#0D1410" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"', '<circle data-m="nf-smoke" cx="17" cy="3" r="1.6" fill="#8A968F" stroke="none" style="opacity: 0"></circle><circle data-m="nf-smoke" cx="17.5" cy="3" r="1.3" fill="#8A968F" stroke="none" style="opacity: 0"></circle><path d="M3 11l9-7 9 7v9H3z"></path><path d="M16 7V4h2v4.5"></path><path d="M10 20v-5h4v5" stroke="#2EB45D"></path>'],
        'services' => ['0 0 24 24', '', '<rect data-m="nf-sq" x="3" y="3" width="8" height="8" rx="2" fill="#2EB45D"></rect><rect data-m="nf-sq" x="13" y="3" width="8" height="8" rx="2" fill="#0D1410"></rect><rect data-m="nf-sq" x="3" y="13" width="8" height="8" rx="2" fill="#F2A230"></rect><rect data-m="nf-sq" x="13" y="13" width="8" height="8" rx="2" fill="#8FE3AE"></rect>'],
        'work' => ['0 0 24 24', 'fill="none" stroke-width="2" stroke-linejoin="round"', '<rect x="3" y="7" width="14" height="14" rx="2" stroke="#8A968F"></rect><g data-m="nf-stack"><rect x="7" y="3" width="14" height="14" rx="2" fill="#FFFFFF" stroke="#0D1410"></rect><path d="M9 14l3-3 2 2 3-4 2 5" stroke="#2EB45D" stroke-linecap="round"></path></g>'],
        'about' => ['0 0 26 24', 'fill="none" stroke-width="2" stroke-linecap="round"', '<g data-m="nf-bob"><circle cx="9" cy="8" r="3.5" stroke="#0D1410"></circle><path d="M3 20c0-3.5 2.7-6 6-6s6 2.5 6 6" stroke="#0D1410"></path></g><g data-m="nf-bob"><circle cx="18" cy="9" r="3" stroke="#2EB45D"></circle><path d="M15 15.5c.9-.6 1.9-.9 3-.9 2.9 0 5 2.2 5 5.4" stroke="#2EB45D"></path></g>'],
        'blog' => ['0 0 24 24', 'fill="none" stroke-linecap="round" stroke-linejoin="round"', '<path d="M4 6h16 M4 10h12" stroke="#C5CCC7" stroke-width="2"></path><path data-m="nf-write" d="M4 15h11" stroke="#2EB45D" stroke-width="2.4" style="transform-origin: 0 50%"></path><g data-m="nf-pen"><path d="M14 20l1-4 6-6 3 3-6 6z" fill="#F2A230" stroke="#0D1410" stroke-width="1.6"></path></g>'],
        'contact' => ['0 0 24 24', '', '<path data-m="nf-wig" d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z" fill="#0D1410" style="transform-box: fill-box; transform-origin: center"></path>'],
    ];
@endphp
<div class="ek ek-page-finder" id="{{ $d['anchor'] ?: 'n-routes' }}">
    <script type="application/json" data-finder-index>@json(\App\Services\SectionTypes\Ekdanta\PageFinderSection::index())</script>
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            $lines = array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $t['heading']))));
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: #FFFFFF">
                <div class="ek-art">
                    <div data-m="nf-head" style="position: absolute; left: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([112, 84])) }}; {{ $mob ? 'width: '.ek_u(342) : '' }}">
                        <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([14, 12])) }}, {{ $v(['0.8125rem', '0.75rem']) }}); letter-spacing: 0.12em; color: var(--tk-green-text-sm)">{{ $t['eyebrow'] }}</span>
                        <h2 style="margin: {{ ek_u($v([8, 4])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u($v([58, 31])) }}, 0.8125rem); line-height: {{ $v([1.02, 1.04]) }}; letter-spacing: -0.05em">{!! $mob ? e(implode(' ', $lines)) : implode('<br>', array_map('e', $lines)) !!}</h2>
                    </div>

                    @foreach ($cards as $i => $card)
                        @php
                            $green = ($card['look'] ?? '') === 'green';
                            [$vb, $attrs, $paths] = $icons[$card['icon'] ?? 'home'] ?? $icons['home'];
                            $about = ($card['icon'] ?? '') === 'about';
                            $place = $mob
                                ? 'left: '.ek_u([24, 201][$i % 2]).'; top: '.ek_u(290 + intdiv($i, 2) * 160).'; width: '.ek_u(165).'; height: '.ek_u(148).'; padding: '.ek_u(14).'; border-radius: '.ek_u(20)
                                : 'left: '.ek_u([96, 520, 944][$i % 3]).'; top: '.ek_u(330 + intdiv($i, 3) * 254).'; width: '.ek_u(400).'; height: '.ek_u(230).'; padding: '.ek_u(26).' '.ek_u(28).'; border-radius: '.ek_u(26);
                        @endphp
                        <a data-m="nf-card-{{ $i }}" href="{{ link_href($card['link'] ?? '') }}" style="position: absolute; {{ $place }}; box-sizing: border-box; background: {{ $green ? 'var(--tk-green)' : 'var(--tk-paper)' }}; color: var(--tk-ink); text-decoration: none; display: flex; flex-direction: column">
                            <span aria-hidden="true" style="display: flex; align-items: center; justify-content: space-between">
                                <span style="width: {{ ek_u($v([60, 44])) }}; height: {{ ek_u($v([60, 44])) }}; border-radius: {{ ek_u($v([18, 14])) }}; background: #FFFFFF; display: flex; align-items: center; justify-content: center; box-shadow: inset 0 0 0 {{ ek_u(1) }} #E3E8E0"><svg viewBox="{{ $vb }}" {!! $attrs !!} style="width: {{ ek_u($about ? $v([32, 26]) : $v([30, 24])) }}; height: {{ ek_u($v([30, 24])) }}; overflow: visible">{!! $paths !!}</svg></span>
                                <span style="width: {{ ek_u($v([44, 32])) }}; height: {{ ek_u($v([44, 32])) }}; border-radius: 999px; background: {{ $green ? 'var(--tk-ink)' : '#FFFFFF' }}; color: {{ $green ? '#FFFFFF' : 'var(--tk-ink)' }}; display: flex; align-items: center; justify-content: center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" style="width: {{ ek_u($v([16, 14])) }}; height: {{ ek_u($v([16, 14])) }}">{!! $arrow !!}</svg></span>
                            </span>
                            <span style="margin-top: auto; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u($v([28, 19])) }}, 0.8125rem); letter-spacing: -0.035em">{{ $card['title'] }}</span>
                            <span style="margin-top: {{ ek_u($v([6, 2])) }}; font-size: max({{ ek_u($v([16.5, 13])) }}, 0.8125rem); line-height: {{ $v([1.45, 1.35]) }}; color: {{ $green ? 'var(--tk-dark-4)' : 'var(--tk-muted)' }}">{{ $mob ? (($card['text_short'] ?? '') ?: ($card['text'] ?? '')) : ($card['text'] ?? '') }}</span>
                        </a>
                    @endforeach

                    <div role="search" data-m="nf-search" data-finder hidden
                         data-one="{{ $d['count_one'] }}" data-many="{{ $d['count_many'] }}" data-none="{{ $d['count_none'] }}"
                         data-kind-page="{{ $d['kind_page'] }}" data-kind-service="{{ $d['kind_service'] }}" data-kind-article="{{ $d['kind_article'] }}"
                         style="position: absolute; left: {{ ek_u($v([864, 24])) }}; top: {{ ek_u($v([150, 210])) }}; width: {{ ek_u($v([480, 342])) }}; z-index: 5">
                        <label class="ek-finder-field" style="height: {{ ek_u($v([64, 56])) }}; box-sizing: border-box; padding: 0 {{ ek_u($v([10, 6])) }} 0 {{ ek_u($v([22, 18])) }}; border-radius: 999px; background: var(--tk-paper); display: flex; align-items: center; gap: {{ ek_u($v([12, 10])) }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#56605A" stroke-width="2.4" stroke-linecap="round" aria-hidden="true" style="width: {{ ek_u($v([20, 18])) }}; height: {{ ek_u($v([20, 18])) }}; flex: none">{!! $glass !!}</svg>
                            <input type="search" aria-label="{{ $d['search_label'] }}" placeholder="{{ $t['placeholder'] }}" autocomplete="off" style="flex: 1; min-width: 0; height: {{ ek_u($v([48, 44])) }}; border: 0; background: none; font-size: max({{ ek_u($v([17, 16])) }}, 0.8125rem); color: var(--tk-ink)">
                            <button type="button" class="ek-press" data-finder-clear hidden aria-label="{{ $d['clear_label'] }}" style="width: {{ ek_u(44) }}; height: {{ ek_u(44) }}; flex: none; border: 0; border-radius: 999px; background: #FFFFFF; cursor: pointer; display: flex; align-items: center; justify-content: center"><svg viewBox="0 0 24 24" fill="none" stroke="#0D1410" stroke-width="2.6" stroke-linecap="round" aria-hidden="true" style="width: {{ ek_u($v([16, 15])) }}; height: {{ ek_u($v([16, 15])) }}"><path d="M6 6l12 12M18 6L6 18"></path></svg></button>
                        </label>
                        <div class="ek-finder-results" data-finder-results hidden style="margin-top: {{ ek_u($v([10, 8])) }}; padding: {{ ek_u($v([10, 8])) }}; border-radius: {{ ek_u($v([22, 20])) }}; background: #FFFFFF; box-shadow: 0 {{ ek_u(30) }} {{ ek_u(60) }} {{ ek_u(-30) }} rgba(13,20,16,0.5), inset 0 0 0 {{ ek_u(1) }} #E3E8E0">
                            <span role="status" data-finder-count style="display: block; padding: {{ $mob ? ek_u(4).' '.ek_u(10).' '.ek_u(6) : ek_u(6).' '.ek_u(12).' '.ek_u(8) }}; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: {{ $mob ? ek_u(11) : 'max('.ek_u(12).', 0.75rem)' }}; letter-spacing: 0.1em; color: var(--tk-muted-2-sm)"></span>
                            <div data-finder-list></div>
                            <span data-finder-none hidden style="display: block; padding: {{ $mob ? ek_u(2).' '.ek_u(10).' '.ek_u(8) : ek_u(4).' '.ek_u(12).' '.ek_u(10) }}; font-size: max({{ ek_u($v([16, 14.5])) }}, 0.8125rem); color: #3B4640">{{ $d['no_hits'] }}</span>
                        </div>
                        <template><a style="display: flex; align-items: center; gap: {{ ek_u($v([12, 10])) }}; padding: {{ ek_u($v([12, 10])) }}; border-radius: {{ ek_u($v([14, 12])) }}; text-decoration: none; color: var(--tk-ink)"><span class="ek-finder-kind" style="flex: none; width: {{ ek_u($v([74, 64])) }}; text-align: center; padding: {{ ek_u($v([4, 3])) }} 0; border-radius: 999px; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: {{ ek_u($v([10, 9])) }}; letter-spacing: 0.08em"></span><span class="ek-finder-title" style="flex: 1; font-size: max({{ ek_u($v([16, 14.5])) }}, 0.8125rem); line-height: 1.3"></span>@unless ($mob)<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: {{ ek_u(16) }}; height: {{ ek_u(16) }}; flex: none">{!! $arrow !!}</svg>@endunless</a></template>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
