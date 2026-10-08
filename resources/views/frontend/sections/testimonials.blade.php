@php
    $data = $section->dataFor('desktop');
    // values(): filter() keeps the original keys, and the carousel uses the
    // index to decide which slide is active and to number the indicators.
    $items = collect((array) ($data['items'] ?? []))
        ->filter(fn ($i) => !empty($i['quote']))
        ->values();
    $carousel = (bool) ($data['carousel'] ?? true) && $items->count() > 1;
    $carouselId = 'gsm-testimonials-'.$section->id;
@endphp

<div class="container py-5">
    @include('frontend.partials.section-heading', ['section' => $section])

    @if ($carousel)
        <div id="{{ $carouselId }}" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                @foreach ($items as $i => $item)
                    <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                        @include('frontend.sections.partials.testimonial-card', ['item' => $item, 'mediaMap' => $mediaMap])
                    </div>
                @endforeach
            </div>
            <div class="carousel-indicators position-static mt-3">
                @foreach ($items as $i => $item)
                    <button type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide-to="{{ $i }}"
                            class="{{ $i === 0 ? 'active' : '' }} bg-secondary"></button>
                @endforeach
            </div>
        </div>
    @else
        <div class="row g-4 justify-content-center" data-anim-children>
            @foreach ($items as $item)
                <div class="col-12 col-md-6 col-lg-4">
                    @include('frontend.sections.partials.testimonial-card', ['item' => $item, 'mediaMap' => $mediaMap])
                </div>
            @endforeach
        </div>
    @endif
</div>
