<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CollectionItem;
use App\Models\Media;
use App\Models\Page;
use App\Models\User;
use App\Services\SectionBuilderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Pages and saved blocks as JSON: media by key, an optional bundle, and the editor's checks on the way in. */
class ContentTransferTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Role::findOrCreate(config('gsm.roles.super_admin'), 'web');
        foreach (['pages.manage', 'developer.mode'] as $perm) {
            Permission::findOrCreate($perm, 'web');
        }
    }

    private function admin(bool $developer = true): User
    {
        $user = User::factory()->create(['is_active' => true]);
        if ($developer) {
            $user->assignRole(config('gsm.roles.super_admin'));
        } else {
            $user->assignRole(Role::findOrCreate('Editor', 'web')->givePermissionTo('pages.manage'));
        }

        return $user;
    }

    private function upload(User $user, string $name): Media
    {
        $this->actingAs($user)->post('/cms/media', ['files' => [UploadedFile::fake()->image($name, 40, 40)]])->assertCreated();

        return Media::query()->latest('id')->firstOrFail();
    }

    private function exportOf(User $user, Page $page, bool $bundle = false): array
    {
        return json_decode($this->actingAs($user)->get(route('admin.pages.export', [$page, 'bundle' => $bundle ? 1 : null]))->assertOk()->streamedContent(), true);
    }

    private function import(User $user, array $payload): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($user)->post(route('admin.pages.import'), ['file' => UploadedFile::fake()->createWithContent('page.json', json_encode($payload))]);
    }

    public function test_a_page_round_trips_with_its_sections_motion_and_settings(): void
    {
        $admin = $this->admin();
        $logo = $this->upload($admin, 'logo.png');
        $logo->forceFill(['key' => 'ekdanta/logo-colour'])->save();
        $page = Page::create(['title' => 'Contact', 'slug' => 'contact', 'status' => Page::STATUS_PUBLISHED,
            'settings' => ['header' => ['variant' => 'pill', 'cta_label' => 'Talk to us', 'menu_id' => 99], 'code' => ['css' => 'body{}']]]);
        $section = app(SectionBuilderService::class)->addSection($page, 'page', 'ekdanta-finale');
        $section->update(['data_desktop' => array_replace($section->data_desktop, ['logo' => $logo->id]), 'data_mobile' => ['line_1' => 'Phone words'],
            'style' => ['motion' => ['desktop' => ['tracks' => ['big' => ['enabled' => false]]]]]]);

        $payload = $this->exportOf($admin, $page);
        $this->assertSame(['format' => 'ekdanta-cms', 'kind' => 'page'], array_slice($payload, 0, 2));
        $this->assertSame('ekdanta/logo-colour', $payload['sections'][0]['data_desktop']['logo'], 'media travels by its key');
        $this->assertArrayNotHasKey('menu_id', $payload['page']['settings']['header'], 'menus are this site\'s own');

        $this->import($admin, $payload)->assertRedirect();
        $copy = Page::query()->where('slug', 'contact-2')->firstOrFail();
        $this->assertSame(Page::STATUS_DRAFT, $copy->status);
        $this->assertSame('pill', $copy->settings['header']['variant']);
        $this->assertSame('body{}', $copy->settings['code']['css']);
        $s = $copy->sections()->firstOrFail();
        $this->assertSame('ekdanta/logo-colour', $s->data_desktop['logo']);
        $this->assertSame('Phone words', $s->data_mobile['line_1']);
        $this->assertFalse($s->style['motion']['desktop']['tracks']['big']['enabled']);
    }

    public function test_an_import_is_checked_like_the_editor(): void
    {
        $page = Page::create(['title' => 'Promo', 'slug' => 'promo', 'status' => Page::STATUS_PUBLISHED, 'settings' => ['code' => ['js' => 'alert(1)']]]);
        $section = app(SectionBuilderService::class)->addSection($page, 'page', 'ekdanta-finale');
        $section->update(['style' => ['custom_js' => 'alert(2)', 'motion' => ['desktop' => ['tracks' => ['x' => ['part' => 'big', 'preset' => 'BAD<script>']]]]]]);
        $payload = $this->exportOf($this->admin(), $page);

        // Without Developer Mode: page code and section code are dropped, motion is sanitised.
        $this->import($this->admin(false), $payload)->assertRedirect();
        $copy = Page::query()->where('slug', 'promo-2')->firstOrFail();
        $this->assertArrayNotHasKey('code', (array) $copy->settings);
        $style = $copy->sections()->firstOrFail()->style;
        $this->assertArrayNotHasKey('custom_js', $style);
        $this->assertArrayNotHasKey('preset', $style['motion']['desktop']['tracks']['x']);

        // A field over its limit, an unknown type, or not an export at all: refused.
        $long = $payload;
        $long['sections'][0]['data_desktop']['line_1'] = str_repeat('x', 5000);
        $this->import($this->admin(), $long)->assertSessionHasErrors('file');
        $unknown = $payload;
        $unknown['sections'][0]['type'] = 'no-such-type';
        $this->import($this->admin(), $unknown)->assertSessionHasErrors('file');
        $this->import($this->admin(), ['title' => 'x'])->assertSessionHasErrors('file');
        $this->assertSame(1, Page::query()->where('slug', 'like', 'promo-%')->count(), 'nothing half-imported');
    }

    public function test_a_bundle_brings_media_and_collection_items_the_other_site_lacks(): void
    {
        $admin = $this->admin();
        $photo = $this->upload($admin, 'team.jpg');
        CollectionItem::create(['collection' => 'clients', 'slug' => 'acme', 'title' => 'Acme', 'status' => 'published', 'data' => ['industry' => 'Manufacturing']]);
        $page = Page::create(['title' => 'About', 'slug' => 'about', 'status' => Page::STATUS_PUBLISHED]);
        $section = app(SectionBuilderService::class)->addSection($page, 'page', 'ekdanta-finale');
        $section->update(['data_desktop' => array_replace($section->data_desktop, ['logo' => $photo->id])]);

        $payload = $this->exportOf($admin, $page, true);
        $ref = $payload['sections'][0]['data_desktop']['logo'];
        $this->assertStringStartsWith('upload/'.$photo->id.'-team', $ref, 'a file without a key gets one in the bundle');
        $this->assertSame($ref, $payload['media'][0]['key']);
        $this->assertSame('acme', collect($payload['items'])->firstWhere('collection', 'clients')['slug']);

        // "Another site": neither the photo nor the client exists.
        $photo->delete();
        CollectionItem::query()->forceDelete();
        $this->import($admin, $payload)->assertRedirect();

        $this->assertTrue(Media::query()->where('key', $ref)->exists());
        $this->assertTrue(CollectionItem::query()->where('collection', 'clients')->where('slug', 'acme')->exists());
        $this->assertSame($ref, Page::query()->where('slug', 'about-2')->firstOrFail()->sections()->first()->data_desktop['logo']);
    }
}
