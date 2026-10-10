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
        'previewUrl' => $page->previewUrl(),
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
                            <div class="dropdown-menu dropdown-menu-right gsm-add-menu" style="max-height: 460px; overflow-y: auto; min-width: 300px;">
                                <div class="px-2 pb-2">
                                    <label for="gsm-add-filter" class="sr-only">Find a section type</label>
                                    <input type="search" id="gsm-add-filter" class="form-control form-control-sm" placeholder="Find a section…" autocomplete="off">
                                </div>
                                @foreach ($pageTypes->groupBy(fn ($type) => $type->group()) as $groupName => $groupTypes)
                                    <h6 class="dropdown-header" data-add-group>{{ $groupName }}</h6>
                                    @foreach ($groupTypes as $type)
                                        <a href="#" class="dropdown-item gsm-add-section"
                                           data-area="page" data-type="{{ $type->key() }}">
                                            <i class="{{ $type->icon() }} fa-fw mr-2" aria-hidden="true"></i>{{ $type->label() }}
                                        </a>
                                    @endforeach
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
                        @php
                            $pageRole = \App\Support\PageRoles::roleOf($page);
                            $pageHeader = (array) $page->setting('header', []);
                            $pageCode = (array) $page->setting('code', []);
                        @endphp
                        <div class="form-group">
                            <label for="page_role"><small>Use this page as</small></label>
                            <select name="page_role" id="page_role" class="form-control form-control-sm">
                                <option value="">An ordinary page</option>
                                @foreach (\App\Support\PageRoles::ROLES as $roleKey => $roleLabel)
                                    <option value="{{ $roleKey }}" @selected($pageRole === $roleKey)>{{ $roleLabel }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">One page per role. It takes effect on the live site once this page is published; previews use it straight away.</small>
                        </div>
                        <div class="form-group">
                            <label for="template_for"><small>Template for</small></label>
                            <select name="template_for" id="template_for" class="form-control form-control-sm">
                                <option value="">Not a template</option>
                                @foreach (\App\Support\Templates::TYPES as $templateKey => $templateLabel)
                                    <option value="{{ $templateKey }}" @selected($page->setting('template_for') === $templateKey)>{{ $templateLabel }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">This page then draws every item at its own address, and is not shown at its own. Its sections read the item.</small>
                        </div>
                        <hr>
                        <p class="text-muted small mb-2"><i class="fas fa-window-maximize mr-1" aria-hidden="true"></i>Header and footer on this page</p>
                        <div class="form-group">
                            <label for="header_variant"><small>Header style</small></label>
                            <select name="header[variant]" id="header_variant" class="form-control form-control-sm">
                                <option value="">Site default (Header Builder)</option>
                                @foreach (['normal' => 'Normal bar', 'overlay' => 'Over the first section (transparent)', 'pill' => 'Fixed floating pill', 'hidden' => 'Hidden'] as $variantKey => $variantLabel)
                                    <option value="{{ $variantKey }}" @selected(($pageHeader['variant'] ?? '') === $variantKey)>{{ $variantLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="header_menu"><small>Header menu</small></label>
                            <select name="header[menu_id]" id="header_menu" class="form-control form-control-sm">
                                <option value="">Site default</option>
                                @foreach ($menus as $menuOption)
                                    <option value="{{ $menuOption->id }}" @selected((int) ($pageHeader['menu_id'] ?? 0) === $menuOption->id)>{{ $menuOption->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-6">
                                <label for="header_cta_label"><small>Header button text</small></label>
                                <input type="text" name="header[cta_label]" id="header_cta_label" maxlength="60"
                                       value="{{ $pageHeader['cta_label'] ?? '' }}" placeholder="Site default" class="form-control form-control-sm">
                            </div>
                            <div class="form-group col-6">
                                <label for="header_cta_url"><small>Header button link</small></label>
                                <input type="text" name="header[cta_url]" id="header_cta_url" maxlength="500"
                                       value="{{ $pageHeader['cta_url'] ?? '' }}" placeholder="e.g. #contact or /contact" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="custom-control custom-switch mb-2">
                            <input type="hidden" name="show_footer" value="0">
                            <input type="checkbox" class="custom-control-input" id="show_footer" name="show_footer" value="1"
                                   @checked($page->setting('show_footer', true) !== false)>
                            <label class="custom-control-label" for="show_footer">Show the site footer</label>
                            <small class="form-text text-muted mt-0">A section that brings its own footer (e.g. Ekdanta · Big Brand Finale) hides it automatically.</small>
                        </div>
                        @can('developer.mode')
                            <details class="mb-2">
                                <summary class="small text-muted">Page code (Developer Mode)</summary>
                                @foreach (['css' => 'Page CSS', 'js' => 'Page JavaScript', 'head' => 'Code before the closing head tag (e.g. conversion tags)', 'body' => 'Code before the closing body tag'] as $codeKey => $codeLabel)
                                    <div class="form-group mt-2 mb-1">
                                        <label class="mb-0" for="page_code_{{ $codeKey }}"><small>{{ $codeLabel }}</small></label>
                                        <textarea name="code[{{ $codeKey }}]" id="page_code_{{ $codeKey }}" rows="3" spellcheck="false"
                                                  class="form-control form-control-sm text-monospace">{{ $pageCode[$codeKey] ?? '' }}</textarea>
                                    </div>
                                @endforeach
                            </details>
                        @endcan
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
                <div class="card-header d-flex align-items-center flex-wrap" style="gap: .35rem;">
                    <h3 class="card-title mr-auto"><i class="fas fa-eye mr-2" aria-hidden="true"></i>Live preview</h3>
                    {{-- Exact screen sizes: the page inside sees that real viewport
                         (media queries, vh), scaled down only to fit this panel. --}}
                    <div class="btn-group btn-group-sm" role="group" aria-label="Preview size">
                        <button type="button" class="btn btn-outline-secondary active" data-gsm-preview-size="fit" aria-pressed="true">Fit</button>
                        @foreach (['1440x900', '1280x720', '390x844', '360x640'] as $previewSize)
                            <button type="button" class="btn btn-outline-secondary" data-gsm-preview-size="{{ $previewSize }}" aria-pressed="false">{{ str_replace('x', ' × ', $previewSize) }}</button>
                        @endforeach
                    </div>
                    @if ($previewTemplate = $page->setting('template_for'))
                        {{-- A template page previews one item: pick which. Drafts included. --}}
                        <label for="gsm-preview-item" class="sr-only">Preview with</label>
                        <select id="gsm-preview-item" class="form-control form-control-sm" style="width: auto; max-width: 220px;">
                            @foreach (\App\Support\Templates::options($previewTemplate) as $optionSlug => $optionTitle)
                                <option value="{{ $optionSlug }}">{{ \Illuminate\Support\Str::limit($optionTitle, 40) }}</option>
                            @endforeach
                        </select>
                    @endif
                    <div class="custom-control custom-switch ml-1">
                        <input type="checkbox" class="custom-control-input" id="gsm-preview-reduce">
                        <label class="custom-control-label small" for="gsm-preview-reduce">Reduce motion</label>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-xs" aria-label="Reload preview"
                            onclick="document.getElementById('gsm-live-preview').contentWindow.location.reload();">
                        <i class="fas fa-sync-alt" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="card-body p-0" id="gsm-preview-stage" style="overflow: hidden;">
                    <iframe id="gsm-live-preview" src="{{ $page->previewUrl() }}" title="Live preview of this page"
                            style="width:100%; height:60vh; border:0; transform-origin: 0 0;"></iframe>
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

@push('scripts')
    <script>
        // "Add a section" filter: by name, keeping group headings that still have matches.
        (function () {
            var input = document.getElementById('gsm-add-filter');
            if (!input) return;
            var menu = input.closest('.gsm-add-menu');
            input.addEventListener('click', function (e) { e.stopPropagation(); });
            input.addEventListener('input', function () {
                var q = input.value.trim().toLowerCase();
                var heading = null, shown = 0;
                var close = function () { if (heading) heading.hidden = shown === 0; };
                menu.querySelectorAll('[data-add-group], .gsm-add-section').forEach(function (el) {
                    if (el.hasAttribute('data-add-group')) { close(); heading = el; shown = 0; return; }
                    var match = el.textContent.toLowerCase().indexOf(q) !== -1;
                    el.hidden = !match;
                    if (match) shown++;
                });
                close();
            });
        })();

        // Live preview: exact-size presets and the Reduce motion switch.
        (function () {
            var frame = document.getElementById('gsm-live-preview');
            var stage = document.getElementById('gsm-preview-stage');
            if (!frame || !stage) return;
            var size = 'fit';

            function layout() {
                if (size === 'fit') {
                    frame.style.width = '100%'; frame.style.height = '60vh'; frame.style.transform = ''; stage.style.height = '';
                    return;
                }
                var wh = size.split('x').map(Number);
                var scale = Math.min(1, stage.clientWidth / wh[0]);
                frame.style.width = wh[0] + 'px';
                frame.style.height = wh[1] + 'px';
                frame.style.transform = 'scale(' + scale + ')';
                stage.style.height = Math.round(wh[1] * scale) + 'px';
            }

            document.querySelectorAll('[data-gsm-preview-size]').forEach(function (button) {
                button.addEventListener('click', function () {
                    size = button.dataset.gsmPreviewSize;
                    document.querySelectorAll('[data-gsm-preview-size]').forEach(function (other) {
                        other.classList.toggle('active', other === button);
                        other.setAttribute('aria-pressed', other === button ? 'true' : 'false');
                    });
                    layout();
                });
            });

            var pick = document.getElementById('gsm-preview-item');
            if (pick) pick.addEventListener('change', function () {
                var url = new URL(frame.src, window.location.href);
                url.searchParams.set('item', pick.value);
                frame.src = url.toString();
            });

            document.getElementById('gsm-preview-reduce').addEventListener('change', function () {
                var url = new URL(frame.src, window.location.href);
                if (this.checked) { url.searchParams.set('reduce-motion', '1'); } else { url.searchParams.delete('reduce-motion'); }
                frame.src = url.toString();
            });

            window.addEventListener('resize', layout);
        })();
    </script>
@endpush
