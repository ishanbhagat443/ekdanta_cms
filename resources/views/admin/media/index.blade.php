@extends($picker ? 'admin.layouts.bare' : 'admin.layouts.app')

@section('page_title', 'Media Library')

@section('page_content')
<div id="media-library"
     data-browse-url="{{ route('admin.media.browse') }}"
     data-upload-url="{{ route('admin.media.store') }}"
     data-media-url="{{ url('cms/media') }}"
     data-folder-url="{{ url('cms/media-folders') }}"
     data-bulk-delete-url="{{ route('admin.media.bulk-delete') }}"
     data-picker="{{ $picker ? '1' : '0' }}"
     data-multiple="{{ $multiple ? '1' : '0' }}"
     data-ckeditor="{{ $ckeditor }}">

    {{-- Toolbar --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="d-flex flex-wrap align-items-center" style="gap: .5rem;">
                <button type="button" class="btn btn-primary btn-sm" id="ml-upload-btn">
                    <i class="fas fa-upload mr-1"></i> Upload
                </button>
                <input type="file" id="ml-file-input" multiple class="d-none">

                <button type="button" class="btn btn-outline-secondary btn-sm" id="ml-new-folder-btn">
                    <i class="fas fa-folder-plus mr-1"></i> New Folder
                </button>

                <div class="input-group input-group-sm" style="width: 220px;">
                    <input type="text" id="ml-search" class="form-control" placeholder="Search media…">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" id="ml-search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>

                <select id="ml-type-filter" class="form-control form-control-sm" style="width: 140px;">
                    <option value="">All types</option>
                    <option value="image">Images</option>
                    <option value="video">Videos</option>
                    <option value="pdf">PDFs</option>
                    <option value="document">Documents</option>
                </select>

                <button type="button" class="btn btn-outline-danger btn-sm d-none" id="ml-bulk-delete">
                    <i class="fas fa-trash mr-1"></i> Delete Selected (<span id="ml-selected-count">0</span>)
                </button>

                @if ($picker)
                    <button type="button" class="btn btn-success btn-sm ml-auto d-none" id="ml-picker-confirm">
                        <i class="fas fa-check mr-1"></i> Use Selected
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-white px-3 py-2" id="ml-breadcrumb">
            <li class="breadcrumb-item active">Library</li>
        </ol>
    </nav>

    {{-- Drop zone + grid --}}
    <div id="ml-dropzone" class="position-relative" style="min-height: 320px;">
        <div id="ml-drop-overlay"
             class="position-absolute w-100 h-100 d-none align-items-center justify-content-center"
             style="z-index: 50; background: rgba(0,123,255,.12); border: 3px dashed #007bff; border-radius: 8px;">
            <div class="text-primary h4 mb-0"><i class="fas fa-cloud-upload-alt mr-2"></i>Drop files to upload</div>
        </div>

        <div id="ml-progress" class="mb-2 d-none">
            <div class="progress" style="height: 6px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%;"></div>
            </div>
            <small class="text-muted" id="ml-progress-text"></small>
        </div>

        <div class="row" id="ml-grid"></div>

        <div class="text-center py-5 text-muted d-none" id="ml-empty">
            <i class="fas fa-images fa-3x mb-3 d-block"></i>
            No media here yet. Drop files anywhere on this area or click Upload.
        </div>

        <div class="text-center my-3">
            <button type="button" class="btn btn-outline-secondary btn-sm d-none" id="ml-load-more">
                Load more
            </button>
        </div>
    </div>
</div>

{{-- Details modal --}}
<div class="modal fade" id="ml-detail-modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title">Media Details</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 col-md-6 text-center mb-3">
                        <div id="mld-preview" class="border rounded bg-light d-flex align-items-center justify-content-center"
                             style="min-height: 240px; max-height: 340px; overflow: hidden;"></div>
                        <small class="text-muted d-block mt-2" id="mld-meta"></small>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label class="mb-0"><small>Title</small></label>
                            <input type="text" id="mld-title" class="form-control form-control-sm">
                        </div>
                        <div class="form-group">
                            <label class="mb-0" for="mld-alt">
                                <small>Describe this image</small>
                            </label>
                            <input type="text" id="mld-alt" class="form-control form-control-sm"
                                   placeholder="e.g. Two people looking at a laptop in a bright office"
                                   aria-describedby="mld-alt-help">
                            <small class="form-text text-muted mt-0" id="mld-alt-help">
                                Read aloud to people using a screen reader, and used by image search.
                                Say what is in the picture, not "photo of".
                            </small>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="mld-decorative" aria-describedby="mld-decorative-help">
                                <label class="custom-control-label" for="mld-decorative"><small>Decorative</small></label>
                            </div>
                            <small class="form-text text-muted mt-0" id="mld-decorative-help">
                                Only decorates (a mark beside text, a layer of a drawing): no description, and screen readers skip it.
                            </small>
                        </div>
                        <div class="form-group">
                            <label class="mb-0"><small>File URL</small></label>
                            <div class="input-group input-group-sm">
                                <input type="text" id="mld-url" class="form-control" readonly>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" id="mld-copy"
                                            title="Copy URL"><i class="fas fa-copy"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap" style="gap:.4rem;">
                            <button type="button" class="btn btn-primary btn-sm" id="mld-save">
                                <i class="fas fa-save mr-1"></i> Save
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="mld-replace">
                                <i class="fas fa-exchange-alt mr-1"></i> Replace File
                            </button>
                            <input type="file" id="mld-replace-input" class="d-none">
                            <button type="button" class="btn btn-outline-danger btn-sm" id="mld-delete">
                                <i class="fas fa-trash mr-1"></i> Delete
                            </button>
                        </div>
                        <small class="text-muted d-block mt-2">
                            Replace keeps the same media ID — everywhere this file is used updates automatically.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@push('scripts')
    <script src="{{ asset('js/admin/media-library.js') }}?v={{ gsm_version() }}"></script>
@endpush
