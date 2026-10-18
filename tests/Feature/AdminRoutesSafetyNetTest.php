<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ButtonStyle;
use App\Models\CollectionItem;
use App\Models\Enquiry;
use App\Models\Form;
use App\Models\GlobalBlock;
use App\Models\Gradient;
use App\Models\Menu;
use App\Models\MotionPreset;
use App\Models\Page;
use App\Models\PageRevision;
use App\Models\Post;
use App\Models\Redirect;
use App\Models\Section;
use App\Models\User;
use App\Services\PageRevisionService;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\EkdantaSiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as Router;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Safety net: a super admin opens every admin page under /cms. Routes without
 * parameters as they are; routes with parameters (edit screens, previews,
 * exports) with one seeded record each, every form and every settings group.
 * Each must answer 200 with no exception. A new route whose parameter has no
 * fixture below fails the test, so it can't slip past.
 */
class AdminRoutesSafetyNetTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_admin_page_opens_for_a_super_admin(): void
    {
        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);
        $seeder = new EkdantaSiteSeeder;
        $seeder->withPages = true;
        foreach (['collections', 'menus', 'finale', 'briefForm', 'samplePosts', 'pages'] as $step) {
            $seeder->{$step}();
        }

        $page = Page::query()->where('slug', 'contact')->firstOrFail();
        app(PageRevisionService::class)->capture($page, null, "Safety net");
        Redirect::create(['from_path' => '/old', 'to_url' => '/new', 'status_code' => 301, 'is_active' => true]);
        Enquiry::create(['source' => 'Ekdanta Brief', 'name' => 'Asha', 'phone' => '90000 00000', 'message' => 'Hello']);
        $collections = CollectionItem::query()->distinct()->pluck('collection')->all();

        // Every value a parameter can take; a list means "open each one".
        $values = [
            'button_style' => [ButtonStyle::query()->firstOrFail()->id],
            'collection' => $collections,
            'form' => Form::query()->pluck('id')->all(),
            'block' => [GlobalBlock::query()->firstOrFail()->id],
            'gradient' => [Gradient::query()->firstOrFail()->id],
            'menu' => [Menu::query()->firstOrFail()->id],
            'motion_preset' => [MotionPreset::query()->firstOrFail()->id],
            'page' => [$page->id],
            'post' => [Post::query()->firstOrFail()->id],
            'redirect' => [Redirect::query()->firstOrFail()->id],
            'role' => [Role::query()->firstOrFail()->id],
            'group' => array_keys(config('settings')),
            'user' => [User::query()->firstOrFail()->id],
            'section' => [Section::query()->firstOrFail()->id],
            'enquiry' => [Enquiry::query()->firstOrFail()->id],
            'type' => ['page'],
            'revision' => [PageRevision::query()->firstOrFail()->id],
        ];

        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole(config('gsm.roles.super_admin'));

        $failures = [];
        $opened = 0;
        foreach (Router::getRoutes() as $route) {
            /** @var Route $route */
            if (! in_array('GET', $route->methods(), true) || ! str_starts_with($route->uri(), 'cms') || $route->getName() === 'cms.login') {
                continue;
            }
            foreach ($this->urls($route, $values, $failures) as $url) {
                $response = $this->actingAs($admin)->followingRedirects()->get($url);
                $opened++;
                if ($response->getStatusCode() !== 200) {
                    $failures[] = "{$response->getStatusCode()} {$url}: ".($response->exception?->getMessage() ?? '');
                }
            }
        }

        $this->assertGreaterThan(60, $opened);
        $this->assertSame([], $failures);
    }

    /** One URL per combination of the route's parameter values. */
    private function urls(Route $route, array $values, array &$failures): array
    {
        $names = $route->parameterNames();
        $sets = [[]];
        foreach (array_diff($names, ['item']) as $name) {
            if (! array_key_exists($name, $values)) {
                $failures[] = "no fixture for {{$name}} in {$route->uri()}";

                return [];
            }
            $next = [];
            foreach ($sets as $set) {
                foreach ($values[$name] as $value) {
                    $next[] = $set + [$name => $value];
                }
            }
            $sets = $next;
        }

        // An item must belong to its collection.
        if (in_array('item', $names, true)) {
            $sets = array_values(array_filter(array_map(function (array $set) {
                $item = CollectionItem::query()->where('collection', $set['collection'])->first();

                return $item ? $set + ['item' => $item->id] : null;
            }, $sets)));
        }

        return array_map(fn (array $set) => route($route->getName(), $set), $sets);
    }
}
