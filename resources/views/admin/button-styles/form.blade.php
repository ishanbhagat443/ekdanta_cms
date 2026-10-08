@extends('admin.layouts.app')

@section('page_title', $style->exists ? 'Edit Button Style' : 'New Button Style')

@php
    $cfg = fn (string $key, $default = null) => old('config.'.$key, data_get($style->config, $key, $default));
    $corners = ['tl' => 'Top Left', 'tr' => 'Top Right', 'br' => 'Bottom Right', 'bl' => 'Bottom Left'];
    $shadowRows = old('config.shadows', data_get($style->config, 'shadows', []));
    $hoverShadowRows = old('config.hover.shadows', data_get($style->config, 'hover.shadows', []));
@endphp

@section('page_content')
<div id="gsm-button-builder" data-picker-src="{{ route('admin.media.index', ['picker' => 1]) }}">
<form method="POST"
      action="{{ $style->exists ? route('admin.button-styles.update', $style) : route('admin.button-styles.store') }}"
      id="gsm-btn-form">
    @csrf
    @if ($style->exists) @method('PUT') @endif

    <div class="row">
        <div class="col-12 col-xl-7">

            {{-- Basics --}}
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-toggle-on mr-2"></i>Button Style</h3></div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $style->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label>Slug <small class="text-muted">(auto if empty)</small></label>
                            <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
                                   value="{{ old('slug', $style->slug) }}" placeholder="primary-gradient">
                            @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <input type="text" name="description" class="form-control" value="{{ old('description', $style->description) }}">
                    </div>
                    <div class="custom-control custom-switch">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" class="custom-control-input" id="gsm-bs-active" name="is_active" value="1"
                               @checked(old('is_active', $style->is_active))>
                        <label class="custom-control-label" for="gsm-bs-active">Active</label>
                    </div>
                </div>
            </div>

            {{-- Typography --}}
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-font mr-2"></i>Typography</h3></div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Font Family</label>
                            <select name="config[typography][family]" class="form-control">
                                <option value="">Inherit</option>
                                @foreach ($fontRoles as $role)
                                    <option value="role:{{ $role }}" @selected($cfg('typography.family') === 'role:'.$role)>
                                        Theme role — {{ ucwords(str_replace('_', ' ', $role)) }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Families come from the Theme Customizer — never hardcoded.</small>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Font Size (px)</label>
                            <input type="number" step="0.5" name="config[typography][size]" class="form-control" value="{{ $cfg('typography.size') }}">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Font Weight</label>
                            <select name="config[typography][weight]" class="form-control">
                                @foreach (['300', '400', '500', '600', '700', '800'] as $w)
                                    <option value="{{ $w }}" @selected((string) $cfg('typography.weight') === $w)>{{ $w }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Letter Spacing (px)</label>
                            <input type="number" step="0.1" name="config[typography][letter_spacing]" class="form-control" value="{{ $cfg('typography.letter_spacing') }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Text Transform</label>
                            <select name="config[typography][transform]" class="form-control">
                                @foreach (['none', 'uppercase', 'capitalize', 'lowercase'] as $t)
                                    <option value="{{ $t }}" @selected($cfg('typography.transform') === $t)>{{ ucfirst($t) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Line Height</label>
                            <input type="number" step="0.05" name="config[typography][line_height]" class="form-control" value="{{ $cfg('typography.line_height') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Spacing --}}
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-arrows-alt mr-2"></i>Padding (px) — per device</h3></div>
                <div class="card-body">
                    @foreach (['desktop' => 'Desktop', 'tablet' => 'Tablet', 'mobile' => 'Mobile'] as $device => $deviceLabel)
                        <div class="form-row align-items-center">
                            <div class="col-md-2"><label class="mb-0">{{ $deviceLabel }}</label></div>
                            @foreach (['top' => 'Top', 'right' => 'Right', 'bottom' => 'Bottom', 'left' => 'Left'] as $side => $sideLabel)
                                <div class="form-group col-md-2">
                                    <small class="text-muted">{{ $sideLabel }}</small>
                                    <input type="number" step="0.5" class="form-control form-control-sm"
                                           name="config[padding][{{ $device }}][{{ $side }}]"
                                           value="{{ $cfg("padding.{$device}.{$side}") }}">
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                    <small class="text-muted">Tablet/mobile fields left empty inherit the desktop padding.</small>
                </div>
            </div>

            {{-- Border + Radius --}}
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-border-style mr-2"></i>Border &amp; Radius</h3></div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Border Width (px)</label>
                            <input type="number" step="0.5" name="config[border][width]" class="form-control" value="{{ $cfg('border.width') }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Border Style</label>
                            <select name="config[border][style]" class="form-control">
                                @foreach (['solid', 'dashed', 'dotted', 'double', 'none'] as $bs)
                                    <option value="{{ $bs }}" @selected($cfg('border.style') === $bs)>{{ ucfirst($bs) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Border Color</label>
                            <input type="color" name="config[border][color]" class="form-control" style="height:38px; padding:3px;"
                                   value="{{ $cfg('border.color', '#ffffff') }}">
                        </div>
                    </div>
                    <label class="mb-1">Border Radius — per corner <small class="text-muted">(any CSS value: 0px, 20px, 50%, 999px)</small></label>
                    <div class="form-row">
                        @foreach ($corners as $key => $label)
                            <div class="form-group col-md-3">
                                <small class="text-muted">{{ $label }}</small>
                                <input type="text" name="config[border][radius][{{ $key }}]"
                                       class="form-control @error('config.border.radius.'.$key) is-invalid @enderror"
                                       value="{{ $cfg("border.radius.{$key}") }}" placeholder="e.g. 999px">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Background --}}
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-fill mr-2"></i>Background</h3></div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Mode</label>
                            <select name="config[background][mode]" class="form-control" id="gsm-bg-mode">
                                @foreach (['solid' => 'Solid Color', 'gradient' => 'Gradient (Library)', 'image' => 'Image', 'video' => 'Video', 'transparent' => 'Transparent'] as $mode => $label)
                                    <option value="{{ $mode }}" @selected($cfg('background.mode', 'gradient') === $mode)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Solid Color</label>
                            <input type="color" name="config[background][color]" class="form-control" style="height:38px; padding:3px;"
                                   value="{{ $cfg('background.color', '#1b2a4a') }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Text Color</label>
                            <input type="color" name="config[background][text_color]" class="form-control" style="height:38px; padding:3px;"
                                   value="{{ $cfg('background.text_color', '#ffffff') }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Gradient (from Library)</label>
                            <select name="config[background][gradient_slug]" class="form-control" id="gsm-bg-gradient">
                                <option value="">— none —</option>
                                @foreach ($gradients as $gradient)
                                    <option value="{{ $gradient->slug }}" @selected($cfg('background.gradient_slug') === $gradient->slug)>{{ $gradient->name }}</option>
                                @endforeach
                            </select>
                            <small><a href="{{ route('admin.gradients.index') }}" target="_blank">Manage Gradient Library <i class="fas fa-external-link-alt"></i></a></small>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Image (Media ID)</label>
                            <div class="input-group">
                                <input type="number" name="config[background][media_id]" id="gsm-bg-image"
                                       class="form-control" value="{{ $cfg('background.media_id') }}">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary gsm-bf-media-pick" data-target="gsm-bg-image">
                                        <i class="fas fa-photo-video"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Video (Media ID)</label>
                            <div class="input-group">
                                <input type="number" name="config[background][video_media_id]" id="gsm-bg-video"
                                       class="form-control" value="{{ $cfg('background.video_media_id') }}">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary gsm-bf-media-pick" data-target="gsm-bg-video">
                                        <i class="fas fa-film"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Hover --}}
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-hand-pointer mr-2"></i>Hover State</h3></div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Background Mode</label>
                            <select name="config[hover][mode]" class="form-control">
                                <option value="">Unchanged</option>
                                @foreach (['solid' => 'Solid', 'gradient' => 'Gradient', 'transparent' => 'Transparent'] as $mode => $label)
                                    <option value="{{ $mode }}" @selected($cfg('hover.mode') === $mode)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Solid Color</label>
                            <input type="color" name="config[hover][color]" class="form-control" style="height:38px; padding:3px;"
                                   value="{{ $cfg('hover.color', '#c29b54') }}">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Gradient</label>
                            <select name="config[hover][gradient_slug]" class="form-control">
                                <option value="">— none —</option>
                                @foreach ($gradients as $gradient)
                                    <option value="{{ $gradient->slug }}" @selected($cfg('hover.gradient_slug') === $gradient->slug)>{{ $gradient->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Text Color</label>
                            <input type="color" name="config[hover][text_color]" class="form-control" style="height:38px; padding:3px;"
                                   value="{{ $cfg('hover.text_color', '#ffffff') }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Border Width (px)</label>
                            <input type="number" step="0.5" name="config[hover][border][width]" class="form-control" value="{{ $cfg('hover.border.width') }}">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Border Style</label>
                            <select name="config[hover][border][style]" class="form-control">
                                <option value="">Unchanged</option>
                                @foreach (['solid', 'dashed', 'dotted', 'double', 'none'] as $bs)
                                    <option value="{{ $bs }}" @selected($cfg('hover.border.style') === $bs)>{{ ucfirst($bs) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Border Color</label>
                            <input type="color" name="config[hover][border][color]" class="form-control" style="height:38px; padding:3px;"
                                   value="{{ $cfg('hover.border.color', '#ffffff') }}">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Radius override (all corners)</label>
                            <div class="form-row">
                                @foreach (array_keys($corners) as $key)
                                    <div class="col-3 pr-1">
                                        <input type="text" name="config[hover][radius][{{ $key }}]" class="form-control form-control-sm"
                                               value="{{ $cfg("hover.radius.{$key}") }}" placeholder="{{ strtoupper($key) }}">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <label class="mb-1">Hover Shadows <small class="text-muted">(unlimited)</small></label>
                    <div id="gsm-hover-shadow-rows" data-name="config[hover][shadows]">
                        @foreach ($hoverShadowRows as $i => $row)
                            @include('admin.button-styles.partials.shadow-row', ['name' => "config[hover][shadows][{$i}]", 'row' => $row])
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-xs btn-outline-primary gsm-shadow-add" data-target="gsm-hover-shadow-rows">
                        <i class="fas fa-plus mr-1"></i> Add Hover Shadow
                    </button>
                </div>
            </div>

            {{-- Animation + Icon --}}
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-magic mr-2"></i>Animation &amp; Icons</h3></div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Hover Animation</label>
                            <select name="config[animation][hover_effect]" class="form-control">
                                @foreach (['none', 'scale', 'ripple', 'glow', 'lift', 'slide', 'pulse'] as $fx)
                                    <option value="{{ $fx }}" @selected($cfg('animation.hover_effect') === $fx)>{{ ucfirst($fx) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Duration (ms)</label>
                            <input type="number" name="config[animation][duration]" class="form-control" value="{{ $cfg('animation.duration', 250) }}">
                        </div>
                        <div class="form-group col-md-2">
                            <label>Left Icon</label>
                            <input type="text" name="config[icon][left]" class="form-control" placeholder="fas fa-arrow-left"
                                   value="{{ $cfg('icon.left') }}">
                        </div>
                        <div class="form-group col-md-2">
                            <label>Right Icon</label>
                            <input type="text" name="config[icon][right]" class="form-control" placeholder="fas fa-arrow-right"
                                   value="{{ $cfg('icon.right') }}">
                        </div>
                        <div class="form-group col-md-2">
                            <label>Icon Spacing (px)</label>
                            <input type="number" name="config[icon][gap]" class="form-control" value="{{ $cfg('icon.gap', 8) }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Shadows --}}
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-clone mr-2"></i>Shadows <small class="text-muted ml-1">unlimited</small></h3></div>
                <div class="card-body">
                    <div id="gsm-shadow-rows" data-name="config[shadows]">
                        @foreach ($shadowRows as $i => $row)
                            @include('admin.button-styles.partials.shadow-row', ['name' => "config[shadows][{$i}]", 'row' => $row])
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-xs btn-outline-primary gsm-shadow-add" data-target="gsm-shadow-rows">
                        <i class="fas fa-plus mr-1"></i> Add Shadow
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> {{ $style->exists ? 'Save Button Style' : 'Create Button Style' }}
            </button>
            <a href="{{ route('admin.button-styles.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>

        {{-- Live preview --}}
        <div class="col-12 col-xl-5">
            <div class="card" style="position: sticky; top: 60px;">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title"><i class="fas fa-eye mr-2"></i>Live Preview</h3>
                    <div class="btn-group btn-group-sm ml-auto" role="group">
                        <button type="button" class="btn btn-outline-secondary active gsm-preview-device" data-device="desktop"><i class="fas fa-desktop"></i></button>
                        <button type="button" class="btn btn-outline-secondary gsm-preview-device" data-device="tablet"><i class="fas fa-tablet-alt"></i></button>
                        <button type="button" class="btn btn-outline-secondary gsm-preview-device" data-device="mobile"><i class="fas fa-mobile-alt"></i></button>
                    </div>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center" style="min-height:220px; background:#f4f2ee;">
                    <a href="#" onclick="return false;" id="gsm-btn-preview">
                        <i id="gsm-preview-icon-left" class="d-none"></i>
                        <span>Button Text</span>
                        <i id="gsm-preview-icon-right" class="d-none"></i>
                    </a>
                </div>
                <div class="card-footer py-2">
                    <small class="text-muted">Preview approximates the compiled CSS; saved styles render with the exact frontend stylesheet. Hover the button to test the hover state.</small>
                </div>
            </div>
        </div>
    </div>
</form>
</div>
@stop

@push('scripts')
<script src="{{ asset('js/admin/media-picker-field.js') }}?v={{ gsm_version() }}"></script>
<script>
    window.gsmGradients = @json($gradients->mapWithKeys(fn ($g) => [$g->slug => $g->toCss()]));
</script>
<script src="{{ asset('js/admin/button-builder.js') }}?v={{ gsm_version() }}"></script>
@endpush
