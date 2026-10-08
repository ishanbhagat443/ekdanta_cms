@php
    $data = $section->dataFor('desktop');
    $items = collect((array) ($data['items'] ?? []))->filter(fn ($i) => !empty($i['title']));
    $isCards = ($data['layout'] ?? 'cards') === 'cards';
@endphp

<div class="container py-5">
    @include('frontend.partials.section-heading', ['section' => $section])

    <div class="row g-4 justify-content-center" data-anim-children>
        @foreach ($items as $item)
            <div class="col-6 col-sm-6 col-lg-{{ $isCards ? 4 : 3 }}">
                <div class="gsm-feature-card {{ $isCards ? 'gsm-glass text-center' : 'gsm-feature-card--row d-flex align-items-start' }} h-100">
                    @if (!empty($item['icon']))
                        <span class="gsm-feature-icon"><i class="{{ $item['icon'] }}"></i></span>
                    @endif
                    <div>
                        <h6 class="gsm-feature-title">{{ $item['title'] }}</h6>
                        @if (!empty($item['text']))
                            <p class="gsm-feature-text mb-0">{{ $item['text'] }}</p>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
