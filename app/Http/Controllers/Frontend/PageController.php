<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\PageRenderService;
use App\Support\PageRoles;
use App\Support\Templates;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function __construct(protected PageRenderService $renderer)
    {
    }

    public function home(Request $request): View
    {
        // The page marked as home (published, or any state in a preview);
        // sites that never marked one keep using the "home" slug.
        $previewing = $this->previewing($request);
        $page = PageRoles::page('home', ! $previewing) ?? ($previewing ? Page::query()->where('slug', 'home')->first() : null);
        $payload = $previewing && $page ? $this->renderer->livePayloadFor($page) : $this->renderer->payloadFor($page->slug ?? 'home');

        // No home page yet (fresh install): an empty shell.
        return $this->render($payload, true);
    }

    public function show(Request $request, string $slug): View|RedirectResponse
    {
        // Admins may preview drafts with ?preview=1 (uncached).
        if ($this->previewing($request)) {
            $page = Page::query()->where('slug', $slug)->firstOrFail();
            $payload = $this->renderer->livePayloadFor($page);

            // A template page previews with the item picked in the builder (or the first one).
            if ($template = $page->setting('template_for')) {
                view()->share('templateItem', Templates::item($template, $request->query('item'), true) ?? Templates::item($template, null, true));
            }
        } else {
            $payload = $this->renderer->payloadFor($slug);
            abort_if($payload === null, 404);
            // A template page is not a destination: its items are (/work/…, /services/…, /blog/…).
            abort_if((bool) $payload['page']->setting('template_for'), 404);

            // The home page lives at "/" only.
            if (PageRoles::page('home')?->is($payload['page'])) {
                return redirect('/', 301);
            }
        }

        return $this->render($payload, false);
    }

    /**
     * /work/{slug}, /services/{slug}, /blog/{slug}: the page marked as this
     * template, drawing one item. A blog article without a template page
     * keeps the blog's own article view.
     */
    public function template(Request $request, string $slug, string $template): View|\Symfony\Component\HttpFoundation\Response
    {
        $previewing = $this->previewing($request);
        $page = Templates::page($template, ! $previewing);

        if ($page === null && $template === 'post') {
            return app(BlogController::class)->show($request, $slug);
        }

        $item = $page ? Templates::item($template, $slug, $previewing) : null;
        abort_if($item === null, 404);

        if ($template === 'post' && ! $previewing) {
            app(\App\Services\BlogService::class)->recordView($item);
        }

        view()->share('templateItem', $item);

        return $this->render($previewing ? $this->renderer->livePayloadFor($page) : $this->renderer->payloadFor($page->slug), false);
    }

    /** One render path for pages, also used for the 404 page. */
    public static function render(?array $payload, bool $isHome): View
    {
        $page = $payload['page'] ?? null;
        $sections = $payload['sections'] ?? collect();

        // The layout's header and footer read these (Page settings). A section
        // that carries its own footer (Ekdanta · Big Brand Finale) hides the
        // site footer by itself.
        $registry = app(\App\Services\SectionTypes\SectionTypeRegistry::class);
        view()->share('currentPage', $page);
        view()->share('hideFooter', $page !== null && (
            $page->setting('show_footer', true) === false
            || $sections->contains(fn ($entry) => $registry->has($entry['section']->type) && $registry->find($entry['section']->type)->hidesFooter())
        ));

        return view('frontend.pages.show', ['page' => $page, 'sections' => $sections, 'isHome' => $isHome]);
    }

    private function previewing(Request $request): bool
    {
        return $request->boolean('preview') && ($request->user()?->can('pages.manage') ?? false);
    }
}
