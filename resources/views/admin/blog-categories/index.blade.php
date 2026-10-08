@extends('admin.layouts.app')

@section('page_title', 'Blog Categories & Tags')

@section('page_content')
    <div class="row">
        {{-- Categories --}}
        <div class="col-12 col-lg-7">
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-folder-open mr-1"></i> Categories</h3></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Name</th><th>Slug</th><th>Posts</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                        <tbody>
                            @forelse ($categories as $category)
                                <tr>
                                    <td>
                                        <form method="POST" action="{{ route('admin.blog-categories.update', $category) }}" class="form-inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="text" name="name" value="{{ $category->name }}"
                                                   class="form-control form-control-sm mr-1" style="width: 160px;">
                                            <input type="hidden" name="is_active" value="{{ $category->is_active ? 1 : 0 }}">
                                            <button type="submit" class="btn btn-xs btn-outline-primary" title="Rename"><i class="fas fa-check"></i></button>
                                        </form>
                                    </td>
                                    <td><code>{{ $category->slug }}</code></td>
                                    <td>{{ $category->posts_count }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('admin.blog-categories.update', $category) }}">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="name" value="{{ $category->name }}">
                                            <input type="hidden" name="is_active" value="{{ $category->is_active ? 0 : 1 }}">
                                            <button type="submit" class="btn btn-xs badge badge-{{ $category->is_active ? 'success' : 'secondary' }} border-0">
                                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="text-right">
                                        <form method="POST" action="{{ route('admin.blog-categories.destroy', $category) }}" class="d-inline js-confirm-delete">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-3">No categories yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <form method="POST" action="{{ route('admin.blog-categories.store') }}" class="form-inline">
                        @csrf
                        <input type="text" name="name" required placeholder="New category name"
                               class="form-control form-control-sm mr-2 @error('name') is-invalid @enderror" value="{{ old('name') }}">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-1"></i>Add Category</button>
                        @error('name') <span class="text-danger small ml-2">{{ $message }}</span> @enderror
                    </form>
                </div>
            </div>
        </div>

        {{-- Tags --}}
        <div class="col-12 col-lg-5">
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-tags mr-1"></i> Tags</h3></div>
                <div class="card-body">
                    @forelse ($tags as $tag)
                        <span class="badge badge-light border mb-1 p-2">
                            {{ $tag->name }} <span class="text-muted">({{ $tag->posts_count }})</span>
                            <form method="POST" action="{{ route('admin.blog-tags.destroy', $tag) }}" class="d-inline js-confirm-delete">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-link btn-xs text-danger p-0 ml-1" title="Delete tag">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </span>
                    @empty
                        <span class="text-muted small">Tags are created automatically when you type them on a post.</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@stop
