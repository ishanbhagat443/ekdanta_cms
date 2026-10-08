@php
    $data = $section->dataFor('desktop');
    $classes = $section->visibilityClasses();
@endphp

{{-- py-2 ≈ the design's 36px strip height (01 Header.png) --}}
<div class="gsm-topbar-strip py-2 {{ $classes }}"
     style="background: {{ $data['bg_color'] ?? '#1b2a4a' }}; color: {{ $data['text_color'] ?? '#fff' }};">
    <div class="container d-flex flex-wrap justify-content-center justify-content-md-between align-items-center small">
        <div>
            @include('frontend.partials.device-text', ['section' => $section, 'key' => 'left_text'])
            @if (!empty($data['left_link_label']))
                <a href="{{ $data['left_link_url'] ?? '#' }}" class="ms-1 fw-bold" style="color: inherit;">
                    {{ $data['left_link_label'] }}
                </a>
            @endif
        </div>
        <div class="d-none d-md-flex align-items-center gap-3">
            @if (($data['show_email'] ?? true) && setting('business.email'))
                <a href="mailto:{{ setting('business.email') }}" style="color: inherit;" class="text-decoration-none">
                    <i class="far fa-envelope me-1"></i>{{ setting('business.email') }}
                </a>
            @endif
            @if (($data['show_phone'] ?? true) && setting('business.phone'))
                <a href="tel:{{ preg_replace('/\s+/', '', setting('business.phone')) }}" style="color: inherit;" class="text-decoration-none">
                    <i class="fas fa-phone-alt me-1"></i>{{ setting('business.phone') }}
                </a>
            @endif
        </div>
    </div>
</div>
