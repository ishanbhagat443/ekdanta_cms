{{-- Renders a template page's CMS blocks in order. Falls back to the given
     blade so a screen is never blank when no blocks are configured.
     Every parent-scope variable (cart items, order, user …) stays visible to
     the section blades because @include inherits the current scope. --}}
@php
    $payload = app(\App\Services\PageRenderService::class)->payloadFor($templateSlug);
    $blocks = $payload['sections'] ?? collect();
@endphp

@forelse ($blocks as $entry)
    @include('frontend.partials.render-section', [
        'section' => $entry['section'],
        'menu' => $entry['menu'],
        'mediaMap' => $entry['media'],
    ])
@empty
    @include('frontend.sections.'.$fallback, [
        'section' => new \App\Models\Section(['type' => $fallback, 'data_desktop' => []]),
        'menu' => null,
        'mediaMap' => [],
    ])
@endforelse
