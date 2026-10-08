<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ChangePasswordRequest;
use App\Http\Requests\Admin\UpdateProfileRequest;
use App\Services\UserService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(protected UserService $userService)
    {
    }

    public function edit(Request $request): View
    {
        return view('admin.profile.edit', ['user' => $request->user()]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $this->userService->updateProfile($request->user(), $request->validated());

        return redirect()->route('admin.profile.edit')->with('success', 'Profile updated.');
    }

    public function updatePassword(ChangePasswordRequest $request): RedirectResponse
    {
        $this->userService->changePassword($request->user(), $request->validated('password'));

        return redirect()->route('admin.profile.edit')->with('success', 'Password changed.');
    }
}
