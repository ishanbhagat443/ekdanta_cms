<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Form;
use App\Services\Concerns\GeneratesUniqueSlug;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FormBuilderService
{
    use GeneratesUniqueSlug;

    public function createForm(array $data): Form
    {
        return DB::transaction(function () use ($data): Form {
            $slug = $this->uniqueSlugFor(Form::class, $data['slug'] ?? $data['name']);
            $form = Form::create($this->attributes($data, $slug) + ['slug' => $slug]);

            $this->syncFields($form, (array) ($data['fields'] ?? []));

            return $form;
        });
    }

    public function updateForm(Form $form, array $data): Form
    {
        return DB::transaction(function () use ($form, $data): Form {
            $attributes = $this->attributes($data, $form->enquiry_source ?: $form->slug);

            if (! empty($data['slug']) && $data['slug'] !== $form->slug) {
                $attributes['slug'] = $this->uniqueSlugFor(Form::class, $data['slug'], $form->id);
            }

            $form->update($attributes);
            $this->syncFields($form, (array) ($data['fields'] ?? []));

            return $form->fresh(['fields']);
        });
    }

    public function deleteForm(Form $form): void
    {
        $form->delete(); // fields + submissions cascade
    }

    /* ── internals ─────────────────────────────────────────────── */

    /**
     * @param  string  $enquiryKey  this form's enquiry source key: its existing
     *                              key, or its slug. One source per form.
     */
    private function attributes(array $data, string $enquiryKey): array
    {
        // "a@b.com, c@d.com" → ["a@b.com", "c@d.com"]
        $emails = collect(explode(',', (string) ($data['notify_emails_csv'] ?? '')))
            ->map(fn ($e) => trim($e))
            ->filter(fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL))
            ->values()
            ->all();

        return [
            'name' => $data['name'],
            'heading' => $data['heading'] ?? null,
            'button_label' => ($data['button_label'] ?? null) ?: 'Submit',
            'success_message' => ($data['success_message'] ?? null) ?: 'Thank you! We will get back to you shortly.',
            'notify_emails' => $emails,
            'enquiry_source' => ! empty($data['store_as_enquiry']) ? $enquiryKey : null,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'settings' => array_filter(array_intersect_key($data, array_flip(['success_action', 'success_page', 'whatsapp_intro', 'then_label', 'then_page'])), fn ($v) => $v !== null && $v !== '') ?: null,
        ];
    }

    /**
     * Replace field definitions from the repeater. Field "name" derives
     * from the label and stays stable for identical labels, so historic
     * submissions keep matching their columns.
     */
    private function syncFields(Form $form, array $rows): void
    {
        $form->fields()->delete();

        $usedNames = [];

        foreach (array_values($rows) as $position => $row) {
            if (empty($row['label']) || empty($row['type'])) {
                continue;
            }

            // A consent box keeps the name "consent", so rewording it keeps old submissions' column.
            $base = $row['type'] === 'consent' ? 'consent' : (Str::slug($row['label'], '_') ?: 'field');
            $name = $base;
            $suffix = 2;
            while (in_array($name, $usedNames, true)) {
                $name = "{$base}_{$suffix}";
                $suffix++;
            }
            $usedNames[] = $name;

            $options = collect(preg_split('/\r\n|\r|\n/', (string) ($row['options'] ?? '')))
                ->map(fn ($o) => trim($o))
                ->filter()
                ->values()
                ->all();

            $form->fields()->create([
                'label' => $row['label'],
                'name' => $name,
                'type' => $row['type'],
                'placeholder' => $row['placeholder'] ?? null,
                'options' => $options === [] ? null : $options,
                'is_required' => (bool) ($row['is_required'] ?? false),
                'validation_rules' => $row['validation_rules'] ?? null,
                'sort_order' => $position,
                'settings' => array_filter([
                    'step' => isset($row['step']) && $row['step'] !== '' ? (int) $row['step'] : null,
                    'display' => ($row['display'] ?? null) === 'chips' ? 'chips' : null,
                    'whatsapp' => $row['whatsapp'] ?? null,
                    'autofill' => $row['type'] === 'hidden' ? ($row['autofill'] ?? null) : null,
                ] + ($row['type'] === 'consent' ? array_intersect_key($row, array_flip(['note', 'short_label', 'short_note', 'link_text', 'link_url'])) : []),
                    fn ($v) => $v !== null && $v !== '') ?: null,
            ]);
        }
    }
}
