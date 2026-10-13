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

if (! function_exists('setting_list')) {
    /** Rows of a "list" setting (config/settings.php), decoded. */
    function setting_list(string $key): array
    {
        $decoded = json_decode((string) setting($key, ''), true);

        return is_array($decoded) ? array_values(array_filter($decoded, 'is_array')) : [];
    }
}

if (! function_exists('business_phones')) {
    /**
     * Settings → Business → Phone Numbers, each with ready-made links:
     * label, number (as typed), menu_label, whatsapp (bool), intl (digits
     * with country code), tel (tel:+…), wa (https://wa.me/…). Falls back to
     * the older single Phone / WhatsApp fields when the list is empty.
     *
     * @return list<array<string, mixed>>
     */
    function business_phones(): array
    {
        $rows = setting_list('business.phones');
        $digits = fn (?string $number) => preg_replace('/\D/', '', (string) $number);

        if ($rows === []) {
            $phone = trim((string) setting('business.phone', ''));
            $whatsapp = trim((string) setting('business.whatsapp', ''));

            if ($phone !== '') {
                $rows[] = ['label' => '', 'number' => $phone, 'whatsapp' => $whatsapp === '' || $digits($whatsapp) === $digits($phone)];
            }
            if ($whatsapp !== '' && $digits($whatsapp) !== $digits($phone)) {
                $rows[] = ['label' => 'WhatsApp', 'number' => $whatsapp, 'whatsapp' => true];
            }
        }

        $code = $digits(setting('business.country_code', ''));
        $rows = array_values(array_filter($rows, fn (array $row) => $digits($row['number'] ?? '') !== ''));

        return array_map(function (array $row) use ($digits, $code): array {
            $number = trim((string) ($row['number'] ?? ''));
            $local = $digits($number);
            // "+44 20…" is already international; "90994 17987" gets the country code.
            $intl = str_starts_with($number, '+') || $code === '' || (strlen($local) > 10 && str_starts_with($local, $code))
                ? $local
                : $code.ltrim($local, '0');

            return [
                'label' => (string) ($row['label'] ?? ''),
                'number' => $number,
                'menu_label' => (string) ($row['menu_label'] ?? ''),
                'whatsapp' => (bool) ($row['whatsapp'] ?? false),
                'intl' => $intl,
                'tel' => 'tel:+'.$intl,
                'wa' => 'https://wa.me/'.$intl,
            ];
        }, $rows);
    }
}

if (! function_exists('business_whatsapp')) {
    /** The first phone number marked WhatsApp, or null. */
    function business_whatsapp(): ?array
    {
        return collect(business_phones())->firstWhere('whatsapp', true);
    }
}

if (! function_exists('business_offices')) {
    /**
     * Settings → Business → Offices (the first is the main one). Falls back
     * to the older single Address / Latitude / Longitude / Working Hours.
     *
     * @return list<array<string, string>>
     */
    function business_offices(): array
    {
        $rows = setting_list('business.offices');

        if ($rows === [] && trim((string) setting('business.address', '')) !== '') {
            $rows[] = [
                'name' => '', 'address' => (string) setting('business.address'), 'directions_url' => '',
                'latitude' => (string) setting('business.latitude', ''), 'longitude' => (string) setting('business.longitude', ''),
                'hours' => (string) setting('business.working_hours', ''),
            ];
        }

        $keys = ['name' => '', 'address' => '', 'directions_url' => '', 'latitude' => '', 'longitude' => '', 'hours' => ''];

        return array_map(fn (array $row) => array_map('strval', array_replace($keys, array_intersect_key($row, $keys))), $rows);
    }
}

if (! function_exists('business_vcard')) {
    /**
     * A contact card (vCard 3.0) built from Settings → Business, so "Save our
     * number" always carries the numbers currently on the site.
     */
    function business_vcard(): string
    {
        // RFC 6350 text escaping: backslash, comma, semicolon, newlines.
        $esc = fn (?string $value) => str_replace(["\\", ',', ';', "\r\n", "\n"], ["\\\\", '\,', '\;', '\n', '\n'], trim((string) $value));
        $name = (string) setting('business.company_name', '');
        $lines = ['BEGIN:VCARD', 'VERSION:3.0', 'FN:'.$esc($name), 'ORG:'.$esc($name)];

        foreach (business_phones() as $i => $phone) {
            $item = 'item'.($i + 1);
            $lines[] = "{$item}.TEL;TYPE=".($phone['whatsapp'] ? 'CELL,VOICE' : 'WORK,VOICE').':+'.$phone['intl'];
            if ($phone['label'] !== '') {
                $lines[] = "{$item}.X-ABLabel:".$esc($phone['label']);
            }
        }

        if ($email = setting('business.email')) {
            $lines[] = 'EMAIL;TYPE=WORK:'.$esc($email);
        }
        if ($office = business_offices()[0] ?? null) {
            $lines[] = 'ADR;TYPE=WORK:;;'.$esc($office['address']).';;;;';
        }
        $lines[] = 'URL:'.$esc(url('/'));
        $lines[] = 'END:VCARD';

        return implode("\r\n", $lines)."\r\n";
    }
}

