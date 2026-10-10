@extends('admin.layouts.app')

@section('page_title', $preset->exists ? 'Edit motion preset' : 'New motion preset')

@section('page_actions')
    <a href="{{ route('admin.motion-presets.index') }}" class="btn btn-outline-secondary btn-sm">Back to the library</a>
@stop

@php
    $json = fn ($value) => $value === null || $value === [] ? '' : json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp

@section('page_content')
<form method="POST" action="{{ $preset->exists ? route('admin.motion-presets.update', $preset) : route('admin.motion-presets.store') }}">
    @csrf
    @if ($preset->exists) @method('PUT') @endif

    <div class="row">
        <div class="col-12 col-xl-7">
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-wand-magic-sparkles mr-2" aria-hidden="true"></i>Preset</h3></div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="mp-name">Name <span class="text-danger" aria-hidden="true">*</span></label>
                            <input type="text" name="name" id="mp-name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $preset->name) }}" required maxlength="100">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="mp-slug">Slug <small class="text-muted">(made from the name if empty)</small></label>
                            <input type="text" name="slug" id="mp-slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $preset->slug) }}" maxlength="80" pattern="[a-z0-9-]+">
                            @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mp-description">What it does</label>
                        <input type="text" name="description" id="mp-description" class="form-control" value="{{ old('description', $preset->description) }}" maxlength="500">
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="mp-kind">Kind</label>
                            <select name="kind" id="mp-kind" class="form-control">
                                @foreach (\App\Models\MotionPreset::KINDS as $value => $label)
                                    <option value="{{ $value }}" @selected(old('kind', $preset->kind) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="mp-trigger">Default trigger</label>
                            <select name="trigger" id="mp-trigger" class="form-control @error('trigger') is-invalid @enderror">
                                @foreach (\App\Support\SectionMotion::TRIGGERS as $value => $label)
                                    <option value="{{ $value }}" @selected(old('trigger', $preset->trigger) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('trigger')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mp-keyframes">Keyframes (JSON)</label>
                        <textarea name="keyframes" id="mp-keyframes" rows="8" spellcheck="false" class="form-control text-monospace @error('keyframes') is-invalid @enderror" aria-describedby="mp-keyframes-help">{{ old('keyframes', $json($preset->keyframes)) }}</textarea>
                        @error('keyframes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small id="mp-keyframes-help" class="form-text text-muted">
                            Anime.js: <code>{"opacity": [0, 1], "translateY": ["{distance}", 0]}</code>. Plain numbers on x, y, translate, top, left, width and height are design px (they scale with the screen); write <code>"32px"</code> for a fixed distance.
                            Extras: <code>"$split": "words"</code> or <code>"chars"</code>, <code>"$count": "en-IN"</code>, <code>"draw": ["0 0", "0 1"]</code>.
                            CSS: <code>{"0%,100%": {"transform": "rotate(0deg)"}, "50%": {"transform": "rotate(calc(var(--angle) * 1deg))"}}</code>.
                        </small>
                    </div>
                    <div class="form-group">
                        <label for="mp-params">Settings a track can change (JSON)</label>
                        <textarea name="params" id="mp-params" rows="3" spellcheck="false" class="form-control text-monospace @error('params') is-invalid @enderror" aria-describedby="mp-params-help">{{ old('params', $json($preset->params)) }}</textarea>
                        @error('params')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small id="mp-params-help" class="form-text text-muted">Default values for the <code>{name}</code> placeholders (Anime.js) or <code>var(--name)</code> variables (CSS), e.g. <code>{"distance": 40}</code>.</small>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-6 col-md-3">
                            <label for="mp-duration">Duration (ms)</label>
                            <input type="number" name="duration" id="mp-duration" class="form-control" min="0" max="600000" value="{{ old('duration', $preset->duration) }}" required>
                        </div>
                        <div class="form-group col-6 col-md-3">
                            <label for="mp-delay">Delay (ms)</label>
                            <input type="number" name="delay" id="mp-delay" class="form-control" min="0" max="60000" value="{{ old('delay', $preset->delay) }}">
                        </div>
                        <div class="form-group col-6 col-md-3">
                            <label for="mp-stagger">Stagger</label>
                            <input type="number" name="stagger" id="mp-stagger" class="form-control" min="0" max="10000" value="{{ old('stagger', $preset->stagger) }}" aria-describedby="mp-stagger-help">
                            <small id="mp-stagger-help" class="form-text text-muted">ms between items; scroll px for scroll presets.</small>
                        </div>
                        <div class="form-group col-6 col-md-3">
                            <label for="mp-ease">Ease</label>
                            <input type="text" name="ease" id="mp-ease" class="form-control @error('ease') is-invalid @enderror" value="{{ old('ease', $preset->ease) }}" required maxlength="60" list="mp-eases">
                            <datalist id="mp-eases">
                                @foreach (array_merge(array_keys(\App\Support\SectionMotion::EASES), ['inCubic', 'outQuart', 'steps(4)', 'spring(80,10)', 'ease', 'ease-in-out', 'ease-out', 'linear']) as $ease)
                                    <option value="{{ $ease }}"></option>
                                @endforeach
                            </datalist>
                            @error('ease')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-primary">{{ $preset->exists ? 'Save preset' : 'Create preset' }}</button>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <div class="card" style="position: sticky; top: 60px;">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title mr-auto">Preview</h3>
                    @if ($preset->exists && $preset->trigger !== 'scrub')
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="mp-replay">
                            <i class="fas fa-play mr-1" aria-hidden="true"></i>Play again
                        </button>
                    @endif
                </div>
                <div class="card-body">
                    @if ($preset->exists)
                        <iframe id="mp-preview" src="{{ route('admin.motion-presets.preview', $preset) }}" title="Preview of {{ $preset->name }}"
                                style="width: 100%; height: 320px; border: 1px solid #dee2e6; border-radius: 6px;"></iframe>
                        @if ($preset->trigger === 'scrub')
                            <label for="mp-scrub" class="mt-2 mb-0 small">Scroll position</label>
                            <input type="range" id="mp-scrub" class="custom-range" min="0" max="1" step="0.001" value="0">
                        @endif
                        <p class="text-muted small mb-0 mt-2">Shows the saved version. Save to see a change.</p>
                    @else
                        <p class="text-muted mb-0">Create the preset to preview it.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</form>
@stop

@push('scripts')
    <script>
        (function () {
            var frame = document.getElementById('mp-preview');
            if (!frame) return;
            var send = function (message) { frame.contentWindow.postMessage(Object.assign({ section: 'gsm-s0' }, message), window.location.origin); };
            var scrub = document.getElementById('mp-scrub');
            if (scrub) scrub.addEventListener('input', function () { send({ type: 'ek-motion-seek', p: Number(scrub.value) }); });
            var replay = document.getElementById('mp-replay');
            if (replay) replay.addEventListener('click', function () { send({ type: 'ek-motion-play' }); });
        })();
    </script>
@endpush
