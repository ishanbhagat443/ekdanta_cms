@php
    $data = $section->dataFor('desktop');
    $image = $mediaMap[$data['media_id'] ?? 0] ?? null;
    $width = $data['width'] ?? 'container';
    $align = $data['alignment'] ?? 'center';
@endphp

@if ($image)
    <div class="{{ $width === 'full' ? '' : 'container' }} py-4 text-{{ $align }}">
        <figure class="mb-0 {{ $width === 'narrow' ? 'mx-auto' : '' }}" @if($width === 'narrow') style="max-width: 720px;" @endif>
            @if (!empty($data['link']))<a href="{{ $data['link'] }}">@endif
                <img loading="lazy" decoding="async" src="{{ $width === 'full' ? $image->url : ($image->variantUrl('card') ?? $image->url) }}"
                     alt="{{ $data['alt'] ?? $image->alt ?? '' }}"
                     class="img-fluid {{ $width === 'full' ? 'w-100' : 'rounded' }}">
            @if (!empty($data['link']))</a>@endif
            @if (!empty($data['caption']))
                <figcaption class="small text-muted mt-2">
                    @include('frontend.partials.device-text', ['section' => $section, 'key' => 'caption'])
                </figcaption>
            @endif
        </figure>
    </div>
@endif
