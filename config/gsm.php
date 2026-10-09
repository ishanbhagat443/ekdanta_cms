<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Ekdanta CMS — project configuration
|--------------------------------------------------------------------------
| Central place for CMS-wide options. Module-specific keys are added as
| each module is built (see docs/Technical-Architecture-Document.md §15).
*/

return [

    'name' => 'Ekdanta CMS',

    // Cache-buster for admin JS/CSS assets — bump on every front-end change.
    'version' => '0.3.1',

    // Where user uploads live. ONE place for the whole project — media
    // library, branding, form attachments, theme fonts. Set CMS_UPLOAD_PATH
    // (absolute filesystem path) + CMS_UPLOAD_URL (matching public URL) in
    // .env to relocate everything; leave unset to use the app's own
    // public/uploads (no symlink needed).
    'uploads' => [
        'path' => env('CMS_UPLOAD_PATH') ?: public_path('uploads'),
        'url' => env('CMS_UPLOAD_URL') ?: (rtrim((string) env('APP_URL'), '/').'/uploads'),
    ],

    // Media library configuration.
    'media' => [
        // Generated image variant widths (px). Applied to raster images only.
        'variants' => [
            'thumb' => 400,
            'card' => 800,
        ],
        // Hard cap on the STORED ORIGINAL's longest edge (px). Anything larger
        // is downscaled on upload. Full-resolution art (e.g. an 8000px logo)
        // decodes to ~4 bytes/pixel in the browser — a 14 MP image costs ~56 MB
        // of bitmap. iOS Safari enforces a per-page image-memory budget and
        // drops EVERY image on the page to a broken placeholder once it is
        // exceeded, while desktop Chrome renders it fine.
        'max_dimension' => 2400,
        // Lossy encode quality for generated variants (jpg/webp).
        'quality' => 82,
        // Also generate a .webp copy of each raster image.
        'webp' => true,
        'og_size' => [1200, 630],
        'max_upload_kb' => 25600, // 25 MB hard cap per file
        // Allowed extensions per media type (drives validation + type detection).
        'allowed' => [
            'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico'],
            'video' => ['mp4', 'webm', 'ogg'],
            'pdf' => ['pdf'],
            'document' => ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'csv', 'txt', 'zip'],
        ],
        'per_page' => 40,
    ],

    // Cache TTLs (seconds).
    'cache' => [
        'page' => 86400,
        'facets' => 600,
        'content' => 3600,
    ],

    // Frontend defaults (overridable via Settings module once built).
    'defaults' => [
        'products_per_page' => 9,
        'posts_per_page' => 12,
        'recent_posts_count' => 4,
    ],

    // Role names (seeded). Super Admin bypasses all permission checks.
    'roles' => [
        'super_admin' => 'Super Admin',
        'admin' => 'Admin',
        'seo_manager' => 'SEO Manager',
        'content_manager' => 'Content Manager',
        'marketing' => 'Marketing',
        'viewer' => 'Viewer',
    ],
];
