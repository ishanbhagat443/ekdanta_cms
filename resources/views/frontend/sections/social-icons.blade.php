@php
    $data = $section->dataFor('desktop');
    $style = $data['style'] ?? 'boxed';

    // Networks live in Settings → Social Media (single source of truth).
    $networks = [
        'facebook' => 'fab fa-facebook-f',
        'instagram' => 'fab fa-instagram',
        'tiktok' => 'fab fa-tiktok',
        'linkedin' => 'fab fa-linkedin-in',
        'twitter' => 'fab fa-x-twitter',
        'youtube' => 'fab fa-youtube',
        'pinterest' => 'fab fa-pinterest-p',
        'threads' => 'fab fa-threads',
        'telegram' => 'fab fa-telegram-plane',
        'whatsapp_link' => 'fab fa-whatsapp',
    ];

    $active = collect($networks)
        ->map(fn ($icon, $key) => ['icon' => $icon, 'url' => setting("social.$key")])
        ->filter(fn ($n) => !empty($n['url']));
@endphp

@if ($active->isNotEmpty())
    <div class="gsm-social-icons">
        @if (!empty($data['title']))
            <h6 class="text-uppercase fw-bold mb-2">{{ $data['title'] }}</h6>
        @endif
        <div class="d-flex flex-wrap gap-2">
            @foreach ($active as $network)
                <a href="{{ $network['url'] }}" target="_blank" rel="noopener"
                   class="{{ ['boxed' => 'gsm-social-box', 'circle' => 'gsm-social-circle'][$style] ?? '' }} text-decoration-none"
                   @if ($style !== 'circle') style="color: inherit;" @endif>
                    <i class="{{ $network['icon'] }}"></i>
                </a>
            @endforeach
        </div>
    </div>
@endif
