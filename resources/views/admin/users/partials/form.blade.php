@php /** @var \App\Models\User|null $user */ $user = $user ?? null; @endphp

<div class="row">
    <div class="col-12 col-lg-6">
        <div class="form-group">
            <label for="name">Name <span class="text-danger">*</span></label>
            <input type="text" name="name" id="name" required
                   value="{{ old('name', $user?->name) }}"
                   class="form-control @error('name') is-invalid @enderror">
            @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="form-group">
            <label for="email">Email <span class="text-danger">*</span></label>
            <input type="email" name="email" id="email" required
                   value="{{ old('email', $user?->email) }}"
                   class="form-control @error('email') is-invalid @enderror">
            @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="form-group">
            <label for="password">Password {!! $user ? '<small class="text-muted">(leave blank to keep current)</small>' : '<span class="text-danger">*</span>' !!}</label>
            <input type="password" name="password" id="password" autocomplete="new-password"
                   @unless($user) required @endunless
                   class="form-control @error('password') is-invalid @enderror">
            @error('password') <span class="invalid-feedback">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="form-group">
            <label for="password_confirmation">Confirm Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation"
                   autocomplete="new-password" @unless($user) required @endunless class="form-control">
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="form-group">
            <label for="role">Role <span class="text-danger">*</span></label>
            <select name="role" id="role" required class="form-control @error('role') is-invalid @enderror">
                <option value="">— Select role —</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}"
                        @selected(old('role', $user?->roles->first()?->name) === $role->name)>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
            @error('role') <span class="invalid-feedback">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="form-group">
            <label class="d-block">Status</label>
            <div class="custom-control custom-switch mt-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="is_active" value="1" class="custom-control-input"
                       @checked((bool) old('is_active', $user?->is_active ?? true))>
                <label class="custom-control-label" for="is_active">Active</label>
            </div>
        </div>
    </div>
</div>
