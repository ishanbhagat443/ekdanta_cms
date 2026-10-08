@extends('admin.layouts.app')

@section('page_title', 'Activity & Login Logs')

@section('page_content')
    <div class="card card-outline card-secondary">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('admin.activity.index') }}" class="form-row align-items-end">
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="small mb-0">Event</label>
                    <select name="event" class="form-control form-control-sm">
                        <option value="">All events</option>
                        @foreach ($events as $key => $label)
                            <option value="{{ $key }}" @selected(($filters['event'] ?? null) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="small mb-0">User</label>
                    <select name="user_id" class="form-control form-control-sm">
                        <option value="">All users</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected((int) ($filters['user_id'] ?? 0) === $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="small mb-0">From</label>
                    <input type="date" name="from" class="form-control form-control-sm" value="{{ $filters['from'] ?? '' }}">
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="small mb-0">To</label>
                    <input type="date" name="to" class="form-control form-control-sm" value="{{ $filters['to'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary btn-sm btn-block"><i class="fas fa-filter mr-1"></i>Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>User</th>
                        <th>Description</th>
                        <th>IP</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td>
                                <span class="badge badge-{{ ['login' => 'success', 'login_failed' => 'danger', 'lockout' => 'danger', 'logout' => 'secondary'][$log->event] ?? 'info' }}">
                                    {{ $log->eventLabel() }}
                                </span>
                            </td>
                            <td>{{ $log->user?->name ?? '—' }}</td>
                            <td>{{ $log->description }}</td>
                            <td>{{ $log->ip }}</td>
                            <td class="text-nowrap">{{ $log->created_at->format('d M Y, H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No activity recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($logs->hasPages())
            <div class="card-footer">{{ $logs->links() }}</div>
        @endif
    </div>
@stop
