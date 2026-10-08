@extends('admin.layouts.app')

@section('page_title', 'Menus')

@section('page_content')
    <div class="row">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Menus</h3></div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Location</th>
                                <th class="text-center">Items</th>
                                <th class="text-center">Status</th>
                                <th class="text-right" style="width:130px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($menus as $menu)
                                <tr>
                                    <td><a href="{{ route('admin.menus.edit', $menu) }}">{{ $menu->name }}</a></td>
                                    <td>
                                        @if ($menu->location)
                                            <span class="badge badge-info">{{ $menu->locationLabel() }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $menu->items_count }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-{{ $menu->is_active ? 'success' : 'secondary' }}">
                                            {{ $menu->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('admin.menus.edit', $menu) }}"
                                           class="btn btn-xs btn-outline-primary" title="Edit items"><i class="fas fa-pen"></i></a>
                                        <form method="POST" action="{{ route('admin.menus.destroy', $menu) }}"
                                              class="d-inline js-confirm-delete">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-outline-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No menus yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <form method="POST" action="{{ route('admin.menus.store') }}">
                @csrf
                <div class="card card-primary card-outline">
                    <div class="card-header"><h3 class="card-title">New Menu</h3></div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Menu Name</label>
                            <input type="text" name="name" id="name" required placeholder="e.g. Main Menu"
                                   class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
                            @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group mb-0">
                            <label for="location">Suggested Location</label>
                            <select name="location" id="location" class="form-control">
                                <option value="">— None —</option>
                                @foreach ($locations as $key => $label)
                                    <option value="{{ $key }}" @selected(old('location') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Just a label — any menu can still be picked in the Header/Footer/Page builders.</small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Create Menu</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop
