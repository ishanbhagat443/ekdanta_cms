@extends('admin.layouts.app')

@section('page_title', 'Edit Block — '.$block->name)

@section('page_actions')
    <div class="btn-group">
        <a href="{{ route('admin.global-blocks.export', $block) }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-file-export mr-1"></i> Export
        </a>
        <a href="{{ route('admin.global-blocks.export', [$block, 'bundle' => 1]) }}" class="btn btn-outline-secondary btn-sm" title="Also carries the images it uses and the collection items, for another site">
            Export with media
        </a>
        <a href="{{ route('admin.global-blocks.index') }}" class="btn btn-default btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Library
        </a>
    </div>
@stop

@section('page_content')
<div id="gsm-global-block-editor"
     data-block-id="{{ $block->id }}"
     data-update-url="{{ route('admin.global-blocks.update', $block) }}"
     data-picker-src="/cms/media?picker=1">

    <div class="row">
        {{-- Content editor --}}
        <div class="col-12 col-xl-7">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <i class="{{ $type->icon() }} mr-2 text-primary"></i>
                    <h3 class="card-title mb-0">{{ $type->label() }}</h3>
                    <span class="badge badge-light border ml-2">v{{ $block->version }}</span>
                    @if ($block->instances->isNotEmpty())
                        <span class="badge badge-info ml-1">used on {{ $block->instances->count() }} page(s)</span>
                    @endif
                </div>
                <div class="card-body">
                    <ul class="nav nav-pills nav-sm mb-3" role="tablist">
                        @foreach (['desktop' => 'fas fa-desktop', 'tablet' => 'fas fa-tablet-alt', 'mobile' => 'fas fa-mobile-alt'] as $device => $deviceIcon)
                            <li class="nav-item">
                                <a class="nav-link py-1 px-3 {{ $device === 'desktop' ? 'active' : '' }}"
                                   data-toggle="pill" href="#tab_block_{{ $device }}" role="tab">
                                    <i class="{{ $deviceIcon }} mr-1"></i>{{ ucfirst($device) }}
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    <div class="tab-content">
                        @foreach (['desktop', 'tablet', 'mobile'] as $device)
                            <div class="tab-pane fade {{ $device === 'desktop' ? 'show active' : '' }}"
                                 id="tab_block_{{ $device }}" role="tabpanel">
                                @if ($device !== 'desktop')
                                    <p class="text-muted small mb-2">
                                        <i class="fas fa-info-circle mr-1"></i> Only device-overridable fields are shown. Empty = inherits desktop.
                                    </p>
                                @endif

                                <form class="gsm-block-form" data-device="{{ $device }}">
                                    @foreach ($type->fields() as $field)
                                        @include('admin.builder.partials.field', [
                                            'field' => $field,
                                            'value' => $block->dataFor($device)[$field['key']] ?? null,
                                            'device' => $device,
                                            'sectionId' => 'gb'.$block->id,
                                            'menus' => [],
                                        ])
                                    @endforeach

                                    @if ($device === 'desktop')
                                        @php $syncedUsage = $block->usage()->where('synced', true); @endphp

                                        <div class="custom-control custom-switch mt-3">
                                            <input type="checkbox" class="custom-control-input" id="propagate_toggle" checked>
                                            <label class="custom-control-label" for="propagate_toggle">
                                                Update everywhere this block is used
                                            </label>
                                        </div>

                                        {{-- Name the consequence here, not in a card further
                                             down: this is the moment the choice is made. --}}
                                        <p class="text-muted small mt-1 mb-0">
                                            @if ($syncedUsage->isEmpty())
                                                Not used on any page yet — saving only changes the block itself.
                                            @else
                                                Saving changes {{ $syncedUsage->count() }}
                                                {{ Str::plural('place', $syncedUsage->count()) }}:
                                                {{ $syncedUsage->pluck('label')->implode(', ') }}.
                                            @endif
                                        </p>
                                    @endif

                                    <button type="submit" class="btn btn-primary btn-sm mt-3">
                                        <i class="fas fa-save mr-1"></i>Save {{ ucfirst($device) }}
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Block details --}}
            <form method="POST" action="{{ route('admin.global-blocks.meta.update', $block) }}">
                @csrf @method('PUT')
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Block Details</h3></div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="col-md-6 form-group">
                                <label><small>Name</small></label>
                                <input type="hidden" name="type" value="{{ $block->type }}">
                                <input type="text" name="name" value="{{ $block->name }}" required class="form-control form-control-sm">
                            </div>
                            <div class="col-md-6 form-group">
                                <label><small>Category</small></label>
                                <select name="category_id" class="form-control form-control-sm">
                                    <option value="">— Uncategorized —</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected($block->category_id === $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label><small>Description</small></label>
                            <textarea name="description" rows="2" class="form-control form-control-sm">{{ $block->description }}</textarea>
                        </div>
                        <div class="form-group mb-0">
                            <label><small>Status</small></label>
                            <select name="status" class="form-control form-control-sm">
                                <option value="active" @selected($block->status === 'active')>Active — placeable on pages</option>
                                <option value="draft" @selected($block->status === 'draft')>Draft — hidden from the picker</option>
                                <option value="archived" @selected($block->status === 'archived')>Archived</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary btn-sm">Save Details</button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Preview + version history --}}
        <div class="col-12 col-xl-5">
            <div class="card" style="position: sticky; top: 60px;">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title mb-0"><i class="fas fa-eye mr-2"></i>Preview</h3>
                    <div class="ml-auto btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary gsm-preview-size active" data-width="100%" title="Desktop"><i class="fas fa-desktop"></i></button>
                        <button type="button" class="btn btn-outline-secondary gsm-preview-size" data-width="768px" title="Tablet"><i class="fas fa-tablet-alt"></i></button>
                        <button type="button" class="btn btn-outline-secondary gsm-preview-size" data-width="375px" title="Mobile"><i class="fas fa-mobile-alt"></i></button>
                        <button type="button" class="btn btn-outline-secondary" id="gsm-block-refresh" title="Refresh"><i class="fas fa-sync-alt"></i></button>
                    </div>
                </div>
                <div class="card-body p-0 bg-light d-flex justify-content-center">
                    <iframe id="gsm-block-preview" src="{{ route('admin.global-blocks.preview', $block) }}"
                            style="width:100%; height:50vh; border:0; transition:width .2s ease; background:#fff;"></iframe>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-history mr-1"></i>Version History</h3></div>
                <div class="card-body p-0 table-responsive" style="max-height: 300px; overflow-y: auto;">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Version</th><th>By</th><th>When</th><th></th></tr></thead>
                        <tbody>
                            @forelse ($block->versions as $version)
                                <tr>
                                    <td>v{{ $version->version }}</td>
                                    <td>{{ $version->author?->name ?? '—' }}</td>
                                    <td class="small text-nowrap">{{ $version->created_at->diffForHumans() }}</td>
                                    <td class="text-right">
                                        <form action="{{ route('admin.global-blocks.versions.restore', [$block, $version]) }}" method="POST"
                                              onsubmit="return confirm('Restore version {{ $version->version }}? This pushes it to every linked page.');">
                                            @csrf
                                            <button class="btn btn-xs btn-outline-primary">Restore</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">No history yet — saved once you edit content.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @php $usage = $block->usage(); @endphp

            @if ($usage->isNotEmpty())
                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">Used on {{ $usage->count() }} {{ Str::plural('place', $usage->count()) }}</h3>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @foreach ($usage as $place)
                                <li class="list-group-item small d-flex justify-content-between align-items-center">
                                    @if ($place['url'])
                                        <a href="{{ $place['url'] }}">{{ $place['label'] }}</a>
                                    @else
                                        <span class="text-muted">{{ $place['label'] }}</span>
                                    @endif

                                    @unless ($place['synced'])
                                        <span class="badge badge-secondary" title="This copy no longer follows the block">
                                            not following
                                        </span>
                                    @endunless
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@stop

@push('scripts')
    <script src="{{ asset('js/admin/media-picker-field.js') }}?v={{ gsm_version() }}"></script>
    <script src="{{ asset('js/admin/global-block-editor.js') }}?v={{ gsm_version() }}"></script>
@endpush
