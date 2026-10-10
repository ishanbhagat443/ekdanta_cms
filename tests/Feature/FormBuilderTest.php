<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\FormSubmissionMail;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\User;
use App\Services\FormBuilderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FormBuilderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate(config('gsm.roles.super_admin'), 'web');
        Permission::findOrCreate('forms.manage', 'web');
    }

    private function admin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(config('gsm.roles.super_admin'));

        return $user;
    }

    /** @param array<int, array<string, mixed>> $fields */
    private function makeForm(array $fields = [], array $overrides = []): Form
    {
        return app(FormBuilderService::class)->createForm(array_merge([
            'name' => 'Enquiry Form',
            'fields' => $fields,
        ], $overrides))->load('fields');
    }

    public function test_admin_forms_require_permission(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user)->get('/cms/forms')->assertForbidden();
    }

    public function test_create_form_then_save_fields_via_repeater(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post('/cms/forms', ['name' => 'Contact Us'])
            ->assertRedirect();

        $form = Form::where('slug', 'contact-us')->firstOrFail();

        $this->actingAs($admin)->put("/cms/forms/{$form->id}", [
            'name' => 'Contact Us',
            'notify_emails_csv' => 'sales@gsm.test, not-an-email, info@gsm.test',
            'is_active' => 1,
            'fields' => [
                ['label' => 'Your Name', 'type' => 'text', 'is_required' => 1],
                ['label' => 'Fabric Interest', 'type' => 'select', 'options' => "Georgette\nChiffon\n\nSatin "],
                ['label' => 'Your Name', 'type' => 'email'], // duplicate label → suffixed name
                ['label' => '', 'type' => 'text'], // no label → skipped
            ],
        ])->assertRedirect();

        $form->refresh()->load('fields');

        $this->assertSame(['your_name', 'fabric_interest', 'your_name_2'], $form->fields->pluck('name')->all());
        $this->assertSame([0, 1, 2], $form->fields->pluck('sort_order')->all());
        $this->assertSame(['Georgette', 'Chiffon', 'Satin'], $form->fields[1]->options);
        $this->assertTrue((bool) $form->fields[0]->is_required);
        // invalid email dropped from notify list
        $this->assertSame(['sales@gsm.test', 'info@gsm.test'], $form->notify_emails);
    }

    public function test_public_submit_stores_submission_and_flashes_success(): void
    {
        $form = $this->makeForm([
            ['label' => 'Your Name', 'type' => 'text', 'is_required' => 1],
            ['label' => 'Your Email', 'type' => 'email', 'is_required' => 1],
        ]);

        $response = $this->from('/contact')->post("/forms/{$form->slug}", [
            'your_name' => 'Ishan',
            'your_email' => 'ishan@example.com',
        ]);

        $response->assertRedirect('/contact#form-'.$form->slug)
            ->assertSessionHas("form_success_{$form->id}", $form->success_message);

        $submission = FormSubmission::firstOrFail();
        $this->assertSame($form->id, $submission->form_id);
        $this->assertSame('Ishan', $submission->data['your_name']);
        $this->assertSame('new', $submission->status);
    }

    public function test_public_submit_enforces_admin_defined_validation(): void
    {
        $form = $this->makeForm([
            ['label' => 'Your Name', 'type' => 'text', 'is_required' => 1, 'validation_rules' => 'min:3'],
            ['label' => 'Your Email', 'type' => 'email', 'is_required' => 1],
            ['label' => 'Fabric', 'type' => 'select', 'options' => "Georgette\nChiffon"],
        ]);

        $this->from('/contact')->post("/forms/{$form->slug}", [
            'your_name' => 'ab', // fails custom min:3
            'your_email' => 'not-an-email',
            'fabric' => 'Denim', // not in options
        ])->assertRedirect('/contact')
            ->assertSessionHasErrors(['your_name', 'your_email', 'fabric']);

        $this->assertSame(0, FormSubmission::count());
    }

    public function test_checkbox_field_accepts_array_and_stores_it(): void
    {
        $form = $this->makeForm([
            ['label' => 'Fabrics', 'type' => 'checkbox', 'options' => "Georgette\nChiffon\nSatin"],
        ]);

        $this->from('/contact')->post("/forms/{$form->slug}", [
            'fabrics' => ['Georgette', 'Satin'],
        ])->assertSessionDoesntHaveErrors();

        $submission = FormSubmission::firstOrFail();
        $this->assertSame(['Georgette', 'Satin'], $submission->data['fabrics']);
        $this->assertSame('Georgette, Satin', $submission->valueFor('fabrics'));
    }

    public function test_file_upload_is_stored_on_public_disk(): void
    {
        Storage::fake('public');

        $form = $this->makeForm([
            ['label' => 'Design File', 'type' => 'file', 'is_required' => 1],
        ]);

        $this->from('/contact')->post("/forms/{$form->slug}", [
            'design_file' => UploadedFile::fake()->create('design.pdf', 200, 'application/pdf'),
        ])->assertSessionDoesntHaveErrors();

        $submission = FormSubmission::firstOrFail();
        $url = $submission->data['design_file'];

        $this->assertStringContainsString('form-uploads/'.now()->format('Y/m'), $url);
        $this->assertCount(1, Storage::disk('public')->allFiles('form-uploads'));

        // disallowed mime rejected
        $this->from('/contact')->post("/forms/{$form->slug}", [
            'design_file' => UploadedFile::fake()->create('script.exe', 10, 'application/x-msdownload'),
        ])->assertSessionHasErrors('design_file');
    }

    public function test_honeypot_silently_drops_bot_submissions(): void
    {
        $form = $this->makeForm([
            ['label' => 'Your Name', 'type' => 'text', 'is_required' => 1],
        ]);

        $this->from('/contact')->post("/forms/{$form->slug}", [
            'your_name' => 'Bot',
            'website' => 'http://spam.example',
        ])->assertRedirect('/contact');

        $this->assertSame(0, FormSubmission::count());
    }

    public function test_notification_email_sent_to_each_configured_address(): void
    {
        Mail::fake();

        $form = $this->makeForm(
            [['label' => 'Your Name', 'type' => 'text', 'is_required' => 1]],
            ['notify_emails_csv' => 'sales@gsm.test, owner@gsm.test'],
        );

        $this->from('/contact')->post("/forms/{$form->slug}", ['your_name' => 'Ishan']);

        // Mailable implements ShouldQueue → queued, not sent inline.
        Mail::assertQueued(FormSubmissionMail::class, 2);
        Mail::assertQueued(FormSubmissionMail::class, fn ($mail) => $mail->hasTo('sales@gsm.test'));
        Mail::assertQueued(FormSubmissionMail::class, fn ($mail) => $mail->hasTo('owner@gsm.test'));
    }

    public function test_inactive_form_returns_404_on_submit(): void
    {
        $form = $this->makeForm(
            [['label' => 'Your Name', 'type' => 'text']],
            ['is_active' => false],
        );

        $this->post("/forms/{$form->slug}", ['your_name' => 'X'])->assertNotFound();
    }

    public function test_admin_can_mark_read_and_delete_submissions(): void
    {
        $admin = $this->admin();
        $form = $this->makeForm([['label' => 'Your Name', 'type' => 'text']]);
        $submission = $form->submissions()->create(['data' => ['your_name' => 'A'], 'status' => 'new']);

        $this->actingAs($admin)
            ->post("/cms/forms/{$form->id}/submissions/{$submission->id}/read")
            ->assertRedirect();
        $this->assertSame('read', $submission->fresh()->status);

        $this->actingAs($admin)
            ->delete("/cms/forms/{$form->id}/submissions/{$submission->id}")
            ->assertRedirect();
        $this->assertSame(0, FormSubmission::count());

        // cross-form access blocked
        $other = $this->makeForm([['label' => 'X', 'type' => 'text']], ['name' => 'Other Form']);
        $foreign = $other->submissions()->create(['data' => [], 'status' => 'new']);
        $this->actingAs($admin)
            ->post("/cms/forms/{$form->id}/submissions/{$foreign->id}/read")
            ->assertNotFound();
    }

    public function test_csv_export_contains_headers_and_values(): void
    {
        $admin = $this->admin();
        $form = $this->makeForm([
            ['label' => 'Your Name', 'type' => 'text'],
            ['label' => 'Fabrics', 'type' => 'checkbox', 'options' => "Georgette\nChiffon"],
        ]);
        $form->submissions()->create([
            'data' => ['your_name' => 'Ishan', 'fabrics' => ['Georgette', 'Chiffon']],
            'ip' => '127.0.0.1',
            'status' => 'new',
        ]);

        $response = $this->actingAs($admin)->get("/cms/forms/{$form->id}/export");
        $response->assertOk();

        $csv = $response->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('ID,Date,IP,Status,"Your Name",Fabrics', $csv);
        $this->assertStringContainsString('Ishan', $csv);
        $this->assertStringContainsString('"Georgette, Chiffon"', $csv);
    }

    public function test_contact_form_seeder_is_idempotent(): void
    {
        $this->seed(\Database\Seeders\FormSeeder::class);
        $this->seed(\Database\Seeders\FormSeeder::class);

        $this->assertSame(1, Form::where('slug', 'contact-form')->count());
        $this->assertSame(4, Form::where('slug', 'contact-form')->first()->fields()->count());
    }

    public function test_steps_chips_hidden_fields_and_after_sending_are_saved(): void
    {
        $form = $this->makeForm();

        $this->actingAs($this->admin())->put("/cms/forms/{$form->id}", [
            'name' => 'Brief',
            'success_action' => 'whatsapp',
            'whatsapp_intro' => 'Hi!',
            'then_label' => 'What happens next',
            'then_page' => '/thank-you',
            'fields' => [
                ['label' => 'Services', 'type' => 'checkbox', 'options' => "SEO
Website", 'step' => 1, 'display' => 'chips', 'whatsapp' => 'Help with: {value}.'],
                ['label' => 'Campaign', 'type' => 'hidden', 'step' => 2, 'autofill' => 'utm_campaign'],
            ],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $form->refresh()->load('fields');
        $this->assertSame('whatsapp', $form->successAction());
        $this->assertSame('/thank-you', $form->setting('then_page'));
        $this->assertSame(['step' => 1, 'display' => 'chips', 'whatsapp' => 'Help with: {value}.'], $form->fields[0]->settings);
        $this->assertSame(['step' => 2, 'autofill' => 'utm_campaign'], $form->fields[1]->settings);

        $this->actingAs($this->admin())->put("/cms/forms/{$form->id}", ['name' => 'Brief', 'success_page' => 'javascript:alert(1)', 'fields' => [['label' => 'X', 'type' => 'hidden', 'autofill' => 'cookies']]])
            ->assertSessionHasErrors(['success_page', 'fields.0.autofill']);
    }

    public function test_a_whatsapp_form_saves_the_enquiry_then_hands_over_the_message(): void
    {
        app(\App\Services\SettingsService::class)->updateGroup('business', ['country_code' => '91', 'phones' => [['label' => 'All', 'number' => '90994 17987', 'whatsapp' => true]]]);
        $form = $this->makeForm([
            ['label' => 'Services', 'type' => 'checkbox', 'options' => "SEO
Website", 'is_required' => 1, 'whatsapp' => 'I would like help with: {value}.'],
            ['label' => 'Your name', 'type' => 'text', 'whatsapp' => 'My name is {value}.'],
            ['label' => 'Page', 'type' => 'hidden', 'autofill' => 'page_url'],
        ], ['store_as_enquiry' => 1, 'success_action' => 'whatsapp', 'whatsapp_intro' => 'Hi Ekdanta!', 'then_label' => 'Next', 'then_page' => '/thank-you']);

        $message = 'Hi Ekdanta! I would like help with: SEO, Website. My name is Asha.';
        $this->postJson("/forms/{$form->slug}", ['services' => ['SEO', 'Website'], 'your_name' => 'Asha', 'page' => 'https://ekdanta.in/contact?utm_source=ads'])
            ->assertOk()
            ->assertJson(['whatsapp' => 'https://wa.me/919099417987?text='.rawurlencode($message), 'then' => ['label' => 'Next', 'url' => '/thank-you']]);

        $enquiry = \App\Models\Enquiry::query()->latest('id')->firstOrFail();
        $this->assertSame($message, $enquiry->message);
        $this->assertSame('https://ekdanta.in/contact?utm_source=ads', $enquiry->extra['Page']);

        // Without JS the browser goes straight to WhatsApp.
        $this->post("/forms/{$form->slug}", ['services' => ['SEO']])->assertRedirect('https://wa.me/919099417987?text='.rawurlencode('Hi Ekdanta! I would like help with: SEO.'));
    }

    public function test_a_page_form_sends_the_visitor_to_its_page(): void
    {
        $form = $this->makeForm([['label' => 'Name', 'type' => 'text']], ['success_action' => 'page', 'success_page' => '/thank-you']);

        $this->postJson("/forms/{$form->slug}", ['name' => 'A'])->assertOk()->assertJson(['redirect' => '/thank-you']);
        $this->post("/forms/{$form->slug}", ['name' => 'A'])->assertRedirect('/thank-you');
    }

    public function test_enquiries_alert_whatsapp_when_it_is_set_up(): void
    {
        \Illuminate\Support\Facades\Http::fake(['graph.facebook.com/*' => \Illuminate\Support\Facades\Http::response(['messages' => [['id' => 'x']]])]);
        foreach (['enabled' => '1', 'phone_number_id' => '123', 'access_token' => 'token', 'recipient_number' => '919099417987'] as $key => $value) {
            \App\Models\Setting::query()->updateOrCreate(['group' => 'whatsapp', 'key' => $key], ['value' => $value]);
        }
        app(\App\Services\SettingsService::class)->flushCache();
        $form = $this->makeForm([['label' => 'Name', 'type' => 'text']], ['store_as_enquiry' => 1]);

        $this->postJson("/forms/{$form->slug}", ['name' => 'Asha'])->assertOk();

        \Illuminate\Support\Facades\Http::assertSent(fn ($request) => str_contains($request->url(), '/123/messages'));
    }
}
