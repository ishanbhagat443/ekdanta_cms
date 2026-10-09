{{--
    Generic group form for every non-typography tab (colors, buttons,
    spacing, radius, shadows, animations, custom_code, dark_mode). Fields
    render entirely from config('theme.{group}.fields') — nothing hardcoded.
--}}
<form method="POST" action="{{ route('admin.theme.update', $groupKey) }}" class="gsm-theme-group-form" data-group="{{ $groupKey }}">
    @csrf
    @method('PUT')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="{{ $schema['icon'] ?? 'fas fa-cog' }} mr-1"></i> {{ $schema['label'] }}</h3>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach ($schema['fields'] as $key => $field)
                    {{-- Site-wide scripts: Developer Mode only (enforced in ThemeCustomizerController). --}}
                    @continue(! empty($field['developer']) && ! auth()->user()?->can('developer.mode'))
                    {{-- select fields without explicit options: resolve from the group's shared enum (weights/levels/types) --}}
                    @php
                        if (($field['type'] ?? null) === 'select' && empty($field['options'])) {
                            $field['options'] = match ($groupKey) {
                                'shadows' => $schema['levels'] ?? [],
                                'animations' => $schema['types'] ?? [],
                                default => $field['options'] ?? [],
                            };
                        }
                    @endphp
                    <div class="col-12 col-md-6">
                        @include('admin.theme.partials.field', [
                            'groupKey' => $groupKey,
                            'key' => $key,
                            'field' => $field,
                            'value' => $values[$key] ?? null,
                        ])
                    </div>
                @endforeach
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-save mr-1"></i> Save {{ $schema['label'] }}
            </button>
        </div>
    </div>
</form>
