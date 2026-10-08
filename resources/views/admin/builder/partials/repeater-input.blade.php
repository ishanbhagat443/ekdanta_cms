{{--
    One repeater sub-field. $sub (schema), $inputName, $inputValue, $isTemplate

    IMPORTANT: a repeater's own sub-fields can themselves be array-shaped
    (a nested "repeater" sub-field, e.g. TeamSection's members[].social_links,
    or a "gallery" sub-field storing multiple media IDs). Every branch below
    is chosen by the sub-field's declared type BEFORE the value ever reaches
    an {{ }} echo, so an array is never handed to htmlspecialchars() via a
    plain <input value="..."> — each array-shaped type gets its own
    array-aware renderer instead.
--}}
@php
    $nameAttr = $isTemplate ? 'data-name' : 'name';
    $subType = $sub['type'] ?? 'text';
@endphp

@if ($subType === 'repeater')
    {{-- Nested repeater (e.g. members[].social_links): reuse the exact same
         repeater UI/JS as a top-level repeater — "Add row" resolves to the
         nearest ancestor .gsm-repeater via closest(), so nesting already
         works with the existing JS unmodified. --}}
    <div class="gsm-repeater border rounded p-2" data-key="{{ $sub['key'] }}" style="flex: 1 1 100%; min-width: 260px;">
        <label class="mb-1 d-block"><small>{{ $sub['label'] }}</small></label>
        @foreach ((array) ($inputValue ?? []) as $i => $nestedRow)
            <div class="gsm-repeater-row d-flex flex-wrap align-items-start mb-2 pb-2 border-bottom" style="gap:.4rem;">
                @foreach ($sub['fields'] ?? [] as $nestedSub)
                    @include('admin.builder.partials.repeater-input', [
                        'sub' => $nestedSub,
                        'inputName' => "{$inputName}[{$i}][{$nestedSub['key']}]",
                        'inputValue' => is_array($nestedRow) ? ($nestedRow[$nestedSub['key']] ?? '') : '',
                        'isTemplate' => $isTemplate,
                    ])
                @endforeach
                <button type="button" class="btn btn-sm btn-outline-danger gsm-repeater-remove"><i class="fas fa-times"></i></button>
            </div>
        @endforeach
        <template class="gsm-repeater-template">
            <div class="gsm-repeater-row d-flex flex-wrap align-items-start mb-2 pb-2 border-bottom" style="gap:.4rem;">
                @foreach ($sub['fields'] ?? [] as $nestedSub)
                    @include('admin.builder.partials.repeater-input', [
                        'sub' => $nestedSub,
                        'inputName' => "{$inputName}[__i__][{$nestedSub['key']}]",
                        'inputValue' => '',
                        'isTemplate' => true,
                    ])
                @endforeach
                <button type="button" class="btn btn-sm btn-outline-danger gsm-repeater-remove"><i class="fas fa-times"></i></button>
            </div>
        </template>
        <button type="button" class="btn btn-xs btn-outline-secondary gsm-repeater-add">
            <i class="fas fa-plus mr-1"></i>Add row
        </button>
    </div>
@elseif ($subType === 'gallery')
    {{-- Multiple media IDs stored as an array — one number+picker row per
         existing ID, matching the media-picker pattern used elsewhere. --}}
    <div class="gsm-repeater-gallery" style="flex: 1 1 100%; min-width: 220px;" data-key="{{ $sub['key'] }}">
        <label class="mb-1 d-block"><small>{{ $sub['label'] }}</small></label>
        @foreach ((array) ($inputValue ?? []) as $i => $mediaId)
            <div class="input-group input-group-sm mb-1">
                <input type="number" {{ $nameAttr }}="{{ $inputName }}[{{ $i }}]" value="{{ is_scalar($mediaId) ? $mediaId : '' }}"
                       class="form-control">
                <div class="input-group-append">
                    <button type="button" class="btn btn-outline-primary gsm-bf-media-pick-near" title="Choose from library">
                        <i class="fas fa-photo-video"></i>
                    </button>
                </div>
            </div>
        @endforeach
    </div>
@elseif ($subType === 'media')
    <div class="input-group input-group-sm" style="width: 170px;">
        <input type="number" {{ $nameAttr }}="{{ $inputName }}" value="{{ is_scalar($inputValue) ? $inputValue : '' }}"
               class="form-control" placeholder="{{ $sub['label'] }}">
        <div class="input-group-append">
            <button type="button" class="btn btn-outline-primary gsm-bf-media-pick-near" title="Choose from library">
                <i class="fas fa-photo-video"></i>
            </button>
        </div>
    </div>
@elseif ($subType === 'textarea' || $subType === 'richtext')
    <textarea {{ $nameAttr }}="{{ $inputName }}" rows="{{ $subType === 'richtext' ? 4 : 2 }}"
              class="form-control form-control-sm {{ $subType === 'richtext' ? 'gsm-richtext-input' : '' }}"
              style="min-width: 220px; flex: 1;"
              placeholder="{{ $sub['label'] }}">{{ is_scalar($inputValue) ? $inputValue : '' }}</textarea>
@elseif ($subType === 'toggle')
    <div class="custom-control custom-switch" style="padding-top: .35rem;">
        <input type="hidden" {{ $nameAttr }}="{{ $inputName }}" value="0">
        <input type="checkbox" {{ $nameAttr }}="{{ $inputName }}" value="1"
               class="custom-control-input" @checked((bool) $inputValue)>
        <label class="custom-control-label">&nbsp;{{ $sub['label'] }}</label>
    </div>
@elseif ($subType === 'select')
    <select {{ $nameAttr }}="{{ $inputName }}" class="form-control form-control-sm" style="min-width: 140px;">
        @foreach ($sub['options'] ?? [] as $optValue => $optLabel)
            <option value="{{ $optValue }}" @selected(is_scalar($inputValue) && (string) $inputValue === (string) $optValue)>{{ $optLabel }}</option>
        @endforeach
    </select>
@else
    {{-- text, number, color, or any other scalar type: the value is
         guaranteed scalar here — every array-shaped type is handled by a
         branch above, so this plain input never receives an array. --}}
    <input type="{{ $subType === 'number' ? 'number' : ($subType === 'color' ? 'color' : 'text') }}"
           {{ $nameAttr }}="{{ $inputName }}" value="{{ is_scalar($inputValue) ? $inputValue : '' }}"
           class="form-control form-control-sm" style="flex: 1; min-width: 120px;"
           placeholder="{{ $sub['label'] }}">
@endif
