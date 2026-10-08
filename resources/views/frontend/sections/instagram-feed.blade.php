@php
    $data = $section->dataFor('desktop');
    $bg = $mediaMap[$data['bg_media_id'] ?? 0] ?? null;
    $handle = $data['handle'] ?? null;
    $profileUrl = $data['profile_url'] ?? null;

    $photos = collect($data['images'] ?? [])
        ->map(fn ($row) => [
            'media' => $mediaMap[$row['image_media_id'] ?? 0] ?? null,
            'url' => $row['url'] ?? null,
        ])
        ->filter(fn ($row) => $row['media'] !== null);
@endphp

@if (!empty($data['heading']) || $photos->isNotEmpty())
    <div class="gsm-insta"
         @if ($bg) style="background-image: url('{{ $bg->url }}'); background-size: cover;" @endif>

        @if (!empty($data['heading']) || $handle)
            <div class="gsm-insta-heading gsm-blackletter">
                @if (!empty($data['heading']))
                    <span>@include('frontend.partials.device-text', ['section' => $section, 'key' => 'heading'])</span>
                @endif
                @if ($handle)
                    @if ($profileUrl)
                        <a href="{{ $profileUrl }}" target="_blank" rel="noopener">{{ $handle }}</a>
                    @else
                        <span>{{ $handle }}</span>
                    @endif
                @endif
            </div>
        @endif

        @if ($photos->isNotEmpty())
            <div class="gsm-insta-strip">
                @foreach ($photos as $photo)
                    <a href="{{ $photo['url'] ?: ($profileUrl ?: '#') }}" target="_blank" rel="noopener"
                       class="gsm-insta-item" aria-label="View on Instagram">
                        <img src="{{ $photo['media']->variantUrl('card') ?? $photo['media']->url }}"
                             alt="{{ $photo['media']->alt ?: 'Instagram photo' }}" loading="lazy" decoding="async">
                    </a>
                @endforeach
            </div>
        @endif
    </div>
@endif
