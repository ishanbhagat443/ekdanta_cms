{{--
    Ekdanta · Article Hero (Typewriter). The article comes from the template
    page ($templateItem), else the newest one. Its title is typed out letter
    by letter (load tracks on aria-hidden letters; the heading's name is the
    title), the first of the green words found in it gets the swoosh, then
    the cover opens: the article's cover image, or the drawing. Scrolling
    fades the text and moves the drawing's layers at different speeds.
    Flows with the page; phones (no design): the same, smaller.
--}}
@php
    $d = $section->viewData('desktop');
    $m = $section->viewData('mobile');
    $post = ($templateItem ?? null) instanceof \App\Models\Post ? $templateItem : \App\Models\Post::query()->live()->latest('published_at')->first();
@endphp
@if ($post)
@php
    $post->loadMissing(['categories', 'featuredMedia']);
    $lines = fn ($v) => array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $v))));
    $title = $post->title;
    $chars = mb_str_split($title);
    // The green word: the first listed word found in the title, as a whole word.
    [$hlFrom, $hlTo] = [-1, -1];
    foreach ($lines($d['highlight'] ?? '') as $word) {
        if (preg_match('/(?<![\p{L}\p{N}])'.preg_quote($word, '/').'(?![\p{L}\p{N}])/iu', $title, $hit, PREG_OFFSET_CAPTURE)) {
            $hlFrom = mb_strlen(substr($title, 0, $hit[0][1]));
            $hlTo = $hlFrom + mb_strlen($hit[0][0]);
            break;
        }
    }
    // Each letter twice over: the outer one appears, the inner one carries the caret.
    $typed = '';
    foreach ($chars as $i => $ch) {
        $typed .= ($i === $hlFrom ? '<span style="position: relative; color: var(--tk-green-text)">' : '')
            .'<span data-m="ah-ch"><span data-m="'.($i === count($chars) - 1 ? 'ah-blink' : 'ah-car').'">'.e($ch).'</span></span>'
            .($i === $hlTo - 1 ? '<svg aria-hidden="true" viewBox="0 0 230 22" preserveAspectRatio="none" style="position: absolute; left: 0; top: 1.16em; width: 100%; height: 0.28em; overflow: visible"><path data-m="ah-swoosh" pathLength="1" stroke-dasharray="1" style="stroke-dashoffset: 0" d="M4 15 C 60 5, 140 4, 226 12" fill="none" stroke="#F2A230" stroke-width="6" stroke-linecap="round" vector-effect="non-scaling-stroke"></path></svg></span>' : '');
    }
    $cat = $post->categories->first();
    $url = route('front.blog.show', $post->slug);
    $drawing = ! $post->featuredMedia || in_array($post->slug, $lines($d['drawing_for'] ?? ''), true);
    $avatar = media_ref($d['avatar'] ?? null)?->web_url;
    $back = '<path d="M19 12H5M11 6l-6 6 6 6"></path>';
