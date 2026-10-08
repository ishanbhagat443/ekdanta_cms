@extends('admin.layouts.app')

@section('page_title', 'Users')

@section('page_content')
    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center">
            <h3 class="card-title mb-2 mb-md-0">Users</h3>
            <div class="ml-auto d-flex">
                <form method="GET" action="{{ route('admin.users.index') }}" class="form-inline mr-2">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" value="{{ $search }}" class="form-control"
                               placeholder="Search name or email…">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-outline-secondary"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                </form>
                @can('users.create')
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i> New User
                    </a>
                @endcan
            </div>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th class="text-right" style="width: 130px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @foreach ($user->roles as $role)
                                    <span class="badge badge-info">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td>
                                <span class="badge badge-{{ $user->is_active ? 'success' : 'secondary' }}">
                                    {{ $user->is_active ? 'Active' : 'Disabled' }}
                                </span>
                            </td>
                            <td class="text-right">
                                @can('update', $user)
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-xs btn-outline-primary"
                                       title="Edit"><i class="fas fa-pen"></i></a>
                                @endcan
                                @can('delete', $user)
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                          class="d-inline js-confirm-delete">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="card-footer">{{ $users->links() }}</div>
        @endif
    </div>
@stop
