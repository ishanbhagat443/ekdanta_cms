<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BlogCmsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate(config('gsm.roles.super_admin'), 'web');
        Permission::findOrCreate('blog.manage', 'web');
    }

    private function admin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(config('gsm.roles.super_admin'));

        return $user;
    }

    private function livePost(array $overrides = []): Post
    {
        return Post::create(array_merge([
            'title' => 'Live Post',
            'slug' => 'live-post-'.uniqid(),
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => now()->subHour(),
            'body' => 'Body text about georgette fabric.',
        ], $overrides));
    }

    public function test_requires_permission(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user)->get('/cms/posts')->assertForbidden();
    }

    public function test_full_post_create_with_tags_gallery_related_seo(): void
    {
        $admin = $this->admin();
        $category = BlogCategory::create(['name' => 'Fabric Guides', 'slug' => 'fabric-guides']);
        $other = $this->livePost(['title' => 'Other Post']);
        $img = Media::create(['disk' => 'public', 'path' => 'media/f.jpg', 'filename' => 'f.jpg', 'mime' => 'image/jpeg', 'type' => 'image', 'size' => 100]);
        $g1 = Media::create(['disk' => 'public', 'path' => 'media/g1.jpg', 'filename' => 'g1.jpg', 'mime' => 'image/jpeg', 'type' => 'image', 'size' => 100]);

        $this->actingAs($admin)->post('/cms/posts', [
            'title' => 'The Art of Crafting Timeless Elegance',
            'author_name' => 'Studio Desk',
            'excerpt' => 'Behind the scenes at the studio.',
            'body' => '<p>Fashion is more than fabric and stitching.</p>',
            'featured_media_id' => $img->id,
            'gallery_ids' => (string) $g1->id,
            'status' => 'published',
            'blog_category_ids' => [$category->id],
            'tags_csv' => 'silk, Wholesale, silk', // dupe collapses
            'related_ids' => [$other->id],
            'seo_meta_title' => 'Crafting Elegance | GSM Journal',
        ])->assertRedirect();

        $post = Post::where('slug', 'the-art-of-crafting-timeless-elegance')->firstOrFail();

        $this->assertNotNull($post->published_at); // auto-set on publish
        $this->assertSame('Studio Desk', $post->displayAuthor());
        $this->assertSame([$category->id], $post->categories->pluck('id')->all());
        $this->assertSame(['silk', 'wholesale'], $post->tags->pluck('slug')->sort()->values()->all());
        $this->assertSame([$g1->id], $post->galleryMedia->pluck('id')->all());
        $this->assertSame([$other->id], $post->relatedPosts->pluck('id')->all());
        $this->assertSame('Crafting Elegance | GSM Journal', $post->seoMeta->meta_title);
    }

    public function test_scheduled_post_flow(): void
    {
        $admin = $this->admin();

        // Scheduled time in the past → rejected.
        $this->actingAs($admin)->post('/cms/posts', [
            'title' => 'Bad Schedule', 'status' => 'scheduled',
            'published_at' => now()->subDay()->format('Y-m-d\TH:i'),
        ])->assertSessionHasErrors('published_at');

        // Valid future schedule.
        $this->actingAs($admin)->post('/cms/posts', [
            'title' => 'Festival Collection Preview', 'status' => 'scheduled',
            'published_at' => now()->addDay()->format('Y-m-d\TH:i'),
        ])->assertRedirect();

        $post = Post::where('slug', 'festival-collection-preview')->firstOrFail();

        // Not visible publicly yet.
        $this->get('/blog/'.$post->slug)->assertNotFound();
        $this->assertFalse($post->isLive());

        // Command does nothing before due time.
        $this->artisan('gsm:publish-scheduled-posts')->assertSuccessful();
        $this->assertSame('scheduled', $post->fresh()->status);

        // Time passes → command publishes it.
        $this->travel(2)->days();
        $this->artisan('gsm:publish-scheduled-posts')->assertSuccessful();
        $this->assertSame('published', $post->fresh()->status);
        $this->get('/blog/'.$post->slug)->assertOk();
        $this->travelBack();
    }

    public function test_listing_search_category_tag_archive_filters(): void
    {
        $category = BlogCategory::create(['name' => 'News', 'slug' => 'news']);
        $tag = BlogTag::create(['name' => 'Silk', 'slug' => 'silk']);

        $a = $this->livePost(['title' => 'Georgette Fabric Guide', 'published_at' => now()->subMonths(2), 'body' => 'All about premium fabrics.']);
        $b = $this->livePost(['title' => 'Factory Tour', 'published_at' => now()->subDay(), 'body' => 'A walk through our production unit.']);
        $a->categories()->attach($category);
        $a->tags()->attach($tag);

        // NOTE: sidebar "Recent Posts" always lists latest posts, so negative
        // assertions must target the excerpt (grid cards render excerpts,
        // sidebar mini-cards do not).
        $a->update(['excerpt' => 'GEORGETTE-EXCERPT-MARKER']);
        $b->update(['excerpt' => 'FACTORY-EXCERPT-MARKER']);

        // Search
        $this->get('/blog?search=Georgette')->assertOk()
            ->assertSee('GEORGETTE-EXCERPT-MARKER')->assertDontSee('FACTORY-EXCERPT-MARKER');
        // Category
        $this->get('/blog?category=news')->assertOk()
            ->assertSee('GEORGETTE-EXCERPT-MARKER')->assertDontSee('FACTORY-EXCERPT-MARKER');
        // Tag
        $this->get('/blog?tag=silk')->assertOk()
            ->assertSee('GEORGETTE-EXCERPT-MARKER')->assertDontSee('FACTORY-EXCERPT-MARKER');
        // Archive month of $b only
        $this->get('/blog?archive='.now()->subDay()->format('Y-m'))
            ->assertOk()->assertSee('FACTORY-EXCERPT-MARKER')->assertDontSee('GEORGETTE-EXCERPT-MARKER');
        // No matches → empty state
        $this->get('/blog?search=zzz-no-match')->assertOk()->assertSee('No posts found.');
        // Archive list shows month labels
        $this->get('/blog')->assertSee(now()->subMonths(2)->format('F Y'));
    }

    public function test_draft_and_future_posts_hidden_from_listing(): void
    {
        $this->livePost(['title' => 'Visible Post']);
        Post::create(['title' => 'Draft Post', 'slug' => 'draft-post', 'status' => 'draft']);
        Post::create(['title' => 'Future Post', 'slug' => 'future-post', 'status' => 'published', 'published_at' => now()->addWeek()]);

        $response = $this->get('/blog')->assertOk();
        $response->assertSee('Visible Post');
        $response->assertDontSee('Draft Post');
        $response->assertDontSee('Future Post');
    }

    public function test_detail_renders_breadcrumb_schema_og_and_counts_views(): void
    {
        $post = $this->livePost(['title' => 'Elegance Story', 'excerpt' => 'A story of craft.']);

        $response = $this->get('/blog/'.$post->slug)->assertOk();

        $response->assertSee('Elegance Story');
        $response->assertSee('breadcrumb', false);
        $response->assertSee('BlogPosting', false);          // JSON-LD article schema
        $response->assertSee('BreadcrumbList', false);       // JSON-LD breadcrumb
        $response->assertSee('og:title', false);             // OpenGraph
        $response->assertSee('og:type" content="article', false);

        $this->assertSame(1, $post->fresh()->views_count);
        $this->get('/blog/'.$post->slug);
        $this->assertSame(2, $post->fresh()->views_count);
    }

    public function test_related_manual_first_then_same_category_fallback(): void
    {
        $category = BlogCategory::create(['name' => 'Craft', 'slug' => 'craft']);

        $main = $this->livePost(['title' => 'Main Post']);
        $manual = $this->livePost(['title' => 'Manual Pick']);
        $sameCat = $this->livePost(['title' => 'Same Category Post']);
        $unrelated = $this->livePost(['title' => 'Unrelated Post']);

        $main->categories()->attach($category);
        $sameCat->categories()->attach($category);
        $main->relatedPosts()->attach($manual->id, ['sort_order' => 0]);

        $repo = app(\App\Repositories\Contracts\PostRepositoryInterface::class);
        $related = $repo->relatedFor($main->fresh(), 3);

        $titles = $related->pluck('title')->all();
        $this->assertSame('Manual Pick', $titles[0]);           // manual first
        $this->assertContains('Same Category Post', $titles);   // category fallback
        $this->assertNotContains('Main Post', $titles);         // never itself
    }

    public function test_popular_ordering_by_views(): void
    {
        $low = $this->livePost(['title' => 'Low Views', 'views_count' => 2]);
        $high = $this->livePost(['title' => 'High Views', 'views_count' => 50]);

        $repo = app(\App\Repositories\Contracts\PostRepositoryInterface::class);

        $this->assertSame(
            ['High Views', 'Low Views'],
            $repo->popular(2)->pluck('title')->all()
        );
    }

    public function test_blog_category_and_tag_management(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/cms/blog-categories', ['name' => 'Guides'])->assertRedirect();
        $category = BlogCategory::where('slug', 'guides')->firstOrFail();

        // Duplicate rejected
        $this->actingAs($admin)->post('/cms/blog-categories', ['name' => 'Guides'])
            ->assertSessionHasErrors('name');

        // Toggle inactive
        $this->actingAs($admin)->put("/cms/blog-categories/{$category->id}", [
            'name' => 'Guides', 'is_active' => '0',
        ]);
        $this->assertFalse($category->fresh()->is_active);

        // Tag delete
        $tag = BlogTag::create(['name' => 'Old', 'slug' => 'old']);
        $this->actingAs($admin)->delete("/cms/blog-tags/{$tag->id}");
        $this->assertDatabaseMissing('blog_tags', ['slug' => 'old']);
    }

    public function test_admin_can_edit_blog_listing_cms_settings_via_pages_form(): void
    {
        Permission::findOrCreate('pages.manage', 'web');
        $admin = $this->admin();
        $listing = \App\Models\Page::create(['title' => 'Blog Listing', 'slug' => 'blog-listing', 'status' => 'draft']);

        $this->actingAs($admin)->put("/cms/pages/{$listing->id}", [
            'title' => 'Blog Listing',
            'slug' => 'blog-listing',
            'status' => 'draft',
            'blog_grid' => [
                'posts_per_page' => '6',
                'pagination_enabled' => '0',
                'columns' => '3',
                'featured_first' => '1',
                'sort_order' => 'popular',
                'show_date' => '1',
                'show_category' => '1',
                'show_author' => '1',
                'show_read_more' => '1',
                'show_featured_badge' => '1',
            ],
            'blog_sidebar' => [
                'search' => '1', 'categories' => '1', 'recent' => '1', 'popular' => '1',
                'tags' => '1', 'newsletter' => '1',
            ],
        ])->assertRedirect();

        $listing->refresh();
        $this->assertSame([
            'posts_per_page' => 6, 'pagination_enabled' => false, 'columns' => 3,
            'featured_first' => true, 'sort_order' => 'popular', 'show_date' => true,
            'show_category' => true, 'show_author' => true, 'show_read_more' => true,
            'show_featured_badge' => true,
        ], $listing->setting('grid'));
        $this->assertTrue($listing->setting('sidebar')['tags']);
        $this->assertTrue($listing->setting('sidebar')['newsletter']);

        // Frontend reflects the saved settings without any code changes.
        $category = BlogCategory::create(['name' => 'Guides', 'slug' => 'guides-2']);
        $tag = BlogTag::create(['name' => 'Silk', 'slug' => 'silk-2']);
        $post = $this->livePost(['title' => 'Featured Post', 'is_featured' => true]);
        $post->categories()->attach($category->id);
        $post->tags()->attach($tag->id);

        $response = $this->get('/blog')->assertOk();
        $response->assertSee('col-md-4', false);       // 3-column grid class
        $response->assertSee('Featured', false);        // featured badge
        $response->assertSee('Read More', false);        // read-more link
        $response->assertSee('Guides', false);           // category badge
        $response->assertSee('Tags', false);              // sidebar Tags widget heading is opt-in via BlogTag with a live post
        $response->assertSee('Newsletter', false);        // sidebar Newsletter widget
    }

    public function test_admin_can_edit_blog_detail_cms_settings_via_pages_form(): void
    {
        Permission::findOrCreate('pages.manage', 'web');
        $admin = $this->admin();
        $detail = \App\Models\Page::create(['title' => 'Blog Detail', 'slug' => 'blog-detail', 'status' => 'draft']);

        $this->actingAs($admin)->put("/cms/pages/{$detail->id}", [
            'title' => 'Blog Detail',
            'slug' => 'blog-detail',
            'status' => 'draft',
            'blog_related' => [
                'enabled' => '1', 'count' => '2', 'same_category_only' => '1',
                'manual_only' => '0', 'sort_order' => 'oldest',
            ],
            'blog_sidebar' => [
                'search' => '1', 'categories' => '1', 'recent' => '1', 'popular' => '1',
                'tags' => '0', 'newsletter' => '0',
            ],
            'blog_social_share' => [
                'facebook' => '0', 'whatsapp' => '0', 'linkedin' => '0', 'twitter' => '0', 'copy_link' => '1',
            ],
            'blog_comments' => ['enabled' => '1'],
            'blog_post_content' => ['show_reading_time' => '1'],
        ])->assertRedirect();

        $detail->refresh();
        $this->assertSame([
            'enabled' => true, 'count' => 2, 'same_category_only' => true,
            'manual_only' => false, 'sort_order' => 'oldest',
        ], $detail->setting('related'));
        $this->assertSame([
            'facebook' => false, 'whatsapp' => false, 'linkedin' => false, 'twitter' => false, 'copy_link' => true,
        ], $detail->setting('social_share'));
        $this->assertTrue($detail->setting('comments')['enabled']);
        $this->assertTrue($detail->setting('post_content')['show_reading_time']);

        // Frontend reflects the saved settings without any code changes.
        $post = $this->livePost(['title' => 'Detail Post']);
        $response = $this->get("/blog/{$post->slug}")->assertOk();
        $response->assertSee('min read', false);              // reading time badge (opt-in)
        $response->assertSee('data-gsm-copy-link', false);     // copy-link button (opt-in)
        $response->assertDontSee('fa-facebook-f', false);      // disabled share icons hidden
    }
}