@endphp
<div class="ek ek-article-hero" id="{{ $d['anchor'] ?: 'p-top' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            $by = str_replace('{author}', $post->displayAuthor(), $t['byline']);
            $meta = str_replace(['{date}', '{minutes}'], [$post->published_at?->format('M Y') ?? '', $post->readingTimeMinutes()], $t['meta']);
            $btn = 'width: '.ek_u($v([42, 40])).'; height: '.ek_u($v([42, 40])).'; border-radius: 999px; background: var(--tk-paper); display: inline-flex; align-items: center; justify-content: center; text-decoration: none; color: var(--tk-ink); border: 0; cursor: pointer';
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div style="padding: {{ ek_u($v([116, 96])) }} {{ ek_u($v([96, 24])) }} {{ ek_u($v([50, 40])) }}; background: #FFFFFF">
                <div data-m="ah-head">
                    <div data-m="ah-crumbs" style="display: flex; align-items: center; gap: {{ ek_u(12) }}; font-size: max({{ ek_u(15) }}, 0.8125rem); color: var(--tk-muted)">
                        <a href="{{ link_href($d['back_link'] ?? '/blog') }}" style="display: inline-flex; align-items: center; gap: {{ ek_u(8) }}; height: {{ ek_u(36) }}; padding: 0 {{ ek_u(14) }} 0 {{ ek_u(10) }}; border-radius: 999px; background: var(--tk-paper); text-decoration: none; color: var(--tk-ink); font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u(14) }}, 0.8125rem)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: {{ ek_u(16) }}; height: {{ ek_u(16) }}">{!! $back !!}</svg>{{ $t['back_label'] }}</a>
                        @if ($cat)
                            <span aria-hidden="true">/</span>
                            <span style="padding: {{ ek_u(6) }} {{ ek_u(12) }}; border-radius: 999px; background: #D9F2E1; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u(12) }}, 0.75rem); letter-spacing: 0.06em; color: var(--tk-green-deep)">{{ mb_strtoupper($cat->name) }}</span>
                        @endif
                    </div>
                    <h1 aria-label="{{ $title }}" style="position: relative; margin: {{ ek_u($v([22, 18])) }} 0 0; max-width: {{ ek_u($v([1100, 342])) }}; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u($v([78, 40])) }}, 1.5rem); line-height: 1.06; letter-spacing: -0.045em"><span aria-hidden="true">{!! $typed !!}</span></h1>
                    <p data-m="ah-lede" style="margin: {{ ek_u($v([26, 18])) }} 0 0; max-width: {{ ek_u($v([820, 342])) }}; font-size: max({{ ek_u($v([21, 17])) }}, 0.8125rem); line-height: 1.55; color: #3B4640">{{ $post->autoExcerpt(40) }}</p>
                    <div data-m="ah-byline" style="margin-top: {{ ek_u($v([28, 22])) }}; display: flex; align-items: center; gap: {{ ek_u($v([14, 12])) }}; flex-wrap: wrap">
                        <span style="width: {{ ek_u($v([50, 44])) }}; height: {{ ek_u($v([50, 44])) }}; border-radius: 999px; background: #FFFFFF; box-shadow: inset 0 0 0 {{ ek_u(1.5) }} var(--tk-line-2); display: flex; align-items: center; justify-content: center">@if ($avatar)<img src="{{ $avatar }}" alt="" style="width: {{ ek_u($v([28, 24])) }}; height: auto">@endif</span>
                        <span style="display: flex; flex-direction: column; gap: {{ ek_u(2) }}"><span style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u($v([16, 15])) }}, 0.8125rem)">{{ ucfirst($by) }}</span><span style="font-size: max({{ ek_u($v([15, 14])) }}, 0.8125rem); color: var(--tk-muted)">{{ $meta }}</span></span>
                        <span data-share style="{{ $mob ? 'flex-basis: 100%' : 'margin-left: auto' }}; display: flex; align-items: center; gap: {{ ek_u(10) }}">
                            <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u(13) }}, 0.8125rem); letter-spacing: 0.1em; color: var(--tk-muted); margin-right: {{ ek_u(4) }}">{{ $t['share_label'] }}</span>
                            @include('frontend.sections.ekdanta.partials.share-links', ['t' => $d, 'url' => $url, 'title' => $title, 'btn' => $btn, 'icon' => $v([[20, 18, 19], [19, 17, 18]])])
                            <span class="visually-hidden" role="status" data-copy-status></span>
                        </span>
                    </div>
                </div>
                <div data-m="ah-cover" style="position: relative; margin-top: {{ ek_u($v([82, 36])) }}; height: {{ ek_u($v([540, 300])) }}; border-radius: {{ ek_u($v([30, 24])) }}; overflow: hidden; background: var(--tk-dark-4)">
                    @if ($drawing)
                        {{-- Phones: the drawing at 42 %, the brochure and phone in view. --}}
                        <div style="position: absolute; left: {{ $mob ? ek_u(-76) : 0 }}; top: {{ $mob ? ek_u(36) : 0 }}; width: {{ ek_u(1248) }}; height: {{ ek_u(540) }}; {{ $mob ? 'transform: scale(0.42); transform-origin: 0 0' : '' }}">
                            @include('frontend.sections.ekdanta.partials.article-drawing', ['d' => $d, 'mob' => $mob])
                        </div>
                    @else
                        <img src="{{ $post->featuredMedia->web_url }}" alt="{{ $post->featuredMedia->alt ?? '' }}" style="width: 100%; height: 100%; object-fit: cover; display: block">
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
@endif
