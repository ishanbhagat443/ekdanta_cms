<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageRevision;
use App\Services\PageRevisionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Version history for a page: list, preview, restore.
 */
class PageRevisionController extends Controller
{
    public function __construct(protected PageRevisionService $revisions)
    {
        $this->middleware('can:pages.manage');
    }

    /** JSON list for the history panel. */
    public function index(Page $page): JsonResponse
    {
        return response()->json([
            'revisions' => $this->revisions->historyFor($page)->map(fn (PageRevision $revision) => [
                'id' => $revision->id,
                'label' => $revision->label,
                'author' => $revision->authorName(),
                'sections' => $revision->sectionCount(),
                'created_at' => $revision->created_at->toIso8601String(),
                'created_for_humans' => $revision->created_at->diffForHumans(),
                'preview_url' => route('admin.pages.revisions.preview', [$page, $revision]),
                'restore_url' => route('admin.pages.revisions.restore', [$page, $revision]),
            ])->all(),
        ]);
    }

    /** Render the snapshot through the real frontend view. Nothing is written. */
    public function preview(Page $page, PageRevision $revision): View
    {
        $this->assertBelongsTo($page, $revision);

        $payload = $this->revisions->previewPayload($page, $revision);

        return view('frontend.pages.show', [
            'page' => $payload['page'],
            'sections' => $payload['sections'],
            // From the snapshot, like everything else on this screen: mixing
            // the live slug in would render the banner differently from what
            // a restore actually produces.
            'isHome' => ($revision->data['page']['slug'] ?? $page->slug) === 'home',
            'revisionPreview' => $revision,
        ]);
    }

    public function restore(Request $request, Page $page, PageRevision $revision): RedirectResponse
    {
        $this->assertBelongsTo($page, $revision);

        $this->revisions->restore($page, $revision, $request->user()?->id);

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('success', 'Restored the version from '.$revision->created_at->format('d M Y, H:i')
                .'. The version you were on was saved to history first.');
    }

    /** A revision id from another page must not be addressable here. */
    private function assertBelongsTo(Page $page, PageRevision $revision): void
    {
        abort_unless($revision->page_id === $page->id, 404);
    }
}
