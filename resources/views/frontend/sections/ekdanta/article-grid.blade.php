{{--
    Ekdanta · Article Grid. Flows with the page (no pinned stage). Every
    live post is drawn; ones that don't match ?topic= / ?search= are hidden,
    and blog-grid.js re-filters as chips are tapped or the hero's search
    box is typed in. Cards rise as they come into view (an enter track).
    Posts without a cover get a tile in their topic's colour.
--}}
@php
    $d = $section->dataFor('desktop');
    $m = $section->dataFor('mobile');
    $u = fn ($n) => "calc({$n} * var(--u))";
    $featured = ($d['skip_featured'] ?? true) ? \App\Models\Post::query()->live()->orderByDesc('is_featured')->latest('published_at')->value('id') : null;
    $posts = \App\Models\Post::query()->live()->with(['categories', 'featuredMedia'])->when($featured, fn ($q) => $q->whereKeyNot($featured))->latest('published_at')->limit(60)->get();
    $topics = \App\Models\BlogCategory::query()->where('is_active', true)->orderBy('sort_order')->get()
        ->map(fn ($c) => [$c->slug, $c->name, $posts->filter(fn ($p) => $p->categories->contains('id', $c->id))->count()])
        ->filter(fn ($c) => $c[2] > 0)->values();
    $topic = (string) request('topic', '');
    $q = trim((string) request('search', ''));
    $match = fn ($p) => ($topic === '' || $p->categories->contains('slug', $topic)) && ($q === '' || str_contains(mb_strtolower($p->title.' '.$p->autoExcerpt().' '.$p->categories->pluck('name')->implode(' ')), mb_strtolower($q)));
    $shown = $posts->filter($match)->count();
    // Tiles for posts without a cover: topic slug => [colour, icon path].
    $art = [
        'websites' => ['#0E2440', 'M3 5h18v14H3z M3 9h18 M7 7h.01 M10 7h.01'],
        'growth' => ['#1F2A44', 'M4 4h5v16H4z M10 4h5v10h-5z M16 4h4v7h-4z'],
        'branding' => ['#2EB45D', 'M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18z M12 8a4 4 0 1 0 0 8'],
    ];
    $base = url()->current();
