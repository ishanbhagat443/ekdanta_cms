@extends('admin.layouts.app')

@section('page_title', 'Edit Menu — '.$menu->name)

@section('page_content')
<div id="gsm-menu-editor"
     data-menu-id="{{ $menu->id }}"
     data-items-url="{{ route('admin.menus.items.store', $menu) }}"
     data-item-url="{{ url('cms/menu-items') }}"
     data-tree-url="{{ route('admin.menus.tree', $menu) }}"
     data-reference-url="{{ url('cms/menus/reference') }}"
     data-picker-src="/cms/media?picker=1">

    <div class="row">
        {{-- Tree --}}
        <div class="col-12 col-lg-7">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title"><i class="fas fa-stream mr-2"></i>{{ $menu->name }} — Structure</h3>
                    <div class="ml-auto">
                        <button type="button" class="btn btn-success btn-sm" id="gsm-save-tree">
                            <i class="fas fa-save mr-1"></i> Save Order &amp; Nesting
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted small">
                        Drag to reorder. Drag onto another item's list to nest (3 levels max:
                        item → dropdown, item marked “Mega” → children become mega-menu columns).
                    </p>

                    <ol class="gsm-tree list-unstyled" id="gsm-tree-root">
                        @foreach ($tree as $node)
                            @include('admin.menus.partials.tree-item', ['node' => $node])
                        @endforeach
                    </ol>

                    @if (count($tree) === 0)
                        <p class="text-center text-muted py-3 mb-0">No items yet — add the first one on the right.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Add item + menu settings --}}
        <div class="col-12 col-lg-5">
            <div class="card card-primary card-outline">
                <div class="card-header"><h3 class="card-title">Add Menu Item</h3></div>
                <div class="card-body">
                    @include('admin.menus.partials.item-form', ['formId' => 'gsm-add-item-form', 'item' => null])
                </div>
                <div class="card-footer">
                    <button type="submit" form="gsm-add-item-form" class="btn btn-primary btn-sm btn-block">
                        <i class="fas fa-plus mr-1"></i> Add Item
                    </button>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.menus.update', $menu) }}">
                @csrf
                @method('PUT')
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Menu Settings</h3></div>
                    <div class="card-body">
                        <div class="form-group">
                            <label><small>Name</small></label>
                            <input type="text" name="name" value="{{ $menu->name }}" required
                                   class="form-control form-control-sm @error('name') is-invalid @enderror">
                            @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label><small>Slug</small></label>
                            <input type="text" name="slug" value="{{ $menu->slug }}"
                                   class="form-control form-control-sm">
                        </div>
                        <div class="form-group">
                            <label><small>Suggested Location</small></label>
                            <select name="location" class="form-control form-control-sm">
                                <option value="">— None —</option>
                                @foreach ($locations as $key => $label)
                                    <option value="{{ $key }}" @selected($menu->location === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label><small>Description</small></label>
                            <textarea name="description" rows="2" class="form-control form-control-sm">{{ $menu->description }}</textarea>
                        </div>
                        <div class="custom-control custom-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" class="custom-control-input" id="menu_is_active" name="is_active" value="1"
                                   @checked($menu->is_active)>
                            <label class="custom-control-label" for="menu_is_active"><small>Active</small></label>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-sm">Save Settings</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit item modal — same field partial, prefixed with edit_ --}}
<div class="modal fade" id="gsm-item-modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="gsm-edit-item-form">
                <div class="modal-header py-2">
                    <h5 class="modal-title">Edit Menu Item</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id">
                    @include('admin.menus.partials.item-form', ['formId' => 'gsm-edit-item-form', 'item' => null, 'prefix' => 'edit_'])
                </div>
                <div class="modal-footer py-2">
                    <button type="submit" class="btn btn-primary btn-sm">Save Item</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script src="{{ asset('js/admin/media-picker-field.js') }}?v={{ gsm_version() }}"></script>
    <script>
        window.gsmMenuLinkTypes = @json($linkTypes);
        window.gsmMenuColumnTypes = @json($columnTypes);
    </script>
    <script src="{{ asset('js/admin/menu-editor.js') }}?v={{ gsm_version() }}"></script>
@endpush
