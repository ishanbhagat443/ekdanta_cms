{{--
    Standalone error page. Deliberately self-contained: no @vite, no theme
    CSS, no setting()/theme() calls. A 500 can happen precisely because the
    database, cache or theme layer is broken — an error page that queries
    them would throw while rendering and fall back to Laravel's own trace.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('code') — @yield('title')</title>
    <style>
        :root { color-scheme: light dark; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.25rem;
            background: #f6f7f9;
            color: #111827;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
        }
        .box { max-width: 34rem; width: 100%; text-align: center; }
        .code {
            font-size: clamp(3.5rem, 12vw, 6rem);
            font-weight: 800;
            letter-spacing: -0.03em;
            margin: 0;
            color: #4b5563;
            line-height: 1;
        }
        h1 { font-size: clamp(1.25rem, 4vw, 1.6rem); margin: 0.75rem 0 0.5rem; }
        p { margin: 0 0 1.75rem; color: #5b6577; }
        .actions { display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap; }
        a.btn {
            display: inline-block;
            padding: 0.7rem 1.4rem;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            background: #111827;
            color: #fff;
            border: 1px solid #111827;
        }
        a.btn--ghost { background: transparent; color: #111827; }
        a.btn:hover { opacity: 0.9; }
        @media (prefers-color-scheme: dark) {
            body { background: #12161d; color: #e8eaed; }
            p { color: #9aa4b2; }
            a.btn { background: #4b5563; border-color: #4b5563; color: #12161d; }
            a.btn--ghost { background: transparent; color: #e8eaed; border-color: #3a4150; }
        }
    </style>
</head>
<body>
    <main class="box">
        <p class="code">@yield('code')</p>
        <h1>@yield('title')</h1>
        <p>@yield('message')</p>
        <div class="actions">
            <a class="btn" href="{{ url('/') }}">Back to home</a>
            @hasSection('secondary')@yield('secondary')@endif
        </div>
    </main>
</body>
</html>
