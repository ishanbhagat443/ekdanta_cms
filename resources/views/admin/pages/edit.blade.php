@extends('admin.layouts.app')

@section('page_title', 'Build Page — '.$page->title)

@section('page_content')
<div id="gsm-page-builder"
     data-sections-url="{{ url('cms/sections') }}"
     data-page-id="{{ $page->id }}"
     data-revisions-url="{{ route('admin.pages.revisions.index', $page) }}"
     data-ai-improve-url="{{ route('admin.ai.improve') }}"
     data-picker-src="{{ route('admin.media.index', ['picker' => 1]) }}">

    @include('admin.builder.partials.toolbar', [
        'formId' => 'gsm-page-form',
        'previewUrl' => url('/'.$page->slug).'?preview=1',
        'historyUrl' => route('admin.pages.revisions.index', $page),
    ])

    <div class="row">
        {{-- Left: sections --}}
        <div class="col-12 col-xl-8">
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-puzzle-piece mr-2"></i>Sections — {{ $page->title }}
                    </h3>
                    <div class="ml-auto">
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-primary btn-sm" data-toggle="modal" data-target="#gsm-add-block-modal">
                                <i class="fas fa-cubes mr-1"></i> Add a saved block
                            </button>
                            <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-plus mr-1"></i> Add a section
                            </button>
                            <div class="dropdown-menu dropdown-menu-right" style="max-height: 420px; overflow-y: auto;">
                                @foreach ($pageTypes as $type)
                                    <a href="#" class="dropdown-item gsm-add-section"
                                       data-area="page" data-type="{{ $type->key() }}">
                                        <i class="{{ $type->icon() }} fa-fw mr-2"></i>{{ $type->label() }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body gsm-section-list" data-area="page">
                    @forelse ($page->sections as $section)
                        @include('admin.builder.partials.section-card', [
                            'section' => $section,
                            'registry' => $registry,
                            'menus' => $menus,
                            'area' => 'page',
                        ])
                    @empty
                        {{-- An empty page is an invitation, not a dead end: offer the
                             fastest route (a finished layout to edit) before the slow
                             one (pick a block, then another, then another). --}}
                        <div class="gsm-empty">
                            <i class="fas fa-wand-magic-sparkles gsm-empty__icon" aria-hidden="true"></i>
                            <h4 class="gsm-empty__title">This page is empty</h4>
                            <p class="gsm-empty__text">
                                Start from a ready-made layout and change the words and pictures,
                                or build it yourself one block at a time.
                            </p>

                            <form method="POST" action="{{ route('admin.pages.starter.apply', $page) }}"
                                  class="gsm-empty__form">
                                @csrf
                                <label class="sr-only" for="gsm-empty-starter">Starter layout</label>
                                <select name="starter" id="gsm-empty-starter" class="form-control form-control-sm">
                                    @foreach ($starters as $starterKey => $starter)
                                        @continue($starterKey === 'blank')
                                        <option value="{{ $starterKey }}">{{ $starter['label'] }} — {{ $starter['description'] }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-primary btn-sm">Use this layout</button>
                            </form>

                            <p class="gsm-empty__or">or use <strong>Create New Block</strong> above to start from scratch</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Right: page settings + preview --}}
        <div class="col-12 col-xl-4">
            <form method="POST" action="{{ route('admin.pages.update', $page) }}" id="gsm-page-form" data-gsm-primary-save>
                @csrf
                @method('PUT')
                <div class="card mb-3">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-cog mr-2"></i>Page settings</h3></div>
                    <div class="card-body">
                        <div class="form-group">
                            <label><small>Title</small></label>
                            <input type="text" name="title" required value="{{ old('title', $page->title) }}"
                                   class="form-control form-control-sm @error('title') is-invalid @enderror">
                            @error('title') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label><small>Slug</small></label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend"><span class="input-group-text">/</span></div>
                                <input type="text" name="slug" value="{{ old('slug', $page->slug) }}"
                                       {{ $page->slug === 'home' ? 'readonly' : '' }}
                                       class="form-control @error('slug') is-invalid @enderror">
                            </div>
                            @error('slug') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label><small>Status</small></label>
                            <select name="status" class="form-control form-control-sm">
                                <option value="draft" @selected($page->status === 'draft')>Draft</option>
                                <option value="published" @selected($page->status === 'published')>Published</option>
                            </select>
                        </div>
                        <hr>
                        <div class="custom-control custom-switch mb-2">
                            <input type="hidden" name="show_banner" value="0">
                            <input type="checkbox" class="custom-control-input" id="show_banner" name="show_banner" value="1"
                                   @checked($page->show_banner)>
                            <label class="custom-control-label" for="show_banner">Show page title banner</label>
                        </div>
                        <div class="form-group">
                            <label><small>Banner heading <span class="text-muted">(blank = the page title)</span></small></label>
                            <input type="text" name="banner_title" value="{{ old('banner_title', $page->banner_title) }}"
                                   class="form-control form-control-sm">
                        </div>
                        <div class="form-group mb-0">
                            <label><small>Banner Image (media ID)</small></label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="banner_media_id" id="banner_media"
                                       value="{{ old('banner_media_id', $page->banner_media_id) }}" class="form-control">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-primary gsm-bf-media-pick"
                                            data-target="banner_media"><i class="fas fa-photo-video"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        @include('admin.partials.seo-fields', ['seo' => $page->seoMeta])
                    </div>
                    @include('admin.pages.partials.template-blocks', ['page' => $page])

                    @if ($page->slug === 'blog-listing')
                        {{-- Blog Listing CMS settings (read by BlogController::index()
                             via Page::setting() — see gridDefaults()/sidebarDefaults()
                             for the exact same default values used here). --}}
                        @php
                            $blogGrid = array_merge([
                                'posts_per_page' => null, 'pagination_enabled' => true, 'columns' => 2,
                                'featured_first' => false, 'sort_order' => 'latest', 'show_date' => true,
                                'show_category' => false, 'show_author' => true, 'show_read_more' => false,
                                'show_featured_badge' => false,
                            ], (array) $page->setting('grid', []));
                            $blogSidebar = array_merge([
                                'search' => true, 'categories' => true, 'recent' => true, 'popular' => true,
                                'tags' => false, 'newsletter' => false,
                            ], (array) $page->setting('sidebar', []));
                        @endphp
                        <div class="card-body pt-0">
                            <hr>
                            <label class="mb-2 d-block"><small class="font-weight-bold text-uppercase">Blog Grid</small></label>
                            <div class="form-group">
                                <label><small>Posts Per Page <span class="text-muted">(blank = site default)</span></small></label>
                                <input type="number" name="blog_grid[posts_per_page]" min="1" max="100"
                                       value="{{ old('blog_grid.posts_per_page', $blogGrid['posts_per_page']) }}" class="form-control form-control-sm">
                            </div>
                            <div class="form-group">
                                <label><small>Grid Columns</small></label>
                                <select name="blog_grid[columns]" class="form-control form-control-sm">
                                    <option value="2" @selected((int) $blogGrid['columns'] === 2)>2 Columns</option>
                                    <option value="3" @selected((int) $blogGrid['columns'] === 3)>3 Columns</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label><small>Sort Order</small></label>
                                <select name="blog_grid[sort_order]" class="form-control form-control-sm">
                                    <option value="latest" @selected($blogGrid['sort_order'] === 'latest')>Latest First</option>
                                    <option value="oldest" @selected($blogGrid['sort_order'] === 'oldest')>Oldest First</option>
                                    <option value="popular" @selected($blogGrid['sort_order'] === 'popular')>Most Popular</option>
                                </select>
                            </div>
                            @foreach ([
                                'pagination_enabled' => 'Enable Pagination',
                                'featured_first' => 'Featured Posts First',
                                'show_date' => 'Show Date',
                                'show_category' => 'Show Category',
                                'show_author' => 'Show Author',
                                'show_read_more' => 'Show “Read More” Link',
                                'show_featured_badge' => 'Show Featured Badge',
                            ] as $key => $label)
                                <div class="custom-control custom-switch mb-1">
                                    <input type="hidden" name="blog_grid[{{ $key }}]" value="0">
                                    <input type="checkbox" class="custom-control-input" id="bg_{{ $key }}"
                                           name="blog_grid[{{ $key }}]" value="1" @checked((bool) $blogGrid[$key])>
                                    <label class="custom-control-label" for="bg_{{ $key }}">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                        <div class="card-body pt-0">
                            <hr>
                            <label class="mb-2 d-block"><small class="font-weight-bold text-uppercase">Sidebar Widgets</small></label>
                            @include('admin.pages.partials.blog-sidebar-fields')
                        </div>
                    @endif

                    @if ($page->slug === 'blog-detail')
                        {{-- Blog Detail CMS settings (read by BlogController::show()
                             via Page::setting() — see relatedDefaults()/sidebarDefaults()/
                             socialShareDefaults()/postContentDefaults() for the exact
                             same default values used here). --}}
                        @php
                            $blogRelated = array_merge([
                                'enabled' => true, 'count' => 3, 'same_category_only' => false,
                                'manual_only' => false, 'sort_order' => 'latest',
                            ], (array) $page->setting('related', []));
                            $blogSidebar = array_merge([
                                'search' => true, 'categories' => true, 'recent' => true, 'popular' => true,
                                'tags' => false, 'newsletter' => false,
                            ], (array) $page->setting('sidebar', []));
                            $blogSocialShare = array_merge([
                                'facebook' => true, 'whatsapp' => true, 'linkedin' => true, 'twitter' => true, 'copy_link' => false,
                            ], (array) $page->setting('social_share', []));
                            $blogComments = array_merge(['enabled' => false], (array) $page->setting('comments', []));
                            $blogPostContent = array_merge(['show_reading_time' => false], (array) $page->setting('post_content', []));
                        @endphp
                        <div class="card-body pt-0">
                            <hr>
                            <label class="mb-2 d-block"><small class="font-weight-bold text-uppercase">Related Posts</small></label>
                            <div class="custom-control custom-switch mb-2">
                                <input type="hidden" name="blog_related[enabled]" value="0">
                                <input type="checkbox" class="custom-control-input" id="br_enabled"
                                       name="blog_related[enabled]" value="1" @checked((bool) $blogRelated['enabled'])>
                                <label class="custom-control-label" for="br_enabled">Enable Related Posts</label>
                            </div>
                            <div class="form-group">
                                <label><small>Number of Posts</small></label>
                                <input type="number" name="blog_related[count]" min="1" max="12"
                                       value="{{ old('blog_related.count', $blogRelated['count']) }}" class="form-control form-control-sm">
                            </div>
                            <div class="form-group">
                                <label><small>Sort Order</small></label>
                                <select name="blog_related[sort_order]" class="form-control form-control-sm">
                                    <option value="latest" @selected($blogRelated['sort_order'] === 'latest')>Latest First</option>
                                    <option value="oldest" @selected($blogRelated['sort_order'] === 'oldest')>Oldest First</option>
                                    <option value="popular" @selected($blogRelated['sort_order'] === 'popular')>Most Popular</option>
                                </select>
                            </div>
                            <div class="custom-control custom-switch mb-1">
                                <input type="hidden" name="blog_related[same_category_only]" value="0">
                                <input type="checkbox" class="custom-control-input" id="br_sco"
                                       name="blog_related[same_category_only]" value="1" @checked((bool) $blogRelated['same_category_only'])>
                                <label class="custom-control-label" for="br_sco">Same Category Only</label>
                            </div>
                            <div class="custom-control custom-switch mb-1">
                                <input type="hidden" name="blog_related[manual_only]" value="0">
                                <input type="checkbox" class="custom-control-input" id="br_mo"
                                       name="blog_related[manual_only]" value="1" @checked((bool) $blogRelated['manual_only'])>
                                <label class="custom-control-label" for="br_mo">Manual Selection Only <span class="text-muted">(picked per-post in Posts → edit)</span></label>
                            </div>
                        </div>

                        <div class="card-body pt-0">
                            <hr>
                            <label class="mb-2 d-block"><small class="font-weight-bold text-uppercase">Sidebar Widgets</small></label>
                            @include('admin.pages.partials.blog-sidebar-fields')
                        </div>

                        <div class="card-body pt-0">
                            <hr>
                            <label class="mb-2 d-block"><small class="font-weight-bold text-uppercase">Social Share</small></label>
                            @foreach ([
                                'facebook' => 'Facebook', 'whatsapp' => 'WhatsApp', 'linkedin' => 'LinkedIn',
                                'twitter' => 'Twitter / X', 'copy_link' => 'Copy Link',
                            ] as $key => $label)
                                <div class="custom-control custom-switch mb-1">
                                    <input type="hidden" name="blog_social_share[{{ $key }}]" value="0">
                                    <input type="checkbox" class="custom-control-input" id="bss_{{ $key }}"
                                           name="blog_social_share[{{ $key }}]" value="1" @checked((bool) $blogSocialShare[$key])>
                                    <label class="custom-control-label" for="bss_{{ $key }}">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>

                        <div class="card-body pt-0">
                            <hr>
                            <label class="mb-2 d-block"><small class="font-weight-bold text-uppercase">Comments</small></label>
                            <div class="custom-control custom-switch mb-1">
                                <input type="hidden" name="blog_comments[enabled]" value="0">
                                <input type="checkbox" class="custom-control-input" id="bc_enabled"
                                       name="blog_comments[enabled]" value="1" @checked((bool) $blogComments['enabled'])>
                                <label class="custom-control-label" for="bc_enabled">Enable Comments</label>
                            </div>

                            <hr>
                            <label class="mb-2 d-block"><small class="font-weight-bold text-uppercase">Post Content</small></label>
                            <div class="custom-control custom-switch mb-1">
                                <input type="hidden" name="blog_post_content[show_reading_time]" value="0">
                                <input type="checkbox" class="custom-control-input" id="bpc_rt"
                                       name="blog_post_content[show_reading_time]" value="1" @checked((bool) $blogPostContent['show_reading_time'])>
                                <label class="custom-control-label" for="bpc_rt">Show Reading Time</label>
                            </div>
                        </div>
                    @endif

                    <div class="card-footer text-muted small">
                        <i class="fas fa-circle-info mr-1" aria-hidden="true"></i>Changes here save with the
                        <strong>Save</strong> button at the top of the screen.
                    </div>
                </div>
            </form>

            <div class="card" style="position: sticky; top: 60px;">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title"><i class="fas fa-eye mr-2"></i>Live preview</h3>
                    <button type="button" class="btn btn-outline-secondary btn-xs ml-auto"
                            onclick="document.getElementById('gsm-live-preview').contentWindow.location.reload();">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                <div class="card-body p-0">
                    <iframe id="gsm-live-preview" src="{{ url('/'.$page->slug).'?preview=1' }}"
                            style="width:100%; height:60vh; border:0;"></iframe>
                </div>
            </div>
        </div>
    </div>

    {{-- Version history --}}
    <div class="modal fade" id="gsm-history-modal" tabindex="-1" aria-labelledby="gsm-history-title">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h5 class="modal-title" id="gsm-history-title">
                        <i class="fas fa-clock-rotate-left mr-2" aria-hidden="true"></i>Version history
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close history">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" data-gsm-history-body>
                    <p class="text-muted mb-0" data-gsm-history-loading>Loading versions…</p>
                </div>
                <div class="modal-footer py-2">
                    <p class="text-muted small mb-0 mr-auto">
                        A version is kept each time you save. Restoring saves your current version first,
                        so you can always come back.
                    </p>
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Existing Block modal --}}
    <div class="modal fade" id="gsm-add-block-modal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h5 class="modal-title"><i class="fas fa-cubes mr-2"></i>Add a saved block</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="text" class="form-control form-control-sm mb-3" id="gsm-block-picker-search" placeholder="Search blocks…">
                    <div class="row" id="gsm-block-picker-grid">
                        @forelse ($globalBlocks as $block)
                            <div class="col-6 col-md-4 gsm-block-picker-item" data-name="{{ strtolower($block->name) }}">
                                <button type="button" class="btn btn-outline-secondary btn-block text-left mb-3 p-2 gsm-add-existing-block"
                                        data-block-id="{{ $block->id }}" style="height: 100%;">
                                    @if ($block->previewMedia)
                                        <img src="{{ $block->previewMedia->variantUrl('thumb') ?? $block->previewMedia->url }}"
                                             class="img-fluid rounded mb-1" style="aspect-ratio:16/10; object-fit:cover; width:100%;" alt="">
                                    @else
                                        <div class="bg-light rounded mb-1 d-flex align-items-center justify-content-center" style="aspect-ratio:16/10;">
                                            <i class="fas fa-cube text-muted"></i>
                                        </div>
                                    @endif
                                    <div class="small font-weight-bold">{{ $block->name }}</div>
                                    <div class="small text-muted">{{ $block->category?->name ?? 'Uncategorized' }}</div>
                                </button>
                            </div>
                        @empty
                            <div class="col-12">
                                <p class="text-center text-muted py-4">
                                    No reusable blocks yet. <a href="{{ route('admin.global-blocks.create') }}" target="_blank">Create one</a> in the Block Library.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.css" rel="stylesheet">
    <link href="{{ asset('css/admin/builder.css') }}?v={{ gsm_version() }}" rel="stylesheet">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.js"></script>
    <script>
        // Rich text editors on all richtext fields (lazy: init when card expands).
        $(function () {
            $('#gsm-page-builder').on('shown.bs.collapse', '.collapse', function () {
                $(this).find('.gsm-richtext-input').each(function () {
                    if (!$(this).next('.note-editor').length) {
                        $(this).summernote({
                            height: 160,
                            toolbar: [
                                ['style', ['style']],
                                ['font', ['bold', 'italic', 'underline', 'clear']],
                                ['para', ['ul', 'ol', 'paragraph']],
                                ['insert', ['link', 'picture', 'table']],
                                ['view', ['codeview']],
                            ],
                            callbacks: {
                                onImageUpload: function (files) {
                                    var editor = $(this);
                                    var form = new FormData();
                                    form.append('upload', files[0]);
                                    fetch('{{ route('admin.media.ckeditor') }}', {
                                        method: 'POST',
                                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                                        body: form
                                    }).then(function (r) { return r.json(); })
                                      .then(function (json) { if (json.url) editor.summernote('insertImage', json.url); });
                                }
                            }
                        });
                    }
                });
            });
        });
    </script>
    <script src="{{ asset('js/admin/media-picker-field.js') }}?v={{ gsm_version() }}"></script>
    <script src="{{ asset('js/admin/header-builder.js') }}?v={{ gsm_version() }}"></script>
@endpush
