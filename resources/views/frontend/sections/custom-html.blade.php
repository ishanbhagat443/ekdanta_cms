@php
    $classes = $section->visibilityClasses();
    $values = $section->deviceValues('html');
@endphp

<div class="gsm-custom-html {{ $classes }}">
    @if (isset($values['*']))
        {!! $values['*'] !!}
    @else
        <div class="d-none d-lg-block">{!! $values['desktop'] !!}</div>
        <div class="d-none d-md-block d-lg-none">{!! $values['tablet'] ?? $values['desktop'] !!}</div>
        <div class="d-block d-md-none">{!! $values['mobile'] ?? $values['desktop'] !!}</div>
    @endif
</div>
