@php
    $data = $section->dataFor('desktop');
    $useBusiness = (bool) ($data['use_business_map'] ?? true);
    $src = $data['embed_url'] ?: ($useBusiness ? setting('business.google_map') : null);
    $height = (int) ($data['height'] ?? 400);
@endphp

<div class="container py-5">
    @include('frontend.partials.section-heading', ['section' => $section])

    @if ($src)
        <iframe src="{{ $src }}" width="100%" height="{{ $height }}" style="border:0;" loading="lazy"
                referrerpolicy="no-referrer-when-downgrade" class="rounded"></iframe>
    @endif
</div>
