@php
    $data = $section->dataFor('desktop');
    $members = collect((array) ($data['members'] ?? []))->filter(fn ($m) => !empty($m['name']));
    $ornament = (bool) ($data['heading_ornament'] ?? false);
    $showBorder = (bool) ($data['show_border'] ?? false);
@endphp

<div class="gsm-team {{ $showBorder ? 'gsm-team-bordered' : '' }}">
    <div class="container py-5">
        @include('frontend.partials.section-heading', ['section' => $section, 'ornament' => $ornament])

        <div class="row g-4 justify-content-center" data-anim-children>
            @foreach ($members as $member)
                @php
                    $photo = $mediaMap[$member['media_id'] ?? 0] ?? null;
                    $socialLinks = collect((array) ($member['social_links'] ?? []))->filter(fn ($s) => !empty($s['url']));
                @endphp
                <div class="col-12 col-sm-6 col-lg-4 text-center">
                    @if ($photo)
                        <img loading="lazy" decoding="async" src="{{ $photo->variantUrl('card') ?? $photo->url }}" alt="{{ $member['name'] }}"
                             class="gsm-team-photo rounded-4 mb-3 w-100">
                    @endif
                    <h5 class="gsm-serif gsm-team-name mb-0">{{ $member['name'] }}</h5>
                    @if (!empty($member['role']))
                        <div class="text-muted small">{{ $member['role'] }}</div>
                    @endif
                    @if (!empty($member['text']))
                        <p class="gsm-text-paragraph small mt-2 mb-0">{{ $member['text'] }}</p>
                    @endif
                    @if ($socialLinks->isNotEmpty())
                        <div class="d-flex justify-content-center gap-2 mt-3">
                            @foreach ($socialLinks as $link)
                                <a href="{{ $link['url'] }}" target="_blank" rel="noopener" class="gsm-team-social" aria-label="Social link">
                                    @if (!empty($link['icon']))
                                        <i class="{{ $link['icon'] }}"></i>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
