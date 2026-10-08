@extends('admin.layouts.app')

@section('page_title', 'Roles & Permissions')

@section('page_content')
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title">Roles</h3>
            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm ml-auto">
                <i class="fas fa-plus mr-1"></i> New Role
            </a>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th>Role</th>
                        <th>Users</th>
                        <th>Permissions</th>
                        <th class="text-right" style="width: 130px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($roles as $role)
                        @php $isSuper = $role->name === config('gsm.roles.super_admin'); @endphp
                        <tr>
                            <td>
                                {{ $role->name }}
                                @if ($isSuper)
                                    <span class="badge badge-warning ml-1" title="Bypasses all permission checks">
                                        <i class="fas fa-crown"></i> full access
                                    </span>
                                @endif
                            </td>
                            <td>{{ $role->users_count }}</td>
                            <td>{{ $isSuper ? 'All' : $role->permissions_count }}</td>
                            <td class="text-right">
                                @unless ($isSuper)
                                    <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-xs btn-outline-primary"
                                       title="Edit"><i class="fas fa-pen"></i></a>
                                    @if ($role->users_count === 0)
                                        <form method="POST" action="{{ route('admin.roles.destroy', $role) }}"
                                              class="d-inline js-confirm-delete">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-outline-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                @endunless
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($roles->hasPages())
            <div class="card-footer">{{ $roles->links() }}</div>
        @endif
    </div>
@stop
