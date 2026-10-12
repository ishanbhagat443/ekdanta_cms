{{--
    Ekdanta · Case Gallery Zoom. Four scroll tracks move the grid picture
    through the design's frames (--px, --py, --ps, design px); --gp (0–3)
    lights the item being shown and swaps the label. Drawn at the end: the
    whole grid. Phones (no design): items above, the frame drawn smaller.
--}}
@php
    $d = $section->viewData('desktop');
    $m = $section->viewData('mobile');
    $case = $templateItem ?? null;
    $case = $case instanceof \App\Models\CollectionItem ? $case : \App\Models\CollectionItem::pick('case-studies', $d['case'] ?? null)->first();
    $glass = '<circle cx="11" cy="11" r="7"></circle><path d="M20 20l-4-4"></path>';
@endphp
@if ($case)
@php
    $made = array_slice(array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $case->get('made'))))), 0, 3);
    $img = media_ref($case->get('image'))?->url;
@endphp
<div class="ek ek-case-gallery" id="{{ $d['anchor'] ?: 'c-gallery' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: var(--tk-ink); color: #FFFFFF">
                <div class="ek-art" data-m="cg-progress-box">
                    <div data-m="cg-head" style="position: absolute; left: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([150, 84])) }}; width: {{ ek_u($v([430, 342])) }}">
                        <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([14, 12])) }}, {{ $v(['0.8125rem', '0.75rem']) }}); letter-spacing: 0.12em; color: var(--tk-mint)">{{ $t['eyebrow'] }}</span>
                        <h2 style="margin: {{ ek_u($v([8, 4])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u($v([54, 32])) }}, 0.8125rem); line-height: 1.04; letter-spacing: -0.045em">{{ $t['heading'] }}</h2>
                        <p style="margin: {{ ek_u($v([14, 8])) }} 0 0; font-size: max({{ ek_u($v([19, 15])) }}, 0.8125rem); line-height: 1.5; color: #BFD2C6">{{ $t['text'] }}</p>
                        <ol style="margin: {{ ek_u($v([30, 16])) }} 0 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: {{ ek_u(6) }}">
                            @foreach ($made as $j => $item)
                                <li class="ek-gallery-item" style="--j: {{ $j }}; display: flex; align-items: center; gap: {{ ek_u($v([14, 12])) }}; height: {{ ek_u($v([54, 44])) }}; padding: 0 {{ ek_u($v([18, 14])) }}; border-radius: {{ ek_u($v([16, 12])) }}; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([18, 15])) }}, 0.8125rem)"><span class="ek-gallery-num" style="font-weight: 700; font-size: max({{ ek_u(13) }}, 0.8125rem)">{{ str_pad((string) ($j + 1), 2, '0', STR_PAD_LEFT) }}</span>{{ $item }}</li>
                            @endforeach
                        </ol>
                    </div>

                    <div data-m="cg-frame" style="position: absolute; left: {{ ek_u($v([600, 24])) }}; top: {{ ek_u($v([120, 428])) }}; width: {{ ek_u($v([744, 342])) }}; height: {{ ek_u($v([660, 303])) }}">
                        <div style="position: absolute; left: 0; top: 0; width: {{ ek_u(744) }}; height: {{ ek_u(660) }}; border-radius: {{ ek_u(28) }}; overflow: hidden; background: #FFFFFF; box-shadow: 0 {{ ek_u(50) }} {{ ek_u(90) }} {{ ek_u(-40) }} rgba(0,0,0,0.8); {{ $mob ? 'transform: scale(0.4597); transform-origin: 0 0' : '' }}">
                            @if ($img)<img data-m="cg-pan" class="ek-gallery-img" src="{{ $img }}" alt="{{ str_replace('{name}', $case->title, (string) $d['image_alt']) }}" loading="lazy" style="position: absolute; left: 0; top: 0; width: {{ ek_u(857) }}; height: {{ ek_u(940) }}; max-width: none; object-fit: cover; object-position: top; display: block; transform-origin: 0 0">@endif
                            <span aria-hidden="true" style="position: absolute; left: {{ ek_u(18) }}; top: {{ ek_u(18) }}; display: flex; align-items: center; gap: {{ ek_u(8) }}; padding: {{ ek_u(8) }} {{ ek_u(14) }}; border-radius: 999px; background: rgba(13,20,16,0.85); font-family: 'Poppins', sans-serif; font-weight: 600; font-size: {{ $mob ? ek_u(28) : 'max('.ek_u(13).', 0.8125rem)' }}"><svg viewBox="0 0 24 24" fill="none" stroke="#8FE3AE" stroke-width="2.6" stroke-linecap="round" style="width: {{ ek_u($v([14, 28])) }}; height: {{ ek_u($v([14, 28])) }}">{!! $glass !!}</svg><span class="ek-gallery-label"><span class="ek-gallery-zoom">{{ $d['zoom_label'] }}</span><span class="ek-gallery-full">{{ $d['full_label'] }}</span></span></span>
                        </div>
                    </div>
                    <div aria-hidden="true" data-m="cg-bar-box" style="position: absolute; left: {{ ek_u($v([600, 24])) }}; top: {{ ek_u($v([806, 748])) }}; width: {{ ek_u($v([744, 342])) }}; height: {{ ek_u(4) }}; border-radius: {{ ek_u(2) }}; background: rgba(255,255,255,0.14); overflow: hidden"><div data-m="cg-bar" style="height: 100%; background: var(--tk-orange); transform-origin: 0 50%"></div></div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endif
