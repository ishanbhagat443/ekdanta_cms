<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Search-as-you-type source for the menu item "reference" picker
 * (Page / Blog Post / Blog Category).
 */
class MenuReferenceController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:menus.manage');
    }

    public function search(Request $request, string $type): JsonResponse
    {
        abort_unless(array_key_exists($type, MenuItem::REFERENCE_TYPES), 404);

        $term = (string) $request->string('q');

        $results = match ($type) {
            'page' => Page::query()->where('status', Page::STATUS_PUBLISHED)
                ->when($term, fn ($q) => $q->where('title', 'like', "%{$term}%"))
                ->orderBy('title')->limit(20)->get(['id', 'title as text']),
            'blog' => Post::query()->where('status', Post::STATUS_PUBLISHED)
                ->when($term, fn ($q) => $q->where('title', 'like', "%{$term}%"))
                ->orderBy('title')->limit(20)->get(['id', 'title as text']),
            'blog_category' => BlogCategory::query()->where('is_active', true)
                ->when($term, fn ($q) => $q->where('name', 'like', "%{$term}%"))
                ->orderBy('name')->limit(20)->get(['id', 'name as text']),
        };

        return response()->json(['results' => $results]);
    }
}
