{{--
    One track row in the Motion tab.
    Vars: $p (name prefix), $id (id prefix), $sid, $device, $trackId, $track, $presets, $json,
          $isDefault (the design or the base device has it), $isEdited (this device changed it)
--}}
@php
    $n = "{$p}[tracks][{$trackId}]";
    $tid = "{$id}_{$trackId}";
@endphp
<div class="border rounded p-2 mb-2 gsm-motion-track" data-motion-track="{{ $trackId }}" data-saved="{{ $isDefault || $isEdited ? '1' : '0' }}">
    <div class="form-row align-items-end">
        <div class="col-6 col-lg-3">
            <label class="mb-0 small" for="{{ $tid }}_part">Part</label>
            <input type="text" class="form-control form-control-sm" id="{{ $tid }}_part" name="{{ $n }}[part]" list="m{{ $sid }}_parts" value="{{ $track['part'] ?? '' }}" maxlength="40" required>
        </div>
        <div class="col-6 col-lg-3">
            <label class="mb-0 small" for="{{ $tid }}_trigger">When</label>
            <select class="form-control form-control-sm" id="{{ $tid }}_trigger" name="{{ $n }}[trigger]">
                <option value="">Preset's default</option>
                @foreach (\App\Support\SectionMotion::TRIGGERS as $value => $label)
                    <option value="{{ $value }}" @selected(($track['trigger'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-8 col-lg-4">
            <label class="mb-0 small" for="{{ $tid }}_preset">Motion</label>
            <select class="form-control form-control-sm" id="{{ $tid }}_preset" name="{{ $n }}[preset]">
                <option value="">Custom keyframes (below)</option>
                @foreach ($presets as $group => $items)
                    <optgroup label="{{ $group }}">
                        @foreach ($items as $preset)
                            <option value="{{ $preset['slug'] }}" @selected(($track['preset'] ?? '') === $preset['slug'])>{{ $preset['name'] }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>
        <div class="col-4 col-lg-2">
            <div class="custom-control custom-switch mb-1">
                <input type="hidden" name="{{ $n }}[enabled]" value="0">
                <input type="checkbox" class="custom-control-input" id="{{ $tid }}_on" name="{{ $n }}[enabled]" value="1" @checked(($track['enabled'] ?? true) !== false)>
                <label class="custom-control-label small" for="{{ $tid }}_on">On</label>
            </div>
        </div>
    </div>

    <details class="mt-1">
        <summary class="small">Timing, scroll range and more <span class="text-muted">({{ $trackId === '__track__' ? 'new' : $trackId }})</span></summary>
        <div class="form-row mt-1">
            @foreach (['duration' => 'Duration (ms)', 'delay' => 'Delay (ms)', 'stagger' => 'Stagger'] as $key => $label)
                <div class="col-4 col-lg-2">
                    <label class="mb-0 small" for="{{ $tid }}_{{ $key }}">{{ $label }}</label>
                    <input type="number" class="form-control form-control-sm" id="{{ $tid }}_{{ $key }}" name="{{ $n }}[{{ $key }}]" min="0" value="{{ $track[$key] ?? '' }}" placeholder="preset">
                </div>
            @endforeach
            <div class="col-6 col-lg-3">
                <label class="mb-0 small" for="{{ $tid }}_ease">Ease</label>
                <input type="text" class="form-control form-control-sm" id="{{ $tid }}_ease" name="{{ $n }}[ease]" list="m{{ $sid }}_eases" value="{{ $track['ease'] ?? '' }}" placeholder="preset" maxlength="60">
            </div>
            <div class="col-6 col-lg-3">
                <label class="mb-0 small" for="{{ $tid }}_reduce">Reduced motion</label>
                <select class="form-control form-control-sm" id="{{ $tid }}_reduce" name="{{ $n }}[reduce]">
                    <option value="final" @selected(($track['reduce'] ?? 'final') === 'final')>Show its final state</option>
                    <option value="start" @selected(($track['reduce'] ?? '') === 'start')>Keep it as drawn (parts that leave as you scroll)</option>
                    <option value="hide" @selected(($track['reduce'] ?? '') === 'hide')>Hide the part</option>
                    <option value="step" @selected(($track['reduce'] ?? '') === 'step')>Switch at its point (scroll tracks)</option>
                </select>
            </div>
        </div>
        <div class="form-row mt-1">
            <div class="col-4 col-lg-2">
                <label class="mb-0 small" for="{{ $tid }}_from">Scroll from (px)</label>
                <input type="number" class="form-control form-control-sm" id="{{ $tid }}_from" name="{{ $n }}[range][0]" value="{{ $track['range'][0] ?? '' }}">
            </div>
            <div class="col-4 col-lg-2">
                <label class="mb-0 small" for="{{ $tid }}_to">Scroll to (px)</label>
                <input type="number" class="form-control form-control-sm" id="{{ $tid }}_to" name="{{ $n }}[range][1]" value="{{ $track['range'][1] ?? '' }}">
            </div>
            <div class="col-4 col-lg-2">
                <label class="mb-0 small" for="{{ $tid }}_step">Step no.</label>
                <input type="number" class="form-control form-control-sm" id="{{ $tid }}_step" name="{{ $n }}[step]" min="0" max="50" value="{{ $track['step'] ?? '' }}">
            </div>
            <div class="col-6 col-lg-3 pt-3">
                <div class="custom-control custom-switch">
                    <input type="hidden" name="{{ $n }}[children]" value="0">
                    <input type="checkbox" class="custom-control-input" id="{{ $tid }}_children" name="{{ $n }}[children]" value="1" @checked(! empty($track['children']))>
                    <label class="custom-control-label small" for="{{ $tid }}_children">Each child in turn</label>
                </div>
            </div>
            <div class="col-6 col-lg-3 pt-3">
                <div class="custom-control custom-switch">
                    <input type="hidden" name="{{ $n }}[once]" value="0">
                    <input type="checkbox" class="custom-control-input" id="{{ $tid }}_once" name="{{ $n }}[once]" value="1" @checked(($track['once'] ?? true) !== false)>
                    <label class="custom-control-label small" for="{{ $tid }}_once">Play once</label>
                </div>
            </div>
        </div>
        <small class="text-muted d-block mt-1">Scroll px count from the moment the section's top reaches the top of the screen (negative = before). "Step no." is for "When its step is active". Stagger is ms, or scroll px for scroll tracks.</small>
        <div class="form-row mt-1">
            <div class="col-12 col-lg-4">
                <label class="mb-0 small" for="{{ $tid }}_params">Preset settings (JSON)</label>
                <textarea class="form-control form-control-sm text-monospace" rows="2" spellcheck="false" id="{{ $tid }}_params" name="{{ $n }}[params]" data-json placeholder='{"distance": 40}'>{{ $json($track['params'] ?? null) }}</textarea>
            </div>
            <div class="col-12 col-lg-8">
                <label class="mb-0 small" for="{{ $tid }}_keyframes">Custom keyframes (JSON, used instead of a preset)</label>
                <textarea class="form-control form-control-sm text-monospace" rows="2" spellcheck="false" id="{{ $tid }}_keyframes" name="{{ $n }}[keyframes]" placeholder='{"opacity": [0, 1], "translateY": [40, 0]}'>{{ $json($track['keyframes'] ?? null) }}</textarea>
            </div>
        </div>
        <div class="mt-2">
            @if ($isEdited || $isDefault)
                <button type="button" class="btn btn-outline-secondary btn-xs" data-motion-reset="{{ $device }}.tracks.{{ $trackId }}"
                        @disabled(! $isEdited)>Reset this track</button>
            @endif
            @unless ($isDefault)
                <button type="button" class="btn btn-outline-danger btn-xs" data-motion-remove="{{ $device }}.tracks.{{ $trackId }}">Remove this track</button>
            @endunless
        </div>
    </details>
</div>
