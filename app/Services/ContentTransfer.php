<?php

declare(strict_types=1);

namespace App\Services;

use App\Collections\CollectionRegistry;
use App\Models\CollectionItem;
use App\Models\GlobalBlock;
use App\Models\Media;
use App\Models\MediaFolder;
use App\Models\Page;
use App\Models\Section;
use App\Services\SectionTypes\SectionTypeRegistry;
use App\Support\Templates;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Pages and saved blocks as JSON, to move them between sites. Media is
 * referred to by its stable key and collection items by their slug, so the
 * other site resolves them. An export can bundle the media files it uses and
 * every collection item (there are few). An import is untrusted: it goes
 * through the editor's own field rules, motion sanitiser and Developer Mode
 * gate, and never replaces what the site already has.
 */
final class ContentTransfer
{
    public const FORMAT = 'ekdanta-cms';

    /** media id => portable reference, collected while exporting */
    private array $used = [];

    private bool $bundle = false;

    public function __construct(
        private SectionTypeRegistry $types,
        private CollectionRegistry $collections,
        private MediaService $media,
    ) {
    }

    /* ── Export ───────────────────────────────────────────────────── */

    public function exportPage(Page $page, bool $bundle = false): array
    {
        $this->start($bundle);
        $page->loadMissing(['seoMeta', 'sections.globalBlock']);
        $settings = (array) $page->settings;

        return $this->finish('page', [
            'page' => [
                'title' => $page->title,
                'slug' => $page->slug,
                'settings' => array_filter([
                    'header' => Arr::except((array) ($settings['header'] ?? []), ['menu_id']) ?: null,
                    'show_footer' => $settings['show_footer'] ?? null,
                    'template_for' => $settings['template_for'] ?? null,
                    'code' => $settings['code'] ?? null,
                    'conversions' => $settings['conversions'] ?? null,
                ], fn ($v) => $v !== null),
                'seo' => $page->seoMeta ? Arr::only($page->seoMeta->toArray(), array_keys(Page::seoRules())) : null,
            ],
            'sections' => $page->sections->sortBy('sort_order')->values()->map(fn (Section $s) => [
                'type' => $s->type,
                'name' => $s->name,
                'is_active' => $s->is_active,
                'block' => $s->is_synced ? $s->globalBlock?->slug : null,
                ...$this->portable($s->type, $s->only(['data_desktop', 'data_tablet', 'data_mobile'])),
                'style' => $this->portableStyle((array) $s->style),
            ])->all(),
        ]);
    }

    public function exportBlock(GlobalBlock $block, bool $bundle = false): array
    {
        $this->start($bundle);

        return $this->finish('block', [
            'name' => $block->name,
            'type' => $block->type,
            'description' => $block->description,
            ...$this->portable($block->type, $block->only(['data_desktop', 'data_tablet', 'data_mobile'])),
            'style' => $this->portableStyle((array) $block->style),
        ]);
    }

    /* ── Import ───────────────────────────────────────────────────── */

    /** A new draft page (its slug made unique) from an export. */
    public function importPage(array $payload): Page
    {
        $this->check($payload, 'page');
        $this->importBundle($payload);
        $in = (array) ($payload['page'] ?? []);
        $settings = (array) ($in['settings'] ?? []);
        $header = Arr::only((array) ($settings['header'] ?? []), ['variant', 'cta_label', 'cta_url']);

        $conversions = Arr::only((array) ($settings['conversions'] ?? []), array_keys(Page::CONVERSION_RULES));
        $valid = Validator::make(['title' => $in['title'] ?? null, 'header' => $header, 'conversions' => $conversions, 'seo' => (array) ($in['seo'] ?? [])], [
            'title' => ['required', 'string', 'max:191'],
            'header.variant' => ['nullable', 'in:normal,overlay,pill,hidden'],
            'header.cta_label' => ['nullable', 'string', 'max:60'],
            'header.cta_url' => ['nullable', 'string', 'max:500'],
            ...Arr::prependKeysWith(Page::CONVERSION_RULES, 'conversions.'),
            ...Arr::prependKeysWith(Page::seoRules(), 'seo.'),
        ]);
        if ($valid->fails()) {
            throw ValidationException::withMessages(['file' => 'This page export has invalid settings: '.$valid->errors()->first()]);
        }

        $sections = array_map(fn ($s, $i) => $this->section((array) $s, $i), (array) ($payload['sections'] ?? []), array_keys((array) ($payload['sections'] ?? [])));

        $base = Str::slug((string) ($in['slug'] ?? $in['title'])) ?: 'imported-page';
        $slug = $base;
        for ($n = 2; Page::withTrashed()->where('slug', $slug)->exists(); $n++) {
            $slug = $base.'-'.$n;
        }

        $page = Page::create([
            'title' => $in['title'],
            'slug' => $slug,
            'status' => Page::STATUS_DRAFT,
            'settings' => array_filter([
                'header' => array_filter($header, fn ($v) => $v !== null && $v !== '') ?: null,
                'show_footer' => isset($settings['show_footer']) ? (bool) $settings['show_footer'] : null,
                'template_for' => isset(Templates::TYPES[$settings['template_for'] ?? '']) ? $settings['template_for'] : null,
                'conversions' => array_filter($conversions, fn ($v) => is_string($v) && $v !== '') ?: null,
                // Page code runs on the public page: only with Developer Mode.
                'code' => Gate::allows('developer.mode') ? array_filter(Arr::only((array) ($settings['code'] ?? []), ['css', 'js', 'head', 'body']), 'is_string') ?: null : null,
            ], fn ($v) => $v !== null),
        ]);
        if ($in['seo'] ?? null) {
            $page->syncSeoMeta((array) $in['seo']);
        }

        $blocks = app(GlobalBlockService::class);
        foreach ($sections as $order => $section) {
            $block = $section['block'] ? GlobalBlock::query()->where('slug', $section['block'])->where('type', $section['type'])->where('status', GlobalBlock::STATUS_ACTIVE)->first() : null;
            $block
                ? $blocks->placeOnPage($block, $page, $order + 1)
                : $page->sections()->create(Arr::except($section, ['block']) + ['sort_order' => $order + 1]);
        }

        return $page;
    }

