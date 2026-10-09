<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Global helper functions
|--------------------------------------------------------------------------
| Thin wrappers only — all logic lives in services. Helpers for settings,
| media URLs and device content are added with their modules.
*/

if (! function_exists('gsm_version')) {
    function gsm_version(): string
    {
        return (string) config('gsm.version', '0.1.0');
    }
}

if (! function_exists('gsm_inline_editing')) {
    /**
     * True when this render is the builder's preview AND the viewer may edit
     * pages — the only situation where the front end should ship inline-edit
     * hooks. A public visitor never receives them.
     */
    function gsm_inline_editing(): bool
    {
        $request = request();

        return $request->boolean('preview')
            && ($request->user()?->can('pages.manage') ?? false);
    }
}

if (! function_exists('setting')) {
    /**
     * Get a setting value by "group.key" notation, e.g. setting('business.email').
     */
    function setting(string $key, mixed $default = null): mixed
    {
        return app(\App\Services\SettingsService::class)->get($key, $default);
    }
}

if (! function_exists('setting_image')) {
    /**
     * Public URL of an image setting, e.g. setting_image('branding.logo_dark').
     */
    function setting_image(string $key): ?string
    {
        return app(\App\Services\SettingsService::class)->imageUrl($key);
    }
}

if (! function_exists('theme')) {
    /**
     * Get a Theme Customizer value by "group.key" notation, falling back to
     * config/theme.php's default, e.g. theme('colors.primary').
     */
    function theme(string $key, mixed $default = null): mixed
    {
        return app(\App\Services\ThemeService::class)->get($key, $default);
    }
}

if (! function_exists('theme_css')) {
    /** Cached CSS custom-property block + component rules for <style>. */
    function theme_css(): string
    {
        return style_safe(app(\App\Services\ThemeService::class)->renderCss());
    }
}

if (! function_exists('style_safe')) {
    /**
     * Admin-written CSS for a raw <style> element. "</" is the only thing
     * that can end the element early (</style><script>…), and it never means
     * anything in CSS, so "<\/" keeps the CSS intact and the page script-free.
     */
    function style_safe(?string $css): string
    {
        return str_replace('</', '<\/', (string) $css);
    }
}

/*
|--------------------------------------------------------------------------
| Uploads — single source of truth for where user files are stored/served
|--------------------------------------------------------------------------
| Change mm_upload_root() (or set CMS_UPLOAD_PATH in .env) to relocate EVERY
| upload in the whole project — media library, branding, form attachments,
| theme fonts. The "public" filesystem disk (config/filesystems.php) points
| here, so all Storage::disk('public') calls follow automatically.
*/

if (! function_exists('mm_upload_root')) {
    /**
     * Absolute filesystem directory where uploads are stored.
     *
     * SET YOUR PATH HERE (one place for the whole app). Default is the app's
     * own public/uploads (no symlink needed). On hosting where uploads live
     * beside the app, set CMS_UPLOAD_PATH in .env to the absolute path, e.g.
     * /home/user/uploads — or return dirname(base_path()).'/uploads'; below.
     */
    function mm_upload_root(): string
    {
        // Reads config('gsm.uploads.path') so the value survives config:cache.
        // Set it once via CMS_UPLOAD_PATH in .env (see config/gsm.php).
        return rtrim((string) config('gsm.uploads.path', public_path('uploads')), '/\\');
    }
}

if (! function_exists('mm_upload_url_base')) {
    /** Public base URL that maps to mm_upload_root() (no trailing slash). */
    function mm_upload_url_base(): string
    {
        return rtrim((string) config('gsm.uploads.url', rtrim((string) config('app.url'), '/').'/uploads'), '/');
    }
}

if (! function_exists('mm_upload_path')) {
    /** Absolute filesystem path for an upload sub-path (e.g. 'media/x.jpg'). */
    function mm_upload_path(string $subPath = ''): string
    {
        $base = mm_upload_root();

        return $subPath === '' ? $base : $base.DIRECTORY_SEPARATOR.ltrim($subPath, '/\\');
    }
}

if (! function_exists('mm_upload_url')) {
    /** Public URL for an upload sub-path (e.g. 'media/x.jpg'). */
    function mm_upload_url(string $subPath = ''): string
    {
        $base = mm_upload_url_base();

        return $subPath === '' ? $base : $base.'/'.ltrim($subPath, '/');
    }
}

if (! function_exists('mm_store_upload')) {
    /**
     * Store an uploaded file under mm_upload_root()/{dir} and return its
     * relative path (e.g. 'media/2026/07/abc.jpg'). Goes through the "public"
     * disk, so it honours mm_upload_root() everywhere.
     */
    function mm_store_upload(\Illuminate\Http\UploadedFile $file, string $dir = 'media'): string
    {
        return $file->store(trim($dir, '/'), 'public');
    }
}

if (! function_exists('page_block')) {
    /**
     * Read an editable block field for a template page (product-detail,
     * collection, cart, checkout, login …). Falls back to the config default.
     *
     * Example: page_block('product-detail', 'related', 'heading')
     */
    function page_block(string $slug, string $block, string $field, mixed $default = null): mixed
    {
        return app(\App\Services\PageTemplateService::class)->get($slug, $block, $field, $default);
    }
}

if (! function_exists('page_block_on')) {
    /** True when a template page block is enabled. */
    function page_block_on(string $slug, string $block): bool
    {
        return app(\App\Services\PageTemplateService::class)->enabled($slug, $block);
    }
}
