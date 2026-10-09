@extends('admin.layouts.app')

@section('page_title', 'Pages')

@section('page_content')
    <div class="row">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title">Pages</h3>
                    <form method="GET" action="{{ route('admin.pages.index') }}" class="form-inline ml-auto">
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search pages…">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-outline-secondary"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Slug</th>
                                <th>Sections</th>
                                <th>Status</th>
                                <th class="text-right" style="width: 160px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pages as $page)
                                <tr>
                                    <td><a href="{{ route('admin.pages.edit', $page) }}">{{ $page->title }}</a></td>
                                    <td><code>/{{ $page->slug }}</code></td>
                                    <td>{{ $page->sections_count }}</td>
                                    <td>
                                        <span class="badge badge-{{ $page->isPublished() ? 'success' : 'secondary' }}">
                                            {{ ucfirst($page->status) }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ $page->previewUrl() }}" target="_blank"
                                           class="btn btn-xs btn-outline-secondary" title="Preview"><i class="fas fa-eye"></i></a>
                                        <a href="{{ route('admin.pages.edit', $page) }}"
                                           class="btn btn-xs btn-outline-primary" title="Build"><i class="fas fa-tools"></i></a>
                                        @if ($page->slug !== 'home')
                                            <form method="POST" action="{{ route('admin.pages.destroy', $page) }}"
                                                  class="d-inline js-confirm-delete">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-outline-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No pages yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($pages->hasPages())
                    <div class="card-footer">{{ $pages->links() }}</div>
                @endif
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <form method="POST" action="{{ route('admin.pages.store') }}">
                @csrf
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Add a page</h3></div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" name="title" id="title" required
                                   class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}">
                            @error('title') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="slug">Web address <small class="text-muted">(blank = made from the title)</small></label>
                            <input type="text" name="slug" id="slug" placeholder="about-us"
                                   class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug') }}">
                            @error('slug') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        {{-- Starting from a finished layout beats starting from
                             nothing: the user edits real sections instead of
                             choosing between 35 block types on an empty page. --}}
                        <label class="mb-1">Start from</label>
                        <div class="gsm-starter-grid">
                            @foreach ($starters as $starterKey => $starter)
                                <label class="gsm-starter" for="starter_{{ $starterKey }}">
                                    <input type="radio" name="starter" id="starter_{{ $starterKey }}"
                                           value="{{ $starterKey }}" @checked($loop->first)>
                                    <span class="gsm-starter__body">
                                        <i class="{{ $starter['icon'] ?? 'fas fa-file' }}" aria-hidden="true"></i>
                                        <span class="gsm-starter__label">{{ $starter['label'] }}</span>
                                        <span class="gsm-starter__hint">{{ $starter['description'] }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Create page</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Whole-site kits. Only worth offering while the site is still small —
         once there is real content this is noise. --}}
    @if ($kits->isNotEmpty())
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-layer-group mr-2"></i>Start a whole site</h3>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    Creates several pages at once and adds them to your menu. Pages you already have
                    are left untouched.
                </p>
                <div class="gsm-kit-grid">
                    @foreach ($kits as $kitKey => $kit)
                        <form method="POST" action="{{ route('admin.pages.kits.apply', $kitKey) }}" class="gsm-kit">
                            @csrf
                            <i class="{{ $kit['icon'] ?? 'fas fa-layer-group' }} gsm-kit__icon" aria-hidden="true"></i>
                            <h4 class="gsm-kit__label">{{ $kit['label'] }}</h4>
                            <p class="gsm-kit__hint">{{ $kit['description'] }}</p>
                            <p class="gsm-kit__pages">
                                {{ collect($kit['pages'])->pluck('title')->implode(' · ') }}
                            </p>
                            <button type="submit" class="btn btn-outline-primary btn-sm">
                                Create these pages
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
@stop

@push('styles')
    <link href="{{ asset('css/admin/builder.css') }}?v={{ gsm_version() }}" rel="stylesheet">
@endpush
