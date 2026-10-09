@extends('admin.layouts.app')

@section('page_title', 'Footer Builder')

@section('page_content')
<div id="gsm-footer-builder"
     data-sections-url="{{ url('cms/sections') }}"
     data-picker-src="{{ route('admin.media.index', ['picker' => 1]) }}">

    @include('admin.builder.partials.toolbar', [
        'formId' => 'gsm-footer-form',
        'previewUrl' => url('/'),
    ])

    <div class="row">
        {{-- Left: widgets --}}
        <div class="col-12 col-xl-7">
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title"><i class="fas fa-shoe-prints mr-2"></i>Footer Widgets</h3>
                    <div class="ml-auto">
                        <div class="btn-group">
                            <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-plus mr-1"></i> Add Widget
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                @foreach ($footerTypes as $type)
                                    <a href="#" class="dropdown-item gsm-add-section"
                                       data-area="footer" data-type="{{ $type->key() }}">
                                        <i class="{{ $type->icon() }} fa-fw mr-2"></i>{{ $type->label() }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body gsm-section-list" data-area="footer">
                    @forelse ($footer->sections as $section)
                        @include('admin.builder.partials.section-card', [
                            'section' => $section,
                            'registry' => $registry,
                            'menus' => $menus,
                            'area' => 'footer',
                        ])
                    @empty
                        <p class="text-muted text-center py-3 mb-0">No widgets yet — use “Add Widget”.</p>
                    @endforelse
                </div>
            </div>

            {{-- Footer settings --}}
            <form method="POST" action="{{ route('admin.footer.update') }}" id="gsm-footer-form" data-gsm-primary-save>
                @csrf
                @method('PUT')
                <div class="card">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-sliders-h mr-2"></i>Footer Settings</h3></div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="col-6 col-md-3">
                                <label><small>Background</small></label>
                                <input type="color" name="bg_color" class="form-control form-control-sm"
                                       value="{{ old('bg_color', $footer->setting('bg_color', '#101828')) }}"
                                       style="height:31px; padding:2px;">
                            </div>
                            <div class="col-6 col-md-3">
                                <label><small>Text Color</small></label>
                                <input type="color" name="text_color" class="form-control form-control-sm"
                                       value="{{ old('text_color', $footer->setting('text_color', '#e8e8ef')) }}"
                                       style="height:31px; padding:2px;">
                            </div>
                            <div class="col-12 col-md-6">
                                <label><small>Container Width</small></label>
                                <select name="container" class="form-control form-control-sm">
                                    <option value="container" @selected($footer->setting('container', 'container') === 'container')>Boxed</option>
                                    <option value="container-fluid" @selected($footer->setting('container') === 'container-fluid')>Full width</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row mt-2">
                            <div class="col-8 col-md-4">
                                <label><small>Background Image (Media ID)</small></label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="bg_media_id" id="gsm-footer-bg-media"
                                           class="form-control form-control-sm"
                                           value="{{ old('bg_media_id', $footer->setting('bg_media_id')) }}">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary gsm-bf-media-pick"
                                                data-target="gsm-footer-bg-media">
                                            <i class="fas fa-photo-video"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted">Shown behind the footer with the overlay on top.</small>
                            </div>
                            <div class="col-4 col-md-3">
                                <label><small>Overlay Opacity (%)</small></label>
                                <input type="number" name="overlay_opacity" min="0" max="95"
                                       class="form-control form-control-sm"
                                       value="{{ old('overlay_opacity', $footer->setting('overlay_opacity', 70)) }}">
                            </div>
                            <div class="col-8 col-md-5">
                                <label><small>Right-Side Image (Media ID)</small></label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="side_media_id" id="gsm-footer-side-media"
                                           class="form-control form-control-sm"
                                           value="{{ old('side_media_id', $footer->setting('side_media_id')) }}">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary gsm-bf-media-pick"
                                                data-target="gsm-footer-side-media">
                                            <i class="fas fa-photo-video"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted">Transparent PNG, sits above the background on the right edge.</small>
                            </div>
                        </div>
                        <div class="form-group mt-3">
                            <label><small>Footer CSS</small></label>
                            <textarea name="custom_css" rows="4" spellcheck="false"
                                      class="form-control form-control-sm text-monospace"
                                      placeholder=".gsm-footer { … }">{{ old('custom_css', $footer->custom_css) }}</textarea>
                        </div>
                        {{-- Runs on every public page, so it is a Developer Mode privilege
                             (FooterBuilderController drops it for everyone else). --}}
                        @can('developer.mode')
                            <div class="form-group">
                                <label><small>Footer JS</small></label>
                                <textarea name="custom_js" rows="4" spellcheck="false"
                                          class="form-control form-control-sm text-monospace">{{ old('custom_js', $footer->custom_js) }}</textarea>
                            </div>
                        @endcan
                    </div>
                    <div class="card-footer text-muted small">
                        <i class="fas fa-circle-info mr-1" aria-hidden="true"></i>Changes here save with the
                        <strong>Save</strong> button at the top of the screen.
                    </div>
                </div>
            </form>
        </div>

        {{-- Right: live preview --}}
        <div class="col-12 col-xl-5">
            <div class="card" style="position: sticky; top: 60px;">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title"><i class="fas fa-eye mr-2"></i>Live Preview</h3>
                    <div class="ml-auto">
                        <button type="button" class="btn btn-outline-secondary btn-xs"
                                onclick="document.getElementById('gsm-live-preview').contentWindow.location.reload();">
                            <i class="fas fa-sync-alt mr-1"></i>Refresh
                        </button>
                        <a href="{{ route('front.home') }}" target="_blank" class="btn btn-outline-secondary btn-xs">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <iframe id="gsm-live-preview" src="{{ route('front.home') }}"
                            style="width:100%; height:75vh; border:0;"
                            onload="try { this.contentWindow.scrollTo(0, this.contentWindow.document.body.scrollHeight); } catch (e) {}"></iframe>
                </div>
                <div class="card-footer py-1">
                    <small class="text-muted">Auto-scrolls to the footer and refreshes after every save.</small>
                </div>
            </div>
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
