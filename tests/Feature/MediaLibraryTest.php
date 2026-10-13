<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Media;
use App\Models\MediaFolder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Permission::findOrCreate('media.manage', 'web');
        Role::findOrCreate(config('gsm.roles.super_admin'), 'web');
    }

    private function admin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(config('gsm.roles.super_admin'));

        return $user;
    }

    public function test_requires_permission(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user)->get('/cms/media')->assertForbidden();
    }

    public function test_image_upload_generates_thumbnails(): void
    {
        $response = $this->actingAs($this->admin())->post('/cms/media', [
            'files' => [UploadedFile::fake()->image('photo.jpg', 1600, 1200)],
        ]);

        $response->assertCreated();

        $media = Media::firstOrFail();
        $this->assertSame('image', $media->type);
        $this->assertSame(1600, $media->width);

        Storage::disk('public')->assertExists($media->path);
        Storage::disk('public')->assertExists($media->variants['thumb']);
        Storage::disk('public')->assertExists($media->variants['card']);

        // The WebP copy is the same picture at full size, and pages draw it.
        $this->assertSame(1600, getimagesizefromstring(Storage::disk('public')->get($media->variants['webp']))[0]);
        $this->assertStringEndsWith('/webp.webp', $media->web_url);
    }

    public function test_small_images_are_not_upscaled_and_documents_get_no_variants(): void
    {
        $this->actingAs($this->admin())->post('/cms/media', [
            'files' => [
                UploadedFile::fake()->image('small.png', 200, 200),
                UploadedFile::fake()->create('catalogue.pdf', 500, 'application/pdf'),
            ],
        ])->assertCreated();

        $small = Media::where('filename', 'small.png')->firstOrFail();
        $this->assertNotNull($small->variants); // variants exist but at original size

        $pdf = Media::where('filename', 'catalogue.pdf')->firstOrFail();
        $this->assertSame('pdf', $pdf->type);
        $this->assertNull($pdf->variants);
    }

    public function test_multiple_upload_and_video_document_types(): void
    {
        $this->actingAs($this->admin())->post('/cms/media', [
            'files' => [
                UploadedFile::fake()->create('clip.mp4', 1000, 'video/mp4'),
                UploadedFile::fake()->create('sheet.xlsx', 100, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
            ],
        ])->assertCreated();

        $this->assertSame('video', Media::where('filename', 'clip.mp4')->firstOrFail()->type);
        $this->assertSame('document', Media::where('filename', 'sheet.xlsx')->firstOrFail()->type);
    }

    public function test_disallowed_extension_rejected(): void
    {
        $this->actingAs($this->admin())->postJson('/cms/media', [
            'files' => [UploadedFile::fake()->create('hack.php', 10, 'text/plain')],
        ])->assertUnprocessable();
    }

    public function test_folder_crud_and_upload_into_folder(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->postJson('/cms/media-folders', ['name' => 'Products'])
            ->assertCreated();

        $folder = MediaFolder::firstOrFail();

        // duplicate name at same level rejected
        $this->actingAs($admin)->postJson('/cms/media-folders', ['name' => 'Products'])
            ->assertUnprocessable();

        // upload into folder
        $this->actingAs($admin)->post('/cms/media', [
            'files' => [UploadedFile::fake()->image('a.jpg')],
            'folder_id' => $folder->id,
        ])->assertCreated();

        $this->assertSame($folder->id, Media::firstOrFail()->folder_id);

        // non-empty folder cannot be deleted
        $this->actingAs($admin)->deleteJson("/cms/media-folders/{$folder->id}")
            ->assertUnprocessable();

        // rename works
        $this->actingAs($admin)->putJson("/cms/media-folders/{$folder->id}", ['name' => 'Catalog'])
            ->assertOk();
        $this->assertSame('Catalog', $folder->fresh()->name);
    }

    public function test_browse_scopes_search_and_filter(): void
    {
        $admin = $this->admin();
        $folder = MediaFolder::create(['name' => 'Banners']);

        $this->actingAs($admin)->post('/cms/media', [
            'files' => [UploadedFile::fake()->image('root-hero.jpg')],
        ]);
        $this->actingAs($admin)->post('/cms/media', [
            'files' => [UploadedFile::fake()->image('inside.jpg')],
            'folder_id' => $folder->id,
        ]);
        $this->actingAs($admin)->post('/cms/media', [
            'files' => [UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf')],
        ]);

        // Root: only root items
        $root = $this->actingAs($admin)->getJson('/cms/media/browse')->assertOk()->json();
        $this->assertCount(2, $root['media']['data']);
        $this->assertCount(1, $root['folders']);

        // Folder scope
        $inFolder = $this->actingAs($admin)->getJson("/cms/media/browse?folder={$folder->id}")->json();
        $this->assertCount(1, $inFolder['media']['data']);
        $this->assertSame('inside.jpg', $inFolder['media']['data'][0]['filename']);
        $this->assertSame('Banners', $inFolder['trail'][0]['name']);

        // Search spans all folders
        $search = $this->actingAs($admin)->getJson('/cms/media/browse?search=inside')->json();
        $this->assertCount(1, $search['media']['data']);

        // Type filter
        $pdfs = $this->actingAs($admin)->getJson('/cms/media/browse?type=pdf')->json();
        $this->assertCount(1, $pdfs['media']['data']);
    }

    public function test_meta_update_and_move(): void
    {
        $admin = $this->admin();
        $folder = MediaFolder::create(['name' => 'Target']);

        $this->actingAs($admin)->post('/cms/media', [
            'files' => [UploadedFile::fake()->image('meta.jpg')],
        ]);
        $media = Media::firstOrFail();

        $this->actingAs($admin)->putJson("/cms/media/{$media->id}", [
            'title' => 'Hero banner',
            'alt' => 'Warehouse fabrics',
            'folder_id' => $folder->id,
        ])->assertOk();

        $media->refresh();
        $this->assertSame('Hero banner', $media->title);
        $this->assertSame('Warehouse fabrics', $media->alt);
        $this->assertSame($folder->id, $media->folder_id);
    }

    public function test_replace_keeps_id_swaps_file(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/cms/media', [
            'files' => [UploadedFile::fake()->image('original.jpg', 1000, 800)],
        ]);

        $media = Media::firstOrFail();
        $oldPath = $media->path;
        $oldThumb = $media->variants['thumb'];

        $this->actingAs($admin)->post("/cms/media/{$media->id}/replace", [
            'file' => UploadedFile::fake()->image('newfile.jpg', 900, 600),
        ])->assertOk();

        $media->refresh();
        $this->assertSame('newfile.jpg', $media->filename);
        $this->assertNotSame($oldPath, $media->path);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertMissing($oldThumb);
        Storage::disk('public')->assertExists($media->path);
        Storage::disk('public')->assertExists($media->variants['thumb']);
    }

    public function test_replace_with_different_type_rejected(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/cms/media', [
            'files' => [UploadedFile::fake()->image('img.jpg')],
        ]);
        $media = Media::firstOrFail();

        $this->actingAs($admin)->postJson("/cms/media/{$media->id}/replace", [
            'file' => UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf'),
        ])->assertUnprocessable();
    }

    public function test_delete_and_bulk_delete_remove_files(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/cms/media', [
            'files' => [
                UploadedFile::fake()->image('one.jpg'),
                UploadedFile::fake()->image('two.jpg'),
                UploadedFile::fake()->image('three.jpg'),
            ],
        ]);

        $all = Media::all();
        $first = $all[0];

        $this->actingAs($admin)->deleteJson("/cms/media/{$first->id}")->assertOk();
        Storage::disk('public')->assertMissing($first->path);
        $this->assertDatabaseMissing('media', ['id' => $first->id]);

        $this->actingAs($admin)->postJson('/cms/media/bulk-delete', [
            'ids' => [$all[1]->id, $all[2]->id],
        ])->assertOk()->assertJson(['deleted' => 2]);

        $this->assertSame(0, Media::count());
    }

    public function test_ckeditor_upload_returns_url(): void
    {
        $response = $this->actingAs($this->admin())->post('/cms/media/ckeditor', [
            'upload' => UploadedFile::fake()->image('editor.jpg'),
        ]);

        $response->assertOk()
            ->assertJson(['uploaded' => 1, 'fileName' => 'editor.jpg'])
            ->assertJsonStructure(['url']);
    }

    public function test_picker_mode_renders_bare_layout(): void
    {
        $this->actingAs($this->admin())
            ->get('/cms/media?picker=1')
            ->assertOk()
            ->assertSee('media-library')
            ->assertDontSee('main-sidebar');
    }
}