if (! function_exists('site_title')) {
    /**
     * The <title> for a page name: Settings → SEO → title pattern (with
     * {page}), else "Page — Site name", else whichever of the two exists.
     */
    function site_title(?string $page): string
    {
        $page = trim((string) $page);
        $pattern = (string) setting('seo.meta_title', '');

        if ($page !== '' && str_contains($pattern, '{page}')) {
            return str_replace('{page}', $page, $pattern);
        }

        return implode(' — ', array_filter([$page, (string) setting('business.company_name', '')]));
    }
}

if (! function_exists('media_ref')) {
    /**
     * The media file a field points at: a numeric id or a stable key.
     * Keyed files (design assets) load in one query per request; ids are
     * remembered as they are asked for.
     */
    function media_ref(mixed $ref): ?\App\Models\Media
    {
        if ($ref === null || $ref === '') {
            return null;
        }

        $store = request()->attributes;
        $keyed = $store->get('media.keyed');
        if ($keyed === null) {
            $keyed = \App\Models\Media::query()->whereNotNull('key')->get()->keyBy('key')->all();
            $store->set('media.keyed', $keyed);
        }

        if (\App\Models\Media::isKey($ref)) {
            return $keyed[$ref] ?? null;
        }
        if (! is_numeric($ref)) {
            return null;
        }

        $byId = $store->get('media.byId', []);
        if (! array_key_exists((int) $ref, $byId)) {
            $byId[(int) $ref] = \App\Models\Media::query()->find((int) $ref);
            $store->set('media.byId', $byId);
        }

        return $byId[(int) $ref];
    }
}

if (! function_exists('link_href')) {
    /** A link field's value as an href (see App\Support\Link). */
    function link_href(?string $value): string
    {
        return \App\Support\Link::href($value);
    }
}

if (! function_exists('ek_u')) {
    /** Ekdanta views: a length in design px, in the composition's unit (--u). */
    function ek_u(int|float|string $n): string
    {
        return "calc({$n} * var(--u))";
    }
}

if (! function_exists('business_text')) {
    /**
     * Contact details from Settings → Business inside a text: {phone:1}
     * (the first number as written there), {whatsapp} (the WhatsApp number)
     * and {email}. Change a number once and every section shows it.
     */
    function business_text(?string $text): string
    {
        $text = (string) $text;
        if (! str_contains($text, '{')) {
            return $text;
        }

        return preg_replace_callback('/\{(phone:([1-9])|whatsapp|email)\}/', fn (array $m) => match (true) {
            $m[1] === 'whatsapp' => business_whatsapp()['number'] ?? '',
            $m[1] === 'email' => (string) setting('business.email', ''),
            default => business_phones()[(int) $m[2] - 1]['number'] ?? '',
        }, $text);
    }
}

if (! function_exists('business_tokens')) {
    /** business_text() on every text in a section's data (lists included). */
    function business_tokens(array $data): array
    {
        array_walk_recursive($data, function (&$value): void {
            if (is_string($value)) {
                $value = business_text($value);
            }
        });

        return $data;
    }
}


if (! function_exists('lang_parts')) {
    /**
     * Marks Devanagari in rendered HTML as Hindi (WCAG 3.1.2, language of
     * parts), so screen readers switch voice for words typed in any field.
     * Only text between tags changes; tags, attributes and the contents of
     * script, style, textarea and title stay as they are.
     */
    function lang_parts(string $html): string
    {
        if (! preg_match('/[\x{0900}-\x{097F}]/u', $html)) {
            return $html;
        }
        $parts = preg_split('/(<[^>]*>)/u', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        $raw = null; // the raw-text element we are inside
        foreach ($parts as $i => $part) {
            if ($i % 2) {
                if ($raw && preg_match('#^</'.$raw.'\b#i', $part)) {
                    $raw = null;
                } elseif (! $raw && preg_match('#^<(script|style|textarea|title)\b#i', $part, $m)) {
                    $raw = strtolower($m[1]);
                }
            } elseif (! $raw) {
                $parts[$i] = preg_replace('/[\x{0900}-\x{097F}]+(?:[\s\-–]+[\x{0900}-\x{097F}]+)*/u', '<span lang="hi">$0</span>', $part);
            }
        }

        return implode('', $parts);
    }
}
