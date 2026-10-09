<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSubmission;
use App\Services\FormBuilderService;
use App\Services\FormSubmissionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FormController extends Controller
{
    public function __construct(
        protected FormBuilderService $builder,
        protected FormSubmissionService $submissions,
    ) {
        $this->middleware('can:forms.manage');
    }

    public function index(): View
    {
        return view('admin.forms.index', [
            'forms' => Form::query()
                ->withCount(['fields', 'submissions', 'submissions as new_submissions_count' => fn ($q) => $q->where('status', 'new')])
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:150']]);

        $form = $this->builder->createForm($data + ['button_label' => null, 'success_message' => null]);

        return redirect()->route('admin.forms.edit', $form)->with('success', 'Form created — add fields below.');
    }

    public function edit(Form $form): View
    {
        return view('admin.forms.edit', [
            'form' => $form->load('fields'),
            'fieldTypes' => FormField::TYPES,
        ]);
    }

    public function update(Request $request, Form $form): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:191', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'heading' => ['nullable', 'string', 'max:191'],
            'button_label' => ['nullable', 'string', 'max:60'],
            'success_message' => ['nullable', 'string', 'max:191'],
            'notify_emails_csv' => ['nullable', 'string', 'max:500'],
            'store_as_enquiry' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'fields' => ['sometimes', 'array', 'max:30'],
            'fields.*.label' => ['nullable', 'string', 'max:150'],
            'fields.*.type' => ['nullable', 'in:'.implode(',', FormField::TYPES)],
            'fields.*.placeholder' => ['nullable', 'string', 'max:150'],
            'fields.*.options' => ['nullable', 'string', 'max:2000'],
            'fields.*.is_required' => ['nullable', 'boolean'],
            'fields.*.validation_rules' => ['nullable', 'string', 'max:191'],
        ]);

        $this->builder->updateForm($form, $data);

        return redirect()->route('admin.forms.edit', $form->fresh())->with('success', 'Form saved.');
    }

    public function destroy(Form $form): RedirectResponse
    {
        $this->builder->deleteForm($form);

        return redirect()->route('admin.forms.index')->with('success', 'Form deleted.');
    }

    /* ── Submissions ───────────────────────────────────────────── */

    public function submissions(Form $form): View
    {
        return view('admin.forms.submissions', [
            'form' => $form->load('fields'),
            'submissions' => $form->submissions()->paginate(20),
        ]);
    }

    public function markRead(Form $form, FormSubmission $submission): RedirectResponse
    {
        abort_unless($submission->form_id === $form->id, 404);
        $submission->update(['status' => 'read']);

        return back()->with('success', 'Marked as read.');
    }

    public function destroySubmission(Form $form, FormSubmission $submission): RedirectResponse
    {
        abort_unless($submission->form_id === $form->id, 404);
        $submission->delete();

        return back()->with('success', 'Submission deleted.');
    }

    public function export(Form $form): StreamedResponse
    {
        return $this->submissions->exportCsv($form);
    }
}
