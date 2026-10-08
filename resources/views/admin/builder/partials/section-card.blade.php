{{--
    One builder section card: drag handle, rename, device tabs, per-device
    form, visibility toggles, clone/delete.
    Vars: $section, $registry, $menus, $area
--}}
@php
    $type = $registry->find($section->type);
    $sid = $section->id;
@endphp

<div class="card gsm-section-card mb-2" data-id="{{ $sid }}">
    <div class="card-header py-2 d-flex align-items-center">
        {{-- Dragging is mouse-only, so the handle also announces itself and
             the section's position for anyone not using one. --}}
        <span class="gsm-drag-handle" title="Drag to move this section up or down"
              aria-label="Drag to reorder this section" role="img">
            <i class="fas fa-grip-vertical"></i>
        </span>
        <i class="{{ $type->icon() }} mr-2 text-primary"></i>
        <strong class="mr-2 gsm-sc-title">{{ $section->name ?? $type->label() }}</strong>
        <span class="badge badge-light border">{{ $type->label() }}</span>
        @if ($section->global_block_id)
            <a href="{{ route('admin.global-blocks.edit', $section->global_block_id) }}" target="_blank"
               class="badge badge-{{ $section->is_synced ? 'info' : 'secondary' }} ml-1"
               title="{{ $section->is_synced ? 'Shared block — editing the original updates it everywhere it is used' : 'Copied from a shared block, now independent' }}">
                <i class="fas fa-cubes mr-1"></i>{{ $section->is_synced ? 'Global' : 'Detached' }}
            </a>
        @endif

        <div class="ml-auto d-flex align-items-center" style="gap:.4rem;">
            <div class="custom-control custom-switch" title="Show or hide this section on the site">
                <input type="checkbox" class="custom-control-input gsm-sc-active" id="active_{{ $sid }}"
                       @checked($section->is_active)>
                <label class="custom-control-label" for="active_{{ $sid }}">&nbsp;</label>
            </div>
            @if ($section->global_block_id && $section->is_synced)
                <button type="button" class="btn btn-xs btn-outline-secondary gsm-sc-detach" title="Stop following the shared block and keep this copy as it is"
                        aria-label="Stop following the shared block">
                    <i class="fas fa-unlink"></i>
                </button>
            @elseif (! $section->global_block_id)
                <button type="button" class="btn btn-xs btn-outline-secondary gsm-sc-promote" title="Save this section so you can reuse it on other pages"
                        aria-label="Save as a reusable block">
                    <i class="fas fa-cubes"></i>
                </button>
            @endif
            <button type="button" class="btn btn-xs btn-outline-secondary gsm-sc-clone" title="Make a copy of this section" aria-label="Duplicate this section">
                <i class="fas fa-clone"></i>
            </button>
            @can('developer.mode')
                <button type="button" class="btn btn-xs btn-outline-dark gsm-sc-devmode" title="Edit this section's raw HTML, CSS and JavaScript" aria-label="Developer mode"
                        data-id="{{ $sid }}" data-url="{{ url('cms/developer/sections/'.$sid) }}">
                    <i class="fas fa-code"></i>
                </button>
                @once
                    @push('scripts')
                        <script src="{{ asset('js/admin/developer-mode.js') }}?v={{ gsm_version() }}"></script>
                    @endpush
                @endonce
            @endcan
            <button type="button" class="btn btn-xs btn-outline-danger gsm-sc-delete" title="Delete this section" aria-label="Delete this section">
                <i class="fas fa-trash"></i>
            </button>
            <button type="button" class="btn btn-xs btn-outline-secondary" data-toggle="collapse"
                    data-target="#body_{{ $sid }}" title="Open or close this section's settings" aria-label="Open or close this section">
                <i class="fas fa-chevron-down"></i>
            </button>
        </div>
    </div>

    <div class="collapse" id="body_{{ $sid }}">
        <div class="card-body py-3">
            {{-- Device tabs --}}
            <ul class="nav nav-pills nav-sm mb-3" role="tablist">
                @foreach (['desktop' => 'fas fa-desktop', 'tablet' => 'fas fa-tablet-alt', 'mobile' => 'fas fa-mobile-alt'] as $device => $deviceIcon)
                    <li class="nav-item">
                        <a class="nav-link py-1 px-3 {{ $device === 'desktop' ? 'active' : '' }}"
                           data-toggle="pill" href="#tab_{{ $sid }}_{{ $device }}" role="tab">
                            <i class="{{ $deviceIcon }} mr-1"></i>{{ ucfirst($device) }}
                        </a>
                    </li>
                @endforeach
                <li class="nav-item ml-auto">
                    <a class="nav-link py-1 px-3" data-toggle="pill" href="#tab_{{ $sid }}_visibility" role="tab">
                        <i class="fas fa-eye mr-1"></i>Visibility
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                @foreach (['desktop', 'tablet', 'mobile'] as $device)
                    <div class="tab-pane fade {{ $device === 'desktop' ? 'show active' : '' }}"
                         id="tab_{{ $sid }}_{{ $device }}" role="tabpanel">

                        @if ($device !== 'desktop')
                            <p class="text-muted small mb-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Only device-overridable fields are shown. Empty = inherits desktop.
                            </p>
                        @endif

                        <form class="gsm-section-form" method="post" data-device="{{ $device }}" data-id="{{ $sid }}">
                            <div class="form-group">
                                <label class="mb-1"><small>Name <span class="text-muted">(only you see this)</span></small></label>
                                <input type="text" name="name" value="{{ $section->name }}"
                                       class="form-control form-control-sm" {{ $device !== 'desktop' ? 'disabled' : '' }}>
                            </div>

                            @foreach ($type->fields() as $field)
                                @include('admin.builder.partials.field', [
                                    'field' => $field,
                                    'value' => $section->dataFor($device)[$field['key']] ?? null,
                                    'device' => $device,
                                    'sectionId' => $sid,
                                    'menus' => $menus,
                                ])
                            @endforeach

                        </form>
                    </div>
                @endforeach

                {{-- Visibility / layout tab --}}
                <div class="tab-pane fade" id="tab_{{ $sid }}_visibility" role="tabpanel">
                    <form class="gsm-visibility-form" method="post" data-id="{{ $sid }}" data-area="{{ $area }}">
                        <p class="text-muted small mb-2">Show this section on:</p>
                        @foreach (['desktop' => 'Desktop', 'tablet' => 'Tablet', 'mobile' => 'Mobile'] as $device => $deviceLabel)
                            <div class="custom-control custom-switch mb-1">
                                <input type="hidden" name="style[visible][{{ $device }}]" value="0">
                                <input type="checkbox" class="custom-control-input" name="style[visible][{{ $device }}]"
                                       id="vis_{{ $sid }}_{{ $device }}" value="1"
                                       @checked((bool) $section->styleValue("visible.$device", true))>
                                <label class="custom-control-label" for="vis_{{ $sid }}_{{ $device }}">{{ $deviceLabel }}</label>
                            </div>
                        @endforeach

                        @if ($area === 'page')
                            <hr>
                            <p class="text-muted small mb-2">Section style:</p>
                            <div class="form-row">
                                <div class="col-6 col-md-3">
                                    <label class="mb-0"><small>Background Color</small></label>
                                    <input type="color" name="style[bg_color]" class="form-control form-control-sm"
                                           value="{{ $section->styleValue('bg_color') ?: '#ffffff' }}" style="height:31px; padding:2px;">
                                    <div class="custom-control custom-checkbox mt-1">
                                        <input type="checkbox" class="custom-control-input" id="nobg_{{ $sid }}"
                                               name="style[bg_none]" value="1" @checked(!$section->styleValue('bg_color'))>
                                        <label class="custom-control-label" for="nobg_{{ $sid }}"><small>No background</small></label>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <label class="mb-0"><small>Background image</small></label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" name="style[bg_media_id]" id="bgm_{{ $sid }}"
                                               value="{{ $section->styleValue('bg_media_id') }}" class="form-control">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-primary gsm-bf-media-pick"
                                                    data-target="bgm_{{ $sid }}"><i class="fas fa-photo-video"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row mt-2">
                                @foreach (['pt' => 'Padding Top', 'pb' => 'Padding Bottom', 'mt' => 'Margin Top', 'mb' => 'Margin Bottom'] as $spaceKey => $spaceLabel)
                                    <div class="col-6 col-md-3">
                                        <label class="mb-0"><small>{{ $spaceLabel }} (px)</small></label>
                                        <input type="number" name="style[{{ $spaceKey }}]" min="0" max="300"
                                               value="{{ $section->styleValue($spaceKey) }}"
                                               class="form-control form-control-sm" placeholder="auto">
                                    </div>
                                @endforeach
                            </div>
                            @php $libraryGradients = app(\App\Services\GradientService::class)->active(); @endphp
                            <div class="form-row mt-2">
                                <div class="col-6 col-md-4">
                                    <label class="mb-0"><small>Background Gradient (Library)</small></label>
                                    <select name="style[bg_gradient]" class="form-control form-control-sm">
                                        <option value="">— none —</option>
                                        @foreach ($libraryGradients as $libGradient)
                                            <option value="{{ $libGradient->slug }}"
                                                @selected($section->styleValue('bg_gradient') === $libGradient->slug)>{{ $libGradient->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6 col-md-4">
                                    <label class="mb-0"><small>Background Video (media ID)</small></label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" name="style[bg_video_media_id]" id="bgv_{{ $sid }}"
                                               value="{{ $section->styleValue('bg_video_media_id') }}" class="form-control">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-primary gsm-bf-media-pick"
                                                    data-target="bgv_{{ $sid }}"><i class="fas fa-film"></i></button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-2">
                                    <label class="mb-0"><small>Overlay Color</small></label>
                                    <input type="color" name="style[overlay_color]" class="form-control form-control-sm"
                                           value="{{ $section->styleValue('overlay_color') ?: '#0a0e1a' }}" style="height:31px; padding:2px;">
                                </div>
                                <div class="col-6 col-md-2">
                                    <label class="mb-0"><small>Overlay %</small></label>
                                    <input type="number" name="style[overlay_opacity]" min="0" max="95"
                                           value="{{ $section->styleValue('overlay_opacity') }}"
                                           class="form-control form-control-sm" placeholder="0 = off">
                                </div>
                            </div>
                            <div class="form-row mt-2">
                                <div class="col-6 col-md-4">
                                    <label class="mb-0"><small>Border Radius (e.g. 24px / 50%)</small></label>
                                    <input type="text" name="style[radius]" value="{{ $section->styleValue('radius') }}"
                                           class="form-control form-control-sm" placeholder="none">
                                </div>
                                <div class="col-6 col-md-2">
                                    <label class="mb-0"><small>Z-Index</small></label>
                                    <input type="number" name="style[z_index]" min="-10" max="100"
                                           value="{{ $section->styleValue('z_index') }}"
                                           class="form-control form-control-sm" placeholder="auto">
                                </div>
                            </div>
                            @include('admin.builder.partials.animation-fields', [
                                'section' => $section,
                                'sid' => $sid,
                            ])

                            {{-- Raw CSS/JS render unescaped into every visitor's page,
                                 so authoring them is the same privilege Developer Mode
                                 needs. SectionController drops these keys server-side
                                 for anyone else; hiding them here just avoids showing
                                 a field that would silently do nothing. --}}
                            @can('developer.mode')
                                <div class="form-group mt-2 mb-1">
                                    <label class="mb-0"><small>Extra CSS for this section</small></label>
                                    <textarea name="style[custom_css]" rows="3" spellcheck="false"
                                              class="form-control form-control-sm text-monospace"
                                              placeholder="#gsm-s{{ $sid }} h2 { color: red; }">{{ $section->styleValue('custom_css') }}</textarea>
                                </div>
                                <div class="form-group mb-1">
                                    <label class="mb-0"><small>Extra JavaScript for this section</small></label>
                                    <textarea name="style[custom_js]" rows="3" spellcheck="false"
                                              class="form-control form-control-sm text-monospace">{{ $section->styleValue('custom_js') }}</textarea>
                                </div>
                            @endcan
                        @endif

                        @if ($area === 'footer' && $section->type !== 'copyright')
                            <p class="text-muted small mt-3 mb-2">Column width (12 = full row):</p>
                            <div class="form-row">
                                @foreach (['desktop' => 3, 'tablet' => 6, 'mobile' => 12] as $device => $defaultWidth)
                                    <div class="col-4">
                                        <label class="mb-0"><small>{{ ucfirst($device) }}</small></label>
                                        <select name="style[width][{{ $device }}]" class="form-control form-control-sm">
                                            @foreach ([12, 9, 8, 6, 4, 3, 2] as $width)
                                                <option value="{{ $width }}"
                                                    @selected((int) $section->styleValue("width.$device", $defaultWidth) === $width)>
                                                    {{ $width }}/12
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