    /** A saved block's fields from an export, checked as the editor checks them. */
    public function importBlock(array $payload): array
    {
        // Older block exports carry no format line.
        if (isset($payload['format'])) {
            $this->check($payload, 'block');
        }
        $this->importBundle($payload);

        return Arr::except($this->section($payload, 0, false), ['block', 'is_active']);
    }

    /* ── internals ────────────────────────────────────────────────── */

    private function start(bool $bundle): void
    {
        $this->used = [];
        $this->bundle = $bundle;
    }

    private function finish(string $kind, array $body): array
    {
        $out = ['format' => self::FORMAT, 'kind' => $kind, 'version' => 1, ...$body];
        if ($this->bundle) {
            $out['items'] = CollectionItem::query()->orderBy('collection')->orderBy('sort_order')->get()
                ->map(fn (CollectionItem $item) => [
                    'collection' => $item->collection, 'slug' => $item->slug, 'title' => $item->title,
                    'status' => $item->status, 'sort_order' => $item->sort_order,
                    'data' => $this->portableFields($this->collections->find($item->collection)->fields(), (array) $item->data),
                ])->all();
            $out['media'] = Media::query()->whereIn('id', array_keys($this->used))->get()
                ->filter(fn (Media $m) => Storage::disk($m->disk)->exists($m->path))
                ->map(fn (Media $m) => ['key' => $this->used[$m->id], 'filename' => $m->filename, 'alt' => $m->alt,
                    'data' => base64_encode((string) Storage::disk($m->disk)->get($m->path))])->values()->all();
        }

        return $out;
    }

    /** data_desktop / data_tablet / data_mobile with media as portable references. */
    private function portable(string $type, array $data): array
    {
        $fields = $this->types->has($type) ? $this->types->find($type)->fields() : [];

        return array_map(fn ($d) => is_array($d) ? $this->portableFields($fields, $d) : $d, $data);
    }

    private function portableFields(array $fields, array $data): array
    {
        foreach ($fields as $field) {
            $key = $field['key'];
            if (($field['type'] ?? null) === 'media' && array_key_exists($key, $data)) {
                $data[$key] = $this->ref($data[$key]);
            }
            if (($field['type'] ?? null) === 'repeater' && is_array($data[$key] ?? null)) {
                $data[$key] = array_map(fn ($row) => is_array($row) ? $this->portableFields($field['fields'] ?? [], $row) : $row, $data[$key]);
            }
        }

        return $data;
    }

    private function portableStyle(array $style): array
    {
        foreach (['bg_media_id', 'bg_video_media_id'] as $key) {
            if (isset($style[$key])) {
                $style[$key] = $this->ref($style[$key]);
            }
        }

        return $style;
    }

    /** A media id becomes its key; one without a key gets "upload/…" when bundled (else it stays an id). */
    private function ref(mixed $value): mixed
    {
        $media = Media::isKey($value) ? Media::query()->where('key', $value)->first() : (is_numeric($value) ? Media::query()->find((int) $value) : null);
        if (! $media) {
            return $value;
        }
        $ref = $media->key ?? ($this->bundle ? 'upload/'.$media->id.'-'.Str::slug(pathinfo($media->filename, PATHINFO_FILENAME)) : $media->id);
        $this->used[$media->id] = $ref;

        return $ref;
    }

    private function check(array $payload, string $kind): void
    {
        if (($payload['format'] ?? null) !== self::FORMAT || ($payload['kind'] ?? null) !== $kind) {
            throw ValidationException::withMessages(['file' => "That file is not a {$kind} export from this CMS."]);
        }
    }

