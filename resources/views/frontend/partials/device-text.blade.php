{{-- Renders a per-device text value as breakpoint-scoped spans.
     Usage: @include('frontend.partials.device-text', ['section' => $s, 'key' => 'message'])
     deviceValues() contract: the '*' key being PRESENT means the value is
     collapsed (identical on all devices) — its value may legitimately be
     null, so key presence must be tested, never isset().

     Inside the builder preview this is also the hook for inline editing.
     One partial renders essentially every piece of section text in the CMS,
     so marking it here makes headings, sub-headings and eyebrows editable on
     the page itself without touching 40-odd section blades.

     Only the collapsed case is offered: once a value differs per device,
     "which one am I editing?" has no honest answer on a single click, and
     the side panel's device tabs are the right tool. --}}
@php
    $values = $section->deviceValues($key);
    $inlineEditable = array_key_exists('*', $values) && gsm_inline_editing();
@endphp

@if (array_key_exists('*', $values))
    @if ($inlineEditable)
        <span class="gsm-editable"
              data-gsm-edit="{{ $section->id }}:{{ $key }}"
              role="textbox"
              tabindex="0"
              aria-label="Edit {{ str_replace('_', ' ', $key) }}"
              title="Click to edit">{{ $values['*'] }}</span>
    @else
        {{ $values['*'] }}
    @endif
@else
    <span class="d-none d-lg-inline">{{ $values['desktop'] ?? '' }}</span>
    <span class="d-none d-md-inline d-lg-none">{{ $values['tablet'] ?? $values['desktop'] ?? '' }}</span>
    <span class="d-inline d-md-none">{{ $values['mobile'] ?? $values['desktop'] ?? '' }}</span>
@endif
