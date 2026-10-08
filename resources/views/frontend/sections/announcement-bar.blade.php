@php
    $data = $section->dataFor('desktop');
    $classes = $section->visibilityClasses();
    $dismissible = (bool) ($data['dismissible'] ?? false);
    $barId = 'gsm-announce-'.$section->id;
@endphp

<div class="gsm-announcement text-center py-1 small {{ $classes }}" id="{{ $barId }}"
     style="background: {{ $data['bg_color'] ?? '#1b2a4a' }}; color: {{ $data['text_color'] ?? '#fff' }};">
    <div class="container position-relative">
        @include('frontend.partials.device-text', ['section' => $section, 'key' => 'message'])
        @if (!empty($data['link_label']))
            <a href="{{ $data['link_url'] ?? '#' }}" class="ms-1 fw-bold text-decoration-underline" style="color: inherit;">
                {{ $data['link_label'] }}
            </a>
        @endif
        @if ($dismissible)
            <button type="button" class="btn-close btn-close-white position-absolute end-0 top-50 translate-middle-y me-2"
                    style="font-size: .6rem;"
                    onclick="document.getElementById('{{ $barId }}').remove(); sessionStorage.setItem('{{ $barId }}', '1');"></button>
            <script>
                if (sessionStorage.getItem('{{ $barId }}')) document.getElementById('{{ $barId }}').remove();
            </script>
        @endif
    </div>
</div>
