<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CollectionItem;
use App\Models\Media;
use App\Models\User;
use App\Support\Link;
use Database\Seeders\EkdantaSiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Collections (typed lists of content), stable media keys and link fields. */
class CollectionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate(config('gsm.roles.super_admin'), 'web');
        foreach (['collections.manage', 'pages.manage'] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
    }

    private function editor(string ...$permissions): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo($permissions);

        return $user;
    }

    private function media(string $key): Media
    {
        return Media::create(['key' => $key, 'disk' => 'public', 'path' => 'media/'.basename($key).'.jpg', 'filename' => basename($key).'.jpg', 'mime' => 'image/jpeg', 'type' => 'image', 'size' => 1]);
    }

    public function test_collections_need_their_permission(): void
    {
        $this->actingAs($this->editor('pages.manage'))->get('/cms/collections/case-studies')->assertForbidden();
        $this->actingAs($this->editor('collections.manage'))->get('/cms/collections/case-studies')->assertOk()->assertSee('Case studies');
        $this->actingAs($this->editor('collections.manage'))->get('/cms/collections/nonsense')->assertNotFound();
    }

    public function test_an_item_is_added_checked_and_edited(): void
    {
        $this->media('ekdanta/case-trilok-infratech');
        $editor = $this->editor('collections.manage');

        $this->actingAs($editor)->get('/cms/collections/case-studies/create')->assertOk()
            ->assertSee('name="data[brief]"', false)->assertSee('name="seo_meta_title"', false);

        $this->actingAs($editor)->post('/cms/collections/case-studies', [
            'title' => 'Trilok Infratech', 'status' => 'published',
            'data' => ['image' => 'ekdanta/case-trilok-infratech', 'colour' => '#0E2C54', 'instagram_url' => 'https://www.instagram.com/ekdanta.in/',
                'facts' => [['value' => '20+', 'label' => 'years of expertise'], ['value' => '', 'label' => '']]],
            'seo_meta_title' => 'Trilok Infratech case study',
        ])->assertRedirect();

        $item = CollectionItem::where('slug', 'trilok-infratech')->firstOrFail();
        $this->assertSame('ekdanta/case-trilok-infratech', $item->get('image'));
        $this->assertCount(1, $item->get('facts'), 'the empty row is dropped');
        $this->assertSame('Trilok Infratech case study', $item->seoMeta->meta_title);
        $this->assertSame(url('/work/trilok-infratech'), $item->url());

        $this->actingAs($editor)->post('/cms/collections/case-studies', [
            'title' => 'Bad', 'status' => 'published', 'data' => ['image' => 'ekdanta/missing', 'instagram_url' => 'javascript:alert(1)', 'colour' => 'red'],
        ])->assertSessionHasErrors(['data.image', 'data.instagram_url', 'data.colour']);

        $this->actingAs($editor)->post('/cms/collections/case-studies', ['title' => 'Copy', 'slug' => 'trilok-infratech', 'status' => 'published'])
            ->assertSessionHasErrors('slug');

        $this->actingAs($editor)->put('/cms/collections/case-studies/'.$item->id, ['title' => 'Trilok', 'slug' => 'trilok-infratech', 'status' => 'draft', 'data' => []])
            ->assertRedirect();
        $this->assertSame('draft', $item->fresh()->status);
    }

    public function test_drag_order_and_delete(): void
    {
        $editor = $this->editor('collections.manage');
        $a = CollectionItem::create(['collection' => 'reels', 'slug' => 'a', 'title' => 'A', 'sort_order' => 1]);
        $b = CollectionItem::create(['collection' => 'reels', 'slug' => 'b', 'title' => 'B', 'sort_order' => 2]);

        $this->actingAs($editor)->postJson('/cms/collections/reels/reorder', ['ids' => [$b->id, $a->id]])->assertOk();
        $this->assertSame(['b', 'a'], CollectionItem::query()->of('reels')->pluck('slug')->all());

        $this->actingAs($editor)->delete('/cms/collections/reels/'.$a->id)->assertRedirect();
        $this->assertSoftDeleted($a);
        $this->actingAs($editor)->get('/cms/collections/faqs/'.$b->id.'/edit')->assertNotFound();
    }

    public function test_the_design_collections_seed_once(): void
    {
        $seeder = new EkdantaSiteSeeder();
        $seeder->collections();
        $seeder->collections();

        $count = fn ($c) => CollectionItem::where('collection', $c)->count();
        $this->assertSame([6, 4, 26, 44, 5, 24, 7], array_map($count, ['case-studies', 'service-groups', 'services', 'clients', 'reels', 'faqs', 'team-roles']));

        $meta = CollectionItem::where('collection', 'services')->where('slug', 'meta-ads')->first();
        $this->assertTrue($meta->get('has_page'));
        $this->assertSame('DIGITAL · PERFORMANCE', $meta->get('group_label'));
        $this->assertSame(3, CollectionItem::where('collection', 'faqs')->where('data->group', 'meta-ads')->count());
        $this->assertSame('Food and beverage', CollectionItem::where('collection', 'clients')->where('slug', 'srk-dairy')->first()->get('industry'));
        $this->assertSame('[Name]', CollectionItem::where('collection', 'case-studies')->where('slug', 'valleyrides')->first()->get('quote_name'));
    }

    public function test_media_fields_take_ids_or_stable_keys(): void
    {
        $logo = $this->media('ekdanta/logo-colour');

        $this->assertSame($logo->id, media_ref('ekdanta/logo-colour')?->id);
        $this->assertSame($logo->id, media_ref((string) $logo->id)?->id);
        $this->assertNull(media_ref('ekdanta/nope'));
        $this->assertNull(media_ref('<script>'));
    }

    public function test_link_fields(): void
    {
        $this->assertSame('/contact', Link::href('/contact'));
        $this->assertSame('#c-brief', Link::href('#c-brief'));
        $this->assertSame('tel:+919099417987', Link::href('tel:+91 90994 17987'));
        $this->assertSame('https://wa.me/916354433194?text=Hi%20Ekdanta', Link::href('wa:+91 63544 33194|Hi Ekdanta'));
        $this->assertSame('#', Link::href('javascript:alert(1)'));
        $this->assertFalse(Link::valid('mailto:nobody'));
    }

    public function test_add_a_section_is_grouped_and_searchable(): void
    {
        $page = \App\Models\Page::create(['title' => 'P', 'slug' => 'p', 'status' => 'draft']);
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole(config('gsm.roles.super_admin'));

        $this->actingAs($admin)->get(route('admin.pages.edit', $page))->assertOk()
            ->assertSee('id="gsm-add-filter"', false)
            ->assertSee('<h6 class="dropdown-header" data-add-group>General</h6>', false);
    }
}
