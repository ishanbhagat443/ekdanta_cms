@extends('admin.layouts.app')

@section('page_title', 'Gradient Library')

@section('page_actions')
    <a href="{{ route('admin.gradients.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus mr-1"></i> New Gradient
    </a>
@stop

@section('page_content')
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="width:180px;">Preview</th>
                    <th>Name</th>
                    <th>CSS Variable</th>
                    <th>Type</th>
                    <th>Stops</th>
                    <th>Status</th>
                    <th class="text-right" style="width:130px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($gradients as $gradient)
                    <tr>
                        <td>
                            <div style="height:34px; border-radius:6px; background-image: {{ $gradient->toCss() }};"></div>
                        </td>
                        <td>
                            <strong>{{ $gradient->name }}</strong>
                            @if ($gradient->description)
                                <br><small class="text-muted">{{ Str::limit($gradient->description, 60) }}</small>
                            @endif
                        </td>
                        <td><code>var({{ $gradient->cssVar() }})</code></td>
                        <td>{{ ucfirst($gradient->type) }}</td>
                        <td>{{ count($gradient->stops ?? []) }}</td>
                        <td>
                            <span class="badge badge-{{ $gradient->is_active ? 'success' : 'secondary' }}">
                                {{ $gradient->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.gradients.edit', $gradient) }}" class="btn btn-xs btn-outline-primary">
                                <i class="fas fa-pen"></i>
                            </a>
                            <form action="{{ route('admin.gradients.destroy', $gradient) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete this gradient? Surfaces using it fall back to their default.');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No gradients yet — create the first one.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@stop
