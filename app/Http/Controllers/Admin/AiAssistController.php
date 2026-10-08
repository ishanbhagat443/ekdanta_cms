<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AiAssistService;
use App\Services\SectionTypes\SectionTypeRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;

/**
 * The editor's AI helpers. Both actions return text for the user to accept or
 * discard — nothing is written to the database here.
 */
class AiAssistController extends Controller
{
    public function __construct(
        protected AiAssistService $ai,
        protected SectionTypeRegistry $registry,
    ) {
        // Writing copy is editing the site, so it needs an editing permission.
        $this->middleware('can:pages.manage');
    }

    public function improve(Request $request): JsonResponse
    {
        if (! $this->ai->allows('improve_text')) {
            return response()->json(['message' => 'The writing assistant is switched off.'], 404);
        }

        $data = $request->validate([
            'text' => ['required', 'string', 'max:5000'],
            'tone' => ['nullable', Rule::in(['clearer', 'shorter', 'longer', 'friendlier', 'formal', 'fix'])],
        ]);

        if ($limited = $this->rateLimited($request)) {
            return $limited;
        }

        try {
            return response()->json([
                'text' => $this->ai->improve($data['text'], $data['tone'] ?? 'clearer'),
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function draftSection(Request $request): JsonResponse
    {
        if (! $this->ai->allows('generate_section')) {
            return response()->json(['message' => 'The writing assistant is switched off.'], 404);
        }

        $data = $request->validate([
            'type' => ['required', 'string', 'max:50'],
            'brief' => ['required', 'string', 'max:1000'],
        ]);

        if (! $this->registry->has($data['type'])) {
            return response()->json(['message' => 'Unknown section type.'], 422);
        }

        if ($limited = $this->rateLimited($request)) {
            return $limited;
        }

        $type = $this->registry->find($data['type']);

        try {
            return response()->json([
                'values' => $this->ai->draftSection($type->label(), $type->fields(), $data['brief']),
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /** Per-user, so one runaway editor cannot spend the whole budget. */
    private function rateLimited(Request $request): ?JsonResponse
    {
        $key = 'ai-assist:'.($request->user()?->id ?? $request->ip());

        if (RateLimiter::tooManyAttempts($key, (int) config('ai.rate_limit', 20))) {
            return response()->json([
                'message' => 'That is a lot of requests in a minute. Wait '
                    .RateLimiter::availableIn($key).' seconds and try again.',
            ], 429);
        }

        RateLimiter::hit($key, 60);

        return null;
    }
}
