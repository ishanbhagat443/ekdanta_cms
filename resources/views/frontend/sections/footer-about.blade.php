@php
    $data = $section->dataFor('desktop');
    $logo = $mediaMap[$data['media_id'] ?? 0] ?? null;
    $maxH = (int) ($data['logo_max_height'] ?? 64);
@endphp

<div class="gsm-footer-about">
    @if ($logo)
        <img loading="lazy" decoding="async" src="{{ $logo->url }}" alt="{{ setting('business.company_name', '') }}"
             class="mb-3" style="max-height: {{ $maxH }}px;">
    @endif
    <p class="small mb-0">
        @include('frontend.partials.device-text', ['section' => $section, 'key' => 'text'])
    </p>
</div>
