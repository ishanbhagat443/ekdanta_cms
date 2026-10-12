{{--
    Ekdanta · Article Grid. Flows with the page (no pinned stage). Every
    live post is drawn; ones that don't match ?topic= / ?search= are hidden,
    and blog-grid.js re-filters as chips are tapped or the hero's search
    box is typed in. Cards rise as they come into view (an enter track).
    Cards: partials/post-card.
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
                            <li data-m="ag-card" data-topic="{{ $post->categories->pluck('slug')->implode(' ') }}" data-text="{{ mb_strtolower($post->title.' '.$post->autoExcerpt().' '.$post->categories->pluck('name')->implode(' ')) }}" @unless ($match($post)) hidden @endunless style="view-transition-name: ek-post-{{ $comp[0] }}-{{ $post->id }}">
                                @include('frontend.sections.ekdanta.partials.post-card', ['readTime' => $d['read_time']])
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endforeach
</div>
