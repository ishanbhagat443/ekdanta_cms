@php
    $data = $section->dataFor('desktop');
    $source = $data['source'] ?? 'custom';
    $links = collect();

    if ($source === 'menu' && $menu) {
        $links = $menu->rootItems
            ->filter(fn ($i) => $i->isVisibleToCurrentUser())
            ->map(fn ($i) => ['label' => $i->displayLabel(), 'url' => $i->resolvedUrl(), 'target' => $i->target]);
    } else {
        $links = collect((array) ($data['links'] ?? []))
            ->map(fn ($l) => ['label' => $l['label'] ?? '', 'url' => $l['url'] ?? '#', 'target' => '_self'])
            ->filter(fn ($l) => $l['label'] !== '');
    }
@endphp

<div class="gsm-footer-links">
    @if (!empty($data['title']))
        <h6 class="text-uppercase fw-bold mb-3">
            @include('frontend.partials.device-text', ['section' => $section, 'key' => 'title'])
        </h6>
    @endif
    <ul class="list-unstyled small mb-0">
        @foreach ($links as $link)
            <li class="mb-2">
                <a href="{{ $link['url'] }}" target="{{ $link['target'] }}"
                   class="text-decoration-none" style="color: inherit;">{{ $link['label'] }}</a>
            </li>
        @endforeach
    </ul>
</div>
