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
        }

        return parent::render($request, $e);
    }
}
