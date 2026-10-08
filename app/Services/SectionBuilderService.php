<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Section;
use App\Repositories\Contracts\SectionRepositoryInterface;
use App\Services\SectionTypes\SectionTypeRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SectionBuilderService
{
    public function __construct(
        protected SectionRepositoryInterface $sections,
        protected SectionTypeRegistry $registry,
        protected LayoutService $layouts,
        protected PageRenderService $pageRenderer,
    ) {
    }

    /** Flush whichever render cache the owner belongs to. */
    private function flushFor(Model $owner): void
    {
        if ($owner instanceof \App\Models\Page) {
            $this->pageRenderer->flushCache($owner);

            return;
        }

        $this->layouts->flushCache();
    }

    public function addSection(Model $owner, string $area, string $typeKey): Section
    {
        $type = $this->registry->find($typeKey);

        if (! in_array($area, $type->allowedAreas(), true)) {
            throw ValidationException::withMessages([
                'type' => "Section [{$typeKey}] cannot be placed in the {$area} area.",
            ]);
        }

        $section = $this->sections->createFor($owner, [
            'type' => $typeKey,
            'name' => $type->label(),
            'sort_order' => $this->sections->nextSortOrder($owner),
            'data_desktop' => $type->defaults(),
        ]);

        $this->flushFor($owner);

        return $section;
    }

    /**
     * Update one device's payload (+ name/active/style). Desktop payload is
     * validated against the type schema; tablet/mobile accept only the
     * type's device-overridable keys (sparse diff, empty = inherit).
     */
    public function updateSection(Section $section, array $input): Section
    {
        $type = $this->registry->find($section->type);
        $device = $input['device'] ?? 'desktop';
        $attributes = [];

        // data === null means "meta-only save" (active toggle, visibility,
        // rename) — never touch the stored payload then.
        if (isset($input['data']) && is_array($input['data'])) {
            $payload = $input['data'];

            if ($device === 'desktop') {
                Validator::make(['data' => $payload], $type->rules())->validate();
                $attributes['data_desktop'] = $payload;
            } else {
                $allowed = $type->deviceKeys();
                $sparse = array_intersect_key($payload, array_flip($allowed));
                // Keep only real overrides; empty string/null = inherit desktop.
                $attributes['data_'.$device] = array_filter(
                    $sparse,
                    fn ($v) => $v !== null && $v !== ''
                );
            }
        }

        if (array_key_exists('name', $input)) {
            $attributes['name'] = (string) $input['name'];
        }
        if (array_key_exists('is_active', $input)) {
            $attributes['is_active'] = (bool) $input['is_active'];
        }
        if (array_key_exists('style', $input)) {
            $attributes['style'] = array_replace((array) $section->style, (array) $input['style']);
        }

        $section = $this->sections->updateSection($section, $attributes);
        $this->flushFor($section->sectionable);

        return $section;
    }

    public function cloneSection(Section $section): Section
    {
        $copy = $section->replicate();
        $copy->name = trim(($section->name ?? $section->type).' (copy)');
        $copy->sort_order = $this->sections->nextSortOrder($section->sectionable);
        $copy->save();

        $this->flushFor($section->sectionable);

        return $copy;
    }

    public function deleteSection(Section $section): void
    {
        $owner = $section->sectionable;
        $this->sections->deleteSection($section);
        $this->flushFor($owner);
    }

    public function reorder(Model $owner, array $orderedIds): void
    {
        $this->sections->reorder($owner, $orderedIds);
        $this->flushFor($owner);
    }
}
