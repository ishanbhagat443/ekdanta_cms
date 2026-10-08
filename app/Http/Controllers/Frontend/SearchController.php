<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Live search suggestions: pages + blog posts. */
class SearchController extends Controller
{
    public function suggest(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json(['pages' => [], 'posts' => []]);
        }

        $pages = Page::query()
            ->where('status', Page::STATUS_PUBLISHED)
            ->where('title', 'like', "%{$q}%")
            ->limit(6)
            ->get()
            ->map(fn (Page $p) => [
                'name' => $p->title,
                'url' => url('/'.$p->slug),
            ]);

        $posts = Post::query()
            ->live()
            ->where('title', 'like', "%{$q}%")
            ->limit(6)
            ->get()
            ->map(fn (Post $p) => [
                'name' => $p->title,
                'url' => route('front.blog.show', $p->slug),
            ]);

        return response()->json(['pages' => $pages, 'posts' => $posts]);
    }
}
