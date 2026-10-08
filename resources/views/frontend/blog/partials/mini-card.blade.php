<a href="{{ route('front.blog.show', $item->slug) }}" class="d-flex text-decoration-none text-dark mb-2">
    @if ($item->featuredMedia)
        <img loading="lazy" decoding="async" src="{{ $item->featuredMedia->variantUrl('thumb') ?? $item->featuredMedia->url }}" alt=""
             class="rounded me-2 flex-shrink-0" style="width: 56px; height: 72px; object-fit: cover;">
    @endif
    <div class="small">
        <div class="text-muted" style="font-size: .72rem;">
            {{ $item->displayAuthor() }} · {{ $item->published_at?->format('M d, Y') }}
        </div>
        <div class="fw-semibold">{{ \Illuminate\Support\Str::limit($item->title, 60) }}</div>
    </div>
</a>
