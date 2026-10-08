@php
    $data = $section->dataFor('desktop');
    $counters = collect((array) ($data['counters'] ?? []))->filter(fn ($c) => ($c['number'] ?? '') !== '');
    $animate = (bool) ($data['animate'] ?? true);
    $showOrnament = (bool) ($data['show_ornament'] ?? true);
    $colLg = max(2, (int) floor(12 / max(1, $counters->count())));
@endphp

{{-- Gold garland border along the top edge (design 03 Counter.png) --}}
@if ($showOrnament)
    <div class="gsm-garland-border" aria-hidden="true"></div>
@endif

<div class="container py-5">
    @include('frontend.partials.section-heading', ['section' => $section])

    <div class="row g-4 mt-1" data-anim-children>
        @foreach ($counters as $counter)
            <div class="col-6 col-lg-{{ $colLg }} text-start">
                <div class="display-5 gsm-serif fst-italic gsm-counter-number">
                    <span @if($animate) class="gsm-countup" data-target="{{ preg_replace('/\D/', '', $counter['number']) ?: $counter['number'] }}" @endif>{{ $counter['number'] }}</span>{{ $counter['suffix'] ?? '' }}
                </div>
                <div class="text-uppercase small text-muted mt-2 gsm-counter-label">{{ $counter['label'] ?? '' }}</div>
            </div>
        @endforeach
    </div>
</div>
