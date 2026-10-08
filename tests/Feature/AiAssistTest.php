<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Services\AiAssistService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The AI assistant is optional. The property that matters most is that an
 * install without a key never reaches the network and never shows the feature.
 */
class AiAssistTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (config('gsm.roles') as $role) {
            Role::findOrCreate($role, 'web');
        }

        Permission::findOrCreate('pages.manage', 'web');
        RateLimiter::clear('ai-assist:1');
    }

    private function admin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(config('gsm.roles.super_admin'));

        return $user;
    }

    private function enable(string $format = 'anthropic'): void
    {
        config()->set('ai.enabled', true);
        config()->set('ai.key', 'test-key');
        config()->set('ai.format', $format);
        config()->set('ai.url', 'https://api.example.test/v1/messages');
        config()->set('ai.features.improve_text', true);
        config()->set('ai.features.generate_section', true);
    }

    /* ── Off by default ──────────────────────────────────────────── */

    public function test_it_is_off_when_no_key_is_configured(): void
    {
        config()->set('ai.enabled', false);
        config()->set('ai.key', null);

        $this->assertFalse(app(AiAssistService::class)->isEnabled());
        $this->assertFalse(app(AiAssistService::class)->allows('improve_text'));
    }

    public function test_the_routes_are_invisible_without_a_key(): void
    {
        config()->set('ai.enabled', false);
        config()->set('ai.key', null);

        Http::fake();

        $this->actingAs($this->admin())
            ->postJson('/cms/ai/improve', ['text' => 'Hello'])
            ->assertNotFound();

        Http::assertNothingSent();
    }

    public function test_a_disabled_feature_stays_disabled_even_with_a_key(): void
    {
        $this->enable();
        config()->set('ai.features.generate_section', false);

        Http::fake();

        $this->actingAs($this->admin())
            ->postJson('/cms/ai/draft-section', ['type' => 'hero', 'brief' => 'A bakery'])
            ->assertNotFound();

        Http::assertNothingSent();
    }

    /* ── Improving text ──────────────────────────────────────────── */

    public function test_it_returns_improved_text(): void
    {
        $this->enable();

        Http::fake([
            '*' => Http::response(['content' => [['type' => 'text', 'text' => 'A clearer sentence.']]]),
        ]);

        $this->actingAs($this->admin())
            ->postJson('/cms/ai/improve', ['text' => 'a sentence what is unclear', 'tone' => 'clearer'])
            ->assertOk()
            ->assertJson(['text' => 'A clearer sentence.']);
    }

    public function test_it_speaks_the_openai_shape_too(): void
    {
        $this->enable('openai');

        Http::fake([
            '*' => Http::response(['choices' => [['message' => ['content' => 'Rewritten.']]]]),
        ]);

        $this->actingAs($this->admin())
            ->postJson('/cms/ai/improve', ['text' => 'Original'])
            ->assertOk()
            ->assertJson(['text' => 'Rewritten.']);
    }

    public function test_an_unknown_tone_is_rejected(): void
    {
        $this->enable();
        Http::fake();

        $this->actingAs($this->admin())
            ->postJson('/cms/ai/improve', ['text' => 'Hi', 'tone' => 'pirate'])
            ->assertStatus(422);

        Http::assertNothingSent();
    }

    public function test_a_provider_failure_is_reported_without_leaking_its_body(): void
    {
        $this->enable();

        Http::fake([
            '*' => Http::response(['error' => ['message' => 'Your account sk-secret is out of credit']], 402),
        ]);

        $response = $this->actingAs($this->admin())
            ->postJson('/cms/ai/improve', ['text' => 'Hello'])
            ->assertStatus(422);

        $this->assertStringNotContainsString('sk-secret', $response->getContent());
        $this->assertStringContainsString('402', $response->json('message'));
    }

    /* ── Drafting a section ──────────────────────────────────────── */

    public function test_it_drafts_only_the_fields_the_section_has(): void
    {
        $this->enable();

        Http::fake([
            '*' => Http::response(['content' => [['type' => 'text', 'text' => json_encode([
                'heading' => 'Fresh bread daily',
                'subheading' => 'Baked before dawn',
                'not_a_field' => 'should be dropped',
            ])]]]),
        ]);

        $values = $this->actingAs($this->admin())
            ->postJson('/cms/ai/draft-section', ['type' => 'hero', 'brief' => 'A village bakery'])
            ->assertOk()
            ->json('values');

        $this->assertSame('Fresh bread daily', $values['heading']);
        $this->assertArrayNotHasKey('not_a_field', $values, 'unknown keys must not pass through');
    }

    public function test_a_fenced_json_reply_is_still_understood(): void
    {
        $this->enable();

        Http::fake([
            '*' => Http::response(['content' => [['type' => 'text',
                'text' => "```json\n{\"heading\": \"Fenced but fine\"}\n```"]]]),
        ]);

        $this->actingAs($this->admin())
            ->postJson('/cms/ai/draft-section', ['type' => 'hero', 'brief' => 'A cafe'])
            ->assertOk()
            ->assertJsonPath('values.heading', 'Fenced but fine');
    }

    public function test_a_nonsense_reply_is_an_error_not_a_crash(): void
    {
        $this->enable();

        Http::fake(['*' => Http::response(['content' => [['type' => 'text', 'text' => 'Sure! Here you go.']]])]);

        $this->actingAs($this->admin())
            ->postJson('/cms/ai/draft-section', ['type' => 'hero', 'brief' => 'A cafe'])
            ->assertStatus(422)
            ->assertJsonStructure(['message']);
    }

    public function test_an_unknown_section_type_is_refused(): void
    {
        $this->enable();
        Http::fake();

        $this->actingAs($this->admin())
            ->postJson('/cms/ai/draft-section', ['type' => 'not-a-section', 'brief' => 'x'])
            ->assertStatus(422);

        Http::assertNothingSent();
    }

    /**
     * A model must not be able to put free text into a media id, a menu id or
     * a select — those carry meaning the rest of the CMS relies on.
     */
    public function test_it_cannot_write_into_non_text_fields(): void
    {
        $this->enable();

        Http::fake([
            '*' => Http::response(['content' => [['type' => 'text', 'text' => json_encode([
                'heading' => 'A real heading',
                'bg_media_id' => 'not-an-id',
                'height' => 'enormous',
                'overlay_opacity' => 'lots',
            ])]]]),
        ]);

        $values = $this->actingAs($this->admin())
            ->postJson('/cms/ai/draft-section', ['type' => 'hero', 'brief' => 'A cafe'])
            ->assertOk()
            ->json('values');

        $this->assertSame('A real heading', $values['heading']);

        foreach (['bg_media_id', 'height', 'overlay_opacity'] as $key) {
            $this->assertArrayNotHasKey($key, $values, "[{$key}] is not a writing field");
        }
    }

    /* ── Guards ──────────────────────────────────────────────────── */

    public function test_it_needs_an_editing_permission(): void
    {
        $this->enable();
        Http::fake();

        $viewer = User::factory()->create(['is_active' => true]);
        $viewer->assignRole(config('gsm.roles.viewer'));

        $this->actingAs($viewer)
            ->postJson('/cms/ai/improve', ['text' => 'Hello'])
            ->assertForbidden();

        Http::assertNothingSent();
    }

    public function test_it_is_rate_limited_per_user(): void
    {
        $this->enable();
        config()->set('ai.rate_limit', 2);

        Http::fake(['*' => Http::response(['content' => [['type' => 'text', 'text' => 'ok']]])]);

        $admin = $this->admin();

        $this->actingAs($admin)->postJson('/cms/ai/improve', ['text' => 'a'])->assertOk();
        $this->actingAs($admin)->postJson('/cms/ai/improve', ['text' => 'b'])->assertOk();

        $this->actingAs($admin)->postJson('/cms/ai/improve', ['text' => 'c'])
            ->assertStatus(429)
            ->assertJsonStructure(['message']);
    }
}
