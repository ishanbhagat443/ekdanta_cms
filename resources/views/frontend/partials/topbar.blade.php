@php
    /** @var array $chrome (view composer) */
    $area = $chrome['topbar'] ?? null;
    $headerLayout = $chrome['header']['layout'] ?? null;
    $showTopbar = $headerLayout?->setting('show_topbar', true) ?? true;
@endphp

@if ($area && $showTopbar && $area['sections']->isNotEmpty())
    <div class="gsm-topbar">
        @foreach ($area['sections'] as $entry)
            @include('frontend.sections.'.$entry['section']->type, [
                'section' => $entry['section'],
                'menu' => $entry['menu'],
                'mediaMap' => $entry['media'],
            ])
        @endforeach
    </div>
@endif
