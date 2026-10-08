{{-- One process step's photo in the gold ogee-arch frame. --}}
<div class="gsm-ogee" style="clip-path: url(#{{ $clipId }});">
    <img src="{{ $image->variantUrl('card') ?? $image->url }}" alt="{{ $image->alt ?: $title }}"
         style="clip-path: url(#{{ $clipId }});" loading="lazy" decoding="async">
</div>
