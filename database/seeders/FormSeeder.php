<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Form;
use App\Services\FormBuilderService;
use Illuminate\Database\Seeder;

/**
 * Default enquiry forms. Idempotent: a form is only created when its slug is
 * missing, so admin edits survive re-seeds. Each saves its submissions as
 * enquiries under its own name (one source per form).
 */
class FormSeeder extends Seeder
{
    public function run(FormBuilderService $builder): void
    {
        foreach ($this->definitions() as $definition) {
            $existing = Form::query()->where('slug', $definition['slug'])->first();

            if ($existing) {
                continue;
            }

            $builder->createForm($definition);
        }
    }

    /** @return list<array<string, mixed>> */
    private function definitions(): array
    {
        $core = fn (string $messagePlaceholder = 'How can we help?') => [
            ['label' => 'Your Name', 'type' => 'text', 'placeholder' => 'Full name', 'is_required' => true],
            ['label' => 'Your Email', 'type' => 'email', 'placeholder' => 'email@example.com', 'is_required' => true],
            ['label' => 'Your Phone', 'type' => 'phone', 'placeholder' => '+91 ...', 'is_required' => false],
            ['label' => 'Your Message', 'type' => 'textarea', 'placeholder' => $messagePlaceholder, 'is_required' => true],
        ];

        return [
            [
                'name' => 'Contact Form',
                'slug' => 'contact-form',
                'heading' => 'Get In Touch',
                'button_label' => 'Send Message',
                'store_as_enquiry' => true,
                'fields' => $core(),
            ],
            [
                'name' => 'Footer Form',
                'slug' => 'footer-form',
                'heading' => null,
                'button_label' => 'Send',
                'store_as_enquiry' => true,
                'fields' => $core('Quick message…'),
            ],
        ];
    }
}
