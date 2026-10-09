{{-- Developer Mode gate: an active override replaces the original Blade
     component's output (originals are never modified — see
     DeveloperModeService). No override → normal component render. --}}
@php $devOverride = $section->activeDeveloperOverride(); @endphp

@if ($devOverride)
    {!! $devOverride->html !!}
    @if ($devOverride->css)
        <style>{!! $devOverride->css !!}</style>
    @endif
    @if ($devOverride->js)
        @push('scripts')
            <script>{!! $devOverride->js !!}</script>
        @endpush
    @endif
@else
    @include(app(\App\Services\SectionTypes\SectionTypeRegistry::class)->find($section->type)->view(), [
        'section' => $section,
        'menu' => $menu,
        'mediaMap' => $mediaMap,
    ])
@endif
