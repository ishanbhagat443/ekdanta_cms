{{-- Standard heading block: eyebrow + heading + subheading (device-aware).
     Typography comes from the Theme Customizer roles (Section Heading /
     Sub Heading). Pass 'ornament' => true to render the black diamond
     divider (design: heading_line.png) under the heading. --}}
@php
    $data = $section->dataFor('desktop');
    $ornament = $ornament ?? false;
@endphp

@if (!empty($data['eyebrow']) || !empty($data['heading']) || !empty($data['subheading']))
    <div class="text-{{ $align ?? 'center' }} mb-4">
        @if (!empty($data['eyebrow']))
            <div class="text-uppercase small fw-bold gsm-eyebrow mb-1">
                @include('frontend.partials.device-text', ['section' => $section, 'key' => 'eyebrow'])
            </div>
        @endif
        @if (!empty($data['heading']))
            <h2 class="gsm-serif gsm-text-section-heading mb-2">
                @include('frontend.partials.device-text', ['section' => $section, 'key' => 'heading'])
            </h2>
        @endif
        @if ($ornament && !empty($data['heading']))
            {{-- Clean gold rule under the heading. --}}
            <span class="gsm-heading-rule" aria-hidden="true"></span>
        @endif
        @if (!empty($data['subheading']))
            <p class="text-muted gsm-text-sub-heading mb-0">
                @include('frontend.partials.device-text', ['section' => $section, 'key' => 'subheading'])
            </p>
        @endif
    </div>
@endif
