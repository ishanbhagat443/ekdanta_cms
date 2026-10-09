{{--
    Motion tab for one page section: scroll stage, steps and tracks, per
    device. Shows what each device actually runs (design defaults + edits);
    saving keeps only what was changed (App\Support\SectionMotion::applyEdit),
    so "Reset" always gets back to the design.

    Vars: $section, $type, $sid
--}}
@php
    use App\Support\SectionMotion;

    $effective = SectionMotion::effective($section);
    $defaults = $type->motionDefaults();
    $stored = SectionMotion::stored($section);
    $presets = collect(SectionMotion::presets())->sortBy('name')->groupBy(fn ($p) => $p['trigger'] === 'scrub' ? 'Scroll' : ($p['kind'] === 'css' ? 'Loops and CSS' : 'Time-based'));
    $parts = array_values(array_unique(['@self', ...$type->motionParts()]));
    $base = fn ($device) => match ($device) {
        'mobile' => isset($defaults['mobile']) ? 'the phone design' : 'Desktop',
        'tablet' => isset($defaults['tablet']) ? 'the tablet design' : (isset($defaults['mobile']) ? 'Mobile' : 'Desktop'),
        default => 'the design',
    };
    $json = fn ($value) => $value === null || $value === [] ? '' : json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp

<form class="gsm-motion-form" method="post" data-id="{{ $sid }}">
    {{-- Preview: drive the live preview on the right. --}}
    <div class="d-flex align-items-center flex-wrap mb-3" style="gap: .5rem;">
        <label for="m{{ $sid }}_scrub" class="mb-0 small text-nowrap">Preview scroll position</label>
        <input type="range" class="custom-range flex-grow-1" id="m{{ $sid }}_scrub" min="0" max="1" step="0.001" value="0"
               data-motion-scrub style="min-width: 140px;">
        <button type="button" class="btn btn-outline-secondary btn-xs" data-motion-play>
            <i class="fas fa-play mr-1" aria-hidden="true"></i>Play
        </button>
    </div>

    <datalist id="m{{ $sid }}_parts">
        @foreach ($parts as $part)<option value="{{ $part }}"></option>@endforeach
    </datalist>
    <datalist id="m{{ $sid }}_eases">
        @foreach (SectionMotion::EASES as $ease => $label)<option value="{{ $ease }}">{{ $label }}</option>@endforeach
        @foreach (['ease', 'ease-in-out', 'ease-out', 'steps(4)', 'spring(80,10)'] as $ease)<option value="{{ $ease }}"></option>@endforeach
    </datalist>

    <ul class="nav nav-pills nav-sm mb-2" role="tablist">
        @foreach (SectionMotion::DEVICES as $device)
            <li class="nav-item">
                <a class="nav-link py-1 px-2 {{ $loop->first ? 'active' : '' }}" data-toggle="pill" href="#m{{ $sid }}_{{ $device }}" role="tab">
                    <small>{{ ucfirst($device) }}</small>
                    @if (! empty($stored[$device])) <span class="badge badge-warning ml-1" title="Changed from {{ $base($device) }}">edited</span> @endif
                </a>
            </li>
        @endforeach
    </ul>

    <div class="tab-content">
        @foreach ($effective as $device => $config)
            @php $p = "motion[{$device}]"; $id = "m{$sid}_{$device}"; @endphp
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ $id }}" role="tabpanel" data-motion-device="{{ $device }}">
                @if ($device !== 'desktop')
                    <p class="text-muted small mb-2">Starts from {{ $base($device) }}. Change a value to give {{ $device }} its own; empty a field to go back.</p>
                @endif

                <div class="custom-control custom-switch mb-2">
                    <input type="hidden" name="{{ $p }}[off]" value="0">
                    <input type="checkbox" class="custom-control-input" id="{{ $id }}_off" name="{{ $p }}[off]" value="1" @checked(! empty($config['off']))>
                    <label class="custom-control-label small" for="{{ $id }}_off">No motion on {{ $device }}</label>
                </div>

                <fieldset class="border rounded p-2 mb-2">
                    <legend class="small w-auto px-1 mb-0">Scroll stage</legend>
                    <div class="form-row align-items-end">
                        <div class="col-6">
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="{{ $p }}[stage][pin]" value="0">
                                <input type="checkbox" class="custom-control-input" id="{{ $id }}_pin" name="{{ $p }}[stage][pin]" value="1" @checked(! empty($config['stage']['pin']))>
                                <label class="custom-control-label small" for="{{ $id }}_pin">Pin a full-screen stage</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="mb-0 small" for="{{ $id }}_len">Scroll length (design px)</label>
                            <input type="number" class="form-control form-control-sm" id="{{ $id }}_len" name="{{ $p }}[stage][length]" min="0" max="30000" step="50"
                                   value="{{ $config['stage']['length'] ?? '' }}">
                        </div>
                    </div>
                    <small class="text-muted d-block mt-1">While pinned, the stage stays on screen for this much scrolling (at 900 px screen height; it scales with the screen). The section's view needs a stage; Ekdanta sections have one.</small>
                </fieldset>

                <details class="mb-2" @if (! empty($config['steps'])) open @endif>
                    <summary class="small">Steps (items that pause in turn)</summary>
                    <div class="form-row mt-1">
                        <div class="col-4">
                            <label class="mb-0 small" for="{{ $id }}_steps_part">Part holding the items</label>
                            <input type="text" class="form-control form-control-sm" id="{{ $id }}_steps_part" name="{{ $p }}[steps][part]" list="m{{ $sid }}_parts" value="{{ $config['steps']['part'] ?? '' }}">
                        </div>
                        <div class="col-3">
                            <label class="mb-0 small" for="{{ $id }}_steps_a">From (px)</label>
                            <input type="number" class="form-control form-control-sm" id="{{ $id }}_steps_a" name="{{ $p }}[steps][range][0]" value="{{ $config['steps']['range'][0] ?? '' }}">
                        </div>
                        <div class="col-3">
                            <label class="mb-0 small" for="{{ $id }}_steps_b">To (px)</label>
                            <input type="number" class="form-control form-control-sm" id="{{ $id }}_steps_b" name="{{ $p }}[steps][range][1]" value="{{ $config['steps']['range'][1] ?? '' }}">
                        </div>
                        <div class="col-2">
                            <label class="mb-0 small" for="{{ $id }}_steps_dwell">Pause</label>
                            <input type="number" class="form-control form-control-sm" id="{{ $id }}_steps_dwell" name="{{ $p }}[steps][dwell]" min="0" max="0.9" step="0.05" placeholder="0.35" value="{{ $config['steps']['dwell'] ?? '' }}">
                        </div>
                    </div>
                    <small class="text-muted">"Pause" is the share of each step that holds still. The current item gets <code>is-active</code> and <code>aria-current</code>.</small>
                </details>

                <details class="mb-2" @if (! empty($config['scene']['params'])) open @endif>
                    <summary class="small">Scene settings</summary>
                    <label class="mb-0 small" for="{{ $id }}_scene">Values for this section's built-in scene (JSON)</label>
                    <textarea class="form-control form-control-sm text-monospace" rows="2" spellcheck="false" id="{{ $id }}_scene" name="{{ $p }}[scene][params]" data-json>{{ $json($config['scene']['params'] ?? null) }}</textarea>
                </details>

                <p class="small mb-1 mt-3"><strong>Tracks</strong> <span class="text-muted">· each moves one part</span></p>
                <div data-motion-tracks>
                    @foreach ((array) ($config['tracks'] ?? []) as $trackId => $track)
                        @include('admin.builder.partials.motion-track', [
                            'p' => $p, 'id' => $id, 'sid' => $sid, 'device' => $device, 'trackId' => $trackId, 'track' => $track,
                            'presets' => $presets, 'json' => $json,
                            'isDefault' => isset($defaults[$device]['tracks'][$trackId]) || ($device !== 'desktop' && isset($effective['desktop']['tracks'][$trackId])),
                            'isEdited' => isset($stored[$device]['tracks'][$trackId]),
                        ])
                    @endforeach
                </div>
                <template data-motion-template>
                    @include('admin.builder.partials.motion-track', [
                        'p' => $p, 'id' => $id, 'sid' => $sid, 'device' => $device, 'trackId' => '__track__',
                        'track' => ['part' => '@self', 'trigger' => 'enter', 'preset' => 'fade-up', 'enabled' => true],
                        'presets' => $presets, 'json' => $json, 'isDefault' => false, 'isEdited' => false,
                    ])
                </template>
                <button type="button" class="btn btn-outline-secondary btn-xs" data-motion-add>
                    <i class="fas fa-plus mr-1" aria-hidden="true"></i>Add a track
                </button>
            </div>
        @endforeach
    </div>

    <div class="d-flex align-items-center flex-wrap mt-3 pt-2 border-top" style="gap: .5rem;">
        <button type="submit" class="btn btn-primary btn-sm">Save motion</button>
        <button type="button" class="btn btn-outline-secondary btn-sm" data-motion-reset="all"
                data-confirm="Undo every motion change on this section, on all devices?">Reset whole section to design default</button>
    </div>
</form>
