<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>CMS Login | {{ setting('business.company_name', config('gsm.name')) }}</title>

    @if (setting_image('branding.favicon'))
        <link rel="icon" href="{{ setting_image('branding.favicon') }}">
    @endif

    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <style>
        :root { --mm-navy: #1b2a4a; --mm-gold: #c29b54; --mm-ink: #14203a; }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 20px;
            font-family: "Poppins", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: var(--mm-ink);
            background:
                radial-gradient(120% 80% at 100% 0%, rgba(194, 155, 84, 0.16), transparent 60%),
                radial-gradient(120% 90% at 0% 100%, rgba(27, 42, 74, 0.10), transparent 55%),
                #f7f8fa;
        }

        .cms-card {
            width: 100%;
            max-width: 440px;
            padding: 56px 48px 48px;
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: blur(16px) saturate(140%);
            -webkit-backdrop-filter: blur(16px) saturate(140%);
            border: 1px solid rgba(255, 255, 255, 0.7);
            border-radius: 22px;
            box-shadow: 0 20px 50px rgba(20, 32, 58, 0.14);
        }

        .cms-logo { display: block; max-height: 74px; margin: 0 auto 32px; }

        .cms-title {
            margin: 0 0 6px;
            font-size: 1.45rem;
            font-weight: 700;
            text-align: center;
            color: var(--mm-navy);
        }

        .cms-sub {
            margin: 0 0 36px;
            font-size: 0.86rem;
            text-align: center;
            color: #7a8698;
        }

        .cms-field { margin-bottom: 22px; }

        .cms-label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            color: var(--mm-navy);
        }

        .cms-input {
            width: 100%;
            height: 48px;
            padding: 0 16px;
            border: 1px solid #e3e7ee;
            border-radius: 12px;
            background: #fff;
            font-size: 0.95rem;
            color: var(--mm-ink);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .cms-input:focus {
            outline: none;
            border-color: var(--mm-gold);
            box-shadow: 0 0 0 4px rgba(194, 155, 84, 0.16);
        }

        .cms-input.is-invalid { border-color: #b3261e; }

        .cms-error { margin-top: 7px; font-size: 0.78rem; color: #b3261e; }

        .cms-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
            font-size: 0.83rem;
        }

        .cms-remember { display: inline-flex; align-items: center; gap: 8px; color: #5b6577; cursor: pointer; }
        .cms-remember input { width: 16px; height: 16px; accent-color: var(--mm-navy); }

        .cms-btn {
            width: 100%;
            height: 50px;
            border: 0;
            border-radius: 999px;
            background: var(--mm-navy);
            color: #fff;
            font-size: 0.95rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            cursor: pointer;
            transition: background 0.2s ease, color 0.2s ease, transform 0.15s ease;
        }

        .cms-btn:hover { background: var(--mm-gold); color: var(--mm-ink); }
        .cms-btn:active { transform: scale(0.99); }

        .cms-foot {
            margin: 34px 0 0;
            text-align: center;
            font-size: 0.8rem;
            color: #8a94a6;
        }

        .cms-foot a { color: var(--mm-navy); font-weight: 600; text-decoration: none; }
        .cms-foot a:hover { color: var(--mm-gold); }

        @media (max-width: 480px) {
            .cms-card { padding: 40px 26px 34px; border-radius: 18px; }
        }
    </style>
</head>
<body>
    <main class="cms-card">
        @if (setting_image('branding.logo_dark'))
            <img src="{{ setting_image('branding.logo_dark') }}" alt="{{ setting('business.company_name', config('app.name')) }}" class="cms-logo">
        @else
            <h2 class="cms-title" style="font-size:1.7rem;">{{ setting('business.company_name', config('app.name')) }}</h2>
        @endif

        <h1 class="cms-title">Content Management</h1>
        <p class="cms-sub">Sign in to manage your site</p>

        <form method="POST" action="{{ route('cms.login') }}" novalidate>
            @csrf

            <div class="cms-field">
                <label class="cms-label" for="cms-email">Email Address</label>
                <input id="cms-email" type="email" name="email" value="{{ old('email') }}" required autofocus
                       autocomplete="username" class="cms-input @error('email') is-invalid @enderror"
                       placeholder="you@example.com">
                @error('email')<div class="cms-error">{{ $message }}</div>@enderror
            </div>

            <div class="cms-field">
                <label class="cms-label" for="cms-password">Password</label>
                <input id="cms-password" type="password" name="password" required
                       autocomplete="current-password" class="cms-input @error('password') is-invalid @enderror"
                       placeholder="••••••••">
                @error('password')<div class="cms-error">{{ $message }}</div>@enderror
            </div>

            <div class="cms-row">
                <label class="cms-remember">
                    <input type="checkbox" name="remember" @checked(old('remember'))>
                    Remember me
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" style="color:#5b6577;text-decoration:none;">Forgot password?</a>
                @endif
            </div>

            <button type="submit" class="cms-btn">Sign In</button>
        </form>

        <p class="cms-foot">
        </p>
    </main>
</body>
</html>
