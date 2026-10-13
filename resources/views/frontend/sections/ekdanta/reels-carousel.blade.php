{{--
    Ekdanta · Reels Carousel. The index-carousel scene places each phone by
    its distance from the current reel (steps, with a pause on each); the
    tracks fade the heading, captions and the moving wall behind. Drawn on
    the first reel. Reels: Collections → Reels.
--}}
@php
    $d = $section->viewData('desktop');
    $m = $section->viewData('mobile');
    $reels = \App\Models\CollectionItem::pick('reels', $d['reels'] ?? null);
    $n = max(1, $reels->count());
@endphp
<div class="ek ek-reels-carousel" id="{{ $d['anchor'] ?: 'j-reels' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            $cols = $mob ? 3 : 5;
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: var(--tk-ink); color: #FFFFFF">
                <div class="ek-art">
                    <div aria-hidden="true" data-m="reel-wall" style="position: absolute; top: 0; left: 0; width: {{ ek_u($v([1440, 390])) }}; height: {{ ek_u($v([900, 844])) }}; display: flex; gap: {{ ek_u($v([12, 8])) }}; opacity: 0.14">
                        @for ($k = 0; $k < $cols; $k++)
                            <div data-m="{{ $k % 2 ? 'reel-wall-down' : 'reel-wall-up' }}" style="width: {{ ek_u($v([278, 125])) }}; flex-shrink: 0; display: flex; flex-direction: column; gap: {{ ek_u($v([12, 8])) }}; transform: translateY({{ ek_u($v([-330, -300])) }})">
                                @for ($j = 0; $j < 3; $j++)
                                    <img loading="lazy" decoding="async" src="{{ media_ref($reels->get((3 * $k + $j) % $n)?->get('cover'))?->web_url }}" alt="" loading="lazy" style="width: {{ ek_u($v([278, 125])) }}; height: {{ ek_u($v([494, 222])) }}; object-fit: cover; display: block; border-radius: {{ ek_u($v([18, 12])) }}">
                                @endfor
                            </div>
                        @endfor
                    </div>

                    <div data-m="reel-head" style="position: absolute; left: {{ $mob ? ek_u(20) : 0 }}; right: {{ $mob ? ek_u(20) : 0 }}; top: {{ ek_u($v([92, 86])) }}; text-align: center">
                        <h2 style="margin: 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u($v([64, 32])) }}; line-height: {{ $v([1.05, 1.06]) }}; letter-spacing: -0.045em">{{ $t['heading'] }}</h2>
                        <p style="margin: {{ ek_u($v([10, 8])) }} 0 0; font-size: max({{ ek_u($v([19, 15])) }}, 0.8125rem); {{ $mob ? 'line-height: 1.45;' : '' }} color: #BFD2C6">{{ $t['text'] }}</p>
                    </div>

                    <ol data-m="reel-cards" style="position: absolute; inset: 0; margin: 0; padding: 0; list-style: none; perspective: {{ ek_u($v([1400, 1000])) }}">
                        @foreach ($reels as $i => $reel)
                            <li @if ($i === 0) class="is-active" @endif style="position: absolute; left: {{ ek_u($v([575, 95])) }}; top: {{ ek_u($v([262, 226])) }}; width: {{ ek_u($v([290, 200])) }}; height: {{ ek_u($v([516, 356])) }}; border-radius: {{ ek_u($v([26, 20])) }}; transform: translate({{ ek_u($i * $v([300, 205])) }}, {{ ek_u(min($i, 2) * $v([14, 12])) }}) scale({{ 1 - min($i, 2) * 0.16 }}); opacity: {{ $i < 2 ? 1 : 0 }}; z-index: {{ 20 - $i * 4 }}">
                                <a href="{{ link_href($reel->get('instagram_url') ?: $t['link']) }}" target="_blank" rel="noopener"
                                   aria-label="{{ str_replace(['{title}', '{brand}'], [$reel->title, (string) $reel->get('brand')], (string) $t['reel_label']) }}"
                                   style="position: relative; display: block; width: 100%; height: 100%; border-radius: inherit; overflow: hidden; background: #1E2622; text-decoration: none">
                                    <img loading="lazy" decoding="async" src="{{ media_ref($reel->get('cover'))?->web_url }}" alt="" loading="lazy" style="width: 100%; height: 100%; object-fit: cover; display: block">
                                    <span style="position: absolute; top: {{ ek_u($v([14, 10])) }}; left: {{ ek_u($v([14, 10])) }}; padding: {{ ek_u($v([5, 4])) }} {{ ek_u($v([10, 8])) }}; border-radius: 999px; background: rgba(13,20,16,0.8); font-family: 'Poppins', sans-serif; font-weight: 600; font-size: {{ $mob ? ek_u(10) : 'max('.ek_u(12).', 0.75rem)' }}; color: #FFFFFF">{{ $reel->get('tag') }}</span>
                                    <span aria-hidden="true" style="position: absolute; right: {{ ek_u($v([14, 10])) }}; bottom: {{ ek_u($v([14, 10])) }}; width: {{ ek_u($v([40, 34])) }}; height: {{ ek_u($v([40, 34])) }}; border-radius: 50%; background: rgba(13,20,16,0.8); display: flex; align-items: center; justify-content: center"><svg viewBox="0 0 24 24" style="width: {{ ek_u($v([16, 14])) }}; height: {{ ek_u($v([16, 14])) }}"><path d="M8 5v14l11-7z" fill="#FFFFFF"></path></svg></span>
                                </a>
                            </li>
                        @endforeach
                    </ol>

                    <div data-m="reel-late" style="position: absolute; left: 0; right: 0; top: {{ ek_u($v([800, 612])) }}; height: {{ ek_u($v([64, 60])) }}">
                        <div data-m="reel-caps">
                            @foreach ($reels as $i => $reel)
                                <div style="position: absolute; left: {{ $mob ? ek_u(24) : 0 }}; right: {{ $mob ? ek_u(24) : 0 }}; top: 0; display: flex; flex-direction: column; align-items: center; gap: {{ ek_u(2) }}; {{ $mob ? 'text-align: center;' : '' }} opacity: {{ $i === 0 ? 1 : 0 }}">
                                    <span style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u($v([22, 19])) }}, 0.8125rem); letter-spacing: -0.02em">{{ $reel->get('brand') }}</span>
                                    <span style="font-size: max({{ ek_u($v([17, 15])) }}, 0.8125rem); color: #BFD2C6">{{ $reel->title }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @php $count = '<span data-m="reel-count">01</span><span style="color: var(--tk-muted)"> / '.str_pad((string) $reels->count(), 2, '0', STR_PAD_LEFT).'</span>'; @endphp
                    @if ($mob)
                        <div data-m="reel-late" style="position: absolute; left: {{ ek_u(24) }}; right: {{ ek_u(24) }}; top: {{ ek_u(720) }}; display: flex; align-items: center; justify-content: space-between">
                            <span aria-hidden="true" style="font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u(24) }}, 0.8125rem); letter-spacing: -0.03em">{!! $count !!}</span>
                            <a href="{{ link_href($t['link']) }}" target="_blank" rel="noopener" style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u(14) }}, 0.8125rem); color: var(--tk-green)">{{ $t['link_label'] }}</a>
                        </div>
                    @else
                        <span aria-hidden="true" data-m="reel-late" style="position: absolute; left: {{ ek_u(96) }}; top: {{ ek_u(812) }}; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u(28) }}; letter-spacing: -0.03em">{!! $count !!}</span>
                        <a href="{{ link_href($t['link']) }}" target="_blank" rel="noopener" data-m="reel-late" style="position: absolute; right: {{ ek_u(96) }}; top: {{ ek_u(816) }}; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u(16) }}, 0.8125rem); color: var(--tk-green)">{{ $t['link_label'] }}</a>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
