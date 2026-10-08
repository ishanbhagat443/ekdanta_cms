@php
    $data = $section->dataFor('desktop');
    $address = $data['address_override'] ?: setting('business.address');
@endphp

<div class="gsm-footer-contact">
    @if (!empty($data['title']))
        <h6 class="text-uppercase fw-bold mb-3">{{ $data['title'] }}</h6>
    @endif
    <ul class="list-unstyled small mb-0">
        @if (($data['show_address'] ?? true) && $address)
            <li class="mb-2 d-flex">
                <i class="fas fa-map-marker-alt me-2 mt-1"></i>
                <span style="white-space: pre-line;">{{ $address }}</span>
            </li>
        @endif
        @if (($data['show_email'] ?? true) && setting('business.email'))
            <li class="mb-2">
                <a href="mailto:{{ setting('business.email') }}" class="text-decoration-none" style="color: inherit;">
                    <i class="far fa-envelope me-2"></i>{{ setting('business.email') }}
                </a>
            </li>
        @endif
        @if (($data['show_phone'] ?? true) && setting('business.phone'))
            <li class="mb-2">
                <a href="tel:{{ preg_replace('/\s+/', '', setting('business.phone')) }}" class="text-decoration-none" style="color: inherit;">
                    <i class="fas fa-phone-alt me-2"></i>{{ setting('business.phone') }}
                </a>
            </li>
        @endif
        @if (($data['show_whatsapp'] ?? true) && setting('business.whatsapp'))
            <li class="mb-2">
                <a href="{{ setting('social.whatsapp_link', 'https://wa.me/'.preg_replace('/\D/', '', (string) setting('business.whatsapp'))) }}"
                   target="_blank" rel="noopener" class="text-decoration-none" style="color: inherit;">
                    <i class="fab fa-whatsapp me-2"></i>{{ setting('business.whatsapp') }}
                </a>
            </li>
        @endif
        @if (($data['show_hours'] ?? false) && setting('business.working_hours'))
            <li class="mb-2"><i class="far fa-clock me-2"></i>{{ setting('business.working_hours') }}</li>
        @endif
    </ul>
</div>
