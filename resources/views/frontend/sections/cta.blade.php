@php
    $data = $section->dataFor('desktop');
    $align = $data['alignment'] ?? 'center';
@endphp

<div class="container py-5 text-{{ $align }}">
    @if (!empty($data['heading']))
        <h2 class="gsm-serif mb-2">@include('frontend.partials.device-text', ['section' => $section, 'key' => 'heading'])</h2>
    @endif
    @if (!empty($data['subheading']))
        <p class="text-muted">@include('frontend.partials.device-text', ['section' => $section, 'key' => 'subheading'])</p>
    @endif
    @if (!empty($data['text']))
        <div class="mx-auto" style="max-width: 720px;">{!! $data['text'] !!}</div>
    @endif
    @include('frontend.partials.buttons', ['buttons' => $data['buttons'] ?? [], 'alignment' => $align])
</div>
