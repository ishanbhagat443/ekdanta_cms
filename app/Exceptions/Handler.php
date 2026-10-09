<?php

namespace App\Exceptions;

use App\Models\NotFoundLog;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        //
    }

    /**
     * 404 Manager: log every real not-found hit on the public site (skip
     * /admin — those are internal navigation mistakes, not broken public
     * links worth reviewing) so an admin can convert recurring 404s into
     * redirects. Laravel's base handler never reports HttpException
     * (including 404s) via report()/reportable(), so this is done here in
     * render() instead — it still delegates to the parent for the actual
     * response.
     */
    public function render($request, Throwable $e)
    {
        if ($e instanceof NotFoundHttpException && $request->isMethod('GET') && ! $request->is('cms', 'cms/*')) {
            NotFoundLog::record('/'.ltrim($request->path(), '/'), $request->headers->get('referer'));

            // The page marked as the 404 page (Page settings → role), with a
            // real 404 status. Anything going wrong falls back to the plain
            // error view: a broken 404 page must never become a 500.
            if (! $request->expectsJson() && ($page = \App\Support\PageRoles::page('not_found'))) {
                try {
                    $payload = app(\App\Services\PageRenderService::class)->payloadFor($page->slug);

                    if ($payload !== null) {
                        return response(\App\Http\Controllers\Frontend\PageController::render($payload, false), 404);
                    }
                } catch (Throwable) {
                    // fall through to the default 404 view
                }
            }
        }

        return parent::render($request, $e);
    }
}
