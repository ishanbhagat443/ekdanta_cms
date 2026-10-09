{{--
    Renders one section-type field for a device tab.
    Vars: $field (schema), $value, $device, $sectionId, $menus (for menu type)
    Inputs are named data[key] inside each device form.
--}}
@php
    $key = $field['key'];
    $type = $field['type'];
    $inputId = "s{$sectionId}_{$device}_{$key}";
    $isDeviceField = (bool) ($field['device'] ?? false);
    $placeholder = $device !== 'desktop' && $isDeviceField ? 'Inherits desktop value' : '';
@endphp

@if ($device === 'desktop' || $isDeviceField)
    <div class="form-group">
        <label for="{{ $inputId }}" class="mb-1">
            <small>{{ $field['label'] }}
                @if ($isDeviceField && $device === 'desktop')
                    <span class="badge badge-light border" title="Can be overridden per device">
                        <i class="fas fa-mobile-alt"></i>
                    </span>
                @endif
            </small>

            {{-- Only on writing fields, and only when an API key is configured:
                 with no key the assistant does not exist as far as the UI is
                 concerned. The result lands in the field for the user to keep
                 or undo — nothing is saved on their behalf. --}}
            @if (in_array($type, ['text', 'textarea', 'richtext'], true) && app(\App\Services\AiAssistService::class)->allows('improve_text'))
                <span class="dropdown gsm-ai">
                    <button type="button" class="gsm-ai__btn" data-toggle="dropdown"
                            title="Improve this text" aria-label="Improve this text with AI">
                        <i class="fas fa-wand-magic-sparkles" aria-hidden="true"></i>
                    </button>
                    <span class="dropdown-menu dropdown-menu-right">
                        @foreach ([
                            'clearer' => 'Make it clearer',
                            'shorter' => 'Make it shorter',
                            'longer' => 'Make it longer',
                            'friendlier' => 'Warmer tone',
                            'formal' => 'More formal',
                            'fix' => 'Fix spelling and grammar',
                        ] as $toneKey => $toneLabel)
                            <button type="button" class="dropdown-item gsm-ai-action"
                                    data-target="{{ $inputId }}" data-tone="{{ $toneKey }}">{{ $toneLabel }}</button>
                        @endforeach
                    </span>
                </span>
            @endif
        </label>

        @switch($type)
            @case('textarea')
                <textarea name="data[{{ $key }}]" id="{{ $inputId }}" rows="2"
                          class="form-control form-control-sm" placeholder="{{ $placeholder }}">{{ $value }}</textarea>
                @break

            @case('code')
                <textarea name="data[{{ $key }}]" id="{{ $inputId }}" rows="5" spellcheck="false"
                          class="form-control form-control-sm text-monospace" placeholder="{{ $placeholder }}">{{ $value }}</textarea>
                @break

            @case('richtext')
                <textarea name="data[{{ $key }}]" id="{{ $inputId }}" rows="6"
                          class="form-control form-control-sm gsm-richtext-input"
                          placeholder="{{ $placeholder }}">{{ $value }}</textarea>
                @break

            @case('toggle')
                <div class="custom-control custom-switch">
                    <input type="hidden" name="data[{{ $key }}]" value="0">
                    <input type="checkbox" name="data[{{ $key }}]" id="{{ $inputId }}" value="1"
                           class="custom-control-input" @checked((bool) $value)>
                    <label class="custom-control-label" for="{{ $inputId }}">&nbsp;</label>
                </div>
                @break

            @case('select')
                <select name="data[{{ $key }}]" id="{{ $inputId }}" class="form-control form-control-sm">
                    @foreach ($field['options'] ?? [] as $optValue => $optLabel)
                        <option value="{{ $optValue }}" @selected($value === $optValue)>{{ $optLabel }}</option>
                    @endforeach
                </select>
                @break

            @case('menu')
                <select name="data[{{ $key }}]" id="{{ $inputId }}" class="form-control form-control-sm">
                    <option value="">— Select menu —</option>
                    @foreach ($menus as $menuOption)
                        <option value="{{ $menuOption->id }}" @selected((int) $value === $menuOption->id)>
                            {{ $menuOption->name }} ({{ $menuOption->items_count }} items)
                        </option>
                    @endforeach
                </select>
                @break

            @case('media')
                {{-- Text, not number: a value can also be a stable key ("ekdanta/logo-colour"). --}}
                <div class="input-group input-group-sm">
                    <input type="text" name="data[{{ $key }}]" id="{{ $inputId }}" value="{{ $value }}"
                           class="form-control gsm-bf-media-id" placeholder="{{ $placeholder ?: 'Media ID' }}">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-outline-primary gsm-bf-media-pick"
                                data-target="{{ $inputId }}">
                            <i class="fas fa-photo-video"></i> Choose
                        </button>
                    </div>
                </div>
                @break

            @case('link')
                <input type="text" name="data[{{ $key }}]" id="{{ $inputId }}" value="{{ $value }}" maxlength="500"
                       class="form-control form-control-sm" placeholder="{{ $placeholder ?: '/contact, #c-brief, https://…, tel:…, wa:…' }}">
                @break

            @case('number')
                <input type="number" name="data[{{ $key }}]" id="{{ $inputId }}" value="{{ $value }}"
                       class="form-control form-control-sm" placeholder="{{ $placeholder }}">
                @break

            @case('color')
                <input type="color" name="data[{{ $key }}]" id="{{ $inputId }}" value="{{ $value ?: '#000000' }}"
                       class="form-control form-control-sm" style="max-width: 80px; height: 31px; padding: 2px;">
                @break

            @case('repeater')
                <div class="gsm-repeater border rounded p-2" data-key="{{ $key }}">
                    @foreach ((array) ($value ?? []) as $i => $row)
                        <div class="gsm-repeater-row d-flex flex-wrap align-items-start mb-2 pb-2 border-bottom" style="gap:.4rem;">
                            @foreach ($field['fields'] as $sub)
                                @include('admin.builder.partials.repeater-input', [
                                    'sub' => $sub,
                                    'inputName' => "data[{$key}][{$i}][{$sub['key']}]",
                                    'inputValue' => $row[$sub['key']] ?? '',
                                    'isTemplate' => false,
                                ])
                            @endforeach
                            <button type="button" class="btn btn-sm btn-outline-danger gsm-repeater-remove" aria-label="Remove this row"><i class="fas fa-times" aria-hidden="true"></i></button>
                        </div>
                    @endforeach
                    <template class="gsm-repeater-template">
                        <div class="gsm-repeater-row d-flex flex-wrap align-items-start mb-2 pb-2 border-bottom" style="gap:.4rem;">
                            @foreach ($field['fields'] as $sub)
                                @include('admin.builder.partials.repeater-input', [
                                    'sub' => $sub,
                                    'inputName' => "data[{$key}][__i__][{$sub['key']}]",
                                    'inputValue' => '',
                                    'isTemplate' => true,
                                ])
                            @endforeach
                            <button type="button" class="btn btn-sm btn-outline-danger gsm-repeater-remove" aria-label="Remove this row"><i class="fas fa-times" aria-hidden="true"></i></button>
                        </div>
                    </template>
                    <button type="button" class="btn btn-xs btn-outline-secondary gsm-repeater-add">
                        <i class="fas fa-plus mr-1"></i>Add row
                    </button>
                </div>
                @break

            @default
                <input type="text" name="data[{{ $key }}]" id="{{ $inputId }}" value="{{ $value }}"
                       class="form-control form-control-sm" placeholder="{{ $placeholder }}">
        @endswitch

        @isset($field['hint'])
            <small class="form-text text-muted">{{ $field['hint'] }}</small>
        @endisset
    </div>
@endif
