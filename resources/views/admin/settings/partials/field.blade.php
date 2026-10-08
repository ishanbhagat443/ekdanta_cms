@php
    $type = $field['type'] ?? 'text';
    $inputId = "setting_{$group}_{$key}";
    // Full-width for long content types, half-width for short inputs.
    $col = in_array($type, ['textarea', 'code', 'image'], true) ? 'col-12' : 'col-12 col-lg-6';
@endphp

<div class="{{ $col }}">
    <div class="form-group">
        <label for="{{ $inputId }}">{{ $field['label'] }}</label>

        @if ($type === 'textarea')
            <textarea name="{{ $key }}" id="{{ $inputId }}" rows="3"
                      class="form-control @error($key) is-invalid @enderror">{{ old($key, $value) }}</textarea>

        @elseif ($type === 'code')
            <textarea name="{{ $key }}" id="{{ $inputId }}" rows="6" spellcheck="false"
                      class="form-control text-monospace @error($key) is-invalid @enderror">{{ old($key, $value) }}</textarea>

        @elseif ($type === 'checkbox')
            <div class="custom-control custom-switch">
                <input type="hidden" name="{{ $key }}" value="0">
                <input type="checkbox" class="custom-control-input" id="{{ $inputId }}"
                       name="{{ $key }}" value="1" @checked((bool) old($key, $value))>
                <label class="custom-control-label" for="{{ $inputId }}">&nbsp;</label>
            </div>

        @elseif ($type === 'image')
            @if ($value)
                <div class="d-flex align-items-center mb-2">
                    <img src="{{ Storage::disk('public')->url($value) }}" alt="{{ $field['label'] }}"
                         class="img-thumbnail mr-3 bg-secondary" style="max-height: 60px; max-width: 160px;">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input"
                               id="remove_{{ $inputId }}" name="remove[{{ $key }}]" value="1">
                        <label class="custom-control-label text-danger" for="remove_{{ $inputId }}">Remove</label>
                    </div>
                </div>
            @endif
            <div class="custom-file">
                <input type="file" name="{{ $key }}" id="{{ $inputId }}"
                       class="custom-file-input @error($key) is-invalid @enderror" accept="image/*,.ico,.svg">
                <label class="custom-file-label" for="{{ $inputId }}">
                    {{ $value ? 'Replace file…' : 'Choose file…' }}
                </label>
                @error($key)
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
            </div>

        @else
            <input type="{{ $type === 'number' ? 'text' : $type }}"
                   name="{{ $key }}" id="{{ $inputId }}"
                   value="{{ old($key, $value) }}"
                   class="form-control @error($key) is-invalid @enderror">
        @endif

        @if ($type !== 'image')
            @error($key)
                <span class="invalid-feedback d-block">{{ $message }}</span>
            @enderror
        @endif

        @isset($field['hint'])
            <small class="form-text text-muted">{{ $field['hint'] }}</small>
        @endisset
    </div>
</div>
