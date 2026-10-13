{{--
    Ekdanta · Case Overview. Scroll tracks raise the three columns and draw
    their rules, slide the "what we made" items in, then count the client
    facts up (numbers only). Phones (no design): the columns stack.
--}}
@php
    $d = $section->viewData('desktop');
    $m = $section->viewData('mobile');
    $case = $templateItem ?? null;
    $case = $case instanceof \App\Models\CollectionItem ? $case : \App\Models\CollectionItem::pick('case-studies', $d['case'] ?? null)->first();
    $tick = '<circle cx="12" cy="12" r="11" fill="#2EB45D"></circle><path d="M7 12.5l3.2 3.2L17 9" fill="none" stroke="#0D1410" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"></path>';
@endphp
@if ($case)
@php
    $made = array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $case->get('made')))));
    $facts = array_values(array_filter((array) $case->get('facts', []), fn ($f) => ($f['value'] ?? '') !== ''));
    $logo = media_ref($case->get('logo'))?->url;
@endphp
<div class="ek ek-case-overview" id="{{ $d['anchor'] ?: 'c-overview' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            $columns = [[$t['brief_label'], $case->get('brief'), []], [$t['approach_label'], $case->get('approach'), []], [$t['made_label'], '', $made]];
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            {{-- Phones: the section flows with the page (not pinned), so text at its minimum size is never cut off. --}}
            <div class="ek-stage" data-ek-stage style="background: var(--tk-paper); {{ $mob ? 'height: auto; min-height: 100svh' : '' }}">
                <div class="ek-art" @if ($mob) style="position: relative; left: auto; top: auto; height: auto; margin: 0 auto; padding: {{ ek_u(84) }} {{ ek_u(24) }} {{ ek_u(56) }}; box-sizing: border-box" @endif>
                    <div data-m="co-head" style="{{ $mob ? '' : 'position: absolute; left: '.ek_u(96).'; top: '.ek_u(116).';' }}">
                        <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([14, 12])) }}, {{ $v(['0.8125rem', '0.75rem']) }}); letter-spacing: 0.12em; color: var(--tk-green-text-sm)">{{ $t['eyebrow'] }}</span>
                        <h2 style="margin: {{ ek_u($v([8, 4])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u($v([56, 32])) }}, 0.8125rem); line-height: 1.04; letter-spacing: -0.045em">{{ $t['heading'] }}</h2>
                    </div>

                    <div style="{{ $mob ? 'margin-top: '.ek_u(28).';' : 'position: absolute; left: '.ek_u(96).'; top: '.ek_u(270).'; width: '.ek_u(1248).';' }} display: {{ $mob ? 'flex; flex-direction: column; gap: '.ek_u(18) : 'grid; grid-template-columns: repeat(3, minmax(0, 1fr))' }}">
                        @foreach ($columns as [$label, $text, $items])
                            <div data-m="co-col" style="position: relative; padding: 0 {{ ek_u($v([36, 0])) }} 0 {{ ek_u($v([28, 18])) }}">
                                <span aria-hidden="true" data-m="co-rule" style="position: absolute; left: 0; top: 0; width: {{ ek_u(3) }}; height: {{ $mob ? '100%' : ek_u(260) }}; border-radius: {{ ek_u(2) }}; background: var(--tk-green); transform-origin: top"></span>
                                <h3 style="margin: 0; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u($v([13, 12])) }}, 0.75rem); letter-spacing: 0.1em; color: var(--tk-green-text-sm)">{{ $label }}</h3>
                                @if ($text)<p style="margin: {{ ek_u($v([14, 6])) }} 0 0; font-size: max({{ ek_u($v([20, 15])) }}, 0.8125rem); line-height: {{ $v([1.55, 1.45]) }}; color: #3B4640">{{ $text }}</p>@endif
                                @if ($items)
                                    <ul style="margin: {{ ek_u($v([14, 6])) }} 0 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: {{ ek_u($v([12, 6])) }}">
                                        @foreach ($items as $item)
                                            <li data-m="co-item" style="display: flex; align-items: center; gap: {{ ek_u($v([12, 10])) }}; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([19, 15])) }}, 0.8125rem)"><svg viewBox="0 0 24 24" aria-hidden="true" style="width: {{ ek_u($v([24, 20])) }}; height: {{ ek_u($v([24, 20])) }}; flex-shrink: 0">{!! $tick !!}</svg>{{ $item }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div data-m="co-facts" style="{{ $mob ? 'margin-top: '.ek_u(32).';' : 'position: absolute; left: '.ek_u(96).'; right: '.ek_u(96).'; top: '.ek_u(610).';' }} padding-top: {{ ek_u($v([28, 16])) }}; border-top: {{ ek_u(1) }} solid #DCE3DA; display: flex; align-items: center; flex-wrap: {{ $mob ? 'wrap' : 'nowrap' }}; gap: {{ ek_u($v([56, 20])) }}">
                        <span style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u($v([13, 12])) }}, 0.75rem); letter-spacing: 0.1em; color: var(--tk-muted); width: {{ $mob ? '100%' : ek_u(150) }}">{{ $t['about_label'] }}</span>
                        @foreach ($facts as $fact)
                            <span style="display: flex; flex-direction: column; gap: {{ ek_u(4) }}"><span data-m="co-fact" style="font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u($v([64, 36])) }}, 0.8125rem); line-height: 1; letter-spacing: -0.05em; color: var(--tk-green-deep)">{{ $fact['value'] }}</span><span style="font-size: max({{ ek_u($v([16, 13])) }}, 0.8125rem); color: var(--tk-muted)">{{ $fact['label'] ?? '' }}</span></span>
                        @endforeach
                        @if ($logo)
                            <span style="margin-left: auto; width: {{ ek_u($v([190, 120])) }}; height: {{ ek_u($v([82, 56])) }}; border-radius: {{ ek_u($v([18, 14])) }}; background: #FFFFFF; box-shadow: 0 {{ ek_u(16) }} {{ ek_u(30) }} {{ ek_u(-24) }} rgba(13,20,16,0.5); display: flex; align-items: center; justify-content: center"><img src="{{ $logo }}" alt="{{ str_replace('{name}', $case->title, (string) $d['logo_alt']) }}" style="width: {{ ek_u($v([135, 90])) }}; height: {{ ek_u($v([48, 32])) }}; object-fit: contain; display: block"></span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endif
