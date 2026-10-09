<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Repositories\Contracts\MenuRepositoryInterface;
use App\Repositories\Contracts\PageRepositoryInterface;
use App\Repositories\Contracts\GlobalBlockRepositoryInterface;
use App\Services\PageRevisionService;
use App\Services\PageStarterService;
use App\Services\PageService;
use App\Services\SectionTypes\SectionTypeRegistry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    public function __construct(
        protected PageRepositoryInterface $pages,
        protected PageService $pageService,
        protected SectionTypeRegistry $registry,
        protected MenuRepositoryInterface $menus,
        protected GlobalBlockRepositoryInterface $globalBlocks,
        protected PageRevisionService $revisions,
        protected PageStarterService $starters,
    ) {
        $this->middleware('can:pages.manage');
    }

    public function index(Request $request): View
    {
        return view('admin.pages.index', [
            'pages' => $this->pages->paginateForAdmin(15, $request->string('search')->toString() ?: null),
            'search' => $request->string('search')->toString(),
            'starters' => $this->starters->starters(),
            'kits' => $this->starters->kits(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:191'],
            'slug' => ['nullable', 'string', 'max:191'],
            'starter' => ['nullable', 'string', Rule::in($this->starters->starters()->keys())],
        ]);

        $page = $this->pageService->createPage($data);

        $starter = $data['starter'] ?? 'blank';

        if ($starter !== 'blank') {
            $this->starters->apply($page, $starter);

            return redirect()
                ->route('admin.pages.edit', $page)
                ->with('success', 'Page created from a starter layout. Edit the text and images to make it yours.');
        }

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('success', 'Page created — add your first section below.');
    }

    /** Create a whole set of pages from a site kit. */
    public function applyKit(Request $request, string $kit): RedirectResponse
    {
        $result = $this->starters->applyKit($kit);

        if ($result['created'] === [] && $result['skipped'] === []) {
            return redirect()->route('admin.pages.index')
                ->with('error', 'That site kit no longer exists.');
        }

        $message = count($result['created']).' page(s) created and added to your menu.';

        if ($result['skipped'] !== []) {
            $message .= ' Left alone because they already have content: '
                .implode(', ', $result['skipped']).'.';
        }

        return redirect()->route('admin.pages.index')->with('success', $message);
    }

    /** Fill an empty page from a starter layout. */
    public function applyStarter(Request $request, Page $page): RedirectResponse
    {
        $data = $request->validate([
            'starter' => ['required', 'string', Rule::in($this->starters->starters()->keys())],
        ]);

        if ($page->sections()->exists()) {
            return back()->with('error', 'This page already has sections, so the starter was not applied.');
        }

        $this->starters->apply($page, $data['starter']);

        return redirect()->route('admin.pages.edit', $page)
            ->with('success', 'Starter layout added. Edit the text and images to make it yours.');
    }

    /** Page settings + section builder on one screen. */
    public function edit(Page $page): View
    {
        $page->load(['sections', 'bannerMedia', 'seoMeta']);

        return view('admin.pages.edit', [
            'page' => $page,
            'pageTypes' => $this->registry->forArea('page'),
            'menus' => $this->menus->allMenus(),
            'registry' => $this->registry,
            'globalBlocks' => $this->globalBlocks->activeOptions(),
            'starters' => $this->starters->starters(),
        ]);
    }

    public function update(Request $request, Page $page): RedirectResponse|JsonResponse
    {
        $data = $request->validate(array_merge([
            'title' => ['required', 'string', 'max:191'],
            'slug' => ['nullable', 'string', 'max:191', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'status' => ['required', 'in:draft,published'],
            'show_banner' => ['nullable', 'boolean'],
            'banner_title' => ['nullable', 'string', 'max:191'],
            'banner_media_id' => ['nullable', 'integer', 'exists:media,id'],
            // Blog Listing / Blog Detail CMS settings (present only on those pages' forms).
            'blog_grid' => ['nullable', 'array'],
            'blog_grid.posts_per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'blog_grid.pagination_enabled' => ['nullable', 'boolean'],
            'blog_grid.columns' => ['nullable', 'integer', 'in:2,3'],
            'blog_grid.featured_first' => ['nullable', 'boolean'],
            'blog_grid.sort_order' => ['nullable', 'in:latest,oldest,popular'],
            'blog_grid.show_date' => ['nullable', 'boolean'],
            'blog_grid.show_category' => ['nullable', 'boolean'],
            'blog_grid.show_author' => ['nullable', 'boolean'],
            'blog_grid.show_read_more' => ['nullable', 'boolean'],
            'blog_grid.show_featured_badge' => ['nullable', 'boolean'],
            'blog_sidebar' => ['nullable', 'array'],
            'blog_sidebar.search' => ['nullable', 'boolean'],
            'blog_sidebar.categories' => ['nullable', 'boolean'],
            'blog_sidebar.recent' => ['nullable', 'boolean'],
            'blog_sidebar.popular' => ['nullable', 'boolean'],
            'blog_sidebar.tags' => ['nullable', 'boolean'],
            'blog_sidebar.newsletter' => ['nullable', 'boolean'],
            'blog_related' => ['nullable', 'array'],
            'blog_related.enabled' => ['nullable', 'boolean'],
            'blog_related.count' => ['nullable', 'integer', 'min:1', 'max:12'],
            'blog_related.same_category_only' => ['nullable', 'boolean'],
            'blog_related.manual_only' => ['nullable', 'boolean'],
            'blog_related.sort_order' => ['nullable', 'in:latest,oldest,popular'],
            'blog_social_share' => ['nullable', 'array'],
            'blog_social_share.facebook' => ['nullable', 'boolean'],
            'blog_social_share.whatsapp' => ['nullable', 'boolean'],
            'blog_social_share.linkedin' => ['nullable', 'boolean'],
            'blog_social_share.twitter' => ['nullable', 'boolean'],
            'blog_social_share.copy_link' => ['nullable', 'boolean'],
            'blog_comments' => ['nullable', 'array'],
            'blog_comments.enabled' => ['nullable', 'boolean'],
            'blog_post_content' => ['nullable', 'array'],
            'blog_post_content.show_reading_time' => ['nullable', 'boolean'],
            'blocks' => ['nullable', 'array'],
            // Page settings: role, header, footer, page code.
            'page_role' => ['nullable', 'in:'.implode(',', array_keys(\App\Support\PageRoles::ROLES))],
            'header' => ['nullable', 'array'],
            'header.variant' => ['nullable', 'in:normal,overlay,pill,hidden'],
            'header.menu_id' => ['nullable', 'integer', 'exists:menus,id'],
            'header.cta_label' => ['nullable', 'string', 'max:60'],
            'header.cta_url' => ['nullable', 'string', 'max:500'],
            'show_footer' => ['nullable', 'boolean'],
            'code' => ['nullable', 'array'],
            'code.*' => ['nullable', 'string', 'max:20000'],
        ], Page::seoRules()));

        // Editable blocks for bespoke template screens — see
        // config/page_templates.php.
        if ($request->has('blocks')) {
            $template = app(\App\Services\PageTemplateService::class);

            $data['settings'] = array_merge((array) $page->settings, [
                'blocks' => $template->sanitise($page->slug, (array) $request->input('blocks', [])),
            ]);

            $template->flushCache();
        }

        if ($request->hasAny(['blog_grid', 'blog_sidebar', 'blog_related', 'blog_social_share', 'blog_comments', 'blog_post_content'])) {
            // Build on whatever the block merge above produced, not on the
            // stored value again — re-reading would drop those edits.
            $settings = (array) ($data['settings'] ?? $page->settings);

            if ($request->has('blog_grid')) {
                $settings['grid'] = $this->blogGridSettings($request);
            }
            if ($request->has('blog_sidebar')) {
                $settings['sidebar'] = $this->blogSidebarSettings($request);
            }
            if ($request->has('blog_related')) {
                $settings['related'] = $this->blogRelatedSettings($request);
            }
            if ($request->has('blog_social_share')) {
                $settings['social_share'] = $this->blogSocialShareSettings($request);
            }
            if ($request->has('blog_comments')) {
                $settings['comments'] = ['enabled' => (bool) ($request->input('blog_comments.enabled') ?? false)];
            }
            if ($request->has('blog_post_content')) {
                $settings['post_content'] = ['show_reading_time' => (bool) ($request->input('blog_post_content.show_reading_time') ?? false)];
            }

            $data['settings'] = $settings;
        }

        $data['settings'] = $this->pageSettings($request, (array) ($data['settings'] ?? $page->settings), $data);

        if ($request->has('page_role')) {
            \App\Support\PageRoles::assign($page, $data['page_role'] ?: null);
        }

        $page->syncSeoMeta($data);
        $this->pageService->updatePage($page, $data);

        // Snapshot AFTER the write, so the history entry is the version the
        // user just saved.
        //
        // Autosave fires every couple of seconds and marks itself; without
        // that flag its snapshots would fill the retention window and evict
        // the handful of explicit saves the user actually wants back. A
        // request that does not say "autosave" is treated as explicit, so a
        // plain form post still writes history.
        if (! $request->boolean('autosave')) {
            $this->revisions->capture($page->refresh(), $request->user()?->id);
        }

        // Autosave posts the same form over fetch(); answer in kind so the
        // builder can show "Saved" without a page reload.
        if ($request->expectsJson()) {
            return response()->json([
                'saved' => true,
                'saved_at' => now()->toIso8601String(),
                'page' => ['slug' => $page->slug, 'status' => $page->status],
            ]);
        }

        return redirect()->route('admin.pages.edit', $page->fresh())->with('success', 'Page saved.');
    }


    /** Blog Listing "Blog Grid" settings — defaults match the seeded/current values. */
    private function blogGridSettings(Request $request): array
    {
        $input = (array) $request->input('blog_grid', []);
        $perPage = $input['posts_per_page'] ?? null;
        $sort = in_array($input['sort_order'] ?? null, ['latest', 'oldest', 'popular'], true) ? $input['sort_order'] : 'latest';
        $columns = in_array((int) ($input['columns'] ?? 2), [2, 3], true) ? (int) $input['columns'] : 2;

        return [
            'posts_per_page' => ($perPage === '' || $perPage === null) ? null : (int) $perPage,
            'pagination_enabled' => (bool) ($input['pagination_enabled'] ?? false),
            'columns' => $columns,
            'featured_first' => (bool) ($input['featured_first'] ?? false),
            'sort_order' => $sort,
            'show_date' => (bool) ($input['show_date'] ?? false),
            'show_category' => (bool) ($input['show_category'] ?? false),
            'show_author' => (bool) ($input['show_author'] ?? false),
            'show_read_more' => (bool) ($input['show_read_more'] ?? false),
            'show_featured_badge' => (bool) ($input['show_featured_badge'] ?? false),
        ];
    }

    /** Blog Listing / Blog Detail "Sidebar Widgets" settings — shared shape on both pages. */
    private function blogSidebarSettings(Request $request): array
    {
        $input = (array) $request->input('blog_sidebar', []);

        return [
            'search' => (bool) ($input['search'] ?? false),
            'categories' => (bool) ($input['categories'] ?? false),
            'recent' => (bool) ($input['recent'] ?? false),
            'popular' => (bool) ($input['popular'] ?? false),
            'tags' => (bool) ($input['tags'] ?? false),
            'newsletter' => (bool) ($input['newsletter'] ?? false),
        ];
    }

    /**
     * Header, footer and page code from the page form, merged over what is
     * stored. Page code runs on the public page, so it needs Developer Mode;
     * without it the stored code is kept, never cleared.
     */
    private function pageSettings(Request $request, array $settings, array $data): array
    {
        $filled = fn ($value) => $value !== null && $value !== '';

        if ($request->has('header')) {
            $settings['header'] = array_filter(\Illuminate\Support\Arr::only((array) ($data['header'] ?? []), ['variant', 'menu_id', 'cta_label', 'cta_url']), $filled);
        }
        if ($request->has('show_footer')) {
            $settings['show_footer'] = $request->boolean('show_footer');
        }
        if ($request->has('code') && \Illuminate\Support\Facades\Gate::allows('developer.mode')) {
            $settings['code'] = array_filter(\Illuminate\Support\Arr::only((array) ($data['code'] ?? []), ['css', 'js', 'head', 'body']), $filled);
        }

        return $settings;
    }

    /** Blog Detail "Related Posts" settings. */
    private function blogRelatedSettings(Request $request): array
    {
        $input = (array) $request->input('blog_related', []);
        $sort = in_array($input['sort_order'] ?? null, ['latest', 'oldest', 'popular'], true) ? $input['sort_order'] : 'latest';

        return [
            'enabled' => (bool) ($input['enabled'] ?? false),
            'count' => max(1, min(12, (int) ($input['count'] ?? 3))),
            'same_category_only' => (bool) ($input['same_category_only'] ?? false),
            'manual_only' => (bool) ($input['manual_only'] ?? false),
            'sort_order' => $sort,
        ];
    }

    /** Blog Detail "Social Share" settings. */
    private function blogSocialShareSettings(Request $request): array
    {
        $input = (array) $request->input('blog_social_share', []);

        return [
            'facebook' => (bool) ($input['facebook'] ?? false),
            'whatsapp' => (bool) ($input['whatsapp'] ?? false),
            'linkedin' => (bool) ($input['linkedin'] ?? false),
            'twitter' => (bool) ($input['twitter'] ?? false),
            'copy_link' => (bool) ($input['copy_link'] ?? false),
        ];
    }

    public function destroy(Page $page): RedirectResponse
    {
        if ($page->slug === 'home') {
            return redirect()->route('admin.pages.index')
                ->with('error', 'The home page cannot be deleted.');
        }

        $this->pageService->deletePage($page);

        return redirect()->route('admin.pages.index')->with('success', 'Page deleted.');
    }
}
