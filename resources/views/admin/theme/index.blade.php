@extends('admin.layouts.app')

@section('page_title', 'Theme Customizer')

@section('page_actions')
    <div class="btn-group">
        <a href="{{ route('admin.theme.export') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-file-export mr-1"></i> Export
        </a>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#gsm-theme-import-modal">
            <i class="fas fa-file-import mr-1"></i> Import
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#gsm-theme-backup-modal">
            <i class="fas fa-save mr-1"></i> Backup
        </button>
        <form action="{{ route('admin.theme.reset') }}" method="POST" class="d-inline"
              onsubmit="return confirm('Reset EVERY theme customization back to defaults? This cannot be undone (create a backup first).');">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-danger">
                <i class="fas fa-undo mr-1"></i> Reset
            </button>
        </form>
    </div>
@stop

@section('page_content')
<div id="gsm-theme-customizer" data-preview-url="{{ $previewUrl }}">
    <div class="row">
        {{-- Tab navigation --}}
        <div class="col-12 col-xl-2 mb-3">
            <div class="card">
                <div class="card-body p-0">
                    <ul class="nav nav-pills flex-column" id="gsm-theme-tabs" role="tablist">
                        @foreach ($groups as $groupKey => $groupSchema)
                            <li class="nav-item">
                                <a class="nav-link rounded-0 {{ $loop->first ? 'active' : '' }}"
                                   id="tab-{{ $groupKey }}" data-toggle="pill" href="#pane-{{ $groupKey }}"
                                   role="tab">
                                    <i class="{{ $groupSchema['icon'] ?? 'fas fa-cog' }} fa-fw mr-1"></i>
                                    {{ $groupSchema['label'] }}
                                </a>
                            </li>
                        @endforeach
                        <li class="nav-item">
                            <a class="nav-link rounded-0" id="tab-backups" data-toggle="pill" href="#pane-backups" role="tab">
                                <i class="fas fa-history fa-fw mr-1"></i> Backups
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Tab panes --}}
        <div class="col-12 col-xl-6 mb-3">
            <div class="tab-content" id="gsm-theme-tab-content">
                @foreach ($groups as $groupKey => $groupSchema)
                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="pane-{{ $groupKey }}" role="tabpanel">
                        @if ($groupKey === 'typography')
                            @include('admin.theme.partials.typography', ['schema' => $groupSchema, 'values' => $values['typography'], 'fonts' => $fonts])
                        @else
                            @include('admin.theme.partials.group-form', ['groupKey' => $groupKey, 'schema' => $groupSchema, 'values' => $values[$groupKey]])
                        @endif
                    </div>
                @endforeach

                <div class="tab-pane fade" id="pane-backups" role="tabpanel">
                    @include('admin.theme.partials.backups', ['presets' => $presets])
                </div>
            </div>
        </div>

        {{-- Live preview --}}
        <div class="col-12 col-xl-4">
            <div class="card" style="position: sticky; top: 60px;">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title"><i class="fas fa-eye mr-2"></i>Live Preview</h3>
                    <div class="ml-auto btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-secondary gsm-preview-size active" data-width="100%" title="Desktop">
                            <i class="fas fa-desktop"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary gsm-preview-size" data-width="768px" title="Tablet">
                            <i class="fas fa-tablet-alt"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary gsm-preview-size" data-width="375px" title="Mobile">
                            <i class="fas fa-mobile-alt"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="gsm-theme-refresh" title="Refresh">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0 bg-light d-flex justify-content-center">
                    <iframe id="gsm-theme-preview" src="{{ $previewUrl }}"
                            style="width:100%; height:80vh; border:0; transition: width .2s ease; background:#fff;"></iframe>
                </div>
                <div class="card-footer py-1">
                    <small class="text-muted">Refreshes automatically after every save.</small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Import modal --}}
<div class="modal fade" id="gsm-theme-import-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.theme.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header py-2">
                    <h5 class="modal-title">Import Theme</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-0">
                        <label>Theme Export File (.json)</label>
                        <input type="file" name="file" accept=".json,application/json" required class="form-control-file @error('file') is-invalid @enderror">
                        @error('file') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        <small class="text-muted">This replaces every current customization. Consider a Backup first.</small>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="submit" class="btn btn-primary btn-sm">Import &amp; Apply</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Backup modal --}}
<div class="modal fade" id="gsm-theme-backup-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.theme.backup') }}">
                @csrf
                <div class="modal-header py-2">
                    <h5 class="modal-title">Backup Current Theme</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-0">
                        <label>Backup Name</label>
                        <input type="text" name="name" required placeholder="e.g. Before Diwali Sale"
                               class="form-control @error('name') is-invalid @enderror">
                        @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="submit" class="btn btn-primary btn-sm">Save Backup</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@push('scripts')
    <script src="{{ asset('js/admin/media-picker-field.js') }}?v={{ gsm_version() }}"></script>
    <script src="{{ asset('js/admin/theme-customizer.js') }}?v={{ gsm_version() }}"></script>
@endpush
