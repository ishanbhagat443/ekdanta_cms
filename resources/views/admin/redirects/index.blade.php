@extends('admin.layouts.app')

@section('page_title', '301 Redirects')

@section('page_content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">All Redirects</h3>
                    <div class="card-tools">
                        <form method="GET" action="{{ route('admin.redirects.index') }}" class="input-group input-group-sm" style="width: 220px;">
                            <input type="text" name="search" class="form-control" placeholder="Search paths…" value="{{ $search }}">
                            <div class="input-group-append">
                                <button class="btn btn-default"><i class="fas fa-search"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>From</th>
                                <th>To</th>
                                <th class="text-center">Code</th>
                                <th class="text-center">Hits</th>
                                <th>Last Hit</th>
                                <th class="text-center">Active</th>
                                <th class="text-right" style="width: 140px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($redirects as $redirect)
                                <tr>
                                    <td><code>{{ $redirect->from_path }}</code></td>
                                    <td><code>{{ \Illuminate\Support\Str::limit($redirect->to_url, 45) }}</code></td>
                                    <td class="text-center">
                                        <span class="badge badge-{{ $redirect->status_code === 301 ? 'primary' : 'info' }}">{{ $redirect->status_code }}</span>
                                    </td>
                                    <td class="text-center">{{ $redirect->hits }}</td>
                                    <td class="text-nowrap">{{ $redirect->last_hit_at?->format('d M Y, H:i') ?? '—' }}</td>
                                    <td class="text-center">
                                        <form action="{{ route('admin.redirects.toggle', $redirect) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button class="btn btn-xs btn-{{ $redirect->is_active ? 'success' : 'secondary' }}"
                                                    title="{{ $redirect->is_active ? 'Disable' : 'Enable' }}">
                                                {{ $redirect->is_active ? 'On' : 'Off' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('admin.redirects.edit', $redirect) }}" class="btn btn-xs btn-primary" title="Edit"><i class="fas fa-pen"></i></a>
                                        <form action="{{ route('admin.redirects.destroy', $redirect) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Delete this redirect?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-xs btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">No redirects yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($redirects->hasPages())
                    <div class="card-footer">{{ $redirects->links() }}</div>
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">{{ $editing ? 'Edit Redirect' : 'New Redirect' }}</h3>
                </div>
                <form action="{{ $editing ? route('admin.redirects.update', $editing) : route('admin.redirects.store') }}" method="POST">
                    @csrf
                    @if ($editing) @method('PUT') @endif
                    <div class="card-body">
                        <div class="form-group">
                            <label for="from_path">From Path</label>
                            <input type="text" name="from_path" id="from_path"
                                   class="form-control @error('from_path') is-invalid @enderror"
                                   value="{{ old('from_path', $editing?->from_path ?? request('from_path')) }}" placeholder="/old-page" required>
                            @error('from_path')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            <small class="text-muted">Path on this site, e.g. <code>/old-collection</code></small>
                        </div>
                        <div class="form-group">
                            <label for="to_url">Redirect To</label>
                            <input type="text" name="to_url" id="to_url"
                                   class="form-control @error('to_url') is-invalid @enderror"
                                   value="{{ old('to_url', $editing?->to_url) }}" placeholder="/new-page or https://…" required>
                            @error('to_url')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label for="status_code">Type</label>
                            <select name="status_code" id="status_code" class="form-control">
                                <option value="301" @selected(old('status_code', $editing?->status_code ?? 301) == 301)>301 — Permanent</option>
                                <option value="302" @selected(old('status_code', $editing?->status_code) == 302)>302 — Temporary</option>
                            </select>
                        </div>
                        <div class="custom-control custom-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                                   @checked(old('is_active', $editing?->is_active ?? true))>
                            <label class="custom-control-label" for="is_active">Active</label>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <button class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> {{ $editing ? 'Update' : 'Create' }}
                        </button>
                        @if ($editing)
                            <a href="{{ route('admin.redirects.index') }}" class="btn btn-default">Cancel</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
