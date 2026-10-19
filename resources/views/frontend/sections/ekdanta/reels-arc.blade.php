{{--
    Ekdanta · Reels Arc. Scroll tracks raise the phones into the arc; the
    phones are also the engine's steps (the counter, aria-current, focus).
    The spotlight follows --af and --focus (scroll tracks) in CSS
    (_reels-arc.scss). Phones (no design): a smaller, tighter arc.
--}}
@php
    $d = $section->viewData('desktop');
    $m = $section->viewData('mobile');
    $reels = \App\Models\CollectionItem::pick('reels', $d['reels'] ?? null)->take(5)->values();
    $n = $reels->count();
    $play = '<path d="M7 4v16l13-8z" fill="#FFFFFF"></path>';
@endphp
<div class="ek ek-reels-arc" id="{{ $d['anchor'] ?: 'w-reels' }}">
    @foreach ($section->comps($d, $m) as $comp => $t)
        @php
            $mob = $comp !== 'desktop';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            $k = $mob ? 0.6 : 1; // phones are drawn at desktop size, scaled on phones
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: var(--tk-ink); color: #FFFFFF">
                <div class="ek-art">
                    <div aria-hidden="true" data-m="rl-glow" style="position: absolute; left: {{ ek_x($v([220, -100])) }}; top: {{ ek_u($v([260, 260])) }}; width: {{ ek_x($v([1000, 590])) }}; height: {{ ek_u($v([700, 500])) }}; border-radius: 999px; background: radial-gradient(ellipse, rgba(46,180,93,0.22), rgba(46,180,93,0) 65%)"></div>
                    <div data-m="rl-head" style="position: absolute; left: {{ ek_x($v([0, 24])) }}; right: {{ ek_x($v([0, 24])) }}; top: {{ ek_u($v([104, 84])) }}; text-align: center">
                        <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([14, 12])) }}, {{ $v(['0.8125rem', '0.75rem']) }}); letter-spacing: 0.12em; color: var(--tk-mint)">{{ $t['eyebrow'] }}</span>
                        <h2 style="margin: {{ ek_u($v([8, 4])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u($v([60, 34])) }}, 0.8125rem); line-height: 1.04; letter-spacing: -0.045em">{{ $t['heading'] }}</h2>
                        <p style="margin: {{ ek_u($v([10, 8])) }} 0 0; font-size: max({{ ek_u($v([19, 15])) }}, 0.8125rem); color: #BFD2C6">{{ $t['text'] }}</p>
                    </div>

                    <div data-m="rl-phones" style="position: absolute; inset: 0">
                        @foreach ($reels as $i => $reel)
                            @php
                                $c = $i - ($n - 1) / 2;
                                $x = $mob ? 195 + $c * 66 - 66 : 720 + $c * 250 - 110;
                                $y = $mob ? 306 + $c * $c * 10 : 330 + $c * $c * 14;
                                $img = media_ref($reel->get('cover'))?->web_url;
                                $href = $reel->get('instagram_url') ?: ($d['link'] ?? '');
                                $name = str_replace(['{title}', '{brand}'], [$reel->title, (string) $reel->get('brand')], (string) $d['reel_label']);
                            @endphp
                            <a data-m="rl-phone" class="ek-reel" href="{{ link_href($href) }}" @if (\App\Support\Link::external($href)) target="_blank" rel="noopener" @endif aria-label="{{ $name }}" style="--i: {{ $i }}; position: absolute; left: {{ ek_x($x) }}; top: {{ ek_u($y) }}; width: {{ ek_x(220 * $k) }}; height: {{ ek_u(400 * $k) }}; text-decoration: none; color: #FFFFFF">
                                <span class="ek-reel-spot" style="position: absolute; left: 0; top: 0; width: {{ ek_x(220) }}; height: {{ ek_u(400) }}; transform-origin: 0 0; --tilt: {{ $c * 4 }}deg; --k: {{ $k }}">
                                    <span data-m="rl-bob" style="position: absolute; inset: 0">
                                        <span class="ek-reel-frame" style="position: absolute; inset: 0; box-sizing: border-box; border-radius: {{ ek_u(32) }}; border: {{ ek_u(7) }} solid #1E2622; overflow: hidden; background: #1E2622">
                                            @if ($img)<img class="ek-reel-img" src="{{ $img }}" alt="" loading="lazy" style="position: absolute; left: 0; top: 0; width: 100%; height: 100%; object-fit: cover; display: block">@endif
                                            <span style="position: absolute; left: 0; right: 0; bottom: 0; height: {{ ek_u(150) }}; background: linear-gradient(0deg, rgba(13,20,16,0.92), rgba(13,20,16,0))"></span>
                                            <span aria-hidden="true" style="position: absolute; left: {{ ek_u(12) }}; top: {{ ek_u(12) }}; padding: {{ ek_u(4) }} {{ ek_u(9) }}; border-radius: 999px; background: rgba(13,20,16,0.8); font-family: 'Poppins', sans-serif; font-weight: 600; font-size: {{ ek_u(10.5) }}">{{ $reel->get('tag') }}</span>
                                            <span aria-hidden="true" style="position: absolute; left: {{ ek_u(14) }}; right: {{ ek_u(14) }}; bottom: {{ ek_u(14) }}; display: flex; flex-direction: column; gap: {{ ek_u(2) }}"><span style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: {{ $mob ? ek_u(22) : 'max('.ek_u(15).', 0.8125rem)' }}">{{ $reel->get('brand') }}</span><span style="font-size: {{ $mob ? ek_u(22) : 'max('.ek_u(13).', 0.8125rem)' }}; line-height: 1.3; color: #DCE7E0">{{ $reel->title }}</span></span>
                                            <span aria-hidden="true" class="ek-reel-play" style="position: absolute; left: {{ ek_x(88) }}; top: {{ ek_u(152) }}; width: {{ ek_u(44) }}; height: {{ ek_u(44) }}; border-radius: 999px; background: rgba(13,20,16,0.75); display: flex; align-items: center; justify-content: center"><span data-m="rl-pulse" style="position: absolute; inset: 0; border-radius: 999px"></span><svg viewBox="0 0 24 24" style="width: {{ ek_u(16) }}; height: {{ ek_u(16) }}">{!! $play !!}</svg></span>
                                        </span>
                                    </span>
                                </span>
                            </a>
                        @endforeach
                    </div>

                    <div data-m="rl-caption" style="position: absolute; left: {{ ek_x($v([96, 24])) }}; right: {{ ek_x($v([96, 24])) }}; top: {{ ek_u($v([812, 744])) }}; display: flex; justify-content: space-between; align-items: center; gap: {{ ek_u(16) }}">
                        <span style="font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u($v([26, 22])) }}, 0.8125rem); letter-spacing: -0.03em; white-space: nowrap"><span data-ek-step-num="2">01</span><span style="color: var(--tk-suffix)"> / {{ str_pad((string) $n, 2, '0', STR_PAD_LEFT) }}</span></span>
                        <a href="{{ link_href($d['link'] ?? '') }}" @if (\App\Support\Link::external($d['link'] ?? '')) target="_blank" rel="noopener" @endif style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([16, 14])) }}, 0.8125rem); color: var(--tk-green); text-align: right">{{ $t['link_label'] }}</a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
