<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Services\Concerns\GeneratesUniqueSlug;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Blog categories + tags on one compact screen.
 */
class BlogCategoryController extends Controller
{
    use GeneratesUniqueSlug;

    public function __construct()
    {
        $this->middleware('can:blog.manage');
    }

    public function index(): View
    {
        return view('admin.blog-categories.index', [
            'categories' => BlogCategory::query()->withCount('posts')->orderBy('sort_order')->orderBy('name')->get(),
            'tags' => BlogTag::query()->withCount('posts')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:blog_categories,name'],
        ]);

        BlogCategory::create([
            'name' => $data['name'],
            'slug' => $this->uniqueSlugFor(BlogCategory::class, $data['name']),
            'sort_order' => (int) BlogCategory::query()->max('sort_order') + 1,
        ]);

        return redirect()->route('admin.blog-categories.index')->with('success', 'Category added.');
    }

    public function update(Request $request, BlogCategory $blogCategory): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:blog_categories,name,'.$blogCategory->id],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $blogCategory->update([
            'name' => $data['name'],
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        return redirect()->route('admin.blog-categories.index')->with('success', 'Category updated.');
    }

    public function destroy(BlogCategory $blogCategory): RedirectResponse
    {
        $blogCategory->delete(); // pivot cascades; posts untouched

        return redirect()->route('admin.blog-categories.index')->with('success', 'Category deleted.');
    }

    public function destroyTag(BlogTag $tag): RedirectResponse
    {
        $tag->delete();

        return redirect()->route('admin.blog-categories.index')->with('success', 'Tag deleted.');
    }
}
