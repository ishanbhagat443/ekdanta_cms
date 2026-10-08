@extends('admin.layouts.app')

@section('page_title', '404 Manager')

@section('page_content')
    <p class="text-muted small">
        Broken links visitors actually hit on the live site. Convert any of them into a
        <a href="{{ route('admin.redirects.index') }}">301 Redirect</a> — doing so marks it resolved automatically.
    </p>

    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Path</th>
                        <th>Referer</th>
                        <th class="text-center">Hits</th>
                        <th>Last Hit</th>
                        <th class="text-right" style="width:180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td><code>{{ $log->path }}</code></td>
                            <td class="text-truncate" style="max-width:220px;">{{ $log->referer ?: '—' }}</td>
                            <td class="text-center"><span class="badge badge-danger">{{ $log->hits }}</span></td>
                            <td class="text-nowrap">{{ $log->last_hit_at->format('d M Y, H:i') }}</td>
                            <td class="text-right">
                                <a href="{{ route('admin.redirects.index', ['from_path' => $log->path]) }}"
                                   class="btn btn-xs btn-primary" title="Create redirect">
                                    <i class="fas fa-random"></i> Redirect
                                </a>
                                <form action="{{ route('admin.not-found.dismiss', $log) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-xs btn-outline-secondary" title="Mark resolved"><i class="fas fa-check"></i></button>
                                </form>
                                <form action="{{ route('admin.not-found.destroy', $log) }}" method="POST" class="d-inline js-confirm-delete">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No unresolved 404s — nice and clean.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($logs->hasPages())
            <div class="card-footer">{{ $logs->links() }}</div>
        @endif
    </div>
@stop
