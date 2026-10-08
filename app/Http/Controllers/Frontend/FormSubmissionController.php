<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Services\FormSubmissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FormSubmissionController extends Controller
{
    public function __construct(protected FormSubmissionService $service)
    {
    }

    public function store(Request $request, string $slug): RedirectResponse|JsonResponse
    {
        $form = Form::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with('fields')
            ->firstOrFail();

        $wantsJson = $request->wantsJson() || $request->ajax();

        // Honeypot: hidden "website" field must stay empty.
        if ($request->filled('website')) {
            return $wantsJson ? response()->json(['ok' => true]) : redirect()->back();
        }

        // Validation failures throw ValidationException — Laravel auto-returns
        // a 422 JSON error bag for AJAX requests (no page reload needed).
        $this->service->submit($form, $request);

        if ($wantsJson) {
            return response()->json([
                'ok' => true,
                'message' => $form->success_message,
            ]);
        }

        return redirect()
            ->to(url()->previous().'#form-'.$form->slug)
            ->with("form_success_{$form->id}", $form->success_message);
    }
}
