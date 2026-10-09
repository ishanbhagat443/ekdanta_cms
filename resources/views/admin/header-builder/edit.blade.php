@extends('admin.layouts.app')

@section('page_title', 'Header Builder')

@section('page_content')
<div id="gsm-header-builder"
     data-sections-url="{{ url('cms/sections') }}"
     data-picker-src="{{ route('admin.media.index', ['picker' => 1]) }}">

    @include('admin.builder.partials.toolbar', [
        'formId' => 'gsm-header-form',
        'previewUrl' => url('/'),
    ])

    <div class="row">
        {{-- Left: areas + sections --}}
        <div class="col-12 col-xl-8">
            @foreach ([['area' => 'topbar', 'title' => 'Top Bar', 'icon' => 'fas fa-window-maximize', 'layout' => $topbar, 'types' => $topbarTypes],
                       ['area' => 'header', 'title' => 'Main Header', 'icon' => 'fas fa-heading', 'layout' => $header, 'types' => $headerTypes]] as $panel)
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center">
                        <h3 class="card-title"><i class="{{ $panel['icon'] }} mr-2"></i>{{ $panel['title'] }}</h3>
                        <div class="ml-auto">
                            <div class="btn-group">
                                <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown">
                                    <i class="fas fa-plus mr-1"></i> Add Section
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    @foreach ($panel['types'] as $type)
                                        <a href="#" class="dropdown-item gsm-add-section"
                                           data-area="{{ $panel['area'] }}" data-type="{{ $type->key() }}">
                                            <i class="{{ $type->icon() }} fa-fw mr-2"></i>{{ $type->label() }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body gsm-section-list" data-area="{{ $panel['area'] }}">
                        @forelse ($panel['layout']->sections as $section)
                            @include('admin.builder.partials.section-card', [
                                'section' => $section,
                                'registry' => $registry,
                                'menus' => $menus,
                                'area' => $panel['area'],
                            ])
                        @empty
                            <p class="text-muted text-center py-3 mb-0 gsm-empty-note">
                                No sections yet — use “Add Section”.
                            </p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Right: header settings + CSS/JS --}}
        <div class="col-12 col-xl-4">
            <form method="POST" action="{{ route('admin.header.update') }}" id="gsm-header-form" data-gsm-primary-save>
                @csrf
                @method('PUT')
                <div class="card">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-sliders-h mr-2"></i>Header Settings</h3></div>
                    <div class="card-body">
                        <div class="custom-control custom-switch mb-2">
                            <input type="hidden" name="sticky" value="0">
                            <input type="checkbox" class="custom-control-input" id="set_sticky" name="sticky" value="1"
                                   @checked($header->setting('sticky', false))>
                            <label class="custom-control-label" for="set_sticky">Sticky header (stays on scroll)</label>
                        </div>
                        <div class="custom-control custom-switch mb-3">
                            <input type="hidden" name="show_topbar" value="0">
                            <input type="checkbox" class="custom-control-input" id="set_topbar" name="show_topbar" value="1"
                                   @checked($header->setting('show_topbar', true))>
                            <label class="custom-control-label" for="set_topbar">Show top bar</label>
                        </div>
                        <div class="form-group">
                            <label><small>Container Width</small></label>
                            <select name="container" class="form-control form-control-sm">
                                <option value="container" @selected($header->setting('container', 'container') === 'container')>Boxed (container)</option>
                                <option value="container-fluid" @selected($header->setting('container') === 'container-fluid')>Full width (container-fluid)</option>
                            </select>
                        </div>
                        <hr>
                        <p class="text-muted small mb-2"><i class="fas fa-bars mr-1" aria-hidden="true"></i>Navigation style (the Nav Menu section)</p>
                        @foreach (['desktop' => 'Desktop', 'tablet' => 'Tablet', 'mobile' => 'Mobile'] as $device => $deviceLabel)
                            @php $navStyle = $header->navStyle($device); @endphp
                            <div class="form-group mb-2">
                                <label class="mb-0" for="nav_style_{{ $device }}"><small>{{ $deviceLabel }}</small></label>
                                <select name="nav_style[{{ $device }}]" id="nav_style_{{ $device }}" class="form-control form-control-sm">
                                    <option value="inline" @selected($navStyle === 'inline')>Links in the header</option>
                                    <option value="dropdown" @selected($navStyle === 'dropdown')>Menu button with a dropdown</option>
                                    <option value="overlay" @selected($navStyle === 'overlay')>Menu button with a full-screen menu</option>
                                </select>
                            </div>
                        @endforeach
                        <hr>
                        <p class="text-muted small mb-2"><i class="fas fa-layer-group mr-1"></i>Header over hero (transparent → solid on scroll)</p>
                        <div class="custom-control custom-switch mb-2">
                            <input type="hidden" name="overlay_hero" value="0">
                            <input type="checkbox" class="custom-control-input" id="set_overlay_hero" name="overlay_hero" value="1"
                                   @checked($header->setting('overlay_hero', false))>
                            <label class="custom-control-label" for="set_overlay_hero">Overlay the header on the first section</label>
                        </div>
                        <div class="form-row">
                            <div class="col-6">
                                <label><small>Transparent Color</small></label>
                                <input type="text" name="transparent_color" class="form-control form-control-sm"
                                       value="{{ old('transparent_color', $header->setting('transparent_color')) }}"
                                       placeholder="transparent / rgba(16,24,40,.25)">
                            </div>
                            <div class="col-6">
                                <label><small>Scrolled Color</small></label>
                                <input type="color" name="scrolled_color" class="form-control form-control-sm"
                                       style="height:31px; padding:2px;"
                                       value="{{ old('scrolled_color', $header->setting('scrolled_color', '#ffffff')) }}">
                            </div>
                        </div>
                        <div class="form-row mt-2">
                            <div class="col-4">
                                <label><small>Blur (px)</small></label>
                                <input type="number" name="blur" min="0" max="30" class="form-control form-control-sm"
                                       value="{{ old('blur', $header->setting('blur', 0)) }}">
                            </div>
                            <div class="col-4">
                                <label><small>Menu Color</small></label>
                                <input type="color" name="menu_color" class="form-control form-control-sm"
                                       style="height:31px; padding:2px;"
                                       value="{{ old('menu_color', $header->setting('menu_color', '#ffffff')) }}">
                            </div>
                            <div class="col-4">
                                <label><small>Menu Hover Color</small></label>
                                <input type="color" name="menu_hover_color" class="form-control form-control-sm"
                                       style="height:31px; padding:2px;"
                                       value="{{ old('menu_hover_color', $header->setting('menu_hover_color', '#4b5563')) }}">
                            </div>
                        </div>
                        <div class="custom-control custom-switch mt-2 mb-3">
                            <input type="hidden" name="scrolled_shadow" value="0">
                            <input type="checkbox" class="custom-control-input" id="set_scrolled_shadow" name="scrolled_shadow" value="1"
                                   @checked($header->setting('scrolled_shadow', true))>
                            <label class="custom-control-label" for="set_scrolled_shadow">Shadow when scrolled</label>
                        </div>
                        <hr>
                        <div class="form-group">
                            <label><small>Header CSS</small></label>
                            <textarea name="custom_css" rows="5" spellcheck="false"
                                      class="form-control form-control-sm text-monospace"
                                      placeholder=".gsm-header { … }">{{ old('custom_css', $header->custom_css) }}</textarea>
                            @error('custom_css') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        {{-- Runs on every public page, so it is a Developer Mode privilege
                             (HeaderBuilderController drops it for everyone else). --}}
                        @can('developer.mode')
                            <div class="form-group">
                                <label><small>Header JS</small></label>
                                <textarea name="custom_js" rows="5" spellcheck="false"
                                          class="form-control form-control-sm text-monospace"
                                          placeholder="// runs on every page">{{ old('custom_js', $header->custom_js) }}</textarea>
                                @error('custom_js') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        @endcan
                    </div>
                    <div class="card-footer text-muted small">
                        <i class="fas fa-circle-info mr-1" aria-hidden="true"></i>Changes here save with the
                        <strong>Save</strong> button at the top of the screen.
                        <a href="{{ route('front.home') }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-external-link-alt mr-1"></i>Preview Site
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script src="{{ asset('js/admin/media-picker-field.js') }}?v={{ gsm_version() }}"></script>
    <script src="{{ asset('js/admin/header-builder.js') }}?v={{ gsm_version() }}"></script>
@endpush

@push('styles')
    <link href="{{ asset('css/admin/builder.css') }}?v={{ gsm_version() }}" rel="stylesheet">
@endpush
