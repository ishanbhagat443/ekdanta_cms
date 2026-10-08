<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Baseline security headers on every response.
 *
 * CSP note: AdminLTE, the builders and CKEditor all rely on inline scripts
 * and inline styles, so 'unsafe-inline' cannot be dropped without rewriting
 * those screens. The policy still earns its keep — it pins where scripts,
 * frames and form posts may come from, and blocks object/base-uri — and it
 * ships in Report-Only until an operator flips `security.csp_enforce` on,
 * so a bad directive can never take the site down silently.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

        // PHP adds this from expose_php=On in php.ini; it advertises the exact
        // patch version to anyone scanning. Strip it at both layers so the
        // header is gone even where php.ini cannot be edited (shared hosting).
        $response->headers->remove('X-Powered-By');
        if (! headers_sent()) {
            header_remove('X-Powered-By');
        }

        if ($csp = $this->contentSecurityPolicy()) {
            $header = config('security.csp_enforce')
                ? 'Content-Security-Policy'
                : 'Content-Security-Policy-Report-Only';

            $response->headers->set($header, $csp);
        }

        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    /** Built from config so a site can add its own CDN without editing code. */
    private function contentSecurityPolicy(): ?string
    {
        if (! config('security.csp_enabled', true)) {
            return null;
        }

        $extra = fn (string $key): string => trim(implode(' ', (array) config("security.csp_allow.{$key}", [])));

        $directives = [
            "default-src 'self'",
            trim("script-src 'self' 'unsafe-inline' 'unsafe-eval' ".$extra('script')),
            trim("style-src 'self' 'unsafe-inline' ".$extra('style')),
            trim("img-src 'self' data: blob: ".$extra('img')),
            trim("font-src 'self' data: ".$extra('font')),
            trim("connect-src 'self' ".$extra('connect')),
            trim("frame-src 'self' ".$extra('frame')),
            trim("media-src 'self' ".$extra('media')),
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
        ];

        if ($reportUri = config('security.csp_report_uri')) {
            $directives[] = 'report-uri '.$reportUri;
        }

        return implode('; ', $directives);
    }
}
