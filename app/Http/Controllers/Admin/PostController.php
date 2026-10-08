<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PostRequest;
use App\Models\BlogCategory;
use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\BlogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct(
        protected PostRepositoryInterface $posts,
        protected UserRepositoryInterface $users,
        protected BlogService $blog,
    ) {
        $this->middleware('can:blog.manage');
    }

    public function index(Request $request): View
    {
        return view('admin.posts.index', [
            'posts' => $this->posts->paginateForAdmin(
                15,
                $request->string('search')->toString() ?: null,
                $request->string('status')->toString() ?: null
            ),
            'search' => $request->string('search')->toString(),
            'status' => $request->string('status')->toString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.posts.create', $this->formData());
    }

    public function store(PostRequest $request): RedirectResponse
    {
        $post = $this->blog->createPost($request->validated(), $request->user()->id);

        return redirect()->route('admin.posts.edit', $post)->with('success', 'Post created.');
    }

    public function edit(Post $post): View
    {
        $post->load(['categories', 'tags', 'relatedPosts:posts.id', 'seoMeta', 'featuredMedia', 'galleryMedia']);

        return view('admin.posts.edit', $this->formData($post->id) + ['post' => $post]);
    }

    public function update(PostRequest $request, Post $post): RedirectResponse
    {
        $this->blog->updatePost($post, $request->validated());

        return redirect()->route('admin.posts.edit', $post->fresh())->with('success', 'Post saved.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->blog->deletePost($post);

        return redirect()->route('admin.posts.index')->with('success', 'Post deleted.');
    }

    private function formData(?int $excludeId = null): array
    {
        return [
            'allCategories' => BlogCategory::query()->orderBy('sort_order')->orderBy('name')->get(),
            'allAuthors' => $this->users->all(),
            'relatedOptions' => $this->posts->optionsForRelated($excludeId),
        ];
    }
}
