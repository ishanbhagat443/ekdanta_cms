@php
    $data = $section->dataFor('desktop');
    $image = $mediaMap[$data['media_id'] ?? 0] ?? null;
    $imageRight = ($data['image_position'] ?? 'right') === 'right';
@endphp

<div class="container py-5">
    <div class="row align-items-center g-4 {{ $imageRight ? '' : 'flex-lg-row-reverse' }}">
        <div class="col-12 col-lg-6">
            @if (!empty($data['heading']))
                <h2 class="gsm-serif mb-2">@include('frontend.partials.device-text', ['section' => $section, 'key' => 'heading'])</h2>
            @endif
            @if (!empty($data['subheading']))
                <p class="text-muted">@include('frontend.partials.device-text', ['section' => $section, 'key' => 'subheading'])</p>
            @endif
            @if (!empty($data['text']))
                <div>{!! $data['text'] !!}</div>
            @endif
            @include('frontend.partials.buttons', ['buttons' => $data['buttons'] ?? [], 'alignment' => 'start'])
        </div>
        <div class="col-12 col-lg-6 text-center">
            @if ($image)
                <img loading="lazy" decoding="async" src="{{ $image->variantUrl('card') ?? $image->url }}" alt="{{ $image->alt ?? '' }}"
                     class="img-fluid rounded shadow-sm">
            @endif
        </div>
    </div>
</div>
