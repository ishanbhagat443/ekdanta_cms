<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\Page;
use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use App\Services\BlogService;
use App\Services\PageRenderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    /** @var array<string, Page|null> per-request memo — see page(). */
    private array $pageCache = [];

    public function __construct(
        protected PostRepositoryInterface $posts,
        protected BlogService $blog,
        protected PageRenderService $renderer,
    ) {
    }

    public function index(Request $request): View
    {
        // A page built in the page builder at /blog replaces this listing.
        if ($payload = app(\App\Services\PageRenderService::class)->payloadFor('blog')) {
            return PageController::render($payload, false);
        }

        $filters = [
            'search' => $request->string('search')->toString() ?: null,
            'category' => $request->string('category')->toString() ?: null,
            'tag' => $request->string('tag')->toString() ?: null,
            'archive' => $request->string('archive')->toString() ?: null,
        ];

        $grid = $this->pageSettings('blog-listing', 'grid', $this->gridDefaults());
        $perPage = (int) ($grid['posts_per_page'] ?: setting('commerce.posts_per_page', config('gsm.defaults.posts_per_page')));

        return view('frontend.blog.index', [
            'posts' => $this->posts->paginateLive($filters + [
                'sort' => $grid['sort_order'],
                'featured_first' => $grid['featured_first'],
            ], $perPage),
            'filters' => $filters,
            'grid' => $grid,
            'sidebar' => $this->sidebarData(),
            'sidebarSettings' => $this->pageSettings('blog-listing', 'sidebar', $this->sidebarDefaults()),
            'hero' => $this->heroFor('blog-listing'),
            // Blog Listing SEO (Meta Title/Description/Canonical/OG Image/…):
            // edited via the standard SEO tab on Admin → Pages → Blog Listing
            // (every Page already has one — no new admin UI needed).
            'listingPage' => $this->page('blog-listing'),
        ]);
    }

    public function show(Request $request, string $slug): View
    {
        $post = $this->posts->findLiveBySlug($slug);

        abort_if($post === null, 404);

        // Count unique-ish views (skip obvious admin previews).
        $this->blog->recordView($post);

        $related = $this->pageSettings('blog-detail', 'related', $this->relatedDefaults());

        return view('frontend.blog.show', [
            'post' => $post,
            'related' => $related['enabled']
                ? $this->posts->relatedFor($post, (int) $related['count'], (bool) $related['same_category_only'], (bool) $related['manual_only'], (string) $related['sort_order'])
                : collect(),
            'sidebar' => $this->sidebarData(),
            'sidebarSettings' => $this->pageSettings('blog-detail', 'sidebar', $this->sidebarDefaults()),
            'socialShare' => $this->pageSettings('blog-detail', 'social_share', $this->socialShareDefaults()),
            'commentsEnabled' => (bool) ($this->pageSettings('blog-detail', 'comments', ['enabled' => false])['enabled'] ?? false),
            'postContent' => $this->pageSettings('blog-detail', 'post_content', $this->postContentDefaults()),
            'hero' => $this->heroFor('blog-detail', $post),
        ]);
    }

    private function sidebarData(): array
    {
        return [
            'recent' => $this->posts->recent((int) setting('commerce.recent_posts_count', config('gsm.defaults.recent_posts_count'))),
            'popular' => $this->posts->popular(4),
            'archives' => $this->posts->archiveMonths(),
            'categories' => BlogCategory::query()
                ->where('is_active', true)
                ->withCount(['posts' => fn ($q) => $q->live()])
                ->orderBy('sort_order')
                ->get(),
            'tags' => BlogTag::query()
                ->withCount(['posts' => fn ($q) => $q->live()])
                ->whereHas('posts', fn ($q) => $q->live())
                ->orderByDesc('posts_count')
                ->limit(20)
                ->get(),
        ];
    }

    /**
     * The reused Hero section for a settings-carrier Page (never publicly
     * routed itself — see BlogPageSeeder). Returns null when the page/section
     * hasn't been seeded yet or is disabled, so the view's static banner
     * fallback still applies (same pattern as the Collection page). For Blog
     * Detail, the post's own title overrides the seeded heading.
     */
    private function heroFor(string $pageSlug, ?Post $post = null): ?array
    {
        $payload = $this->renderer->payloadFor($pageSlug);
        $entry = ($payload['sections'] ?? collect())->firstWhere('section.type', 'hero');

        if ($entry === null || $entry['section']->is_active !== true) {
            return null;
        }

        if ($post !== null) {
            $data = $entry['section']->data_desktop;
            $data['heading'] = $post->title;
            $entry['section']->data_desktop = $data;
        }

        return $entry;
    }

    /** One named settings group from a Blog settings-carrier Page, merged over defaults. */
    private function pageSettings(string $pageSlug, string $group, array $defaults): array
    {
        $stored = (array) ($this->page($pageSlug)?->setting($group, []) ?? []);

        return array_merge($defaults, $stored);
    }

    /**
     * Memoized per-request — 'blog-listing'/'blog-detail' are looked up
     * several times per request. array_key_exists (not ??=) so a genuine
     * "page not found" result is also cached, not re-queried each call.
     */
    private function page(string $pageSlug): ?Page
    {
        if (! array_key_exists($pageSlug, $this->pageCache)) {
            $this->pageCache[$pageSlug] = Page::query()
                ->where('slug', $pageSlug)
                ->with('seoMeta.ogMedia', 'seoMeta.twitterMedia')
                ->first();
        }

        return $this->pageCache[$pageSlug];
    }

    private function gridDefaults(): array
    {
        return [
            'posts_per_page' => null,
            'pagination_enabled' => true,
            'columns' => 2,
            'featured_first' => false,
            'sort_order' => 'latest',
            'show_date' => true,
            'show_category' => false,
            'show_author' => true,
            'show_read_more' => false,
            'show_featured_badge' => false,
        ];
    }

    private function sidebarDefaults(): array
    {
        return [
            'search' => true,
            'categories' => true,
            'recent' => true,
            'popular' => true,
            'tags' => false,
            'newsletter' => false,
        ];
    }

    private function relatedDefaults(): array
    {
        return [
            'enabled' => true,
            'count' => 3,
            'same_category_only' => false,
            'manual_only' => false,
            'sort_order' => 'latest',
        ];
    }

    private function socialShareDefaults(): array
    {
        return [
            'facebook' => true,
            'whatsapp' => true,
            'linkedin' => true,
            'twitter' => true,
            'copy_link' => false,
        ];
    }

    private function postContentDefaults(): array
    {
        return [
            // Reading Time is computed (Post::readingTimeMinutes()), not
            // stored — there's nothing to "edit," so it's shown as an extra
            // badge only when explicitly opted in. Off by default: does not
            // exist on the page today, so this preserves the exact current UI.
            'show_reading_time' => false,
        ];
    }
}
