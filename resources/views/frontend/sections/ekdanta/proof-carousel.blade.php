{{--
    Ekdanta · Proof Carousel. The cards are the steps; the track's place and
    each card's tilt, dip, size and fade follow the steps' index (--ek-f) in
    CSS (_proof-carousel.scss). Drawn at the start (first card in front).
--}}
@php
    $d = $section->dataFor('desktop');
    $m = $section->dataFor('mobile');
    $u = fn ($n) => "calc({$n} * var(--u))";
    $cases = \App\Models\CollectionItem::pick('case-studies', $d['cases'] ?? null)->values();
    $lines = fn ($text) => array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $text)), fn ($l) => $l !== ''));
@endphp
<div class="ek ek-proof-carousel" id="{{ $d['anchor'] ?: 's-work' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            $link = '<a class="ek-press" href="'.e(link_href($d['link'] ?? '/work')).'" style="height: '.$u($v([48, 50])).'; box-sizing: border-box; padding: 0 '.$u(20).'; display: '.($mob ? 'flex; justify-content: center' : 'inline-flex').'; align-items: center; gap: '.$u(8).'; border-radius: 999px; box-shadow: inset 0 0 0 '.$u(1.5).' rgba(255,255,255,0.5); font-family: \'Poppins\', sans-serif; font-weight: 600; font-size: max('.$u($v([15, 14.5])).', 0.8125rem); text-decoration: none">'.e($t['link_label']).'</a>';
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: var(--tk-ink); color: #FFFFFF">
                <div class="ek-art">
                    <div data-m="proof-head" style="position: absolute; left: {{ $u($v([96, 24])) }}; top: {{ $u($v([112, 82])) }}; {{ $mob ? 'width: '.$u(342) : '' }}">
                        <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ $u($v([14, 12])) }}, 0.75rem); letter-spacing: 0.12em; color: var(--tk-mint)">{{ $t['eyebrow'] }}</span>
                        <h2 style="margin: {{ $u($v([8, 4])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ $u($v([56, 32])) }}; line-height: 1.04; letter-spacing: -0.045em">{{ $t['heading'] }}</h2>
                        <p style="margin: {{ $u($v([10, 6])) }} 0 0; font-size: max({{ $u($v([19, 14.5])) }}, 0.8125rem); color: #BFD2C6">{{ $t['text'] }}</p>
                    </div>
                    @unless ($mob)
                        <div data-m="proof-ui" style="position: absolute; right: {{ $u(96) }}; top: {{ $u(150) }}">{!! $link !!}</div>
                    @endunless

                    <div data-m="proof-track" class="ek-proof-track" style="position: absolute; top: {{ $u($v([272, 214])) }}; perspective: {{ $u($v([1600, 1200])) }}">
                        <ol data-m="proof-cards" style="margin: 0; padding: 0; list-style: none; display: flex; gap: {{ $u($v([28, 16])) }}">
                            @foreach ($cases as $i => $case)
                                <li class="ek-proof-card {{ $i === 0 ? 'is-active' : '' }}" style="--i: {{ $i }}; flex-shrink: 0; width: {{ $u($v([360, 250])) }}; height: {{ $u($v([500, 420])) }}; border-radius: {{ $u($v([24, 22])) }}; overflow: hidden; background: #18231E; box-shadow: 0 {{ $u($v([30, 24])) }} {{ $u($v([60, 50])) }} {{ $u($v([-30, -24])) }} rgba(0,0,0,0.8); display: flex; flex-direction: column">
                                    <div style="height: {{ $u($v([290, 230])) }}; overflow: hidden; background: #FFFFFF">@if ($img = media_ref($case->get('image')))<img src="{{ $img->url }}" alt="{{ str_replace('{name}', $case->title, $d['image_alt']) }}" loading="lazy" style="width: 100%; height: 100%; object-fit: cover; object-position: top; display: block">@endif</div>
                                    <div style="padding: {{ $u($v([18, 14])) }} {{ $u($v([22, 16])) }}; display: flex; flex-direction: column; gap: {{ $u($v([10, 8])) }}">
                                        <span style="align-self: flex-start; padding: {{ $u($v([5, 4])) }} {{ $u($v([10, 9])) }}; border-radius: 999px; background: var(--tk-green); color: var(--tk-ink); font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ $u($v([11, 10])) }}, 0.6875rem); letter-spacing: 0.06em">{{ $case->get('sector') }}</span>
                                        <h3 style="margin: 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ $u($v([25, 20])) }}, 0.8125rem); line-height: 1.1; letter-spacing: -0.03em">{{ $case->title }}</h3>
                                        <ul style="margin: 0; padding: 0; list-style: none; display: flex; flex-wrap: wrap; gap: {{ $u($v([6, 5])) }}">
                                            @foreach ($lines($case->get('made')) as $made)
                                                <li style="padding: {{ $u($v([5, 4])) }} {{ $u($v([10, 9])) }}; border-radius: 999px; box-shadow: inset 0 0 0 {{ $u(1) }} rgba(255,255,255,0.3); font-size: max({{ $u($v([13.5, 12])) }}, {{ $mob ? '0.75rem' : '0.8125rem' }}); color: #DCE7E0">{{ $made }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </div>

                    <div data-m="proof-ui" style="position: absolute; left: {{ $u($v([96, 24])) }}; {{ $mob ? 'right: '.$u(24).'; top: '.$u(674) : 'top: '.$u(812) }}; display: flex; align-items: center; gap: {{ $u($v([18, 14])) }}">
                        <span aria-hidden="true" style="font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ $u($v([26, 22])) }}, 0.8125rem); letter-spacing: -0.03em"><span data-ek-step-num="2">01</span><span style="color: var(--tk-muted)"> / {{ sprintf('%02d', $cases->count()) }}</span></span>
                        <span aria-hidden="true" style="{{ $mob ? 'flex: 1;' : 'width: '.$u(300).';' }} height: {{ $u(4) }}; border-radius: {{ $u(2) }}; background: rgba(255,255,255,0.15)"><span data-m="proof-bar" style="display: block; height: 100%; border-radius: {{ $u(2) }}; background: var(--tk-green); transform-origin: 0 50%"></span></span>
                    </div>
                    @if ($mob)
                        <div data-m="proof-ui" style="position: absolute; left: {{ $u(24) }}; right: {{ $u(24) }}; top: {{ $u(724) }}">{!! $link !!}</div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
