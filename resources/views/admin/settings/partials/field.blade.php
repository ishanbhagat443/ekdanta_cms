@php
    $type = $field['type'] ?? 'text';
    $inputId = "setting_{$group}_{$key}";
    // Full-width for long content types, half-width for short inputs.
    $col = in_array($type, ['textarea', 'code', 'image', 'list'], true) ? 'col-12' : 'col-12 col-lg-6';
    // Never saved: show the wording the site already uses (config "default").
    $value ??= $field['default'] ?? null;
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

        @elseif ($type === 'list')
            {{-- Repeating rows (same markup as the page builder's repeater). The
                 empty hidden field lets "remove every row" clear the setting. --}}
            @php
                $rows = old($key, json_decode((string) $value, true) ?: []);
                $rows = is_array($rows) ? array_values($rows) : [];
            @endphp
            <input type="hidden" name="{{ $key }}" value="">
            <div class="gsm-repeater border rounded p-2" id="{{ $inputId }}" data-key="{{ $key }}">
                {{-- Saved rows, then one blank "__i__" row inside <template> that "Add" clones. --}}
                @foreach ($rows + ['__i__' => []] as $i => $row)
                    @if ($i === '__i__') <template class="gsm-repeater-template"> @endif
                    <div class="gsm-repeater-row d-flex flex-wrap align-items-start mb-2 pb-2 border-bottom" style="gap:.4rem;">
                        @foreach ($field['fields'] as $sub)
                            @include('admin.builder.partials.repeater-input', [
                                'sub' => $sub, 'inputName' => "{$key}[{$i}][{$sub['key']}]",
                                'inputValue' => is_array($row) ? ($row[$sub['key']] ?? '') : '', 'isTemplate' => $i === '__i__',
                            ])
                        @endforeach
                        <button type="button" class="btn btn-sm btn-outline-danger gsm-repeater-remove" aria-label="Remove this row"><i class="fas fa-times" aria-hidden="true"></i></button>
                    </div>
                    @if ($i === '__i__') </template> @endif
                @endforeach
                <button type="button" class="btn btn-sm btn-outline-secondary gsm-repeater-add"><i class="fas fa-plus mr-1" aria-hidden="true"></i>Add {{ strtolower(\Illuminate\Support\Str::singular($field['label'])) }}</button>
            </div>
            @foreach ($errors->get($key.'.*') as $messages)
                <span class="invalid-feedback d-block">{{ $messages[0] }}</span>
            @endforeach

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
