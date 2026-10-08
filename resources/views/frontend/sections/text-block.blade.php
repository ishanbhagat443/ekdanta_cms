@php
    $data = $section->dataFor('desktop');
    $align = $data['alignment'] ?? 'center';
    $narrow = ($data['width'] ?? 'container') === 'narrow';
    $bodyValues = $section->deviceValues('body');
@endphp

<div class="container py-5 text-{{ $align }}">
    <div class="{{ $narrow ? 'mx-auto' : '' }}" @if($narrow) style="max-width: 760px;" @endif>
        @include('frontend.partials.section-heading', ['section' => $section, 'align' => $align])

        @if (isset($bodyValues['*']))
            <div class="gsm-richtext">{!! $bodyValues['*'] !!}</div>
        @else
            <div class="gsm-richtext d-none d-lg-block">{!! $bodyValues['desktop'] !!}</div>
            <div class="gsm-richtext d-none d-md-block d-lg-none">{!! $bodyValues['tablet'] ?? $bodyValues['desktop'] !!}</div>
            <div class="gsm-richtext d-block d-md-none">{!! $bodyValues['mobile'] ?? $bodyValues['desktop'] !!}</div>
        @endif

        @include('frontend.partials.buttons', ['buttons' => $data['buttons'] ?? [], 'alignment' => $align])
    </div>
</div>
