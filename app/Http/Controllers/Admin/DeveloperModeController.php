<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Services\DeveloperModeService;
use App\Services\ThemeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Developer Mode (Super Admin only): inspect + safely override the
 * HTML/CSS/JS of any CMS section. Originals are never modified.
 */
class DeveloperModeController extends Controller
{
    public function __construct(protected DeveloperModeService $developer)
    {
        $this->middleware('can:developer.mode');
    }

    /** GET — full modal payload (generated code, info, versions). */
    public function show(Section $section): JsonResponse
    {
        return response()->json($this->developer->payloadFor($section));
    }

    /** PUT — save the override (previous state is version-snapshotted). */
    public function update(Request $request, Section $section): JsonResponse
    {
        $data = $request->validate([
            'html' => ['nullable', 'string', 'max:200000'],
            'css' => ['nullable', 'string', 'max:50000'],
            'js' => ['nullable', 'string', 'max:50000'],
        ]);

        $this->developer->save($section, $data, $request->user()?->id);

        return response()->json($this->developer->payloadFor($section));
    }

    /** POST — Reset to Original (override deactivated, history kept). */
    public function reset(Request $request, Section $section): JsonResponse
    {
        $this->developer->reset($section, $request->user()?->id);

        return response()->json($this->developer->payloadFor($section));
    }

    /** POST — restore one entry from the version history. */
    public function restoreVersion(Request $request, Section $section, int $version): JsonResponse
    {
        $restored = $this->developer->restoreVersion($section, $version, $request->user()?->id);

        abort_if($restored === null, 404);

        return response()->json($this->developer->payloadFor($section));
    }

    /** POST — sandboxed preview document for the modal's iframe. */
    public function preview(Request $request, Section $section): Response
    {
        $data = $request->validate([
            'html' => ['nullable', 'string', 'max:200000'],
            'css' => ['nullable', 'string', 'max:50000'],
            'js' => ['nullable', 'string', 'max:50000'],
        ]);

        $themeCss = app(ThemeService::class)->renderCss();
        // Same frontend bundle the live site loads (see frontend/layouts/app).
        $assets = (string) app(\Illuminate\Foundation\Vite::class)(['resources/scss/frontend.scss', 'resources/js/frontend/app.js']);

        $doc = '<!DOCTYPE html><html><head><meta charset="utf-8">'
            .'<meta name="viewport" content="width=device-width, initial-scale=1">'
            .$assets
            .'<style>'.$themeCss.'</style>'
            .'<style>'.($data['css'] ?? '').'</style>'
            .'</head><body>'
            .($data['html'] ?? '')
            .'<script>'.($data['js'] ?? '').'</script>'
            .'</body></html>';

        return response($doc)->header('X-Frame-Options', 'SAMEORIGIN');
    }
}