    /** One exported section, ready to save: known type, valid fields, safe style. */
    private function section(array $s, int $index, bool $onPage = true): array
    {
        $type = (string) ($s['type'] ?? '');
        if (! $this->types->has($type) || ($onPage && ! in_array('page', $this->types->find($type)->allowedAreas(), true))) {
            throw ValidationException::withMessages(['file' => 'Section '.($index + 1).": unknown type \"{$type}\"."]);
        }
        $schema = $this->types->find($type);
        $desktop = $this->localFields($schema->fields(), (array) ($s['data_desktop'] ?? []));
        $valid = Validator::make(['data' => $desktop], $schema->rules());
        if ($valid->fails()) {
            throw ValidationException::withMessages(['file' => 'Section '.($index + 1)." ({$type}): ".$valid->errors()->first()]);
        }
        // Per-device values: only the fields that may differ per device, as in the editor.
        $device = fn ($d) => is_array($d) ? array_filter(Arr::only($this->localFields($schema->fields(), $d), $schema->deviceKeys()), fn ($v) => $v !== null && $v !== '') ?: null : null;
        $style = Section::withoutPrivilegedStyle((array) ($s['style'] ?? []));
        foreach (['bg_media_id', 'bg_video_media_id'] as $key) {
            if (isset($style[$key])) {
                $style[$key] = $this->localId($style[$key]);
            }
        }

        return [
            'type' => $type,
            'name' => Str::limit((string) ($s['name'] ?? $schema->label()), 100, ''),
            'is_active' => (bool) ($s['is_active'] ?? true),
            'block' => is_string($s['block'] ?? null) ? $s['block'] : null,
            'data_desktop' => $desktop,
            'data_tablet' => $device($s['data_tablet'] ?? null),
            'data_mobile' => $device($s['data_mobile'] ?? null),
            'style' => array_filter($style, fn ($v) => $v !== null),
        ];
    }

    /** Media references this site can't resolve are dropped (the field falls back to empty). */
    private function localFields(array $fields, array $data): array
    {
        foreach ($fields as $field) {
            $key = $field['key'];
            if (($field['type'] ?? null) === 'media' && array_key_exists($key, $data)) {
                $data[$key] = $this->localRef($data[$key]);
            }
            if (($field['type'] ?? null) === 'repeater' && is_array($data[$key] ?? null)) {
                $data[$key] = array_values(array_map(fn ($row) => is_array($row) ? $this->localFields($field['fields'] ?? [], $row) : [], $data[$key]));
            }
        }

        return $data;
    }

    private function localRef(mixed $value): mixed
    {
        if (Media::isKey($value)) {
            return Media::query()->where('key', $value)->exists() ? $value : null;
        }

        return is_numeric($value) && Media::query()->whereKey((int) $value)->exists() ? (int) $value : null;
    }

    private function localId(mixed $value): ?int
    {
        $ref = $this->localRef($value);

        return is_string($ref) ? Media::query()->where('key', $ref)->value('id') : $ref;
    }

    /** Bundled media (by key) and collection items (by slug) the site lacks; nothing it has is replaced. */
    private function importBundle(array $payload): void
    {
        $allowed = collect((array) config('gsm.media.allowed'))->flatten()->map(fn ($e) => strtolower((string) $e))->all();
        $folder = null;

        foreach (array_slice((array) ($payload['media'] ?? []), 0, 200) as $file) {
            $key = $file['key'] ?? null;
            $name = basename((string) ($file['filename'] ?? ''));
            $bytes = base64_decode((string) ($file['data'] ?? ''), true);
            if (! Media::isKey($key) || Media::query()->where('key', $key)->exists() || $bytes === false
                || ! in_array(strtolower(pathinfo($name, PATHINFO_EXTENSION)), $allowed, true)
                || strlen($bytes) > (int) config('gsm.media.max_upload_kb') * 1024) {
                continue;
            }
            $folder ??= MediaFolder::query()->firstOrCreate(['name' => 'Imported', 'parent_id' => null]);
            $path = tempnam(sys_get_temp_dir(), 'imp');
            file_put_contents($path, $bytes);
            try {
                $media = $this->media->upload(new UploadedFile($path, $name, null, null, true), $folder->id, auth()->id());
                $media->forceFill(['key' => $key, 'alt' => Str::limit((string) ($file['alt'] ?? ''), 191, '')])->save();
            } finally {
                @unlink($path);
            }
        }

        foreach ((array) ($payload['items'] ?? []) as $item) {
            $collection = (string) ($item['collection'] ?? '');
            $slug = (string) ($item['slug'] ?? '');
            if (! $this->collections->all()->has($collection) || ! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)
                || CollectionItem::withTrashed()->where('collection', $collection)->where('slug', $slug)->exists()) {
                continue;
            }
            $type = $this->collections->find($collection);
            $data = Arr::only($this->localFields($type->fields(), (array) ($item['data'] ?? [])), array_column($type->fields(), 'key'));
            $valid = Validator::make(['title' => $item['title'] ?? null, 'data' => $data], ['title' => ['required', 'string', 'max:200'], ...$type->rules()]);
            if ($valid->fails()) {
                continue;
            }
            CollectionItem::create([
                'collection' => $collection, 'slug' => $slug, 'title' => $item['title'],
                'status' => ($item['status'] ?? null) === CollectionItem::STATUS_PUBLISHED ? CollectionItem::STATUS_PUBLISHED : CollectionItem::STATUS_DRAFT,
                'sort_order' => max(0, min(100000, (int) ($item['sort_order'] ?? 0))), 'data' => $data,
            ]);
        }
    }
}
