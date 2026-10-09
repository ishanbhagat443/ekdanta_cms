@php
    $data = $section->dataFor('desktop');
    $cards = collect((array) ($data['cards'] ?? []))->filter(fn ($c) => !empty($c['title']));
    $cols = (int) ($data['columns'] ?? 3);
    $colClass = 'col-12 col-sm-6 col-lg-'.max(3, (int) floor(12 / $cols));
@endphp

<div class="container py-5">
    @include('frontend.partials.section-heading', ['section' => $section])

    <div class="row g-4" data-anim-children>
        @foreach ($cards as $card)
            @php $image = $mediaMap[$card['media_id'] ?? 0] ?? null; @endphp
            <div class="{{ $colClass }}">
                <div class="card h-100 shadow-sm border-0">
                    @if ($image)
                        <img loading="lazy" decoding="async" src="{{ $image->variantUrl('card') ?? $image->url }}" alt="{{ $card['title'] }}"
                             class="card-img-top" style="aspect-ratio: 4/3; object-fit: cover;">
                    @endif
                    <div class="card-body">
                        <h5 class="card-title">{{ $card['title'] }}</h5>
                        @if (!empty($card['text']))
                            <p class="card-text small text-muted">{{ $card['text'] }}</p>
                        @endif
                        @if (!empty($card['link_label']))
                            <a href="{{ $card['link_url'] ?? '#' }}" class="fw-bold small text-decoration-none"
                               style="color: #111827;">{{ $card['link_label'] }} <i class="fas fa-arrow-right ms-1"></i></a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
