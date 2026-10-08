@php
    /** @var \Spatie\Permission\Models\Role|null $role */
    $role = $role ?? null;
    $assigned = collect(old('permissions', $assigned ?? []));
@endphp

<div class="form-group">
    <label for="name">Role Name <span class="text-danger">*</span></label>
    <input type="text" name="name" id="name" required
           value="{{ old('name', $role?->name) }}"
           class="form-control @error('name') is-invalid @enderror" style="max-width: 400px;">
    @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
    @error('role') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
</div>

<label class="d-block mt-3">Permissions</label>

@if (count($matrix) === 0)
    <p class="text-muted">No permissions defined yet. Module permissions appear here as modules are built.</p>
@endif

<div class="row">
    @foreach ($matrix as $module => $permissions)
        <div class="col-12 col-md-6 col-xl-4 mb-3">
            <div class="card card-outline card-secondary h-100 mb-0">
                <div class="card-header py-2">
                    <strong class="text-capitalize">{{ str_replace(['-', '_'], ' ', $module) }}</strong>
                    <a href="#" class="small float-right js-toggle-module" data-module="{{ $module }}">toggle all</a>
                </div>
                <div class="card-body py-2">
                    @foreach ($permissions as $permission)
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                   id="perm_{{ $permission->id }}" data-module="{{ $module }}"
                                   class="custom-control-input"
                                   @checked($assigned->contains($permission->name))>
                            <label class="custom-control-label font-weight-normal" for="perm_{{ $permission->id }}">
                                {{ $permission->name }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>

@push('scripts')
    <script>
        document.querySelectorAll('.js-toggle-module').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                var boxes = document.querySelectorAll('input[data-module="' + this.dataset.module + '"]');
                var allChecked = Array.prototype.every.call(boxes, function (b) { return b.checked; });
                boxes.forEach(function (b) { b.checked = !allChecked; });
            });
        });
    </script>
@endpush
