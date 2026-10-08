@php
    $data = $section->dataFor('desktop');
    $items = collect((array) ($data['items'] ?? []))->filter(fn ($i) => ($i['title'] ?? '') !== '')->values();
    $bg = $mediaMap[$data['bg_media_id'] ?? 0] ?? null;
    $clipId = 'gsm-ogee-'.$section->id;
@endphp

@if ($items->isNotEmpty())
    <div class="gsm-process py-5"
         @if ($bg) style="background-image: url('{{ $bg->url }}'); background-size: cover; background-position: center;" @endif>

        {{-- Responsive ogee-arch clip (objectBoundingBox: scales with the box).
             The exported assets contain no frame graphic — flagged; this SVG
             path reproduces the gold mughal-arch frame from the design. --}}
        <svg width="0" height="0" aria-hidden="true" focusable="false" style="position:absolute;">
            <defs>
                <clipPath id="{{ $clipId }}" clipPathUnits="objectBoundingBox">
                    <path d="M.5 0C.56.06.66.07.72.12.78.17.79.25.78.31.77.36.8.4.8.45V.55C.8.6.77.64.78.69.79.75.78.83.72.88.66.93.56.94.5 1 .44.94.34.93.28.88.22.83.21.75.22.69.23.64.2.6.2.55V.45C.2.4.23.36.22.31.21.25.22.17.28.12.34.07.44.06.5 0Z"/>
                </clipPath>
            </defs>
        </svg>

        <div class="container">
            @include('frontend.partials.section-heading', ['section' => $section, 'ornament' => true])

            <div class="gsm-process-row" style="--gsm-process-count: {{ $items->count() }};">
                @foreach ($items as $index => $item)
                    @php
                        $image = $mediaMap[$item['image_media_id'] ?? 0] ?? null;
                        $textFirst = $index % 2 === 0; // steps 1,3: text above the line
                    @endphp
                    <div class="gsm-process-step">
                        <div class="gsm-process-slot gsm-process-top">
                            @if ($textFirst)
                                @include('frontend.sections.partials.process-text', ['item' => $item])
                            @elseif ($image)
                                @include('frontend.sections.partials.process-image', ['image' => $image, 'clipId' => $clipId, 'title' => $item['title']])
                            @endif
                        </div>

                        <div class="gsm-process-marker">
                            <span class="gsm-process-num">{{ $index + 1 }}</span>
                        </div>

                        <div class="gsm-process-slot gsm-process-bottom">
                            @if ($textFirst)
                                @if ($image)
                                    @include('frontend.sections.partials.process-image', ['image' => $image, 'clipId' => $clipId, 'title' => $item['title']])
                                @endif
                            @else
                                @include('frontend.sections.partials.process-text', ['item' => $item])
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
