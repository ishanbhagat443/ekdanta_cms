<?php

declare(strict_types=1);

namespace App\Services;

use App\Support\Csv;
use App\Mail\FormSubmissionMail;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSubmission;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class FormSubmissionService
{
    /** Runtime Laravel rules from the admin-defined fields. */
    public function validationRules(Form $form): array
    {
        $rules = [];

        foreach ($form->fields as $field) {
            $fieldRules = [$field->is_required ? 'required' : 'nullable'];

            $fieldRules = array_merge($fieldRules, match ($field->type) {
                'email' => ['email:rfc,strict', 'max:191'],
                'phone' => ['string', 'max:30', 'regex:/^[+\d\s\-()]+$/'],
                'date' => ['date'],
                'textarea' => ['string', 'max:5000'],
                'select', 'radio' => ['string', 'in:'.implode(',', (array) $field->options)],
                'checkbox' => ['array'],
                'consent' => ['in:'.implode(',', FormField::CONSENT_VALUES)],
                'file' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx'],
                default => ['string', 'max:1000'],
            });

            if ($field->type === 'checkbox') {
                $rules[$field->name.'.*'] = ['string', 'in:'.implode(',', (array) $field->options)];
            }

            if ($field->validation_rules) {
                $fieldRules = array_merge($fieldRules, explode('|', $field->validation_rules));
            }

            $rules[$field->name] = $fieldRules;
        }

        return $rules;
    }

    /** Field-name → label map so validation errors read naturally. */
    public function attributeNames(Form $form): array
    {
        return $form->fields->pluck('label', 'name')->all();
    }

    public function submit(Form $form, Request $request): FormSubmission
    {
        $validated = Validator::make(
            $request->all(),
            $this->validationRules($form),
            $form->fields->where('type', 'consent')->mapWithKeys(fn (FormField $f) => [$f->name.'.required' => 'Tick the box above to send.'])->all(),
            $this->attributeNames($form)
        )->validate();

        $data = [];

        foreach ($form->fields as $field) {
            $value = $validated[$field->name] ?? null;

            // A ticked consent box keeps the proof: when, and the exact words shown.
            if ($field->type === 'consent' && $value !== null) {
                $value = 'Yes, '.now()->format('j M Y, H:i T').': '.$field->consentText($value === 'short');
            }

            if ($value instanceof UploadedFile) {
                $value = mm_upload_url(
                    mm_store_upload($value, 'form-uploads/'.now()->format('Y/m'))
                );
            }

            $data[$field->name] = $value;
        }

        $submission = $form->submissions()->create([
            'data' => $data,
            'ip' => $request->ip(),
            'status' => 'new',
        ]);

        if ($form->enquiry_source) {
            app(EnquiryService::class)->fromSubmission($form, $submission);
        }

        foreach ((array) $form->notify_emails as $email) {
            Mail::to($email)->send(new FormSubmissionMail($form, $submission));
        }

        return $submission;
    }

    /** Streamed CSV (UTF-8 BOM → opens cleanly in Excel). */
    public function exportCsv(Form $form): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $fields = $form->fields;

        return response()->streamDownload(function () use ($form, $fields): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM for Excel

            Csv::put($out, array_merge(['ID', 'Date', 'IP', 'Status'], $fields->pluck('label')->all()));

            $form->submissions()->orderBy('id')->chunk(200, function ($chunk) use ($out, $fields): void {
                foreach ($chunk as $submission) {
                    Csv::put($out, array_merge(
                        [
                            $submission->id,
                            $submission->created_at->format('Y-m-d H:i'),
                            $submission->ip,
                            $submission->status,
                        ],
                        $fields->map(fn (FormField $f) => $submission->valueFor($f->name))->all()
                    ));
                }
            });

            fclose($out);
        }, \Illuminate\Support\Str::slug($form->name).'-submissions.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
