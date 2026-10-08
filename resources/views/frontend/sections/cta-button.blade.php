@php
    $data = $section->dataFor('desktop');
    $classes = $section->visibilityClasses();
    $style = $data['style'] ?? 'gradient';
    $inDrawer = $inDrawer ?? false;

    $btnClass = match ($style) {
        'outline' => 'btn btn-outline-primary rounded-pill',
        'solid' => 'btn btn-primary rounded-pill',
        default => 'btn gsm-btn-gradient rounded-pill text-white',
    };
@endphp

<a href="{{ $data['url'] ?? '#' }}"
   target="{{ !empty($data['new_tab']) ? '_blank' : '_self' }}"
   @if(!empty($data['new_tab'])) rel="noopener" @endif
   class="{{ $btnClass }} px-4 {{ $inDrawer ? 'w-100 mt-3' : 'order-3 ms-lg-3 d-none d-lg-inline-block' }} {{ $classes }}">
    @if (!empty($data['icon']))<i class="{{ $data['icon'] }} me-1"></i>@endif
    @include('frontend.partials.device-text', ['section' => $section, 'key' => 'label'])
</a>
