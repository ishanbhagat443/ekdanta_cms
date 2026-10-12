<?php

declare(strict_types=1);

namespace App\Support;

use DOMDocument;
use DOMElement;

/**
 * A blog article's body, ready for Ekdanta · Article Body: every H2 gets an
 * id and a contents entry (its data-toc text, else its own), and each block
 * is marked to rise into view (data-m="ab-rise"; the items of a compare,
 * reel-ideas or checklist block one by one). Scripts, inline event handlers
 * and javascript: links are dropped: custom code needs developer mode.
 *
 * The in-article blocks (admin: Insert block) are the views in
 * resources/views/frontend/article-blocks.
 */
final class ArticleBody
{
    public const BLOCKS = [
        'journey' => 'Buyer journey strip',
        'compare' => 'Compare rows',
        'figure' => 'Wide figure',
        'quote' => 'Pull quote',
        'reels' => 'Reel ideas',
        'checklist' => 'Checklist',
    ];

    /** Blocks whose items rise one by one. */
    private const GROUPS = ['ek-b-compare', 'ek-b-reels', 'ek-b-check'];

    /** @return array{html: string, toc: list<array{id: string, label: string}>} */
    public static function prepare(string $html): array
    {
        if (trim($html) === '') {
            return ['html' => '', 'toc' => []];
        }

        $doc = new DOMDocument();
        $errors = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"><div id="ek-article-root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($errors);
        $root = $doc->getElementById('ek-article-root');
        if (! $root) {
            return ['html' => '', 'toc' => []];
        }

        foreach (iterator_to_array($doc->getElementsByTagName('script')) as $script) {
            $script->parentNode?->removeChild($script);
        }
        foreach ($root->getElementsByTagName('*') as $el) {
            foreach (iterator_to_array($el->attributes) as $attr) {
                $name = strtolower($attr->name);
                $value = preg_replace('/[\s\x00-\x1F]+/', '', strtolower($attr->value));
                if (str_starts_with($name, 'on') || (in_array($name, ['href', 'src', 'xlink:href', 'action', 'formaction'], true) && str_starts_with((string) $value, 'javascript:'))) {
                    $el->removeAttribute($attr->name);
                }
            }
        }

        $toc = [];
        $used = [];
        foreach ($root->getElementsByTagName('h2') as $h2) {
            $id = $h2->getAttribute('id');
            if ($id === '' || isset($used[$id])) {
                $id = 'h-'.(count($toc) + 1);
            }
            $used[$id] = true;
            $h2->setAttribute('id', $id);
            $label = trim($h2->getAttribute('data-toc')) ?: trim(preg_replace('/\s+/u', ' ', $h2->textContent));
            $toc[] = ['id' => $id, 'label' => $label];
        }

        $out = '';
        foreach ($root->childNodes as $node) {
            if ($node instanceof DOMElement) {
                $group = array_intersect(self::GROUPS, explode(' ', $node->getAttribute('class')));
                foreach ($group ? $node->childNodes : [$node] as $item) {
                    if ($item instanceof DOMElement && ! $item->hasAttribute('data-m')) {
                        $item->setAttribute('data-m', 'ab-rise');
                    }
                }
            }
            $out .= $doc->saveHTML($node);
        }

        return ['html' => $out, 'toc' => $toc];
    }

    /** name => [label, html]: the Insert block menu of the article editor. */
    public static function blocks(): array
    {
        $blocks = [];
        foreach (self::BLOCKS as $name => $label) {
            $blocks[$name] = ['label' => $label, 'html' => trim(view('frontend.article-blocks.'.$name)->render())];
        }

        return $blocks;
    }
}
