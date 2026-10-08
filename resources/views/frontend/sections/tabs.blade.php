@php
    $data = $section->dataFor('desktop');
    $tabs = collect((array) ($data['tabs'] ?? []))->filter(fn ($t) => !empty($t['title']));
    $tabId = 'gsm-tabs-'.$section->id;
@endphp

<div class="container py-5">
    @include('frontend.partials.section-heading', ['section' => $section])

    <ul class="nav nav-tabs justify-content-center" role="tablist">
        @foreach ($tabs as $i => $tab)
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $i === 0 ? 'active' : '' }}" data-bs-toggle="tab"
                        data-bs-target="#{{ $tabId }}-{{ $i }}" type="button" role="tab">
                    {{ $tab['title'] }}
                </button>
            </li>
        @endforeach
    </ul>
    <div class="tab-content border border-top-0 rounded-bottom p-4">
        @foreach ($tabs as $i => $tab)
            <div class="tab-pane fade {{ $i === 0 ? 'show active' : '' }}" id="{{ $tabId }}-{{ $i }}" role="tabpanel">
                {!! nl2br(e($tab['body'])) !!}
            </div>
        @endforeach
    </div>
</div>
