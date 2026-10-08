@extends('admin.layouts.app')

@section('page_title', 'My Profile')

@section('page_content')
    <div class="row">
        {{-- Profile info --}}
        <div class="col-12 col-lg-6 mb-3">
            <form method="POST" action="{{ route('admin.profile.update') }}">
                @csrf
                @method('PUT')
                <div class="card h-100">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-user mr-1"></i> Profile</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" required
                                   value="{{ old('name', $user->name) }}"
                                   class="form-control @error('name') is-invalid @enderror">
                            @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" required
                                   value="{{ old('email', $user->email) }}"
                                   class="form-control @error('email') is-invalid @enderror">
                            @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group mb-0">
                            <label class="d-block">Role</label>
                            @foreach ($user->roles as $role)
                                <span class="badge badge-info">{{ $role->name }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Update Profile</button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Change password --}}
        <div class="col-12 col-lg-6 mb-3">
            <form method="POST" action="{{ route('admin.profile.password') }}">
                @csrf
                @method('PUT')
                <div class="card h-100">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-key mr-1"></i> Change Password</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="current_password">Current Password <span class="text-danger">*</span></label>
                            <input type="password" name="current_password" id="current_password" required
                                   autocomplete="current-password"
                                   class="form-control @error('current_password') is-invalid @enderror">
                            @error('current_password') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="password">New Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" id="password" required
                                   autocomplete="new-password"
                                   class="form-control @error('password') is-invalid @enderror">
                            @error('password') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group mb-0">
                            <label for="password_confirmation">Confirm New Password <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" id="password_confirmation" required
                                   autocomplete="new-password" class="form-control">
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-key mr-1"></i> Change Password</button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Two-Factor Authentication (schema ready — verification flow not yet built) --}}
        <div class="col-12 col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-shield-alt mr-1"></i> Two-Factor Authentication</h3>
                </div>
                <div class="card-body">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="tfa_toggle" disabled
                               @checked($user->two_factor_confirmed_at)>
                        <label class="custom-control-label" for="tfa_toggle">Enable Two-Factor Authentication</label>
                    </div>
                    <small class="text-muted d-block mt-2">
                        Coming soon — the database and UI are ready; the verification flow (QR code + recovery codes)
                        will be enabled in a future update.
                    </small>
                </div>
            </div>
        </div>
    </div>
@stop
