<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DeveloperOverride;
use App\Models\Section;
use App\Repositories\Contracts\DeveloperOverrideRepositoryInterface;
use App\Services\SectionTypes\SectionTypeRegistry;

/**
 * Developer Mode: per-section HTML/CSS/JS override system. Originals are
 * never modified — overrides live in developer_overrides with full version
 * history. No override → the original Blade component renders; active
 * override → the frontend renders the stored markup instead.
 */
class DeveloperModeService
{
    public function __construct(
        protected DeveloperOverrideRepositoryInterface $overrides,
        protected LayoutService $layouts,
        protected SectionTypeRegistry $registry,
    ) {
    }

    /** Fresh render of the ORIGINAL Blade component (ignores overrides). */
    public function renderOriginal(Section $section): string
    {
        $context = $this->layouts->contextFor($section);

        return view('frontend.sections.'.$section->type, [
            'section' => $section,
            'menu' => $context['menu'],
            'mediaMap' => $context['media'],
        ])->render();
    }

    /** Everything the Developer Mode modal needs for one section. */
    public function payloadFor(Section $section): array
    {
        $override = $this->overrides->findForSection($section);
        $original = $this->renderOriginal($section);

        return [
            'section_id' => $section->id,
            'type' => $section->type,
            'name' => $section->name ?: $section->type,
            'has_override' => $override !== null && $override->is_active,
            'original_html' => $original,
            'html' => $override?->html ?? $original,
            'css' => $override?->css ?? (string) $section->styleValue('custom_css', ''),
            'js' => $override?->js ?? (string) $section->styleValue('custom_js', ''),
            'info' => $this->componentInfo($section),
            'versions' => $override?->versions()->with('author:id,name')->limit(20)->get()
                ->map(fn ($v) => [
                    'id' => $v->id,
                    'label' => $v->label,
                    'author' => $v->author?->name,
                    'created_at' => $v->created_at?->format('d M Y H:i'),
                ])->all() ?? [],
        ];
    }

    /** TAB 5 — where this section actually comes from. */
    public function componentInfo(Section $section): array
    {
        try {
            $type = $this->registry->find($section->type);
        } catch (\Throwable) {
            $type = null;
        }

        return [
            'Blade file' => 'resources/views/frontend/sections/'.$section->type.'.blade.php',
            'Component name' => $type ? $type->label().' ('.$section->type.')' : $section->type,
            'Component class' => $type ? get_class($type) : '—',
            'CSS file' => 'resources/scss/frontend.scss (compiled via Vite) + Theme Customizer stylesheet',
            'JavaScript file' => 'resources/js/frontend/app.js',
            'Data source' => 'Section payload (data_desktop / data_tablet / data_mobile JSON)',
            'Database table' => 'sections (overrides: developer_overrides)',
            'Model' => \App\Models\Section::class,
            'Controller' => \App\Http\Controllers\Admin\SectionController::class,
            'Repository' => \App\Repositories\Eloquent\SectionRepository::class,
            'Service' => \App\Services\SectionBuilderService::class.' / '.LayoutService::class.' / '.PageRenderService::class,
        ];
    }

    /** Save an override (snapshotting the previous state) + flush caches. */
    public function save(Section $section, array $data, ?int $userId = null): DeveloperOverride
    {
        $existing = $this->overrides->findForSection($section);
        $existing?->snapshot('before save', $userId);

        $override = $this->overrides->upsertForSection($section, [
            'html' => $data['html'] ?? null,
            'css' => $data['css'] ?? null,
            'js' => $data['js'] ?? null,
            'is_active' => true,
            'updated_by' => $userId,
        ]);

        $this->flushRenderCaches($section);

        return $override;
    }

    /** Reset to Original: snapshot, then remove the override entirely. */
    public function reset(Section $section, ?int $userId = null): void
    {
        $override = $this->overrides->findForSection($section);
        if ($override === null) {
            return;
        }

        // Deactivate (history rows stay restorable) — original component returns.
        $override->snapshot('before reset', $userId);
        $override->update(['is_active' => false, 'updated_by' => $userId]);

        $this->flushRenderCaches($section);
    }

    /** Restore a version from the history as the new active override. */
    public function restoreVersion(Section $section, int $versionId, ?int $userId = null): ?DeveloperOverride
    {
        $override = $this->overrides->findForSection($section);
        $version = $override?->versions()->whereKey($versionId)->first();

        if ($override === null || $version === null) {
            return null;
        }

        $override->snapshot('before restore', $userId);
        $override->update([
            'html' => $version->html,
            'css' => $version->css,
            'js' => $version->js,
            'is_active' => true,
            'updated_by' => $userId,
        ]);

        $this->flushRenderCaches($section);

        return $override->refresh();
    }

    /** Overrides render inside cached page/layout payloads — flush both. */
    private function flushRenderCaches(Section $section): void
    {
        $this->layouts->flushCache();

        $owner = $section->sectionable;
        app(PageRenderService::class)->flushCache($owner instanceof \App\Models\Page ? $owner : null);
    }
}
