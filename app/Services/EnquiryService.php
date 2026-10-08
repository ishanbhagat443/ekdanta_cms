<?php

declare(strict_types=1);

namespace App\Services;

use App\Support\Csv;
use App\Models\Enquiry;
use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EnquiryService
{
    /**
     * Funnel a Form Builder submission into the enquiry inbox.
     * Core columns are mapped by field type (first email field → email, etc.);
     * whatever doesn't fit lands in `extra` as label => value.
     */
    public function fromSubmission(Form $form, FormSubmission $submission): Enquiry
    {
        $fields = $form->fields;
        $used = [];

        $pick = function (callable $matcher) use ($fields, $submission, &$used): ?string {
            foreach ($fields as $field) {
                if (in_array($field->name, $used, true) || ! $matcher($field)) {
                    continue;
                }
                $used[] = $field->name;

                return $submission->valueFor($field->name) ?: null;
            }

            return null;
        };

        // Prefer a text field whose name mentions "name"; fall back to the first text field.
        $name = $pick(fn ($f) => $f->type === 'text' && str_contains($f->name, 'name'))
            ?? $pick(fn ($f) => $f->type === 'text');

        $enquiry = Enquiry::create([
            'source' => $form->enquiry_source,
            'name' => $name,
            'email' => $pick(fn ($f) => $f->type === 'email'),
            'phone' => $pick(fn ($f) => $f->type === 'phone'),
            'message' => $pick(fn ($f) => $f->type === 'textarea'),
            'extra' => $this->extras($fields, $submission, $used) ?: null,
            'ip' => $submission->ip,
            'status' => 'new',
            'form_submission_id' => $submission->id,
        ]);

        return $enquiry;
    }

    /** @return array<string, string> label => value for fields not mapped to core columns */
    private function extras($fields, FormSubmission $submission, array $used): array
    {
        $extra = [];

        foreach ($fields as $field) {
            if (in_array($field->name, $used, true)) {
                continue;
            }
            if (($value = $submission->valueFor($field->name)) !== '') {
                $extra[$field->label] = $value;
            }
        }

        return $extra;
    }

    /** Streamed CSV of the (filtered) inbox — UTF-8 BOM so Excel opens it cleanly. */
    public function exportCsv(array $filters): StreamedResponse
    {
        return response()->streamDownload(function () use ($filters): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            Csv::put($out, ['ID', 'Source', 'Name', 'Email', 'Phone', 'Message', 'Extra', 'IP', 'Date', 'Status', 'Admin Notes']);

            Enquiry::query()->filtered($filters)->orderBy('id')->chunk(200, function ($chunk) use ($out): void {
                foreach ($chunk as $enquiry) {
                    Csv::put($out, [
                        $enquiry->id,
                        $enquiry->sourceLabel(),
                        $enquiry->name,
                        $enquiry->email,
                        $enquiry->phone,
                        $enquiry->message,
                        collect($enquiry->extra ?? [])->map(fn ($v, $k) => "{$k}: {$v}")->implode('; '),
                        $enquiry->ip,
                        $enquiry->created_at->format('Y-m-d H:i'),
                        $enquiry->status,
                        $enquiry->admin_notes,
                    ]);
                }
            });

            fclose($out);
        }, 'enquiries-'.Str::slug($filters['source'] ?? 'all').'-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
