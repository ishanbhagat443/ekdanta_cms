@extends('admin.layouts.app')

@section('page_title', 'Block Library')

@section('page_actions')
    <div class="btn-group">
        <a href="{{ route('admin.global-blocks.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus mr-1"></i> New Block
        </a>
        <button type="button" class="btn btn-outline-secondary btn-sm" data-toggle="modal" data-target="#gsm-import-block-modal">
            <i class="fas fa-file-import mr-1"></i> Import
        </button>
    </div>
@stop

@section('page_content')
    <div class="card card-outline card-secondary">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('admin.global-blocks.index') }}" class="form-row align-items-end">
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="small mb-0">Search</label>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Name or description…"
                           class="form-control form-control-sm">
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="small mb-0">Category</label>
                    <select name="category" class="form-control form-control-sm">
                        <option value="">All categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->slug }}" @selected(($filters['category'] ?? null) === $category->slug)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="small mb-0">Block Type</label>
                    <select name="type" class="form-control form-control-sm">
                        <option value="">All types</option>
                        @foreach ($sectionTypes as $sectionType)
                            <option value="{{ $sectionType->key() }}" @selected(($filters['type'] ?? null) === $sectionType->key())>{{ $sectionType->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="small mb-0">Sort</label>
                    <select name="sort" class="form-control form-control-sm">
                        <option value="newest" @selected(($filters['sort'] ?? 'newest') === 'newest')>Newest</option>
                        <option value="recent" @selected(($filters['sort'] ?? '') === 'recent')>Recently Used</option>
                        <option value="most_used" @selected(($filters['sort'] ?? '') === 'most_used')>Most Used</option>
                        <option value="name" @selected(($filters['sort'] ?? '') === 'name')>Name A–Z</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <div class="custom-control custom-checkbox mt-4">
                        <input type="checkbox" class="custom-control-input" id="favorites_only" name="favorites" value="1"
                               @checked(! empty($filters['favorites']))>
                        <label class="custom-control-label" for="favorites_only">Favorites only</label>
                    </div>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-primary btn-sm btn-block"><i class="fas fa-filter"></i></button>
                </div>
            </form>
        </div>
    </div>

    <div class="row" id="gsm-block-grid">
        @forelse ($blocks as $block)
            <div class="col-12 col-md-6 col-lg-4 col-xl-3 mb-4">
                <div class="card h-100">
                    <a href="{{ route('admin.global-blocks.edit', $block) }}" class="d-block position-relative">
                        @if ($block->previewMedia)
                            <img src="{{ $block->previewMedia->variantUrl('card') ?? $block->previewMedia->url }}" alt="{{ $block->name }}"
                                 class="card-img-top" style="aspect-ratio: 16/10; object-fit: cover;" loading="lazy">
                        @else
                            <div class="bg-light card-img-top d-flex align-items-center justify-content-center" style="aspect-ratio: 16/10;">
                                <i class="fas fa-cube fa-2x text-muted"></i>
                            </div>
                        @endif
                        <span class="badge badge-{{ $block->status === 'active' ? 'success' : ($block->status === 'draft' ? 'secondary' : 'dark') }} position-absolute"
                              style="top:8px; left:8px;">{{ ucfirst($block->status) }}</span>
                    </a>
                    <div class="card-body py-2">
                        <div class="d-flex align-items-start">
                            <h3 class="h6 mb-1">
                                <a href="{{ route('admin.global-blocks.edit', $block) }}" class="text-dark">{{ $block->name }}</a>
                            </h3>
                            <button type="button" class="btn btn-xs btn-link ml-auto p-0 gsm-toggle-favorite"
                                    data-url="{{ route('admin.global-blocks.favorite', $block) }}"
                                    title="Toggle favorite">
                                <i class="{{ $block->is_favorite ? 'fas' : 'far' }} fa-star" style="color:#4b5563;"></i>
                            </button>
                        </div>
                        <div class="small text-muted mb-1">
                            {{ $block->category?->name ?? 'Uncategorized' }} · v{{ $block->version }}
                        </div>
                        <div class="small text-muted">
                            <i class="fas fa-layer-group mr-1"></i>{{ $block->instances_count }} page(s)
                            @if ($block->use_count)
                                &middot; used {{ $block->use_count }}×
                            @endif
                        </div>
                    </div>
                    <div class="card-footer py-1 d-flex justify-content-between">
                        <a href="{{ route('admin.global-blocks.edit', $block) }}" class="btn btn-xs btn-outline-primary">
                            <i class="fas fa-pen"></i> Edit
                        </a>
                        <div>
                            <form action="{{ route('admin.global-blocks.duplicate', $block) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-xs btn-outline-secondary" title="Duplicate"><i class="fas fa-clone"></i></button>
                            </form>
                            <a href="{{ route('admin.global-blocks.export', $block) }}" class="btn btn-xs btn-outline-secondary" title="Export">
                                <i class="fas fa-file-export"></i>
                            </a>
                            <form action="{{ route('admin.global-blocks.destroy', $block) }}" method="POST" class="d-inline js-confirm-delete">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <p class="text-center text-muted py-5">No blocks yet — create your first reusable block.</p>
            </div>
        @endforelse
    </div>

    {{ $blocks->links() }}

    {{-- Import modal --}}
    <div class="modal fade" id="gsm-import-block-modal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.global-blocks.import') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header py-2">
                        <h5 class="modal-title">Import Block</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-0">
                            <label>Block Export File (.json)</label>
                            <input type="file" name="file" accept=".json,application/json" required class="form-control-file @error('file') is-invalid @enderror">
                            @error('file') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="submit" class="btn btn-primary btn-sm">Import as Draft</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@push('scripts')
    <script src="{{ asset('js/admin/global-block-library.js') }}?v={{ gsm_version() }}"></script>
@endpush
