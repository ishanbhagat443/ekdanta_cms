{{--
    Ekdanta · Keep Reading. More articles: the article's related ones
    (Blog → Related posts) first, then its topic's newest, then the newest.
    The heading and the cards rise as they come into view. Phones (no
    design): the cards scroll sideways.
--}}
@php
    $d = $section->viewData('desktop');
    $m = $section->viewData('mobile');
    $current = ($templateItem ?? null) instanceof \App\Models\Post ? $templateItem : null;
    $count = max(1, min(6, (int) ($d['count'] ?? 3)));
    $with = ['categories', 'featuredMedia'];
    $posts = collect();
    if ($current) {
        $posts = $current->relatedPosts()->live()->with($with)->get();
        $cats = $current->categories()->pluck('blog_categories.id');
        $posts = $posts->concat(\App\Models\Post::query()->live()->with($with)->whereKeyNot($current->id)
            ->whereHas('categories', fn ($q) => $q->whereIn('blog_categories.id', $cats))->latest('published_at')->limit($count)->get());
    }
    $posts = $posts->concat(\App\Models\Post::query()->live()->with($with)->when($current, fn ($q) => $q->whereKeyNot($current->id))->latest('published_at')->limit($count)->get())
        ->unique('id')->take($count)->values();
    $arrow = '<path d="M5 12h14M13 6l6 6-6 6"></path>';
@endphp
@if ($posts->isNotEmpty())
<div class="ek ek-keep-reading" id="{{ $d['anchor'] ?: 'p-more' }}">
    @foreach ($section->comps($d, $m) as $comp => $t)
        @php
            $mob = $comp !== 'desktop';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div style="padding: {{ ek_u($v([110, 72])) }} {{ ek_u($v([96, 24])) }} {{ ek_u($v([130, 80])) }}; background: var(--tk-paper)">
                <div data-m="kr-head" style="display: flex; align-items: flex-end; justify-content: space-between; gap: {{ ek_u(20) }}; {{ $mob ? 'flex-wrap: wrap' : '' }}">
                    <span style="display: flex; flex-direction: column">
                        <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([14, 12])) }}, {{ $v(['0.8125rem', '0.75rem']) }}); letter-spacing: 0.12em; color: var(--tk-green-text-sm)">{{ $t['eyebrow'] }}</span>
                        <h2 style="margin: {{ ek_u($v([8, 4])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u($v([48, 30])) }}, 0.8125rem); line-height: 1.04; letter-spacing: -0.045em">{{ $t['heading'] }}</h2>
                    </span>
                    <a class="ek-press" href="{{ link_href($d['button_link'] ?? '/blog') }}" style="flex: none; height: {{ ek_u($v([50, 46])) }}; box-sizing: border-box; padding: 0 {{ ek_u(22) }}; display: inline-flex; align-items: center; gap: {{ ek_u(10) }}; background: var(--tk-ink); color: #FFFFFF; border-radius: 999px; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u(15) }}, 0.8125rem); text-decoration: none">{{ $t['button_label'] }}<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: {{ ek_u(16) }}; height: {{ ek_u(16) }}">{!! $arrow !!}</svg></a>
                </div>
                <ul class="ek-kr-row" style="margin: {{ ek_u($v([44, 28])) }} 0 0; padding: 0; list-style: none; display: flex; gap: {{ ek_u($v([24, 16])) }}; {{ $mob ? 'overflow-x: auto; scroll-snap-type: x mandatory; scrollbar-width: none; margin-right: '.ek_u(-24).'; padding-right: '.ek_u(24).'; padding-bottom: '.ek_u(40).'; margin-bottom: '.ek_u(-40) : '' }}">
                    @foreach ($posts as $post)
                        <li data-m="kr-card" style="flex: none; width: {{ ek_x($v([400, 300])) }}; scroll-snap-align: start">
                            @include('frontend.sections.ekdanta.partials.post-card', ['readTime' => $t['read_time']])
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endforeach
</div>
@endif