@endphp
<div class="ek ek-article-grid" id="{{ $d['anchor'] ?: 'b-posts' }}" data-blog-grid>
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            $title = $q !== '' ? str_replace('{q}', $q, $t['results_heading']) : $t['heading'];
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}" data-grid-comp data-heading="{{ $t['heading'] }}" data-results="{{ $t['results_heading'] }}">
            <div style="padding: {{ $u($v([100, 72])) }} 0 {{ $u($v([120, 80])) }}; background: #FFFFFF">
                <div style="width: {{ $u($v([1248, 342])) }}; margin: 0 auto">
                    <div data-m="ag-head">
                        <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ $u($v([14, 12])) }}, {{ $v(['0.8125rem', '0.75rem']) }}); letter-spacing: 0.12em; color: var(--tk-green-text-sm)">{{ $t['eyebrow'] }}</span>
                        <h2 data-grid-title style="margin: {{ $u($v([8, 4])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ $u($v([48, 30])) }}, 0.8125rem); line-height: 1.04; letter-spacing: -0.045em">{{ $title }}</h2>
                    </div>
                    @if ($topics->isNotEmpty())
                        <nav aria-label="{{ $d['filter_label'] }}" data-m="ag-chips" style="margin-top: {{ $u($v([32, 18])) }}; display: flex; gap: {{ $u($v([8, 6])) }}; {{ $mob ? 'overflow-x: auto; scrollbar-width: none; margin-right: '.$u(-24).'; padding-right: '.$u(24) : 'flex-wrap: wrap' }}">
                            @foreach ([['', $t['all_label'], $posts->count()], ...$topics] as [$slug, $name, $count])
                                <a class="ek-ag-chip ek-press" data-topic="{{ $slug }}" href="{{ $base }}{{ $slug !== '' ? '?topic='.$slug : '' }}#{{ $d['anchor'] ?: 'b-posts' }}" @if ($slug === $topic) aria-current="true" @endif style="flex: none; height: {{ $u($v([42, 38])) }}; padding: 0 {{ $u($v([16, 14])) }}; display: inline-flex; align-items: center; gap: {{ $u(6) }}; border-radius: 999px; box-shadow: inset 0 0 0 {{ $u(1) }} rgba(13,20,16,0.14); font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ $u($v([14, 13])) }}, 0.8125rem); text-decoration: none; white-space: nowrap">{{ $name }} <span style="opacity: 0.6">{{ $count }}</span></a>
                            @endforeach
                        </nav>
                    @endif
                    <div data-grid-empty @if ($shown > 0) hidden @endif style="margin-top: {{ $u($v([40, 24])) }}; height: {{ $u($v([240, 200])) }}; border-radius: {{ $u(24) }}; border: {{ $u(2) }} dashed #C5CCC7; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: {{ $u(8) }}; text-align: center; padding: 0 {{ $u(20) }}"><span style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ $u($v([22, 18])) }}, 0.8125rem)">{{ $t['empty_title'] }}</span><span style="font-size: max({{ $u($v([17, 15])) }}, 0.8125rem); color: var(--tk-muted)">{{ $t['empty_text'] }}</span></div>
                    <ul style="margin: {{ $u($v([40, 24])) }} 0 0; padding: 0; list-style: none; display: grid; grid-template-columns: repeat({{ $v([3, 1]) }}, minmax(0, 1fr)); gap: {{ $u($v([30, 18])) }} {{ $u(24) }}">
                        @foreach ($posts as $post)
                            @php
                                $cat = $post->categories->first();
                                [$artBg, $icon] = $art[$cat?->slug] ?? ['var(--tk-ink)', $art['branding'][1]];
                            @endphp
                            <li data-m="ag-card" data-topic="{{ $post->categories->pluck('slug')->implode(' ') }}" data-text="{{ mb_strtolower($post->title.' '.$post->autoExcerpt().' '.$post->categories->pluck('name')->implode(' ')) }}" @unless ($match($post)) hidden @endunless style="view-transition-name: ek-post-{{ $comp[0] }}-{{ $post->id }}">
                                <a href="{{ route('front.blog.show', $post->slug) }}" style="display: block; height: {{ $u($v([470, 400])) }}; border-radius: {{ $u($v([24, 20])) }}; overflow: hidden; background: #FFFFFF; box-shadow: 0 {{ $u(1) }} 0 rgba(13,20,16,0.04), 0 {{ $u(24) }} {{ $u(48) }} {{ $u(-32) }} rgba(13,20,16,0.45), inset 0 0 0 {{ $u(1) }} rgba(13,20,16,0.07); text-decoration: none; color: var(--tk-ink)">
                                    @if ($post->featuredMedia)
                                        <span style="display: block; height: {{ $u($v([230, 190])) }}; background: var(--tk-paper) url({{ $post->featuredMedia->url }}) center top / cover no-repeat" role="img" aria-label="{{ $post->featuredMedia->alt ?: $post->title }}"></span>
                                    @else
                                        <span aria-hidden="true" style="display: flex; height: {{ $u($v([230, 190])) }}; align-items: center; justify-content: center; background: {{ $artBg }}"><span style="width: {{ $u(96) }}; height: {{ $u(96) }}; border-radius: {{ $u(28) }}; background: rgba(255,255,255,0.16); display: flex; align-items: center; justify-content: center"><svg viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width: {{ $u(48) }}; height: {{ $u(48) }}"><path d="{{ $icon }}"></path></svg></span></span>
                                    @endif
                                    <span style="display: flex; flex-direction: column; gap: {{ $u($v([10, 8])) }}; padding: {{ $u($v([20, 16])) }} {{ $u($v([24, 18])) }}">
                                        <span style="display: flex; align-items: center; gap: {{ $u(10) }}">@if ($cat)<span style="padding: {{ $u(5) }} {{ $u(10) }}; border-radius: 999px; background: #D9F2E1; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ $u(11) }}, 0.6875rem); letter-spacing: 0.06em; color: var(--tk-green-deep)">{{ mb_strtoupper($cat->name) }}</span>@endif<span style="font-size: max({{ $u(14) }}, 0.8125rem); color: var(--tk-muted)">{{ str_replace('{minutes}', $post->readingTimeMinutes(), $d['read_time']) }}</span></span>
                                        <span style="font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ $u($v([22, 19])) }}, 0.8125rem); line-height: 1.22; letter-spacing: -0.03em">{{ $post->title }}</span>
                                        <span style="font-size: max({{ $u($v([15.5, 14])) }}, 0.8125rem); line-height: 1.5; color: var(--tk-muted)">{{ $post->autoExcerpt() }}</span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endforeach
</div>
