<div class="card">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-history mr-1"></i> Saved Backups</h3></div>
    <div class="card-body table-responsive p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Name</th><th>Saved</th><th class="text-right" style="width:140px;">Actions</th></tr></thead>
            <tbody>
                @forelse ($presets as $preset)
                    <tr>
                        <td>
                            {{ $preset->name }}
                            @if ($preset->is_system)<span class="badge badge-secondary ml-1">Factory Default</span>@endif
                        </td>
                        <td class="text-nowrap">{{ $preset->created_at->format('d M Y, H:i') }}</td>
                        <td class="text-right">
                            <form method="POST" action="{{ route('admin.theme.backup.restore', $preset) }}" class="d-inline"
                                  onsubmit="return confirm('Restore this backup? Current unsaved customizations will be replaced.');">
                                @csrf
                                <button class="btn btn-xs btn-outline-primary" title="Restore"><i class="fas fa-undo"></i></button>
                            </form>
                            @unless ($preset->is_system)
                                <form method="POST" action="{{ route('admin.theme.backup.destroy', $preset) }}" class="d-inline js-confirm-delete">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">No backups yet — use the "Backup" button above.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
