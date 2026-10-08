@php
    $data = $section->dataFor('desktop');
    $bg = $mediaMap[$data['bg_media_id'] ?? 0] ?? null;
    $photo = $mediaMap[$data['image_media_id'] ?? 0] ?? null;
    $bullets = array_values(array_filter($data['bullets'] ?? [], fn ($b) => filled($b['text'] ?? null)));
    $buttonLabel = $data['button_label'] ?? null;
    $buttonUrl = $data['button_url'] ?? null;
@endphp

<div class="gsm-choose"
     @if ($bg) style="background-image: url('{{ $bg->url }}'); background-size: cover;" @endif>
    <div class="container py-5">
        @include('frontend.partials.section-heading', ['section' => $section, 'ornament' => true])

        <div class="gsm-choose-media">
            @if ($photo)
                <img src="{{ $photo->variantUrl('banner') ?? $photo->url }}"
                     alt="{{ $photo->alt ?: ($data['heading'] ?? 'Why choose us') }}"
                     class="gsm-choose-photo" loading="lazy" decoding="async">
            @endif

            @if (count($bullets))
                <ul class="gsm-choose-strip list-unstyled" role="list">
                    @foreach ($bullets as $bullet)
                        <li class="gsm-choose-bullet gsm-serif">{{ $bullet['text'] }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

        @if ($buttonLabel && $buttonUrl)
            <div class="text-center mt-4">
                <x-gsm-button :url="url($buttonUrl)" :label="$buttonLabel" :style="$data['button_style'] ?? null" />
            </div>
        @endif
    </div>
</div>
