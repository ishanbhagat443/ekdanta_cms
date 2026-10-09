<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\PageRenderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function __construct(protected PageRenderService $renderer)
    {
    }

    public function home(Request $request): View
    {
        // Admins may preview a draft home page with ?preview=1 (uncached).
        $draft = $this->previewing($request) ? Page::query()->where('slug', 'home')->first() : null;
        $payload = $draft ? $this->renderer->livePayloadFor($draft) : $this->renderer->payloadFor('home');

        // No published home page yet (fresh install) — render empty shell.
        return view('frontend.pages.show', [
            'page' => $payload['page'] ?? null,
            'sections' => $payload['sections'] ?? collect(),
            'isHome' => true,
        ]);
    }

    public function show(Request $request, string $slug): View
    {
        // Admins may preview drafts with ?preview=1 (uncached).
        if ($this->previewing($request)) {
            $page = Page::query()->where('slug', $slug)->firstOrFail();
            $payload = $this->renderer->livePayloadFor($page);
        } else {
            $payload = $this->renderer->payloadFor($slug);
            abort_if($payload === null, 404);
        }

        return view('frontend.pages.show', [
            'page' => $payload['page'],
            'sections' => $payload['sections'],
            'isHome' => false,
        ]);
    }

    private function previewing(Request $request): bool
    {
        return $request->boolean('preview') && ($request->user()?->can('pages.manage') ?? false);
    }
}
