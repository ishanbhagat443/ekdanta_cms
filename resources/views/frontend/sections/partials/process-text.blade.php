{{-- One process step's text cell. $item = ['title' => ..., 'text' => ...] --}}
<div class="gsm-process-text text-center">
    <h3 class="gsm-serif fw-bold gsm-process-title mb-2">{{ $item['title'] }}</h3>
    @if (!empty($item['text']))
        <p class="gsm-text-paragraph mb-0">{{ $item['text'] }}</p>
    @endif
</div>
