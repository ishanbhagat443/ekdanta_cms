{{--
    An article card (Article Grid, Keep Reading): cover image, or a tile in
    its topic's colour; topic, reading time, title, excerpt. Needs $post,
    $v (desktop / phone values) and $readTime ("{minutes} min read").
--}}
@php
    // Tiles for posts without a cover: topic slug => [colour, icon path].
    $art = [
        'websites' => ['#0E2440', 'M3 5h18v14H3z M3 9h18 M7 7h.01 M10 7h.01'],
        'growth' => ['#1F2A44', 'M4 4h5v16H4z M10 4h5v10h-5z M16 4h4v7h-4z'],
        'branding' => ['#2EB45D', 'M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18z M12 8a4 4 0 1 0 0 8'],
    ];
    $cat = $post->categories->first();
    [$artBg, $icon] = $art[$cat?->slug] ?? ['var(--tk-ink)', $art['branding'][1]];
@endphp
<a href="{{ route('front.blog.show', $post->slug) }}" style="display: block; height: {{ ek_u($v([470, 400])) }}; border-radius: {{ ek_u($v([24, 20])) }}; overflow: hidden; background: #FFFFFF; box-shadow: 0 {{ ek_u(1) }} 0 rgba(13,20,16,0.04), 0 {{ ek_u(24) }} {{ ek_u(48) }} {{ ek_u(-32) }} rgba(13,20,16,0.45), inset 0 0 0 {{ ek_u(1) }} rgba(13,20,16,0.07); text-decoration: none; color: var(--tk-ink)">
    @if ($post->featuredMedia)
        <span style="display: block; height: {{ ek_u($v([230, 190])) }}; background: var(--tk-paper) url({{ $post->featuredMedia->web_url }}) center top / cover no-repeat" role="img" aria-label="{{ $post->featuredMedia->alt ?: $post->title }}"></span>
    @else
        <span aria-hidden="true" style="display: flex; height: {{ ek_u($v([230, 190])) }}; align-items: center; justify-content: center; background: {{ $artBg }}"><span style="width: {{ ek_u(96) }}; height: {{ ek_u(96) }}; border-radius: {{ ek_u(28) }}; background: rgba(255,255,255,0.16); display: flex; align-items: center; justify-content: center"><svg viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width: {{ ek_u(48) }}; height: {{ ek_u(48) }}"><path d="{{ $icon }}"></path></svg></span></span>
    @endif
    <span style="display: flex; flex-direction: column; gap: {{ ek_u($v([10, 8])) }}; padding: {{ ek_u($v([20, 16])) }} {{ ek_u($v([24, 18])) }}">
        <span style="display: flex; align-items: center; gap: {{ ek_u(10) }}">@if ($cat)<span style="padding: {{ ek_u(5) }} {{ ek_u(10) }}; border-radius: 999px; background: #D9F2E1; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u(11) }}, 0.6875rem); letter-spacing: 0.06em; color: var(--tk-green-deep)">{{ mb_strtoupper($cat->name) }}</span>@endif<span style="font-size: max({{ ek_u(14) }}, 0.8125rem); color: var(--tk-muted)">{{ str_replace('{minutes}', $post->readingTimeMinutes(), $readTime) }}</span></span>
        <span style="font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u($v([22, 19])) }}, 0.8125rem); line-height: 1.22; letter-spacing: -0.03em">{{ $post->title }}</span>
        <span style="font-size: max({{ ek_u($v([15.5, 14])) }}, 0.8125rem); line-height: 1.5; color: var(--tk-muted)">{{ $post->autoExcerpt() }}</span>
    </span>
</a>
