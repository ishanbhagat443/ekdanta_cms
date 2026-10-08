<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DeveloperOverride;
use App\Models\Media;
use App\Models\Page;
use App\Models\PageRevision;
use App\Models\Section;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Version history for the page builder.
 *
 * A snapshot is the page's own editable fields plus every section, in order.
 * Restoring replays that snapshot over the live rows: sections still present
 * are updated in place (so IDs, and anything pointing at them, survive),
 * sections added since are removed, and deleted ones come back.
 */
class PageRevisionService
{
    /** Snapshots kept per page. Oldest beyond this are pruned on capture. */
    public const KEEP_PER_PAGE = 30;

    public function __construct(
        protected PageRenderService $renderer,
        protected PageService $pages,
    ) {
    }

    /**
     * Capture the page as it stands right now.
     *
     * Skips writing when nothing changed since the last snapshot — repeatedly
     * hitting Save should not fill the history with identical entries.
     */
    public function capture(Page $page, ?int $userId, ?string $label = null): ?PageRevision
    {
        $data = $this->snapshot($page);
        $latest = $this->latestFor($page);

        if ($latest && $latest->data == $data) {
            return null;
        }

        $revision = PageRevision::create([
            'page_id' => $page->id,
            'user_id' => $userId,
            'label' => $label,
            'data' => $data,
        ]);

        $this->prune($page);

        return $revision;
    }

