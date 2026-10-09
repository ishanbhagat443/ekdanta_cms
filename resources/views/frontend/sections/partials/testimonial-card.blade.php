@php $photo = $mediaMap[$item['media_id'] ?? 0] ?? null; @endphp

<div class="gsm-glass gsm-testimonial-card text-center mx-auto" style="max-width: 640px;">
    @if ($photo)
        <img loading="lazy" decoding="async" src="{{ $photo->variantUrl('thumb') ?? $photo->url }}" alt="{{ $item['name'] ?? '' }}"
             class="rounded-circle mb-3" style="width: 72px; height: 72px; object-fit: cover;">
    @endif
    @if (!empty($item['rating']))
        <div class="mb-2" style="color: #4b5563;">
            @for ($star = 1; $star <= 5; $star++)
                <i class="{{ $star <= (int) $item['rating'] ? 'fas' : 'far' }} fa-star"></i>
            @endfor
        </div>
    @endif
    <blockquote class="fst-italic">“{{ $item['quote'] }}”</blockquote>
    <div class="fw-bold">{{ $item['name'] ?? '' }}</div>
    @if (!empty($item['role']))
        <div class="text-muted small">{{ $item['role'] }}</div>
    @endif
</div>
