<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

/**
 * Staff login for the CMS (/cms/login). Only users holding a CMS role may
 * sign in — role-less accounts are rejected.
 */
class CmsLoginController extends Controller
{
    use AuthenticatesUsers;

    /** Where staff land after signing in. */
    protected $redirectTo = '/cms/dashboard';

    public function __construct()
    {
        $this->middleware('guest:web')->except('logout');
    }

    /** Always the CMS guard, never whichever guard happens to be default. */
    protected function guard(): \Illuminate\Contracts\Auth\StatefulGuard
    {
        return \Illuminate\Support\Facades\Auth::guard('web');
    }

    public function showLoginForm(): View
    {
        return view('auth.cms-login');
    }

    /** Reject non-staff accounts before the session is established. */
    protected function authenticated(Request $request, $user): ?RedirectResponse
    {
        if (! $user->isStaff()) {
            $this->guard()->logout();

            throw ValidationException::withMessages([
                'email' => 'This account has no CMS access.',
            ])->redirectTo(route('cms.login'));
        }

        return null;
    }

    /** Sign out of the CMS. */
    public function logout(Request $request): RedirectResponse
    {
        $this->guard()->logout();
        $request->session()->regenerate();

        return redirect()->route('cms.login');
    }
}
