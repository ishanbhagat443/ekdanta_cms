{{-- Meta pill pair on blog cards (design 08 Blog.png): author/brand + date. --}}
<div class="gsm-blog-meta">
    <span class="gsm-blog-pill">{{ $post->displayAuthor() }}</span>
    @if ($post->published_at)
        <span class="gsm-blog-pill">
            <i class="fas fa-calendar-alt" aria-hidden="true"></i>
            {{ $post->published_at->format('F j, Y') }}
        </span>
    @endif
</div>
