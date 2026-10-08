<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Admin-managed 301/302 redirects, matched on the exact request path
 * before routing logic runs. Lookup map is cached; any Redirect save
 * busts it (model events).
 */
class HandleRedirects
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->isMethod('GET') || $request->is('cms', 'cms/*')) {
            return $next($request);
        }

        $path = Redirect::normalisePath($request->path());
        $hit = Redirect::activeMap()[$path] ?? null;

        if ($hit === null) {
            return $next($request);
        }

        // Count quietly — no model events, cache map stays valid.
        DB::table('redirects')->where('id', $hit['id'])->update([
            'hits' => DB::raw('hits + 1'),
            'last_hit_at' => now(),
        ]);

        return redirect($hit['to'], $hit['code']);
    }
}
