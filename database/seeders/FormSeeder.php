<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Form;
use App\Services\FormBuilderService;
use Illuminate\Database\Seeder;

/**
 * Default enquiry forms — one per enquiry stream. Idempotent: a form is
 * only created when its slug is missing, so admin edits survive re-seeds.
 * (Existing forms do get their enquiry_source backfilled if empty.)
 */
class FormSeeder extends Seeder
{
    public function run(FormBuilderService $builder): void
    {
        foreach ($this->definitions() as $definition) {
            $existing = Form::query()->where('slug', $definition['slug'])->first();

            if ($existing) {
                if (! $existing->enquiry_source && ! empty($definition['enquiry_source'])) {
                    $existing->update(['enquiry_source' => $definition['enquiry_source']]);
                }

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
                'enquiry_source' => 'contact',
                'fields' => $core(),
            ],
            [
                'name' => 'Footer Form',
                'slug' => 'footer-form',
                'heading' => null,
                'button_label' => 'Send',
                'enquiry_source' => 'footer',
                'fields' => $core('Quick message…'),
            ],
            [
                'name' => 'Distributor Form',
                'slug' => 'distributor-form',
                'heading' => 'Become a Distributor',
                'button_label' => 'Apply Now',
                'enquiry_source' => 'distributor',
                'fields' => array_merge(
                    array_slice($core(), 0, 3),
                    [
                        ['label' => 'Company Name', 'type' => 'text', 'placeholder' => 'Business / firm name', 'is_required' => true],
                        ['label' => 'City', 'type' => 'text', 'placeholder' => 'Operating city', 'is_required' => true],
                        ['label' => 'Your Message', 'type' => 'textarea', 'placeholder' => 'Tell us about your distribution network', 'is_required' => true],
                    ],
                ),
            ],
            [
                'name' => 'Dealer Form',
                'slug' => 'dealer-form',
                'heading' => 'Become a Dealer',
                'button_label' => 'Apply Now',
                'enquiry_source' => 'dealer',
                'fields' => array_merge(
                    array_slice($core(), 0, 3),
                    [
                        ['label' => 'Shop Name', 'type' => 'text', 'placeholder' => 'Shop / showroom name', 'is_required' => true],
                        ['label' => 'City', 'type' => 'text', 'placeholder' => 'Shop city', 'is_required' => true],
                        ['label' => 'Your Message', 'type' => 'textarea', 'placeholder' => 'Tell us about your store', 'is_required' => true],
                    ],
                ),
            ],
            [
                'name' => 'Career Form',
                'slug' => 'career-form',
                'heading' => 'Join Our Team',
                'button_label' => 'Apply Now',
                'enquiry_source' => 'career',
                'fields' => array_merge(
                    array_slice($core(), 0, 3),
                    [
                        ['label' => 'Position Applied For', 'type' => 'text', 'placeholder' => 'e.g. Merchandiser', 'is_required' => true],
                        ['label' => 'Resume', 'type' => 'file', 'is_required' => false],
                        ['label' => 'Your Message', 'type' => 'textarea', 'placeholder' => 'Brief cover note', 'is_required' => false],
                    ],
                ),
            ],
        ];
    }
}
