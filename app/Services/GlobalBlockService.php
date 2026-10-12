<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\GlobalBlock;
use App\Models\GlobalBlockVersion;
use App\Models\Layout;
use App\Models\Page;
use App\Models\Section;
use App\Repositories\Contracts\GlobalBlockRepositoryInterface;
use App\Services\SectionTypes\SectionTypeRegistry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GlobalBlockService
{
    private const RENDER_CACHE_PREFIX = 'global_block.render.';

    public function __construct(
        protected GlobalBlockRepositoryInterface $blocks,
        protected SectionTypeRegistry $registry,
        protected LayoutService $layouts,
        protected PageRenderService $pageRenderer,
    ) {
    }

    /** Create a brand-new master block from a chosen SectionType's defaults. */
    public function create(array $data): GlobalBlock
    {
        $type = $this->registry->find($data['type']);

        $block = $this->blocks->create([
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['slug'] ?? $data['name']),
            'category_id' => $data['category_id'] ?? null,
            'type' => $data['type'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? GlobalBlock::STATUS_ACTIVE,
            'version' => 1,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
            'data_desktop' => $type->defaults(),
        ]);

        return $block;
    }

    /**
     * Create a master FROM an existing on-page Section — the "promote this
     * section to a reusable block" flow. The section becomes the first
     * linked instance.
     */
    public function createFromSection(Section $section, array $data): GlobalBlock
    {
        $block = DB::transaction(function () use ($section, $data): GlobalBlock {
            $block = $this->blocks->create([
                'name' => $data['name'],
                'slug' => $this->uniqueSlug($data['slug'] ?? $data['name']),
                'category_id' => $data['category_id'] ?? null,
                'type' => $section->type,
                'description' => $data['description'] ?? null,
                'status' => GlobalBlock::STATUS_ACTIVE,
                'version' => 1,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
                'data_desktop' => $section->data_desktop,
                'data_tablet' => $section->data_tablet,
                'data_mobile' => $section->data_mobile,
                'style' => $section->style,
            ]);

            $section->update(['global_block_id' => $block->id, 'is_synced' => true]);
            $this->touchUsage($block);

            return $block;
        });

        $this->flushInstances($block);

        return $block;
    }

    /**
     * Save the master payload. Writes a version snapshot first, then bumps
     * version, then — if $propagate — pushes the new payload to every
     * synced instance and flushes their page/layout caches.
     */
    public function update(GlobalBlock $block, array $data, bool $propagate = true): GlobalBlock
    {
        $type = $this->registry->find($block->type);

        if (isset($data['data_desktop'])) {
            \Illuminate\Support\Facades\Validator::make(
                ['data' => $data['data_desktop']],
                $type->rules()
            )->validate();
        }

        DB::transaction(function () use ($block, $data): void {
            $this->snapshotVersion($block);

            $block->update([
                'name' => $data['name'] ?? $block->name,
                'category_id' => array_key_exists('category_id', $data) ? $data['category_id'] : $block->category_id,
                'description' => array_key_exists('description', $data) ? $data['description'] : $block->description,
                'status' => $data['status'] ?? $block->status,
                'preview_media_id' => array_key_exists('preview_media_id', $data) ? $data['preview_media_id'] : $block->preview_media_id,
                'data_desktop' => array_key_exists('data_desktop', $data) ? $data['data_desktop'] : $block->data_desktop,
                'data_tablet' => array_key_exists('data_tablet', $data) ? $data['data_tablet'] : $block->data_tablet,
                'data_mobile' => array_key_exists('data_mobile', $data) ? $data['data_mobile'] : $block->data_mobile,
                'style' => array_key_exists('style', $data) ? array_replace((array) $block->style, (array) $data['style']) : $block->style,
                'version' => $block->version + 1,
                'updated_by' => Auth::id(),
            ]);
        });

        $this->flushRenderCache($block);

        if ($propagate) {
            $this->pushToInstances($block);
        }

        return $block->refresh();
    }

    public function toggleFavorite(GlobalBlock $block): GlobalBlock
    {
        $block->update(['is_favorite' => ! $block->is_favorite]);

        return $block;
    }

    public function duplicate(GlobalBlock $block): GlobalBlock
    {
        $copy = $this->blocks->create([
            'name' => trim($block->name.' (Copy)'),
            'slug' => $this->uniqueSlug($block->name.'-copy'),
            'category_id' => $block->category_id,
            'type' => $block->type,
            'description' => $block->description,
            'status' => GlobalBlock::STATUS_DRAFT,
            'version' => 1,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
            'data_desktop' => $block->data_desktop,
            'data_tablet' => $block->data_tablet,
            'data_mobile' => $block->data_mobile,
            'style' => $block->style,
        ]);

        return $copy;
    }

    public function delete(GlobalBlock $block): void
    {
        // Instances survive: they simply become local (unlinked) sections
        // with the payload they already had — deleting a master must never
        // silently delete content off a live page.
        $block->instances()->update(['global_block_id' => null, 'is_synced' => false]);
        $this->flushInstances($block);

        $this->blocks->delete($block);
        Cache::forget(self::RENDER_CACHE_PREFIX.$block->id);
    }

    /* ── Placing a block on a page ─────────────────────────────────── */

    /**
     * "Add Existing Block" — creates a new Section instance on $owner
     * linked to the master, carrying its current payload. Bumps use_count
     * / last_used_at for the "Most Used"/"Recently Used" library views.
     */
    public function placeOnPage(GlobalBlock $block, \Illuminate\Database\Eloquent\Model $owner, int $sortOrder): Section
    {
        if ($block->status !== GlobalBlock::STATUS_ACTIVE) {
            throw ValidationException::withMessages(['global_block_id' => 'This block is not active and cannot be placed.']);
        }

        $section = $owner->sections()->create([
            'type' => $block->type,
            'name' => $block->name,
            'sort_order' => $sortOrder,
            'data_desktop' => $block->data_desktop,
            'data_tablet' => $block->data_tablet,
            'data_mobile' => $block->data_mobile,
            'style' => $block->style,
            'global_block_id' => $block->id,
            'is_synced' => true,
        ]);

        $this->touchUsage($block);
        $this->flushOwnerCache($owner);

        return $section;
    }

    /** "Detach From Global" — freezes the instance's current payload locally. */
    public function detach(Section $section): Section
    {
        $section->update(['is_synced' => false]);

        return $section;
    }

    /** Re-link a detached instance and immediately pull the master's current payload. */
    public function reattach(Section $section, GlobalBlock $block): Section
    {
        $section->update([
            'global_block_id' => $block->id,
            'is_synced' => true,
            'data_desktop' => $block->data_desktop,
            'data_tablet' => $block->data_tablet,
            'data_mobile' => $block->data_mobile,
            'style' => $block->style,
        ]);

        $this->flushOwnerCache($section->sectionable);

        return $section;
    }

    /* ── Version history ──────────────────────────────────────────── */

    public function restoreVersion(GlobalBlock $block, GlobalBlockVersion $version): GlobalBlock
    {
        abort_unless($version->global_block_id === $block->id, 404);

        return $this->update($block, [
            'data_desktop' => $version->data_desktop,
            'data_tablet' => $version->data_tablet,
            'data_mobile' => $version->data_mobile,
            'style' => $version->style,
        ]);
    }

    /* ── Export / Import ──────────────────────────────────────────── */

    /** The block as JSON: media by key; with $bundle, its media files and the collection items too (ContentTransfer). */
    public function exportPayload(GlobalBlock $block, bool $bundle = false): array
    {
        return app(\App\Services\ContentTransfer::class)->exportBlock($block, $bundle);
    }

    public function import(array $payload): GlobalBlock
    {
        // An imported file is untrusted: the editor's field rules, motion
        // sanitiser and Developer Mode gate (ContentTransfer).
        $fields = app(\App\Services\ContentTransfer::class)->importBlock($payload);
        $name = \Illuminate\Support\Str::limit((string) ($payload['name'] ?? '') ?: 'Imported Block', 100, '');

        return $this->blocks->create([
            ...\Illuminate\Support\Arr::only($fields, ['type', 'data_desktop', 'data_tablet', 'data_mobile', 'style']),
            'name' => $name,
            'slug' => $this->uniqueSlug($name),
            'description' => is_string($payload['description'] ?? null) ? \Illuminate\Support\Str::limit($payload['description'], 500, '') : null,
            'status' => GlobalBlock::STATUS_DRAFT,
            'version' => 1,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);
    }

    /* ── Cached rendering ─────────────────────────────────────────── */

    /** Rendered blocks are cached per master version — bumping version auto-invalidates. */
    public function cacheKeyFor(GlobalBlock $block): string
    {
        return self::RENDER_CACHE_PREFIX.$block->id.'.v'.$block->version;
    }

    /* ── internals ─────────────────────────────────────────────────── */

    private function snapshotVersion(GlobalBlock $block): void
    {
        GlobalBlockVersion::create([
            'global_block_id' => $block->id,
            'version' => $block->version,
            'data_desktop' => $block->data_desktop,
            'data_tablet' => $block->data_tablet,
            'data_mobile' => $block->data_mobile,
            'style' => $block->style,
            'created_by' => Auth::id(),
        ]);
    }

    /** "Update All Pages": push the master's current payload to every synced instance. */
    private function pushToInstances(GlobalBlock $block): void
    {
        $instances = $block->instances()->where('is_synced', true)->with('sectionable')->get();

        foreach ($instances as $section) {
            $section->update([
                'data_desktop' => $block->data_desktop,
                'data_tablet' => $block->data_tablet,
                'data_mobile' => $block->data_mobile,
                'style' => $block->style,
            ]);
        }

        $this->flushInstances($block);
    }

    private function touchUsage(GlobalBlock $block): void
    {
        $block->increment('use_count');
        $block->update(['last_used_at' => now()]);
    }

    private function flushRenderCache(GlobalBlock $block): void
    {
        // Old + new key: version already bumped in update(), so forgetting
        // the pre-bump key here would be a no-op; belt-and-suspenders forget
        // both the current and previous version's cache entry.
        Cache::forget(self::RENDER_CACHE_PREFIX.$block->id.'.v'.$block->version);
        Cache::forget(self::RENDER_CACHE_PREFIX.$block->id.'.v'.($block->version - 1));
    }

    private function flushInstances(GlobalBlock $block): void
    {
        foreach ($block->instances()->with('sectionable')->get() as $section) {
            $this->flushOwnerCache($section->sectionable);
        }
    }

    private function flushOwnerCache(?\Illuminate\Database\Eloquent\Model $owner): void
    {
        if ($owner instanceof Page) {
            $this->pageRenderer->flushCache($owner);

            return;
        }

        if ($owner instanceof Layout) {
            $this->layouts->flushCache();
        }
    }

    private function uniqueSlug(string $source): string
    {
        $base = Str::slug($source) ?: 'block';
        $slug = $base;
        $suffix = 2;

        while (GlobalBlock::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
