<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CollectionItem;
use App\Models\Page;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Pages marked "Template for …" draw collection items and blog articles at their own addresses. */
class TemplatesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Role::findOrCreate(config('gsm.roles.super_admin'), 'web');
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(config('gsm.roles.super_admin'));

        return $user;
    }

    private function templatePage(string $slug, string $template, string $heading, string $status = Page::STATUS_PUBLISHED): Page
    {
        $page = Page::create(['title' => ucfirst($slug), 'slug' => $slug, 'status' => $status, 'settings' => ['template_for' => $template]]);
        $page->sections()->create(['type' => 'text-block', 'name' => 'T', 'sort_order' => 0, 'is_active' => true, 'data_desktop' => ['heading' => $heading]]);

        return $page;
    }

    private function caseStudy(string $slug, string $title, string $status = CollectionItem::STATUS_PUBLISHED): CollectionItem
    {
        return CollectionItem::create(['collection' => 'case-studies', 'slug' => $slug, 'title' => $title, 'status' => $status, 'data' => []]);
    }

    public function test_a_case_study_opens_at_its_own_address(): void
    {
        $this->templatePage('case-study-template', 'case_study', 'Template heading');
        $item = $this->caseStudy('heaven-solar', 'Heaven Solar');
        $item->syncSeoMeta(['seo_meta_description' => 'Solar case study']);
        $this->caseStudy('valleyrides', 'ValleyRides', CollectionItem::STATUS_DRAFT);

        $this->get('/work/heaven-solar')->assertOk()
            ->assertSee('Template heading')
            ->assertSee('<title>Heaven Solar', false)
            ->assertSee('Solar case study', false);

        $this->get('/work/no-such-case')->assertNotFound();
        $this->get('/work/valleyrides')->assertNotFound();
        $this->get('/case-study-template')->assertNotFound();
    }

    public function test_the_builder_previews_any_item_including_drafts(): void
    {
        $page = $this->templatePage('case-study-template', 'case_study', 'Template heading', Page::STATUS_DRAFT);
        $this->caseStudy('heaven-solar', 'Heaven Solar');
        $this->caseStudy('valleyrides', 'ValleyRides', CollectionItem::STATUS_DRAFT);
        $admin = $this->admin();

        $this->actingAs($admin)->get('/case-study-template?preview=1&item=valleyrides')->assertOk()->assertSee('<title>ValleyRides', false);
        $this->actingAs($admin)->get('/work/valleyrides?preview=1')->assertOk();
        $this->get('/work/heaven-solar')->assertNotFound(); // the template page is still a draft

        $this->actingAs($admin)->get(route('admin.pages.edit', $page))->assertOk()
            ->assertSee('id="gsm-preview-item"', false)
            ->assertSee('<option value="valleyrides">ValleyRides</option>', false);
    }

    public function test_only_services_with_a_page_open(): void
    {
        $this->templatePage('service-template', 'service', 'Service template');
        CollectionItem::create(['collection' => 'services', 'slug' => 'meta-ads', 'title' => 'Meta ads', 'data' => ['has_page' => true]]);
        CollectionItem::create(['collection' => 'services', 'slug' => 'seo', 'title' => 'SEO', 'data' => ['has_page' => false]]);

        $this->get('/services/meta-ads')->assertOk()->assertSee('Service template');
        $this->get('/services/seo')->assertNotFound();
    }

    public function test_blog_pages_from_the_builder_or_the_built_in_blog(): void
    {
        $post = Post::create(['title' => 'Reels for factories', 'slug' => 'reels-for-factories', 'body' => 'x', 'status' => Post::STATUS_PUBLISHED, 'published_at' => now()]);

        // No builder pages: the built-in listing and article.
        $this->get('/blog')->assertOk()->assertSee('Reels for factories');
        $this->get('/blog/reels-for-factories')->assertOk();

        // While the builder's blog page and article template are drafts, the built-in blog keeps answering.
        $blog = Page::create(['title' => 'Blog', 'slug' => 'blog', 'status' => Page::STATUS_DRAFT]);
        $blog->sections()->create(['type' => 'text-block', 'name' => 'T', 'sort_order' => 0, 'is_active' => true, 'data_desktop' => ['heading' => 'Built blog']]);
        $template = $this->templatePage('article-template', 'post', 'Article template');
        $template->update(['status' => Page::STATUS_DRAFT]);
        $this->get('/blog')->assertOk()->assertSee('Reels for factories')->assertDontSee('Built blog');
        $this->get('/blog/reels-for-factories')->assertOk()->assertDontSee('Article template');

        // Published, a page at /blog and the page marked as the article template take over.
        $blog->update(['status' => Page::STATUS_PUBLISHED]);
        $template->update(['status' => Page::STATUS_PUBLISHED]);
        app(\App\Services\PageRenderService::class)->flushCache();

        $this->get('/blog')->assertOk()->assertSee('Built blog');
        $this->get('/blog/reels-for-factories')->assertOk()->assertSee('Article template')->assertSee('<title>Reels for factories', false);
        $this->get('/blog/nope')->assertNotFound();
        $this->get('/blogs/reels-for-factories')->assertRedirect(url('/blog/reels-for-factories'));
    }

    public function test_template_setting_saves_from_the_page_form(): void
    {
        $page = Page::create(['title' => 'X', 'slug' => 'x', 'status' => 'draft']);

        $this->actingAs($this->admin())->put(route('admin.pages.update', $page), ['title' => 'X', 'slug' => 'x', 'status' => 'draft', 'template_for' => 'service'])
            ->assertSessionHasNoErrors();
        $this->assertSame('service', $page->fresh()->setting('template_for'));

        $this->put(route('admin.pages.update', $page), ['title' => 'X', 'slug' => 'x', 'status' => 'draft', 'template_for' => 'shop'])
            ->assertSessionHasErrors('template_for');
    }
}
