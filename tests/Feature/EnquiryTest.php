<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Enquiry;
use App\Models\Form;
use App\Models\User;
use App\Services\FormBuilderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EnquiryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate(config('gsm.roles.super_admin'), 'web');
        Permission::findOrCreate('enquiries.manage', 'web');
    }

    private function admin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(config('gsm.roles.super_admin'));

        return $user;
    }

    private function sourceForm(string $source = 'contact', array $extraFields = []): Form
    {
        return app(FormBuilderService::class)->createForm([
            'name' => ucfirst($source).' Form',
            'store_as_enquiry' => true, // source = the form itself (its slug)
            'fields' => array_merge([
                ['label' => 'Your Name', 'type' => 'text', 'is_required' => 1],
                ['label' => 'Your Email', 'type' => 'email', 'is_required' => 1],
                ['label' => 'Your Phone', 'type' => 'phone'],
                ['label' => 'Your Message', 'type' => 'textarea'],
            ], $extraFields),
        ])->load('fields');
    }

    private function makeEnquiry(array $overrides = []): Enquiry
    {
        return Enquiry::create(array_merge([
            'source' => 'contact',
            'name' => 'Ishan',
            'email' => 'ishan@example.com',
            'phone' => '+91 9876543210',
            'message' => 'Interested in georgette sarees.',
            'ip' => '127.0.0.1',
            'status' => 'new',
        ], $overrides));
    }

    public function test_enquiries_require_permission(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user)->get('/cms/enquiries')->assertForbidden();
    }

    public function test_submission_on_source_form_creates_enquiry_with_core_fields_mapped(): void
    {
        $form = $this->sourceForm('distributor', [
            ['label' => 'Company Name', 'type' => 'text', 'is_required' => 1],
        ]);

        $this->from('/contact')->post("/forms/{$form->slug}", [
            'your_name' => 'Ishan',
            'your_email' => 'ishan@example.com',
            'your_phone' => '+91 9876543210',
            'your_message' => 'Want distributorship for Gujarat.',
            'company_name' => 'Bhagat Textiles',
        ])->assertSessionDoesntHaveErrors();

        $enquiry = Enquiry::firstOrFail();

        $this->assertSame($form->slug, $enquiry->source);
        $this->assertSame('Distributor Form', $enquiry->sourceLabel());
        $this->assertSame('Ishan', $enquiry->name);
        $this->assertSame('ishan@example.com', $enquiry->email);
        $this->assertSame('+91 9876543210', $enquiry->phone);
        $this->assertSame('Want distributorship for Gujarat.', $enquiry->message);
        $this->assertSame(['Company Name' => 'Bhagat Textiles'], $enquiry->extra);
        $this->assertSame('new', $enquiry->status);
        $this->assertNotNull($enquiry->form_submission_id);
        $this->assertNotNull($enquiry->ip);
    }

    public function test_form_without_source_does_not_create_enquiry(): void
    {
        $form = app(FormBuilderService::class)->createForm([
            'name' => 'Plain Form',
            'fields' => [['label' => 'Your Name', 'type' => 'text', 'is_required' => 1]],
        ]);

        $this->from('/x')->post("/forms/{$form->slug}", ['your_name' => 'A'])
            ->assertSessionDoesntHaveErrors();

        $this->assertSame(0, Enquiry::count());
    }

    public function test_index_filters_by_source_status_search_and_date(): void
    {
        $this->makeEnquiry(['source' => 'contact', 'name' => 'Amit Shah', 'status' => 'new']);
        $this->makeEnquiry(['source' => 'dealer', 'name' => 'Priya Patel', 'status' => 'closed']);
        $old = $this->makeEnquiry(['source' => 'career', 'name' => 'Old Applicant']);
        $old->forceFill(['created_at' => now()->subMonths(2)])->save();

        $admin = $this->admin();

        $this->actingAs($admin)->get('/cms/enquiries?source=dealer')
            ->assertOk()->assertSee('Priya Patel')->assertDontSee('Amit Shah');

        $this->actingAs($admin)->get('/cms/enquiries?status=new')
            ->assertOk()->assertSee('Amit Shah')->assertDontSee('Priya Patel');

        $this->actingAs($admin)->get('/cms/enquiries?q=priya')
            ->assertOk()->assertSee('Priya Patel')->assertDontSee('Amit Shah');

        $this->actingAs($admin)->get('/cms/enquiries?from='.now()->subDay()->toDateString())
            ->assertOk()->assertSee('Amit Shah')->assertDontSee('Old Applicant');

        $this->actingAs($admin)->get('/cms/enquiries?source=nonsense')
            ->assertSessionHasErrors('source');
    }

    public function test_show_marks_new_as_read_and_update_saves_status_and_notes(): void
    {
        $admin = $this->admin();
        $enquiry = $this->makeEnquiry();

        $this->actingAs($admin)->get("/cms/enquiries/{$enquiry->id}")->assertOk();
        $this->assertSame('read', $enquiry->fresh()->status);

        $this->actingAs($admin)->put("/cms/enquiries/{$enquiry->id}", [
            'status' => 'replied',
            'admin_notes' => 'Sent catalogue on WhatsApp.',
        ])->assertRedirect();

        $enquiry->refresh();
        $this->assertSame('replied', $enquiry->status);
        $this->assertSame('Sent catalogue on WhatsApp.', $enquiry->admin_notes);

        $this->actingAs($admin)->put("/cms/enquiries/{$enquiry->id}", ['status' => 'bogus'])
            ->assertSessionHasErrors('status');
    }

    public function test_delete_enquiry(): void
    {
        $admin = $this->admin();
        $enquiry = $this->makeEnquiry();

        $this->actingAs($admin)->delete("/cms/enquiries/{$enquiry->id}")->assertRedirect();
        $this->assertSame(0, Enquiry::count());
    }

    public function test_export_respects_filters_and_has_bom(): void
    {
        $admin = $this->admin();
        $this->makeEnquiry(['source' => 'contact', 'name' => 'Contact Person', 'admin_notes' => 'note-x']);
        $this->makeEnquiry(['source' => 'career', 'name' => 'Career Person', 'extra' => ['Position Applied For' => 'Merchandiser']]);

        $csv = $this->actingAs($admin)->get('/cms/enquiries/export?source=career')
            ->assertOk()->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('ID,Source,Name,Email,Phone,Message,Extra,IP,Date,Status,"Admin Notes"', $csv);
        $this->assertStringContainsString('Career Person', $csv);
        $this->assertStringContainsString('Position Applied For: Merchandiser', $csv);
        $this->assertStringNotContainsString('Contact Person', $csv);
    }

    public function test_seeder_creates_the_default_forms_each_its_own_source(): void
    {
        $this->seed(\Database\Seeders\FormSeeder::class);
        $this->seed(\Database\Seeders\FormSeeder::class); // idempotent

        // One source per form, keyed by the form; no previous-client forms.
        $this->assertEqualsCanonicalizing(
            ['contact-form', 'footer-form'],
            Form::whereNotNull('enquiry_source')->pluck('enquiry_source')->all(),
        );
        $this->assertSame(2, Form::count());
    }
}
