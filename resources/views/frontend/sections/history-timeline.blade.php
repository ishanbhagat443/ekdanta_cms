@php
    $data = $section->dataFor('desktop');
    $bg = $mediaMap[$data['bg_media_id'] ?? 0] ?? null;
    $milestones = collect((array) ($data['milestones'] ?? []))->filter(fn ($m) => !empty($m['year']))->values();
    $badgeClipId = 'gsm-history-scallop-'.$section->id;
@endphp

<div class="gsm-floral-border" aria-hidden="true"></div>

@if ($milestones->isNotEmpty())
    <div class="gsm-history"
         @if ($bg) style="background-image: url('{{ $bg->url }}'); background-size: cover;" @endif>

        {{-- 8-point scalloped badge shape (design 07): reproduced with an
             objectBoundingBox clipPath since no frame asset was exported. --}}
        <svg width="0" height="0" aria-hidden="true" focusable="false" style="position:absolute;">
            <defs>
                <clipPath id="{{ $badgeClipId }}" clipPathUnits="objectBoundingBox">
                    <path d="M.5 0 C.56.05.6.06.66.03 C.73 0 .79.05.8.12 C.81.19.85.22.92.24 C1 .27.99.34.94.4 C.9.45.9.5.94.55 C1 .61 1 .68.94.72 C.87.76.83.8.8.87 C.77.94.71.97.65.94 C.59.91.55.92.5 1 C.45.92.41.91.35.94 C.29.97.23.94.2.87 C.17.8.13.76.06.72 C0 .68.01.61.06.55 C.1.5.1.45.06.4 C.01.34 0 .27.06.24 C.13.22.17.19.2.12 C.21.05.27 0 .34.03 C.4.06.44.05.5 0 Z"/>
                </clipPath>
            </defs>
        </svg>

        <div class="container py-5">
            @include('frontend.partials.section-heading', ['section' => $section, 'ornament' => true])

            {{-- Horizontally scrollable, unbounded track — the CMS can add
                 any number of years without breaking the layout (design
                 07 Our History.png shows the last card cut off at the
                 frame edge, confirming the timeline continues). --}}
            <div class="gsm-history-slider">
                <div class="gsm-history-track" data-gsm-history-track>
                    {{-- Real inline SVG connector (design 07 Our History.png):
                         one smooth cubic-bezier segment per adjacent milestone
                         pair, alternating up/down to thread through each
                         badge's translateY(±22%) center. JS (gsm-history-line.js)
                         sizes/repositions this to the track's actual geometry
                         on load/resize, since the CMS can add any number of
                         milestones. Never stored in the DB — purely decorative
                         markup that lives in this Blade view. --}}
                    <svg class="gsm-history-line" data-gsm-history-line
                         viewBox="0 0 1000 200" preserveAspectRatio="none" aria-hidden="true" focusable="false">
                        <path data-gsm-history-path fill="none" stroke="#b3aa98" stroke-width="2"
                              stroke-dasharray="6 7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>

                    @foreach ($milestones as $index => $milestone)
                        @php
                            $image = $mediaMap[$milestone['image_media_id'] ?? 0] ?? null;
                            // The badge+content unit itself rises/falls in a wave
                            // (design 07); text always sits above the badge,
                            // the photo always sits below it.
                            $badgeHigh = $index % 2 === 0;
                            $badgeColor = $badgeHigh ? 'gsm-history-badge-gold' : 'gsm-history-badge-orange';
                        @endphp
                        <div class="gsm-history-item {{ $badgeHigh ? 'gsm-history-item-high' : 'gsm-history-item-low' }}" data-gsm-history-item>
                            <div class="gsm-history-text">{{ $milestone['text'] }}</div>

                            <div class="gsm-history-marker">
                                <span class="gsm-history-badge {{ $badgeColor }} gsm-serif" data-gsm-history-badge
                                      style="clip-path: url(#{{ $badgeClipId }});">{{ $milestone['year'] }}</span>
                            </div>

                            @if ($image)
                                <img loading="lazy" decoding="async" src="{{ $image->variantUrl('card') ?? $image->url }}"
                                     alt="{{ $milestone['year'] }}" class="gsm-history-photo">
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="gsm-history-dots" aria-hidden="true"></div>
            </div>
        </div>
    </div>
@endif
