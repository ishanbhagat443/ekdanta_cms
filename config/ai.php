<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| AI writing assistant
|--------------------------------------------------------------------------
| Optional, and off unless an API key is present. With no key the buttons
| never render and no request is ever made — the CMS is fully usable, and a
| self-hosted install sends nothing anywhere by default.
|
| Anthropic is the default provider. Any OpenAI-compatible endpoint works by
| pointing `url` at it and setting `format` to 'openai'.
*/

return [

    'enabled' => (bool) env('AI_API_KEY'),

    'provider' => env('AI_PROVIDER', 'anthropic'),

    'key' => env('AI_API_KEY'),

    'url' => env('AI_API_URL', 'https://api.anthropic.com/v1/messages'),

    // 'anthropic' or 'openai' — how the request body and response are shaped.
    'format' => env('AI_API_FORMAT', 'anthropic'),

    'model' => env('AI_MODEL', 'claude-sonnet-4-5'),

    'max_tokens' => (int) env('AI_MAX_TOKENS', 1024),

    'timeout' => (int) env('AI_TIMEOUT', 30),

    /*
    | What the assistant is allowed to do. Each is a separate switch so an
    | operator can allow rewriting without allowing generation.
    */
    'features' => [
        'improve_text' => (bool) env('AI_IMPROVE_TEXT', true),
        'generate_section' => (bool) env('AI_GENERATE_SECTION', true),
    ],

    /*
    | Per-user rate limit (requests per minute). A runaway loop in the editor
    | should cost pennies, not a bill.
    */
    'rate_limit' => (int) env('AI_RATE_LIMIT', 20),
];
