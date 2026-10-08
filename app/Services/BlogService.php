<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BlogTag;
use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use App\Services\Concerns\GeneratesUniqueSlug;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BlogService
{
    use GeneratesUniqueSlug;

    public function __construct(protected PostRepositoryInterface $posts)
    {
    }

    public function createPost(array $data, int $editorUserId): Post
    {
        return DB::transaction(function () use ($data, $editorUserId): Post {
            /** @var Post $post */
            $post = $this->posts->create(
                $this->attributes($data, $editorUserId) + [
                    'slug' => $this->uniqueSlugFor(Post::class, $data['slug'] ?? $data['title']),
                ]
            );

            $this->syncRelations($post, $data);
            $this->flushSitemap();

            return $post;
        });
    }

    public function updatePost(Post $post, array $data): Post
    {
        return DB::transaction(function () use ($post, $data): Post {
            $attributes = $this->attributes($data, $post->user_id);

            if (! empty($data['slug']) && $data['slug'] !== $post->slug) {
                $attributes['slug'] = $this->uniqueSlugFor(Post::class, $data['slug'], $post->id);
            }

            $post = $this->posts->update($post, $attributes);
            $this->syncRelations($post, $data);
            $this->flushSitemap();

            return $post;
        });
    }

    public function deletePost(Post $post): void
    {
        $post->seoMeta()->delete();
        $this->posts->delete($post);
        $this->flushSitemap();
    }

    /** Console command entry: flip due scheduled posts to published. */
    public function publishDuePosts(): int
    {
        $due = $this->posts->dueScheduled();

        foreach ($due as $post) {
            $post->update(['status' => Post::STATUS_PUBLISHED]);
        }

        if ($due->isNotEmpty()) {
            $this->flushSitemap();
        }

        return $due->count();
    }

    /**
     * The sitemap is cached for an hour, so any change to what is published
     * has to drop it — otherwise a new post is missing (and a deleted one
     * still listed) until the TTL happens to expire.
     */
    private function flushSitemap(): void
    {
        app(SeoService::class)->flushSitemap();
    }

    public function recordView(Post $post): void
    {
        $post->increment('views_count');
    }

    /* ── internals ─────────────────────────────────────────────── */

    private function attributes(array $data, ?int $userId): array
    {
        $status = $data['status'] ?? Post::STATUS_DRAFT;

        // published without a date → now; scheduled requires future date
        // (validated in the FormRequest).
        $publishedAt = $data['published_at'] ?? null;
        if ($status === Post::STATUS_PUBLISHED && ! $publishedAt) {
            $publishedAt = now();
        }

        return [
            'title' => $data['title'],
            'user_id' => $data['user_id'] ?? $userId,
            'author_name' => $data['author_name'] ?? null,
            'excerpt' => $data['excerpt'] ?? null,
            'body' => $data['body'] ?? null,
            'featured_media_id' => $data['featured_media_id'] ?? null,
            'status' => $status,
            'is_featured' => (bool) ($data['is_featured'] ?? false),
            'published_at' => $publishedAt,
        ];
    }

    private function syncRelations(Post $post, array $data): void
    {
        $post->categories()->sync((array) ($data['blog_category_ids'] ?? []));
        $this->syncTags($post, (string) ($data['tags_csv'] ?? ''));
        $this->syncGallery($post, (string) ($data['gallery_ids'] ?? ''));
        $this->syncRelated($post, (array) ($data['related_ids'] ?? []));
        $post->syncSeoMeta($data);
    }

    /** "silk, wholesale ,New" → tags created on the fly, synced. */
    private function syncTags(Post $post, string $csv): void
    {
        $ids = collect(explode(',', $csv))
            ->map(fn ($name) => trim($name))
            ->filter()
            ->unique(fn ($name) => mb_strtolower($name))
            ->map(function (string $name) {
                return BlogTag::query()->firstOrCreate(
                    ['slug' => Str::slug($name)],
                    ['name' => $name]
                )->id;
            });

        $post->tags()->sync($ids);
    }

    private function syncGallery(Post $post, string $csv): void
    {
        $ids = array_filter(explode(',', $csv));

        DB::table('mediables')
            ->where('mediable_type', Post::class)
            ->where('mediable_id', $post->id)
            ->where('collection', 'gallery')
            ->delete();

        $rows = [];
        foreach (array_values($ids) as $position => $mediaId) {
            $rows[] = [
                'media_id' => (int) $mediaId,
                'mediable_type' => Post::class,
                'mediable_id' => $post->id,
                'collection' => 'gallery',
                'sort_order' => $position,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($rows !== []) {
            DB::table('mediables')->insert($rows);
        }
    }

    private function syncRelated(Post $post, array $ids): void
    {
        $sync = [];
        foreach (array_values($ids) as $position => $id) {
            if ((int) $id !== $post->id) {
                $sync[(int) $id] = ['sort_order' => $position];
            }
        }

        $post->relatedPosts()->sync($sync);
    }
}
