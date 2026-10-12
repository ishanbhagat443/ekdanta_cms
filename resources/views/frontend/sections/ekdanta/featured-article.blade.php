{{--
    Ekdanta · Featured Article. Scroll tracks open the card (--curtain,
    0.5 → 0 of each side), settle and drift the cover, then bring the text
    in. Phones (no design): cover above, text below.
--}}
@php
    $d = $section->viewData('desktop');
    $m = $section->viewData('mobile');
    $post = \App\Models\Post::query()->live()->with(['categories', 'featuredMedia', 'author'])->orderByDesc('is_featured')->latest('published_at')->first();
    $mark = media_ref($d['mark'] ?? null)?->url;
    $arrow = '<path d="M5 12h14M13 6l6 6-6 6"></path>';
@endphp
@if ($post)
@php
    $meta = str_replace(['{author}', '{minutes}'], [$post->displayAuthor(), $post->readingTimeMinutes()], (string) $d['meta']);
    $cover = $post->featuredMedia;
@endphp
<div class="ek ek-featured-article" id="{{ $d['anchor'] ?: 'b-feat' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: var(--tk-paper)">
                <div class="ek-art">
                    <div data-m="fa-head" style="position: absolute; left: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([112, 84])) }}">
                        <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([14, 12])) }}, {{ $v(['0.8125rem', '0.75rem']) }}); letter-spacing: 0.12em; color: var(--tk-green-text-sm)">{{ $t['eyebrow'] }}</span>
                        <h2 style="margin: {{ ek_u($v([8, 4])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u($v([48, 30])) }}, 0.8125rem); line-height: 1.04; letter-spacing: -0.045em">{{ $t['heading'] }}</h2>
                    </div>
                    <article data-m="fa-card" class="ek-fa-card" style="position: absolute; left: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([250, 184])) }}; width: {{ ek_u($v([1248, 342])) }}; height: {{ ek_u($v([560, 600])) }}; border-radius: {{ ek_u($v([30, 24])) }}; overflow: hidden; background: var(--tk-ink); color: #FFFFFF; box-shadow: 0 {{ ek_u(50) }} {{ ek_u(90) }} {{ ek_u(-50) }} rgba(13,20,16,0.7)">
                        <div style="position: absolute; left: 0; top: 0; {{ $mob ? 'right: 0; height: '.ek_u(220) : 'width: '.ek_u(640).'; height: '.ek_u(560) }}; overflow: hidden; background: #FFFFFF">
                            @if ($cover)<img data-m="fa-cover" src="{{ $cover->url }}" alt="{{ $cover->alt ?: $post->title }}" loading="lazy" style="width: 100%; height: {{ ek_u($v([700, 300])) }}; object-fit: cover; object-position: top; display: block; transform-origin: 50% 0">@endif
                        </div>
                        <div style="position: absolute; left: {{ ek_u($v([700, 20])) }}; top: {{ ek_u($v([60, 240])) }}; width: {{ ek_u($v([500, 302])) }}; display: flex; flex-direction: column; gap: {{ ek_u($v([18, 10])) }}">
                            @if ($category = $post->categories->first())<span data-m="fa-chip" style="align-self: flex-start; padding: {{ ek_u($v([6, 5])) }} {{ ek_u($v([12, 10])) }}; border-radius: 999px; background: var(--tk-green); font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u($v([12, 11])) }}, 0.75rem); letter-spacing: 0.06em; color: var(--tk-ink)">{{ mb_strtoupper($category->name) }}</span>@endif
                            <h3 data-m="fa-text" style="margin: 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u($v([38, 24])) }}, 0.8125rem); line-height: 1.1; letter-spacing: -0.04em">{{ $post->title }}</h3>
                            <p data-m="fa-text" style="margin: 0; font-size: max({{ ek_u($v([19, 15])) }}, 0.8125rem); line-height: 1.55; color: #BFD2C6">{{ $post->autoExcerpt() }}</p>
                            <span data-m="fa-meta" style="display: flex; align-items: center; gap: {{ ek_u(12) }}; font-size: max({{ ek_u($v([15, 13])) }}, 0.8125rem); color: #9FC7AE">@if ($mark)<span style="width: {{ ek_u(36) }}; height: {{ ek_u(36) }}; border-radius: 999px; background: #FFFFFF; display: flex; align-items: center; justify-content: center; flex: none"><img src="{{ $mark }}" alt="" style="width: {{ ek_u(22) }}; height: auto"></span>@endif{{ $meta }}</span>
                            <a data-m="fa-meta" class="ek-press" href="{{ route('front.blog.show', $post->slug) }}" style="align-self: flex-start; margin-top: {{ ek_u($v([8, 2])) }}; height: {{ ek_u($v([52, 44])) }}; box-sizing: border-box; padding: 0 {{ ek_u($v([22, 18])) }}; display: inline-flex; align-items: center; gap: {{ ek_u(10) }}; background: #FFFFFF; color: var(--tk-ink); border-radius: 999px; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([15, 14])) }}, 0.8125rem); text-decoration: none">{{ $t['button_label'] }}<span class="visually-hidden">: {{ $post->title }}</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: {{ ek_u(16) }}; height: {{ ek_u(16) }}">{!! $arrow !!}</svg></a>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endif
