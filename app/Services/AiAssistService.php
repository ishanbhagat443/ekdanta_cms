<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Optional AI help for writing copy.
 *
 * Deliberately narrow: it rewrites a piece of text, or drafts the fields of
 * ONE section the user already chose. It never picks section types, never
 * writes to the database, and never returns markup — the answer comes back as
 * plain values that go through exactly the same validation and escaping as
 * anything typed by hand.
 *
 * Disabled unless AI_API_KEY is set, so a self-hosted install sends nothing
 * anywhere by default.
 */
class AiAssistService
{
    public function isEnabled(): bool
    {
        return (bool) config('ai.enabled') && (string) config('ai.key') !== '';
    }

    public function allows(string $feature): bool
    {
        return $this->isEnabled() && (bool) config("ai.features.{$feature}");
    }

    /**
     * Rewrite one piece of copy.
     *
     * @param  string  $tone  one of the presets the editor offers
     */
    public function improve(string $text, string $tone = 'clearer'): string
    {
        $instruction = match ($tone) {
            'shorter' => 'Rewrite it to be shorter and punchier, keeping every fact.',
            'longer' => 'Expand it by one or two sentences, adding no new claims.',
            'friendlier' => 'Rewrite it in a warmer, more conversational voice.',
            'formal' => 'Rewrite it in a more formal, professional voice.',
            'fix' => 'Correct the spelling, grammar and punctuation. Change nothing else.',
            default => 'Rewrite it to be clearer and easier to read.',
        };

        $prompt = <<<PROMPT
            You are helping edit copy on a small business website.

            {$instruction}

            Rules:
            - Reply with the rewritten text and nothing else. No quotes, no preamble, no markdown.
            - Keep it roughly the same length unless asked otherwise.
            - Keep the same language as the original.
            - Do not invent facts, numbers, names or claims.

            Text:
            {$text}
            PROMPT;

        return $this->ask($prompt);
    }

    /**
     * Draft values for one section's fields.
     *
     * @param  array<int, array{key: string, label: string, type: string}>  $fields
     * @return array<string, string>  only the keys that were asked for
     */
    public function draftSection(string $sectionLabel, array $fields, string $brief): array
    {
        // Writing fields only. A model must never be able to put free text
        // into a media id, a menu id or a select — those carry meaning the
        // rest of the CMS relies on.
        $writable = collect($fields)
            ->filter(fn (array $field) => in_array($field['type'] ?? '', ['text', 'textarea', 'richtext'], true));

        $wanted = $writable
            ->map(fn (array $field) => '- '.$field['key'].' ('.$field['label'].')')
            ->implode("\n");

        if ($wanted === '') {
            return [];
        }

        $prompt = <<<PROMPT
            You are writing copy for one section of a small business website.

            Section type: {$sectionLabel}
            What the site is about: {$brief}

            Write a value for each of these fields:
            {$wanted}

            Rules:
            - Reply with ONLY a JSON object mapping each field key to its text.
            - No markdown, no code fences, no commentary.
            - Plain text values. No HTML.
            - Short and concrete. A heading is a few words, not a sentence.
            - Do not invent specific numbers, prices, dates or customer names.
            PROMPT;

        $answer = $this->ask($prompt);

        // Models sometimes wrap JSON in a fence despite being asked not to.
        $answer = trim(preg_replace('/^```(?:json)?|```$/m', '', $answer));

        $decoded = json_decode($answer, true);

        if (! is_array($decoded)) {
            throw new RuntimeException('The assistant did not return usable text. Try again.');
        }

        // Only the keys we asked for, only strings: whatever comes back is
        // untrusted input like any other, and still gets validated downstream.
        $allowed = $writable->pluck('key')->all();

        return collect($decoded)
            ->only($allowed)
            ->filter(fn ($value) => is_string($value) && trim($value) !== '')
            ->map(fn (string $value) => trim($value))
            ->all();
    }

    /* ── Transport ───────────────────────────────────────────────── */

    private function ask(string $prompt): string
    {
        if (! $this->isEnabled()) {
            throw new RuntimeException('The writing assistant is switched off.');
        }

        try {
            $response = Http::timeout((int) config('ai.timeout', 30))
                ->withHeaders($this->headers())
                ->post((string) config('ai.url'), $this->body($prompt));
        } catch (\Throwable $e) {
            Log::warning('AI assist request failed.', ['error' => $e->getMessage()]);

            throw new RuntimeException('Could not reach the writing assistant. Try again in a moment.');
        }

        if (! $response->successful()) {
            // The provider's error text can carry account details; log it,
            // show the editor something safe.
            Log::warning('AI assist returned an error.', [
                'status' => $response->status(),
                'body' => mb_substr($response->body(), 0, 500),
            ]);

            throw new RuntimeException('The writing assistant returned an error ('.$response->status().').');
        }

        $text = $this->extractText($response->json() ?? []);

        if ($text === '') {
            throw new RuntimeException('The assistant sent an empty reply. Try again.');
        }

        return $text;
    }

    /** @return array<string, string> */
    private function headers(): array
    {
        if (config('ai.format') === 'anthropic') {
            return [
                'x-api-key' => (string) config('ai.key'),
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ];
        }

        return [
            'Authorization' => 'Bearer '.config('ai.key'),
            'content-type' => 'application/json',
        ];
    }

    /** @return array<string, mixed> */
    private function body(string $prompt): array
    {
        $shared = [
            'model' => (string) config('ai.model'),
            'max_tokens' => (int) config('ai.max_tokens', 1024),
        ];

        if (config('ai.format') === 'anthropic') {
            return $shared + [
                'messages' => [['role' => 'user', 'content' => $prompt]],
            ];
        }

        return [
            'model' => $shared['model'],
            'max_completion_tokens' => $shared['max_tokens'],
            'messages' => [['role' => 'user', 'content' => $prompt]],
        ];
    }

    /** @param array<string, mixed> $payload */
    private function extractText(array $payload): string
    {
        // Anthropic: content is a list of blocks.
        if (isset($payload['content']) && is_array($payload['content'])) {
            return trim(collect($payload['content'])
                ->filter(fn ($block) => ($block['type'] ?? '') === 'text')
                ->pluck('text')
                ->implode("\n"));
        }

        // OpenAI-compatible.
        return trim((string) ($payload['choices'][0]['message']['content'] ?? ''));
    }
}
