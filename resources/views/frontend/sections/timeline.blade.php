@php
    $data = $section->dataFor('desktop');
    $milestones = collect((array) ($data['milestones'] ?? []))->filter(fn ($m) => !empty($m['year']));
@endphp

<div class="container py-5">
    @include('frontend.partials.section-heading', ['section' => $section])

    <div class="gsm-timeline mx-auto" style="max-width: 720px;">
        @foreach ($milestones as $milestone)
            <div class="d-flex mb-4">
                <div class="flex-shrink-0 me-3">
                    <span class="badge rounded-pill px-3 py-2" style="background: #4b5563; font-size: .95rem;">
                        {{ $milestone['year'] }}
                    </span>
                </div>
                <div class="border-start ps-3">{{ $milestone['text'] ?? '' }}</div>
            </div>
        @endforeach
    </div>
</div>
