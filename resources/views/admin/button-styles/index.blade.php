@extends('admin.layouts.app')

@section('page_title', 'Button Builder')

@section('page_actions')
    <a href="{{ route('admin.button-styles.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus mr-1"></i> New Button Style
    </a>
@stop

@section('page_content')
{{-- Real compiled CSS so previews are exactly what the frontend renders. --}}
<style>
    {!! $styles->map(fn ($s) => $service->styleCss($s))->implode('') !!}
</style>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th style="width:240px;">Preview</th>
                    <th>Name</th>
                    <th>CSS Class</th>
                    <th>Background</th>
                    <th>Status</th>
                    <th class="text-right" style="width:170px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($styles as $style)
                    <tr>
                        <td class="py-3">
                            <span class="{{ $style->cssClass() }}">
                                @if ($icon = $style->get('icon.left'))<i class="{{ $icon }}"></i>@endif
                                <span>Button Text</span>
                                @if ($icon = $style->get('icon.right'))<i class="{{ $icon }}"></i>@endif
                            </span>
                        </td>
                        <td>
                            <strong>{{ $style->name }}</strong>
                            @if ($style->description)
                                <br><small class="text-muted">{{ Str::limit($style->description, 60) }}</small>
                            @endif
                        </td>
                        <td><code>.{{ $style->cssClass() }}</code></td>
                        <td>{{ ucfirst((string) $style->get('background.mode', '—')) }}</td>
                        <td>
                            <span class="badge badge-{{ $style->is_active ? 'success' : 'secondary' }}">
                                {{ $style->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.button-styles.edit', $style) }}" class="btn btn-xs btn-outline-primary"><i class="fas fa-pen"></i></a>
                            <form action="{{ route('admin.button-styles.duplicate', $style) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-xs btn-outline-secondary" title="Duplicate"><i class="fas fa-clone"></i></button>
                            </form>
                            <form action="{{ route('admin.button-styles.destroy', $style) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete this button style? Buttons using it fall back to the theme default.');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No button styles yet — create the first one.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@stop
