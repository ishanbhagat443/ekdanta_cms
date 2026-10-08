{{-- Blog card (design B1): image, author+date pills, title, excerpt.
     $grid (optional — Blog Listing CMS display toggles): show_date,
     show_category, show_author, show_read_more, show_featured_badge.
     Defaults below match today's exact output when $grid isn't passed
     (e.g. the "You May Also Like" / related-posts callers). --}}
@php
    $grid = $grid ?? [];
    $showDate = $grid['show_date'] ?? true;
    $showCategory = $grid['show_category'] ?? false;
    $showAuthor = $grid['show_author'] ?? true;
    $showReadMore = $grid['show_read_more'] ?? false;
    $showFeaturedBadge = $grid['show_featured_badge'] ?? false;
@endphp
<article class="card border-0 shadow-sm h-100 position-relative">
    @if ($showFeaturedBadge && $post->is_featured)
        <span class="badge position-absolute top-0 start-0 m-2" style="background:#c29b54; z-index:1;">Featured</span>
    @endif
    <a href="{{ route('front.blog.show', $post->slug) }}">
        @if ($post->featuredMedia)
            <img loading="lazy" decoding="async" src="{{ $post->featuredMedia->variantUrl('card') ?? $post->featuredMedia->url }}"
                 alt="{{ $post->title }}" class="card-img-top" style="aspect-ratio: 3/4; object-fit: cover;">
        @else
            <div class="bg-light card-img-top d-flex align-items-center justify-content-center" style="aspect-ratio: 3/4;">
                <i class="far fa-image fa-3x text-muted"></i>
            </div>
        @endif
    </a>
    <div class="card-body">
        @if ($showAuthor || $showDate || ($showCategory && $post->categories->isNotEmpty()))
            <div class="mb-2 small">
                @if ($showAuthor)
                    <span class="badge rounded-pill px-2" style="background: #f5f0e6; color: #c29b54;">{{ $post->displayAuthor() }}</span>
                @endif
                @if ($showDate)
                    <span class="badge rounded-pill px-2" style="background: #f5f0e6; color: #c29b54;">
                        <i class="far fa-calendar-alt me-1"></i>{{ $post->published_at?->format('F d, Y') }}
                    </span>
                @endif
                @if ($showCategory)
                    @foreach ($post->categories as $category)
                        <span class="badge rounded-pill px-2" style="background: #eee; color: #555;">{{ $category->name }}</span>
                    @endforeach
                @endif
            </div>
        @endif
        <h3 class="h5 gsm-serif">
            <a href="{{ route('front.blog.show', $post->slug) }}" class="text-decoration-none text-dark">{{ $post->title }}</a>
        </h3>
        <p class="small text-muted {{ $showReadMore ? 'mb-2' : 'mb-0' }}">{{ $post->autoExcerpt() }}</p>
        @if ($showReadMore)
            <a href="{{ route('front.blog.show', $post->slug) }}" class="small text-decoration-none" style="color:#c29b54;">Read More →</a>
        @endif
    </div>
</article>
