@php
    $values = $section->deviceValues('text');
    $render = fn (?string $text) => str_replace('{year}', date('Y'), (string) $text);
@endphp

<div class="container text-center small opacity-75">
    @if (array_key_exists('*', $values))
        {{ $render($values['*']) }}
    @else
        <span class="d-none d-lg-inline">{{ $render($values['desktop']) }}</span>
        <span class="d-none d-md-inline d-lg-none">{{ $render($values['tablet'] ?? $values['desktop']) }}</span>
        <span class="d-inline d-md-none">{{ $render($values['mobile'] ?? $values['desktop']) }}</span>
    @endif
</div>
