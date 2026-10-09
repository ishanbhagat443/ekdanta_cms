@extends('admin.layouts.app')

@section('page_title', 'Motion Library')

@section('page_actions')
    <a href="{{ route('admin.motion-presets.export') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-file-export mr-1" aria-hidden="true"></i> Export all
    </a>
    <a href="{{ route('admin.motion-presets.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus mr-1" aria-hidden="true"></i> New preset
    </a>
@stop

@section('page_content')
<p class="text-muted">
    Named motion that any section's <strong>Motion</strong> tab can use. Change a preset here and every
    track using it changes too. Built-in presets can be edited but not deleted; duplicate one to start your own.
</p>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Kind</th>
                    <th>Default trigger</th>
                    <th>Timing</th>
                    <th class="text-right" style="width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($presets as $preset)
                    <tr>
                        <td>
                            <strong>{{ $preset->name }}</strong>
                            @if ($preset->is_system) <span class="badge badge-light border ml-1">Built-in</span> @endif
                            @if ($preset->description)
                                <br><small class="text-muted">{{ Str::limit($preset->description, 90) }}</small>
                            @endif
                        </td>
                        <td><code>{{ $preset->slug }}</code></td>
                        <td>{{ $preset->kind === 'css' ? 'CSS' : 'Anime.js' }}</td>
                        <td>{{ \App\Support\SectionMotion::TRIGGERS[$preset->trigger] ?? $preset->trigger }}</td>
                        <td><small>{{ $preset->trigger === 'scrub' ? 'Scroll range' : $preset->duration.' ms' }} · {{ $preset->ease }}</small></td>
                        <td class="text-right text-nowrap">
                            <a href="{{ route('admin.motion-presets.edit', $preset) }}" class="btn btn-xs btn-outline-primary" aria-label="Edit {{ $preset->name }}">
                                <i class="fas fa-pen" aria-hidden="true"></i>
                            </a>
                            <form action="{{ route('admin.motion-presets.duplicate', $preset) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-xs btn-outline-secondary" aria-label="Duplicate {{ $preset->name }}"><i class="fas fa-copy" aria-hidden="true"></i></button>
                            </form>
                            @unless ($preset->is_system)
                                <form action="{{ route('admin.motion-presets.destroy', $preset) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this preset? Tracks that use it stop moving until they get another.');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-xs btn-outline-danger" aria-label="Delete {{ $preset->name }}"><i class="fas fa-trash" aria-hidden="true"></i></button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No presets yet: create the first one.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">Import presets</h3></div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.motion-presets.import') }}" enctype="multipart/form-data" class="form-inline" style="gap: .5rem;">
            @csrf
            <label for="motion-import" class="mr-2">A file exported from a Motion Library</label>
            <input type="file" name="file" id="motion-import" accept=".json,application/json" class="form-control-file" required>
            <button class="btn btn-outline-primary btn-sm">Import</button>
        </form>
        @error('file') <div class="text-danger small mt-2" role="alert">{{ $message }}</div> @enderror
        <p class="text-muted small mb-0 mt-2">New presets are added and your own presets with the same slug are updated. Built-in presets are never overwritten.</p>
    </div>
</div>
@stop
