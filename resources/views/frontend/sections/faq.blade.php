@php
    $data = $section->dataFor('desktop');
    $items = collect((array) ($data['items'] ?? []))->filter(fn ($i) => !empty($i['question']));
    $accId = 'gsm-faq-'.$section->id;
@endphp

<div class="container py-5" style="max-width: 860px;">
    @include('frontend.partials.section-heading', ['section' => $section])

    <div class="accordion" id="{{ $accId }}">
        @foreach ($items as $i => $item)
            <div class="accordion-item">
                <h3 class="accordion-header">
                    <button class="accordion-button {{ $i === 0 ? '' : 'collapsed' }}" type="button"
                            data-bs-toggle="collapse" data-bs-target="#{{ $accId }}-{{ $i }}">
                        {{ $item['question'] }}
                    </button>
                </h3>
                <div id="{{ $accId }}-{{ $i }}" class="accordion-collapse collapse {{ $i === 0 ? 'show' : '' }}"
                     data-bs-parent="#{{ $accId }}">
                    <div class="accordion-body">{!! nl2br(e($item['answer'])) !!}</div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@if (($data['schema_enabled'] ?? true) && $items->isNotEmpty())
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $items->map(fn ($i) => [
                '@type' => 'Question',
                'name' => $i['question'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $i['answer'] ?? ''],
            ])->values()->all(),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endif
