{{-- Renders a buttons repeater: [['label','url','style'], …].
     'style' is a legacy keyword (gradient/outline/solid) or a Button
     Builder slug — Builder styles win when the slug exists. --}}
@php $buttons = collect((array) ($buttons ?? []))->filter(fn ($b) => !empty($b['label'])); @endphp

@if ($buttons->isNotEmpty())
    {{-- gap-3 ≈ the design's ~24px space between hero CTAs (02 Hero.png) --}}
    <div class="d-flex flex-wrap gap-3 {{ ($alignment ?? 'center') === 'center' ? 'justify-content-center' : (($alignment ?? '') === 'end' ? 'justify-content-end' : '') }} mt-3">
        @foreach ($buttons as $button)
            @php
                $styleKey = $button['style'] ?? 'gradient';
                $fallbackClass = match ($styleKey) {
                    'outline' => 'btn btn-outline-primary rounded-pill px-4',
                    'solid' => 'btn btn-primary rounded-pill px-4',
                    default => 'btn gsm-btn-gradient text-white rounded-pill px-4 gsm-text-buttons',
                };
            @endphp
            <x-gsm-button :url="$button['url'] ?? '#'"
                          :label="$button['label']"
                          :style="in_array($styleKey, ['gradient', 'outline', 'solid'], true) ? null : $styleKey"
                          :fallback-class="$fallbackClass" />
        @endforeach
    </div>
@endif
