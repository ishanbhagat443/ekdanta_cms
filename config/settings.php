<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Settings schema
|--------------------------------------------------------------------------
| Single source of truth for the Settings module. Each group renders as a
| tab; each field renders by "type" and validates by "rules". Adding a
| field here is all that is needed — no migration, no view change.
|
| Field types: text, email, url, number, textarea, code, image, checkbox
*/

return [

    'business' => [
        'label' => 'Business Information',
        'icon' => 'fas fa-building',
        'fields' => [
            'company_name' => ['label' => 'Company Name', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:191']],
            'email' => ['label' => 'Email', 'type' => 'email', 'rules' => ['nullable', 'email:rfc,strict', 'max:191']],
            'support_email' => ['label' => 'Support Email', 'type' => 'email', 'rules' => ['nullable', 'email:rfc,strict', 'max:191']],
            'phone' => ['label' => 'Phone', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:30']],
            'whatsapp' => ['label' => 'WhatsApp Number', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:30'], 'hint' => 'With country code, e.g. +91 92742 03144. Used for "Get Price" buttons.'],
            'address' => ['label' => 'Address', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:1000']],
            'google_map' => ['label' => 'Google Map Embed URL', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:2000'], 'hint' => 'Paste the src URL from Google Maps → Share → Embed a map.'],
            'latitude' => ['label' => 'Latitude', 'type' => 'number', 'rules' => ['nullable', 'numeric', 'between:-90,90']],
            'longitude' => ['label' => 'Longitude', 'type' => 'number', 'rules' => ['nullable', 'numeric', 'between:-180,180']],
            'gst' => ['label' => 'GST Number', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:30']],
            'pan' => ['label' => 'PAN Number', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:20']],
            'working_hours' => ['label' => 'Working Hours', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:191'], 'hint' => 'e.g. Mon–Sat, 9:00 AM – 7:00 PM'],
        ],
    ],

    'branding' => [
        'label' => 'Branding',
        'icon' => 'fas fa-palette',
        'fields' => [
            'logo_light' => ['label' => 'Light Logo', 'type' => 'image', 'rules' => ['nullable', 'mimes:jpg,jpeg,png,svg,webp', 'max:2048'], 'hint' => 'Used on dark backgrounds (footer, dark header).'],
            'logo_dark' => ['label' => 'Dark Logo', 'type' => 'image', 'rules' => ['nullable', 'mimes:jpg,jpeg,png,svg,webp', 'max:2048'], 'hint' => 'Used on light backgrounds (main header).'],
            'favicon' => ['label' => 'Favicon', 'type' => 'image', 'rules' => ['nullable', 'mimes:png,ico,svg', 'max:512'], 'hint' => 'PNG/ICO/SVG, square, at least 64×64.'],
            'logo_email' => ['label' => 'Email Logo', 'type' => 'image', 'rules' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'], 'hint' => 'PNG/JPG only — SVG is not supported by email clients.'],
            'logo_login' => ['label' => 'Login Logo', 'type' => 'image', 'rules' => ['nullable', 'mimes:jpg,jpeg,png,svg,webp', 'max:2048'], 'hint' => 'Shown on the admin login screen.'],
            'watermark' => ['label' => 'Watermark', 'type' => 'image', 'rules' => ['nullable', 'mimes:png,svg,webp', 'max:1024'], 'hint' => 'Transparent PNG recommended. Applied to uploaded images.'],
        ],
    ],

    'social' => [
        'label' => 'Social Media',
        'icon' => 'fas fa-share-alt',
        'fields' => [
            'facebook' => ['label' => 'Facebook', 'type' => 'url', 'rules' => ['nullable', 'url', 'max:500']],
            'instagram' => ['label' => 'Instagram', 'type' => 'url', 'rules' => ['nullable', 'url', 'max:500']],
            'tiktok' => ['label' => 'TikTok', 'type' => 'url', 'rules' => ['nullable', 'url', 'max:500']],
            'linkedin' => ['label' => 'LinkedIn', 'type' => 'url', 'rules' => ['nullable', 'url', 'max:500']],
            'twitter' => ['label' => 'Twitter / X', 'type' => 'url', 'rules' => ['nullable', 'url', 'max:500']],
            'youtube' => ['label' => 'YouTube', 'type' => 'url', 'rules' => ['nullable', 'url', 'max:500']],
            'pinterest' => ['label' => 'Pinterest', 'type' => 'url', 'rules' => ['nullable', 'url', 'max:500']],
            'threads' => ['label' => 'Threads', 'type' => 'url', 'rules' => ['nullable', 'url', 'max:500']],
            'telegram' => ['label' => 'Telegram', 'type' => 'url', 'rules' => ['nullable', 'url', 'max:500']],
            'whatsapp_link' => ['label' => 'WhatsApp Link', 'type' => 'url', 'rules' => ['nullable', 'url', 'max:500'], 'hint' => 'e.g. https://wa.me/919974038427'],
        ],
    ],

    'seo' => [
        'label' => 'SEO Defaults',
        'icon' => 'fas fa-search',
        'fields' => [
            'google_analytics' => ['label' => 'Google Analytics ID', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:30'], 'hint' => 'e.g. G-XXXXXXXXXX'],
            'google_tag_manager' => ['label' => 'Google Tag Manager ID', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:30'], 'hint' => 'e.g. GTM-XXXXXXX'],
            'meta_title' => ['label' => 'Default Meta Title', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:191'], 'hint' => 'Used when a page has no title of its own. {page} token is replaced by the page name.'],
            'meta_description' => ['label' => 'Default Meta Description', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:500']],
            'meta_keywords' => ['label' => 'Default Meta Keywords', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:500'], 'hint' => 'Comma separated.'],
            'og_title' => ['label' => 'Default Open Graph Title', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:191']],
            'og_description' => ['label' => 'Default Open Graph Description', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:500']],
            'og_image' => ['label' => 'Default Open Graph Image', 'type' => 'image', 'rules' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:2048'], 'hint' => 'Recommended 1200×630. Used for social sharing previews.'],
            'twitter_handle' => ['label' => 'Twitter / X Handle', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:30', 'regex:/^@[A-Za-z0-9_]+$/'], 'hint' => 'e.g. @yourbrand — rendered as twitter:site.'],
            'schema_json' => ['label' => 'Organization Schema (JSON-LD)', 'type' => 'code', 'rules' => ['nullable', 'string', 'max:10000', 'json'], 'hint' => 'Valid JSON only. Rendered site-wide inside a script tag.'],
            'robots' => ['label' => 'robots.txt Content', 'type' => 'code', 'rules' => ['nullable', 'string', 'max:5000']],
            'canonical' => ['label' => 'Canonical Base URL', 'type' => 'url', 'rules' => ['nullable', 'url', 'max:191'], 'hint' => 'e.g. https://example.com — used to build canonical tags.'],
        ],
    ],

    /*
    |----------------------------------------------------------------
    | WhatsApp — inquiry delivery via the Meta WhatsApp Cloud API
    |----------------------------------------------------------------
    | Every credential is admin-managed here (never hardcoded). When
    | "Enable API sending" is off or credentials are incomplete, inquiries
    | are still saved to the CMS inbox — only the WhatsApp push is skipped.
    */
    'whatsapp' => [
        'label' => 'WhatsApp',
        'icon' => 'fab fa-whatsapp',
        'fields' => [
            'enabled' => ['label' => 'Enable API sending', 'type' => 'checkbox', 'rules' => ['nullable', 'boolean'], 'hint' => 'When on, new inquiries are pushed to WhatsApp via the Meta Cloud API. Inquiries are always saved to the CMS regardless.'],
            'recipient_number' => ['label' => 'Recipient Number (receives inquiries)', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:30'], 'hint' => 'Digits with country code, no +. e.g. 919274203144. Defaults to the Business WhatsApp number.'],
            'phone_number_id' => ['label' => 'Phone Number ID (Meta)', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:60'], 'hint' => 'From Meta → WhatsApp → API Setup.'],
            'business_account_id' => ['label' => 'WhatsApp Business Account ID', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:60']],
            'access_token' => ['label' => 'Access Token', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:1000'], 'hint' => 'Permanent System-User token. Kept server-side only, never exposed to the frontend.'],
            'template_name' => ['label' => 'Template Name', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:100'], 'hint' => 'Approved template to send. Leave blank to send a plain text message (only delivers inside an active 24h session).'],
            'template_language' => ['label' => 'Template Language', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:10'], 'hint' => 'e.g. en or en_US. Defaults to en_US.'],
            'api_version' => ['label' => 'Graph API Version', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:10'], 'hint' => 'e.g. v21.0. Defaults to v21.0.'],
            'webhook_verify_token' => ['label' => 'Webhook Verify Token', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:100'], 'hint' => 'Any secret string you also enter in Meta → Webhooks. Callback URL: /whatsapp/webhook'],
        ],
    ],

    /*
    |----------------------------------------------------------------
    | Custom WhatsApp API — bring-your-own gateway
    |----------------------------------------------------------------
    | Optional alternative to the Meta Cloud API. When an API URL is set
    | here, order/inquiry pushes go through this endpoint instead. The
    | body template supports {{message}}, {{phone}}, {{order_number}}.
    */
    'whatsapp_custom' => [
        'label' => 'Custom WhatsApp API',
        'icon' => 'fas fa-plug',
        'fields' => [
            'api_url' => ['label' => 'API URL', 'type' => 'url', 'rules' => ['nullable', 'url', 'max:500'], 'hint' => 'Full endpoint URL. Leave blank to use the Meta Cloud API settings instead.'],
            'method' => ['label' => 'HTTP Method (POST or GET)', 'type' => 'text', 'rules' => ['nullable', 'in:POST,GET,post,get'], 'hint' => 'POST (default) or GET.'],
            'headers' => ['label' => 'Headers (JSON object)', 'type' => 'code', 'lang' => 'json', 'rules' => ['nullable', 'string', 'max:4000', 'json'], 'hint' => 'e.g. {"Authorization": "Bearer XXX", "Content-Type": "application/json"}'],
            'body_template' => ['label' => 'Request Body Template (JSON)', 'type' => 'code', 'lang' => 'json', 'rules' => ['nullable', 'string', 'max:8000'], 'hint' => 'Placeholders: {{message}}, {{phone}}, {{order_number}}. e.g. {"to": "{{phone}}", "text": "{{message}}"}'],
            'success_contains' => ['label' => 'Success Response Contains', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:100'], 'hint' => 'Substring that marks a successful response (blank = any HTTP 2xx).'],
            'timeout' => ['label' => 'Timeout (seconds)', 'type' => 'number', 'rules' => ['nullable', 'integer', 'between:3,60']],
        ],
    ],

];
