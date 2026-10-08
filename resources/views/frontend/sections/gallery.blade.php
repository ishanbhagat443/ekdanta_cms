@php
    $data = $section->dataFor('desktop');
    $images = collect((array) ($data['images'] ?? []))->filter(fn ($i) => isset($mediaMap[$i['media_id'] ?? 0]));
    $cols = (int) ($data['columns'] ?? 3);
    $colClass = 'col-6 col-md-'.max(2, (int) floor(12 / $cols));
    $lightbox = (bool) ($data['lightbox'] ?? true);
@endphp

<div class="container py-5">
    @include('frontend.partials.section-heading', ['section' => $section])

    <div class="row g-3" data-anim-children>
        @foreach ($images as $item)
            @php $media = $mediaMap[$item['media_id']]; @endphp
            <div class="{{ $colClass }}">
                @if ($lightbox)
                    <a href="{{ $media->url }}" target="_blank" rel="noopener" class="d-block">
                @endif
                    <figure class="mb-0">
                        <img loading="lazy" decoding="async" src="{{ $media->variantUrl('card') ?? $media->url }}"
                             alt="{{ $item['caption'] ?? $media->alt ?? '' }}"
                             class="img-fluid rounded w-100" style="aspect-ratio: 3/4; object-fit: cover;">
                        @if (!empty($item['caption']))
                            <figcaption class="small text-muted mt-1 text-center">{{ $item['caption'] }}</figcaption>
                        @endif
                    </figure>
                @if ($lightbox)
                    </a>
                @endif
            </div>
        @endforeach
    </div>
</div>