    /** Newest first — the order the history panel lists them in. */
    public function historyFor(Page $page, int $limit = self::KEEP_PER_PAGE): Collection
    {
        return PageRevision::query()
            ->where('page_id', $page->id)
            ->with('author:id,name')
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    public function latestFor(Page $page): ?PageRevision
    {
        return PageRevision::query()->where('page_id', $page->id)->latest('id')->first();
    }

    /**
     * Roll the page back to a snapshot.
     *
     * The current state is captured first, so "restore" is itself undoable —
     * a rollback should never be the one action a user cannot walk back.
     */
    public function restore(Page $page, PageRevision $revision, ?int $userId): Page
    {
        DB::transaction(function () use ($page, $revision, $userId): void {
            // Inside the transaction: if the restore fails, this safety
            // snapshot rolls back with it instead of littering the history.
            $this->capture($page, $userId, 'Before restore');

            $data = $revision->data;

            // Through PageService, not a raw save: it de-duplicates a slug
            // that has been taken since (a raw write would hit the unique
            // index and 500) and flushes the render cache for BOTH the old
            // and new slug, so no stale URL keeps serving the old page.
            $this->pages->updatePage($page, $this->pageAttributes($data['page'] ?? []));

            $this->restoreSections($page, (array) ($data['sections'] ?? []));
        });

        $this->renderer->flushCache($page);
        Cache::forget('seo.sitemap'); // a restore can change slug or status

        $page->refresh();

        // Record where the page landed. Without this the newest entry is the
        // "Before restore" safety copy, which the history panel badges as
        // "Current" — so restoring the top row would quietly undo the restore.
        $this->capture($page, $userId, 'Restored');

        return $page;
    }

    /**
     * Render payload for a snapshot, WITHOUT writing anything.
     *
     * The sections are built in memory and pushed onto the page's relation,
     * so livePayloadFor() — and therefore the frontend blades — treat them
     * exactly like real ones. That is the whole point: a preview that goes
     * through a different renderer is a preview that can lie.
     */
    public function previewPayload(Page $page, PageRevision $revision): array
    {
        $preview = $page->replicate();
        $preview->id = $page->id;
        $preview->exists = true;
        $preview->fill($this->pageAttributes($revision->data['page'] ?? []));

        $sectionIds = collect($revision->data['sections'] ?? [])
            ->pluck('id')
            ->filter()
            ->all();

        $overrides = DeveloperOverride::query()
            ->whereIn('section_id', $sectionIds)
            ->get()
            ->keyBy('section_id');

        $sections = collect($revision->data['sections'] ?? [])
            ->map(function (array $snap) use ($page, $overrides): Section {
                $section = new Section([
                    'type' => $snap['type'] ?? 'custom-html',
                    'name' => $snap['name'] ?? null,
                    'sort_order' => $snap['sort_order'] ?? 0,
                    'is_active' => (bool) ($snap['is_active'] ?? true),
                    'data_desktop' => $snap['data_desktop'] ?? null,
                    'data_tablet' => $snap['data_tablet'] ?? null,
                    'data_mobile' => $snap['data_mobile'] ?? null,
                    'style' => $snap['style'] ?? null,
                ]);

                $section->id = $snap['id'] ?? null;
                $section->sectionable_type = $page->getMorphClass();
                $section->sectionable_id = $page->id;

                // A section that still exists keeps whatever Developer Mode
                // override is attached to it — that override is what the page
                // would actually render after a restore.
                $section->setRelation(
                    'developerOverride',
                    $section->id ? $overrides->get($section->id) : null
                );

                return $section;
            })
            ->filter(fn (Section $section) => $section->is_active)
            ->sortBy('sort_order')
            ->values();

        $preview->setRelation('sections', $sections);

        // Resolve the banner from the SNAPSHOT's media id, not the live page's:
        // showing today's banner over an old version is exactly the kind of
        // half-truth that makes a preview untrustworthy.
        $bannerId = $preview->banner_media_id;
        $preview->setRelation(
            'bannerMedia',
            $bannerId ? ($bannerId === $page->banner_media_id ? $page->bannerMedia : Media::find($bannerId)) : null
        );

        $preview->setRelation('seoMeta', $page->seoMeta);

        return $this->renderer->livePayloadFor($preview);
    }

    /* ── Snapshot shape ──────────────────────────────────────────── */

    /** @return array<string, mixed> */
    public function snapshot(Page $page): array
    {
        return [
            'page' => [
                'title' => $page->title,
                'slug' => $page->slug,
                'status' => $page->status,
                'show_banner' => (bool) $page->show_banner,
                'banner_title' => $page->banner_title,
                'banner_media_id' => $page->banner_media_id,
                'settings' => $page->settings,
            ],
            'sections' => $page->sections()
                ->orderBy('sort_order')
                ->get()
                ->map(fn (Section $section) => [
                    'id' => $section->id,
                    'type' => $section->type,
                    'name' => $section->name,
                    'sort_order' => $section->sort_order,
                    'is_active' => (bool) $section->is_active,
                    'data_desktop' => $section->data_desktop,
                    'data_tablet' => $section->data_tablet,
                    'data_mobile' => $section->data_mobile,
                    'style' => $section->style,
                    'global_block_id' => $section->global_block_id,
                    'is_synced' => (bool) $section->is_synced,
                ])
                ->values()
                ->all(),
        ];
    }

    /* ── Internals ───────────────────────────────────────────────── */

    /** @param array<string, mixed> $data */
    private function pageAttributes(array $data): array
    {
        return [
            'title' => $data['title'] ?? '',
            'slug' => $data['slug'] ?? '',
            'status' => $data['status'] ?? Page::STATUS_DRAFT,
            'show_banner' => (bool) ($data['show_banner'] ?? true),
            'banner_title' => $data['banner_title'] ?? null,
            'banner_media_id' => $data['banner_media_id'] ?? null,
            'settings' => $data['settings'] ?? null,
        ];
    }

    /**
     * Replay the snapshot's sections onto the page.
     *
     * @param  list<array<string, mixed>>  $snapshots
     */
    private function restoreSections(Page $page, array $snapshots): void
    {
        $existing = $page->sections()->get()->keyBy('id');
        $keep = [];

        foreach ($snapshots as $position => $snap) {
            $attributes = [
                'type' => $snap['type'] ?? 'custom-html',
                'name' => $snap['name'] ?? null,
                'sort_order' => $snap['sort_order'] ?? $position,
                'is_active' => (bool) ($snap['is_active'] ?? true),
                'data_desktop' => $snap['data_desktop'] ?? null,
                'data_tablet' => $snap['data_tablet'] ?? null,
                'data_mobile' => $snap['data_mobile'] ?? null,
                'style' => $snap['style'] ?? null,
                'global_block_id' => $snap['global_block_id'] ?? null,
                'is_synced' => (bool) ($snap['is_synced'] ?? false),
            ];

            $id = $snap['id'] ?? null;

            if ($id !== null && $existing->has($id)) {
                // Update in place: the row keeps its ID, so a Developer Mode
                // override or global-block link attached to it stays valid.
                $section = $existing->get($id);
                $section->fill($attributes)->save();
                $keep[] = $section->id;

                continue;
            }

            // Deleted since the snapshot — recreate it (new ID; nothing else
            // can still be referencing a row that no longer exists).
            $keep[] = $page->sections()->create($attributes)->id;
        }

        // Anything added after the snapshot is not part of that version.
        $page->sections()->whereNotIn('id', $keep ?: [0])->delete();
    }

    /** Keep the newest KEEP_PER_PAGE snapshots; drop everything older. */
    private function prune(Page $page): void
    {
        $oldestKept = PageRevision::query()
            ->where('page_id', $page->id)
            ->latest('id')
            ->offset(self::KEEP_PER_PAGE - 1)
            ->limit(1)
            ->value('id');

        if ($oldestKept === null) {
            return;
        }

        PageRevision::query()
            ->where('page_id', $page->id)
            ->where('id', '<', $oldestKept)
            ->delete();
    }
}
