<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Services\RoleService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(
        protected RoleRepositoryInterface $roles,
        protected RoleService $roleService,
    ) {
        $this->middleware('can:roles.manage');
    }

    public function index(): View
    {
        return view('admin.roles.index', [
            'roles' => $this->roles->paginateWithCounts(),
        ]);
    }

    public function create(): View
    {
        return view('admin.roles.create', [
            'matrix' => $this->roleService->permissionMatrix(),
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $this->roleService->createRole(
            $request->validated('name'),
            $request->validated('permissions', [])
        );

        return redirect()->route('admin.roles.index')->with('success', 'Role created.');
    }

    public function edit(Role $role): View
    {
        return view('admin.roles.edit', [
            'role' => $role,
            'matrix' => $this->roleService->permissionMatrix(),
            'assigned' => $role->permissions->pluck('name')->all(),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $this->roleService->updateRole(
            $role,
            $request->validated('name'),
            $request->validated('permissions', [])
        );

        return redirect()->route('admin.roles.index')->with('success', 'Role updated.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->roleService->deleteRole($role);

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted.');
    }
}
