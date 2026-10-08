@php
    $data = $section->dataFor('desktop');
    $items = collect((array) ($data['items'] ?? []))->filter(fn ($i) => !empty($i['title']));
    $side = $mediaMap[$data['side_media_id'] ?? 0] ?? null;
    $firstOpen = (bool) ($data['first_open'] ?? true);
    $accId = 'gsm-acc-'.$section->id;
    $align = $data['heading_align'] ?? 'center';
    $ornament = (bool) ($data['heading_ornament'] ?? false);
    $isCardStyle = ($data['panel_style'] ?? 'default') === 'cards';
@endphp

<div class="container py-5">
    @include('frontend.partials.section-heading', ['section' => $section, 'align' => $align, 'ornament' => $ornament])

    <div class="row g-4 align-items-center">
        <div class="{{ $side ? 'col-12 col-lg-7' : 'col-12' }}">
            <div class="accordion {{ $isCardStyle ? 'gsm-accordion-cards' : '' }}" id="{{ $accId }}">
                @foreach ($items as $i => $item)
                    <div class="accordion-item">
                        <h3 class="accordion-header">
                            <button class="accordion-button {{ $i === 0 && $firstOpen ? '' : 'collapsed' }}"
                                    type="button" data-bs-toggle="collapse" data-bs-target="#{{ $accId }}-{{ $i }}">
                                {{ $item['title'] }}
                                @if ($isCardStyle)
                                    <span class="gsm-accordion-toggle-icon" aria-hidden="true"></span>
                                @endif
                            </button>
                        </h3>
                        <div id="{{ $accId }}-{{ $i }}"
                             class="accordion-collapse collapse {{ $i === 0 && $firstOpen ? 'show' : '' }}"
                             data-bs-parent="#{{ $accId }}">
                            <div class="accordion-body">{!! nl2br(e($item['body'])) !!}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @if ($side)
            <div class="col-12 col-lg-5 text-center">
                <img loading="lazy" decoding="async" src="{{ $side->variantUrl('card') ?? $side->url }}" alt="" class="img-fluid rounded {{ $isCardStyle ? 'gsm-accordion-side-photo' : '' }}">
            </div>
        @endif
    </div>
</div>
