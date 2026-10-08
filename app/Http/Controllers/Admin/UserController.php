<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\UserService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        protected UserRepositoryInterface $users,
        protected RoleRepositoryInterface $roles,
        protected UserService $userService,
    ) {
        $this->authorizeResource(User::class, 'user');
    }

    public function index(Request $request): View
    {
        return view('admin.users.index', [
            'users' => $this->users->paginateWithRoles(15, $request->string('search')->toString() ?: null),
            'search' => $request->string('search')->toString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', ['roles' => $this->roles->allRoles()]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->userService->createUser($request->validated());

        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user,
            'roles' => $this->roles->allRoles(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->userService->updateUser($user, $request->validated());

        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        // Gate::before lets Super Admins bypass policies, so these
        // invariants must be enforced here for everyone.
        abort_if($request->user()->is($user), 403, 'You cannot delete your own account.');
        abort_if($user->hasRole(config('gsm.roles.super_admin')), 403, 'Super Admin accounts cannot be deleted.');

        $this->userService->deleteUser($user);

        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }
}
