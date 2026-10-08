@php
    $data = $section->dataFor('desktop');
    $slides = collect((array) ($data['slides'] ?? []))->filter(fn ($s) => isset($mediaMap[$s['media_id'] ?? 0]));
    $carouselId = 'gsm-slider-'.$section->id;
    $height = ['small' => '320px', 'medium' => '480px', 'large' => '620px'][$data['height'] ?? 'medium'] ?? '480px';
    $autoplay = (bool) ($data['autoplay'] ?? true);
    $interval = (int) ($data['interval'] ?? 5000);
@endphp

@if ($slides->isNotEmpty())
    <div id="{{ $carouselId }}" class="carousel slide"
         @if ($autoplay) data-bs-ride="carousel" @endif>
        <div class="carousel-indicators">
            @foreach ($slides as $i => $slide)
                <button type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide-to="{{ $i }}"
                        class="{{ $i === 0 ? 'active' : '' }}"></button>
            @endforeach
        </div>
        <div class="carousel-inner">
            @foreach ($slides as $i => $slide)
                @php $media = $mediaMap[$slide['media_id']]; @endphp
                <div class="carousel-item {{ $i === 0 ? 'active' : '' }}" data-bs-interval="{{ $interval }}">
                    <div class="d-flex align-items-center text-white text-center"
                         style="height: {{ $height }};
                                background: linear-gradient(rgba(10,14,26,.45), rgba(10,14,26,.45)), url('{{ $media->url }}') center/cover no-repeat;">
                        <div class="container">
                            @if (!empty($slide['heading']))
                                <h2 class="display-5 gsm-serif">{{ $slide['heading'] }}</h2>
                            @endif
                            @if (!empty($slide['text']))
                                <p class="lead">{{ $slide['text'] }}</p>
                            @endif
                            @if (!empty($slide['button_label']))
                                <a href="{{ $slide['button_url'] ?? '#' }}"
                                   class="btn gsm-btn-gradient text-white rounded-pill px-4">{{ $slide['button_label'] }}</a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>
@endif
