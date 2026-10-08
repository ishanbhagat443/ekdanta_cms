<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Security headers
|--------------------------------------------------------------------------
| Read by App\Http\Middleware\SecurityHeaders (runs on every `web` response).
|
| The Content-Security-Policy ships in **Report-Only** mode: browsers report
| violations but block nothing. Watch the console (or a report endpoint) on
| the admin builders, CKEditor and the public site, add whatever legitimate
| host is missing to `csp_allow` below, then set CSP_ENFORCE=true.
|
| Static files under /uploads are served by the web server, not Laravel, so
| their headers live in public/uploads/.htaccess instead. That file needs
| Apache's mod_headers enabled to take effect.
*/

return [

    'csp_enabled' => env('CSP_ENABLED', true),

    // false = Content-Security-Policy-Report-Only (observe, block nothing).
    // true  = Content-Security-Policy (enforced). Flip only after the
    // report-only run is clean.
    'csp_enforce' => env('CSP_ENFORCE', false),

    // Optional endpoint that receives violation reports.
    'csp_report_uri' => env('CSP_REPORT_URI'),

    /*
    | Extra origins appended per directive. 'self' plus these. Add the CDNs
    | the site actually uses — the defaults cover what ships in the box:
    | Font Awesome (cdnjs) and Google Fonts.
    */
    'csp_allow' => [
        'script' => [],
        'style' => [
            'https://cdnjs.cloudflare.com',
            'https://fonts.googleapis.com',
        ],
        'img' => [],
        'font' => [
            'https://cdnjs.cloudflare.com',
            'https://fonts.gstatic.com',
        ],
        'connect' => [],
        'frame' => [],
        'media' => [],
    ],

];
