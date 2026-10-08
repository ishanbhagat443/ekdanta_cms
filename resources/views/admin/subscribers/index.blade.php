@extends('admin.layouts.app')

@section('page_title', 'Newsletter Subscribers')

@section('page_actions')
    <a href="{{ route('admin.subscribers.export') }}" class="btn btn-success btn-sm">
        <i class="fas fa-file-export mr-1"></i> Export CSV
    </a>
@stop

@section('page_content')
    <div class="card card-outline card-secondary">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('admin.subscribers.index') }}" class="input-group input-group-sm" style="max-width: 320px;">
                <input type="text" name="search" class="form-control" placeholder="Search email…" value="{{ $search }}">
                <div class="input-group-append">
                    <button class="btn btn-default"><i class="fas fa-search"></i></button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th class="text-center">Status</th>
                        <th>IP</th>
                        <th>Subscribed</th>
                        <th class="text-right" style="width:80px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($subscribers as $subscriber)
                        <tr>
                            <td>{{ $subscriber->email }}</td>
                            <td class="text-center">
                                <span class="badge badge-{{ $subscriber->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($subscriber->status) }}</span>
                            </td>
                            <td>{{ $subscriber->ip }}</td>
                            <td class="text-nowrap">{{ $subscriber->created_at->format('d M Y, H:i') }}</td>
                            <td class="text-right">
                                <form action="{{ route('admin.subscribers.destroy', $subscriber) }}" method="POST" class="d-inline js-confirm-delete">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No subscribers yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($subscribers->hasPages())
            <div class="card-footer">{{ $subscribers->links() }}</div>
        @endif
    </div>
@stop
