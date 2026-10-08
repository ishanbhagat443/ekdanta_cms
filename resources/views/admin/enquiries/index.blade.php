@extends('admin.layouts.app')

@section('page_title', 'Enquiries')

@section('page_actions')
    <a href="{{ route('admin.enquiries.export', request()->query()) }}" class="btn btn-success btn-sm">
        <i class="fas fa-file-excel mr-1"></i> Export Excel (CSV)
    </a>
@stop

@section('page_content')
    {{-- Per-source counters --}}
    <div class="row">
        @foreach ($sources as $key => $label)
            @php $stat = $counts->get($key); @endphp
            <div class="col-6 col-md">
                <a href="{{ route('admin.enquiries.index', ['source' => $key]) }}" class="text-decoration-none">
                    <div class="info-box {{ ($filters['source'] ?? null) === $key ? 'bg-primary' : '' }}">
                        <div class="info-box-content px-3 py-2">
                            <span class="info-box-text">{{ $label }}</span>
                            <span class="info-box-number">
                                {{ $stat->total ?? 0 }}
                                @if ($stat && $stat->new_count)
                                    <small class="badge badge-danger ml-1">{{ $stat->new_count }} new</small>
                                @endif
                            </span>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="card card-outline card-secondary">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('admin.enquiries.index') }}" class="form-row align-items-end">
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="small mb-0">Search</label>
                    <input type="text" name="q" class="form-control form-control-sm" placeholder="Name, email, phone, message…"
                           value="{{ $filters['q'] ?? '' }}">
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="small mb-0">Source</label>
                    <select name="source" class="form-control form-control-sm">
                        <option value="">All sources</option>
                        @foreach ($sources as $key => $label)
                            <option value="{{ $key }}" @selected(($filters['source'] ?? null) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="small mb-0">Status</label>
                    <select name="status" class="form-control form-control-sm">
                        <option value="">All statuses</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? null) === $status)>{{ ucfirst($status) }}</option>
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
                <div class="col-md-1">
                    <button class="btn btn-primary btn-sm btn-block"><i class="fas fa-filter"></i></button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Source</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Message</th>
                        <th>Date</th>
                        <th class="text-center">Status</th>
                        <th class="text-right" style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($enquiries as $enquiry)
                        <tr class="{{ $enquiry->status === 'new' ? 'font-weight-bold' : '' }}">
                            <td>{{ $enquiry->id }}</td>
                            <td><span class="badge badge-info">{{ $enquiry->sourceLabel() }}</span></td>
                            <td>{{ $enquiry->name }}</td>
                            <td>{{ $enquiry->email }}</td>
                            <td>{{ $enquiry->phone }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($enquiry->message, 50) }}</td>
                            <td class="text-nowrap">{{ $enquiry->created_at->format('d M Y, H:i') }}</td>
                            <td class="text-center">
                                <span class="badge badge-{{ ['new' => 'danger', 'read' => 'warning', 'replied' => 'success', 'closed' => 'secondary'][$enquiry->status] ?? 'secondary' }}">
                                    {{ ucfirst($enquiry->status) }}
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.enquiries.show', $enquiry) }}" class="btn btn-xs btn-primary" title="View"><i class="fas fa-eye"></i></a>
                                <form action="{{ route('admin.enquiries.destroy', $enquiry) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this enquiry?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">No enquiries match.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($enquiries->hasPages())
            <div class="card-footer">{{ $enquiries->links() }}</div>
        @endif
    </div>
@stop
