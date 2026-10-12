{{--
    Ekdanta · Next Case. Scroll tracks slide the next case's name in, fade
    its story and buttons in and raise its grid. Phones (no design): text,
    stacked buttons, then a smaller tilted grid.
--}}
@php
    $d = $section->dataFor('desktop');
    $m = $section->dataFor('mobile');
    $all = \App\Models\CollectionItem::query()->of('case-studies')->published()->get();
    $case = $templateItem ?? null;
    $case = $case instanceof \App\Models\CollectionItem ? $case : (\App\Models\CollectionItem::pick('case-studies', $d['case'] ?? null)->first() ?? $all->first());
    $next = null;
    if ($case && $all->count() > 1) {
        $next = $all->firstWhere('slug', (string) $case->get('next')) ?? $all[($all->search(fn ($c) => $c->id === $case->id) + 1) % $all->count()];
    }
    $arrow = '<path d="M5 12h14M13 6l6 6-6 6"></path>';
@endphp
@if ($next)
@php
    $at = $all->search(fn ($c) => $c->id === $next->id);
    $num = fn (int $i) => str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT);
    $hex = ltrim((string) $next->get('colour'), '#');
    $bg = strlen($hex) === 6 ? '#'.$hex : 'var(--tk-ink)';
    $img = media_ref($next->get('image'))?->url;
    $len = mb_strlen($next->title);
    $size = $len <= 11 ? [120, 56] : ($len <= 16 ? [100, 48] : [84, 40]);
@endphp
<div class="ek ek-next-case" id="{{ $d['anchor'] ?: 'c-next' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            $button = 'height: '.ek_u($v([56, 48])).'; box-sizing: border-box; display: inline-flex; align-items: center; border-radius: 999px; font-family: \'Poppins\', sans-serif; font-weight: 600; font-size: max('.ek_u($v([16, 15])).', 0.8125rem); text-decoration: none'.($mob ? '; justify-content: center' : '');
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: {{ $bg }}; color: #FFFFFF">
                <div class="ek-art">
                    <p data-m="nc-eyebrow" style="position: absolute; left: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([150, 84])) }}; margin: 0; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([14, 12])) }}, {{ $v(['0.8125rem', '0.75rem']) }}); letter-spacing: 0.12em; color: rgba(255,255,255,0.7)">{{ str_replace(['{n}', '{total}'], [$num($at), $num($all->count() - 1)], $t['eyebrow']) }}</p>
                    <h2 data-m="nc-name" style="position: absolute; left: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([190, 112])) }}; width: {{ ek_u($v([760, 342])) }}; margin: 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u($v($size)) }}, 0.8125rem); line-height: 0.98; letter-spacing: -0.05em">{{ $next->title }}</h2>
                    <div data-m="nc-story" style="position: absolute; left: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([470, 236])) }}; width: {{ ek_u($v([560, 342])) }}">
                        <p style="margin: 0; font-size: max({{ ek_u($v([20, 15])) }}, 0.8125rem); line-height: {{ $v([1.55, 1.5]) }}; color: rgba(255,255,255,0.85)">{{ $next->get('description') }}</p>
                    </div>
                    <div data-m="nc-story" style="position: absolute; left: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([640, 350])) }}; {{ $mob ? 'width: '.ek_u(342).'; flex-direction: column;' : '' }} display: flex; gap: {{ ek_u($v([12, 8])) }}">
                            <a class="ek-press" href="{{ $next->url() }}" style="{{ $button }}; padding: 0 {{ ek_u($v([26, 20])) }}; gap: {{ ek_u(10) }}; background: #FFFFFF; color: var(--tk-ink)">{{ $t['button_label'] }}<span class="visually-hidden">: {{ $next->title }}</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: {{ ek_u(18) }}; height: {{ ek_u(18) }}">{!! $arrow !!}</svg></a>
                            <a class="ek-press" href="{{ link_href($d['button_2_link'] ?? '') }}" style="{{ $button }}; padding: 0 {{ ek_u($v([24, 20])) }}; border: {{ ek_u(1.5) }} solid rgba(255,255,255,0.7)">{{ $t['button_2_label'] }}</a>
                    </div>
                    <div data-m="nc-grid" style="position: absolute; left: {{ ek_u($v([900, 80])) }}; top: {{ ek_u($v([140, 478])) }}; width: {{ ek_u($v([440, 230])) }}; height: {{ ek_u($v([600, 314])) }}; border-radius: {{ ek_u($v([28, 20])) }}; overflow: hidden; background: #FFFFFF; box-shadow: 0 {{ ek_u(60) }} {{ ek_u(100) }} {{ ek_u(-40) }} rgba(0,0,0,0.7); transform: rotate(-3deg)">
                        @if ($img)<img src="{{ $img }}" alt="{{ str_replace('{name}', $next->title, (string) $d['image_alt']) }}" loading="lazy" style="width: 100%; height: 100%; object-fit: cover; object-position: top; display: block">@endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endif
