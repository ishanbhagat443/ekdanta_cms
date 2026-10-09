@php
    $type = $field['type'] ?? 'color';
    $inputId = 'theme_'.$groupKey.'_'.$key;
    $inputName = $key;
@endphp

<div class="form-group">
    <label for="{{ $inputId }}"><small>{{ $field['label'] }}</small></label>

    @switch($type)
        @case('color')
            <div class="input-group input-group-sm">
                <input type="color" name="{{ $inputName }}" id="{{ $inputId }}"
                       value="{{ $value ?? $field['default'] }}" class="form-control form-control-color gsm-theme-field" style="max-width:52px;">
                <input type="text" class="form-control gsm-theme-color-text" data-for="{{ $inputId }}"
                       value="{{ $value ?? $field['default'] }}">
            </div>
            @break

        @case('select')
            <select name="{{ $inputName }}" id="{{ $inputId }}" class="form-control form-control-sm gsm-theme-field">
                @foreach ($field['options'] ?? [] as $optValue => $optLabel)
                    <option value="{{ $optValue }}" @selected(($value ?? $field['default']) === $optValue)>{{ $optLabel }}</option>
                @endforeach
            </select>
            @break

        @case('toggle')
            <div class="custom-control custom-switch">
                <input type="hidden" name="{{ $inputName }}" value="0">
                <input type="checkbox" class="custom-control-input gsm-theme-field" id="{{ $inputId }}"
                       name="{{ $inputName }}" value="1" @checked($value ?? $field['default'])>
                <label class="custom-control-label" for="{{ $inputId }}"></label>
            </div>
            @break

        @case('number')
            <input type="number" name="{{ $inputName }}" id="{{ $inputId }}"
                   value="{{ $value ?? $field['default'] }}" min="{{ $field['min'] ?? 0 }}" max="{{ $field['max'] ?? 999 }}"
                   class="form-control form-control-sm gsm-theme-field">
            @break

        @case('code')
            <textarea name="{{ $inputName }}" id="{{ $inputId }}" rows="6" spellcheck="false"
                      class="form-control form-control-sm text-monospace gsm-theme-field"
                      data-lang="{{ $field['lang'] ?? 'css' }}">{{ $value }}</textarea>
            @break

        @default
            <input type="text" name="{{ $inputName }}" id="{{ $inputId }}"
                   value="{{ $value ?? $field['default'] ?? '' }}" class="form-control form-control-sm gsm-theme-field">
    @endswitch

    @if (! empty($field['hint']))
        <small class="form-text text-muted">{{ $field['hint'] }}</small>
    @endif
</div>
