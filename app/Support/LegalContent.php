<?php

declare(strict_types=1);

namespace App\Support;

/**
 * The legal pages' words (docs/ekdanta-content/legal/<name>.json) as the data
 * of the four Ekdanta legal sections: the section types' defaults (Privacy)
 * and what `ekdanta:seed` puts on the Privacy and Terms pages. Every
 * "[…]" placeholder comes through exactly.
 *
 * Body blocks are rows of [type, text]: a heading starts a numbered part;
 * a list has one item per line; label/value rows are "label | value".
 */
final class LegalContent
{
    private static array $cache = [];

    /** @return array<string, array<string, mixed>> section type => desktop data */
    public static function sections(string $name, string $prefix = 'l'): array
    {
        $d = self::$cache[$name] ??= (array) json_decode((string) file_get_contents(base_path("docs/ekdanta-content/legal/{$name}.json")), true);
        $theme = (array) ($d['theme'] ?? []);
        $date = preg_match('/Last updated (.+?) ·/u', (string) ($d['meta'] ?? ''), $m) ? $m[1] : '[Add date]';
        $lightShort = strtoupper((string) ($theme['short_fg'] ?? '')) !== '#FFFFFF';

        $blocks = [];
        foreach ((array) ($d['sections'] ?? []) as [$heading, $parts]) {
            $blocks[] = ['type' => 'heading', 'text' => $heading];
            foreach ($parts as [$type, $content]) {
                $blocks[] = match ($type) {
                    'ul' => ['type' => 'list', 'text' => implode("\n", $content)],
                    'kv' => ['type' => 'labels', 'text' => implode("\n", array_map(fn ($row) => $row[0].' | '.$row[1], $content))],
                    'note' => ['type' => 'note', 'text' => $content],
                    default => ['type' => 'paragraph', 'text' => $content],
                };
            }
        }

        return [
            'ekdanta-legal-hero' => [
                'eyebrow' => $d['eyebrow'], 'heading_1' => $d['h1'][0], 'heading_2' => $d['h1'][1], 'text' => $d['sub'],
                'date' => $date, 'meta' => 'Last updated {date} · {minutes} min read',
                'button_1_label' => $d['btn1'], 'button_1_link' => "#{$prefix}-short",
                'button_2_label' => $d['btn2'], 'button_2_link' => $d['btn2_href'],
                'chips' => implode("\n", $d['chips']), 'visual' => $prefix === 't' ? 'terms' : 'privacy',
                'bg' => $theme['hero_bg'], 'fg' => $theme['hero_fg'], 'accent' => $theme['hero_acc'], 'sub' => $theme['hero_sub'],
                'anchor' => "{$prefix}-top",
            ],
            'ekdanta-legal-short' => [
                'eyebrow' => 'THE SHORT VERSION', 'title' => $d['short_title'], 'text' => $d['short_sub'],
                'cards' => array_map(fn ($c) => ['title' => $c[0], 'text' => $c[1], 'icon' => $c[2]], $d['cards']),
                'bg' => $theme['short_bg'], 'fg' => $theme['short_fg'],
                // Small text on a light card takes the approved shades (#1B8141, #56605A).
                'accent' => $lightShort ? '#1B8141' : '#8FE3AE', 'muted' => $lightShort ? '#4A564F' : '#BFD2C6',
                'card_bg' => $lightShort ? '#F4F6F1' : '#1E2823',
                'anchor' => "{$prefix}-short",
            ],
            'ekdanta-legal-body' => [
                'blocks' => $blocks, 'toc_label' => 'ON THIS PAGE', 'end_note' => "Last updated {$date}",
                'ask_before' => 'Questions?', 'ask_label' => '{email}', 'ask_link' => 'email:',
                'anchor' => "{$prefix}-full",
            ],
            'ekdanta-legal-questions' => [
                'eyebrow' => 'STILL HAVE QUESTIONS?', 'title' => $d['ask_title'], 'text' => $d['ask_sub'],
                'rows' => array_map(fn ($r) => ['label' => $r[0], 'value' => $r[1]], $d['ask_rows']),
                'button_1_label' => $d['ask_btn1'][0], 'button_1_link' => $d['ask_btn1'][1],
                'button_2_label' => $d['ask_btn2'][0], 'button_2_link' => $d['ask_btn2'][1],
                'bg' => $theme['ask_bg'],
                'anchor' => "{$prefix}-ask",
            ],
        ];
    }

    /** Words in a body's blocks, for the reading time (200 a minute, as articles). */
    public static function minutes(array $blocks): int
    {
        $text = implode(' ', array_map(fn ($b) => str_replace('|', ' ', (string) ($b['text'] ?? '')), $blocks));

        return max(1, (int) ceil(str_word_count($text) / 200));
    }
}
