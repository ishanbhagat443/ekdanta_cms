{{-- Contact information cards (Designs/Contact Us Page/03 Company Details):
     responsive 3 / 2 / 1 grid of cards (gradient shield icon, gold-underline
     title, description) framed by garland ornaments on the left and right.
     Every value is CMS-driven; the shield reuses the theme CTA gradient. --}}
@php
    $data = $section->dataFor('desktop');
    $garlandLeft = $mediaMap[$data['garland_left_media_id'] ?? 0] ?? null;
    $garlandRight = $mediaMap[$data['garland_right_media_id'] ?? 0] ?? null;
    $underline = $mediaMap[$data['title_underline_media_id'] ?? 0] ?? null;
    $cardBg = $data['card_bg'] ?? '#ffffff';
    $cardRadius = (int) ($data['card_radius'] ?? 6);
    $cardGap = (int) ($data['card_gap'] ?? 24);
    $cardShadow = (bool) ($data['card_shadow'] ?? true);
    $cards = collect($data['cards'] ?? [])
        ->filter(fn ($c) => ($c['is_visible'] ?? true) && ! empty($c['title']))
        ->values();
@endphp

@if ($cards->isNotEmpty())
    <div class="gsm-contact-details">
        <div class="container">
            @if (! empty($data['heading']) || ! empty($data['subheading']))
                @include('frontend.partials.section-heading', ['section' => $section])
            @endif

            <div class="gsm-contact-details-inner">
                @if ($garlandLeft)
                    <img src="{{ $garlandLeft->url }}" alt="" loading="lazy" decoding="async"
                         class="gsm-contact-garland gsm-contact-garland-left">
                @endif

                <div class="gsm-contact-cards" style="--gsm-card-gap: {{ $cardGap }}px;">
                    @foreach ($cards as $card)
                        <div class="gsm-contact-card position-relative"
                             style="background: {{ $cardBg }}; border-radius: {{ $cardRadius }}px;{{ $cardShadow ? ' box-shadow: 0 12px 30px rgba(0,0,0,.08);' : '' }}">
                            <span class="gsm-contact-icon" aria-hidden="true">
                                <i class="{{ $card['icon'] ?? 'fas fa-circle' }}"></i>
                            </span>
                            <div class="gsm-contact-card-body">
                                <h3 class="gsm-contact-card-title gsm-serif">
                                    <span>{{ $card['title'] }}</span>
                                    @if ($underline)
                                        <img src="{{ $underline->url }}" alt="" class="gsm-contact-underline" loading="lazy" decoding="async">
                                    @endif
                                </h3>
                                @if (! empty($card['description']))
                                    <p class="gsm-contact-card-desc">{!! nl2br(e($card['description'])) !!}</p>
                                @endif
                            </div>
                            @if (! empty($card['link']))
                                <a href="{{ $card['link'] }}" class="stretched-link" aria-label="{{ $card['title'] }}"></a>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if ($garlandRight)
                    <img src="{{ $garlandRight->url }}" alt="" loading="lazy" decoding="async"
                         class="gsm-contact-garland gsm-contact-garland-right">
                @endif
            </div>
        </div>
    </div>
@endif
