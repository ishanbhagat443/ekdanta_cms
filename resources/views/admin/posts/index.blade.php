@extends('admin.layouts.app')

@section('page_title', 'Blog Posts')

@section('page_actions')
    <a href="{{ route('admin.posts.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus mr-1"></i> New Post
    </a>
@stop

@section('page_content')
    <div class="card">
        <div class="card-header">
            <form method="GET" action="{{ route('admin.posts.index') }}" class="form-inline">
                <div class="input-group input-group-sm mr-2 mb-1">
                    <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search title…">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-outline-secondary"><i class="fas fa-search"></i></button>
                    </div>
                </div>
                <select name="status" class="form-control form-control-sm mb-1" onchange="this.form.submit()">
                    <option value="">All statuses</option>
                    @foreach (['draft' => 'Draft', 'published' => 'Published', 'scheduled' => 'Scheduled'] as $statusValue => $statusLabel)
                        <option value="{{ $statusValue }}" @selected($status === $statusValue)>{{ $statusLabel }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 64px;">Image</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Categories</th>
                        <th>Views</th>
                        <th>Status</th>
                        <th>Publish Date</th>
                        <th class="text-right" style="width: 110px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($posts as $post)
                        <tr>
                            <td>
                                @if ($post->featuredMedia)
                                    <img src="{{ $post->featuredMedia->thumb_url }}" alt="" class="img-thumbnail" style="max-height: 48px;">
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.posts.edit', $post) }}">{{ $post->title }}</a>
                                <div class="text-muted small"><code>{{ $post->slug }}</code></div>
                            </td>
                            <td class="small">{{ $post->displayAuthor() }}</td>
                            <td class="small">
                                @foreach ($post->categories as $category)
                                    <span class="badge badge-info">{{ $category->name }}</span>
                                @endforeach
                            </td>
                            <td class="small">{{ number_format($post->views_count) }}</td>
                            <td>
                                @php
                                    $badge = match ($post->status) {
                                        'published' => 'success', 'scheduled' => 'warning', default => 'secondary',
                                    };
                                @endphp
                                <span class="badge badge-{{ $badge }}">{{ ucfirst($post->status) }}</span>
                            </td>
                            <td class="small">{{ $post->published_at?->format('d M Y, H:i') ?? '—' }}</td>
                            <td class="text-right">
                                @if ($post->isLive())
                                    <a href="{{ route('front.blog.show', $post->slug) }}" target="_blank"
                                       class="btn btn-xs btn-outline-secondary" title="View"><i class="fas fa-eye"></i></a>
                                @endif
                                <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-xs btn-outline-primary"><i class="fas fa-pen"></i></a>
                                <form method="POST" action="{{ route('admin.posts.destroy', $post) }}" class="d-inline js-confirm-delete">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No posts yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($posts->hasPages())
            <div class="card-footer">{{ $posts->links() }}</div>
        @endif
    </div>
@stop
