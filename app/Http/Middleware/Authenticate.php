<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        // CMS pages send staff to the CMS login; the storefront uses its own.
        if ($request->is('cms', 'cms/*')) {
            return route('cms.login');
        }

        // Remember where the shopper was headed so login can return them there.
        $request->session()->put('url.intended', $request->fullUrl());

        return route('cms.login');
    }
}
