{{--
    Ekdanta · Article Body + Reading Rail. The article comes from the
    template page ($templateItem), else the newest one. Its text is drawn
    once for every screen size (headings keep one id each for the contents
    links): a sticky rail beside it on desktop, stacked above it on phones
    (styles: scss/ekdanta/_article.scss). Blocks rise into view as they
    arrive (enter tracks on ab-rise, marked by ArticleBody); article.js
    follows the heading in view and counts down the minutes left.
--}}
@php
    $d = $section->dataFor('desktop');
    $u = fn ($n) => "calc({$n} * var(--u))";
    $post = ($templateItem ?? null) instanceof \App\Models\Post ? $templateItem : \App\Models\Post::query()->live()->latest('published_at')->first();
@endphp
@if ($post)
@php
    $post->loadMissing('tags');
    $body = \App\Support\ArticleBody::prepare((string) $post->body);
    $minutes = $post->readingTimeMinutes();
    $url = route('front.blog.show', $post->slug);
    $author = $d['author_name'] ?: ucfirst($post->displayAuthor());
    $authorImage = media_ref($d['author_image'] ?? null)?->url;
    $btn = 'width: '.$u(46).'; height: '.$u(46).'; border-radius: 999px; background: var(--tk-paper); display: inline-flex; align-items: center; justify-content: center; text-decoration: none; color: var(--tk-ink); border: 0; cursor: pointer';
    $ring = round(2 * M_PI * 22, 2);
@endphp
<div class="ek ek-article-body" id="{{ $d['anchor'] ?: 'p-body' }}" data-article data-minutes="{{ $minutes }}" data-left="{{ $d['left_label'] }}" data-done="{{ $d['done_label'] }}" data-read="{{ $d['read_label'] }}">
    <div class="ek-ab">
        <aside class="ek-ab__rail" aria-label="{{ $d['rail_label'] }}" data-m="ab-rail">
            @if ($body['toc'] !== [])
                <span class="ek-ab__label">{{ $d['toc_label'] }}</span>
                <nav class="ek-ab__toc" aria-label="{{ \Illuminate\Support\Str::ucfirst(mb_strtolower($d['toc_label'])) }}" data-toc>
                    <span class="ek-ab__bar" aria-hidden="true"></span>
                    @foreach ($body['toc'] as $entry)
                        <a href="#{{ $entry['id'] }}">{{ $entry['label'] }}</a>
                    @endforeach
                </nav>
            @endif
            <div class="ek-ab__progress">
                <svg viewBox="0 0 56 56" aria-hidden="true"><circle cx="28" cy="28" r="22" fill="none" stroke="#DDE2DA" stroke-width="5"></circle><circle data-ring cx="28" cy="28" r="22" fill="none" stroke="#2EB45D" stroke-width="5" stroke-linecap="round" stroke-dasharray="{{ $ring }}" stroke-dashoffset="{{ $ring }}" transform="rotate(-90 28 28)"></circle></svg>
                <span><span class="ek-ab__left" data-left>{{ str_replace('{minutes}', $minutes, $d['left_label']) }}</span><span data-read>{{ str_replace('{percent}', '0', $d['read_label']) }}</span></span>
            </div>
            <div class="ek-ab__share" data-share>
                <span class="ek-ab__label ek-ab__label--muted">{{ $d['share_label'] }}</span>
                <div>@include('frontend.sections.ekdanta.partials.share-links', ['t' => $d, 'url' => $url, 'title' => $post->title, 'btn' => $btn, 'icon' => [20, 18, 19]])</div>
                <span class="ek-ab__copied" role="status" data-copy-status></span>
            </div>
        </aside>
        <article class="ek-ab__main">
            <div class="ek-ab-text" data-article-text>{!! $body['html'] !!}</div>
            @if ($post->tags->isNotEmpty())
                <div class="ek-ab__tags" data-m="ab-rise">
                    <span class="ek-ab__label ek-ab__label--muted">{{ $d['tags_label'] }}</span>
                    @foreach ($post->tags as $tag)
                        <a href="{{ url('/blog') }}?search={{ rawurlencode($tag->name) }}">{{ $tag->name }}</a>
                    @endforeach
                </div>
            @endif
            <div class="ek-ab__author" data-m="ab-rise">
                <span class="ek-ab__avatar">@if ($authorImage)<img src="{{ $authorImage }}" alt="">@endif</span>
                <span class="ek-ab__who">
                    <span class="ek-ab__label">{{ $d['author_kicker'] }}</span>
                    <span class="ek-ab__name">{{ $author }}</span>
                    @if ($d['author_bio'])<span class="ek-ab__bio">{{ $d['author_bio'] }}</span>@endif
                    @if ($d['author_link_label'])
                        <a href="{{ link_href($d['author_link'] ?? '/about') }}">{{ $d['author_link_label'] }}<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"></path></svg></a>
                    @endif
                </span>
            </div>
        </article>
    </div>
</div>
@endif
