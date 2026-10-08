<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Media;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Support\Collection;

/**
 * The "what do I do first?" checklist on the dashboard.
 *
 * Every step is answered from real state — no stored flags to drift out of
 * sync, and nothing to tick by hand. A step that is done is done because the
 * site actually has that thing, which also means the list disappears on its
 * own once the site is genuinely set up.
 */
class OnboardingService
{
    public function __construct(protected ThemeService $theme)
    {
    }

    /** @return Collection<int, array<string, mixed>> */
    public function steps(): Collection
    {
        return collect([
            [
                'key' => 'logo',
                'title' => 'Add your logo',
                'text' => 'Your logo appears in the header, the footer and on emails.',
                'done' => (bool) (setting_image('branding.logo_dark') ?: setting_image('branding.logo_light')),
                'url' => route('admin.settings.edit', 'branding'),
                'action' => 'Upload a logo',
                'icon' => 'fas fa-fingerprint',
            ],
            [
                'key' => 'brand',
                'title' => 'Choose your colours and fonts',
                'text' => 'Set them once and every page follows.',
                'done' => $this->brandTouched(),
                'url' => route('admin.brand.index'),
                'action' => 'Open Brand',
                'icon' => 'fas fa-palette',
            ],
            [
                'key' => 'page',
                'title' => 'Build your first page',
                'text' => 'Start from a ready-made layout and change the words to yours.',
                'done' => $this->hasPageWithContent(),
                'url' => route('admin.pages.index'),
                'action' => 'Create a page',
                'icon' => 'fas fa-file-lines',
            ],
            [
                'key' => 'menu',
                'title' => 'Set up your menu',
                'text' => 'Decide what visitors see in the navigation at the top.',
                'done' => $this->hasMenuItems(),
                'url' => route('admin.menus.index'),
                'action' => 'Edit the menu',
                'icon' => 'fas fa-stream',
            ],
            [
                'key' => 'business',
                'title' => 'Add your contact details',
                'text' => 'Your address, phone and email fill the footer and contact page.',
                'done' => (bool) setting('business.email') || (bool) setting('business.phone'),
                'url' => route('admin.settings.edit', 'business'),
                'action' => 'Add details',
                'icon' => 'fas fa-address-card',
            ],
            [
                'key' => 'publish',
                'title' => 'Publish your site',
                'text' => 'A published page is one visitors can actually see.',
                'done' => Page::where('status', Page::STATUS_PUBLISHED)->exists(),
                'url' => route('admin.pages.index'),
                'action' => 'Review pages',
                'icon' => 'fas fa-rocket',
            ],
        ]);
    }

    public function remaining(): int
    {
        return $this->steps()->reject(fn (array $step) => $step['done'])->count();
    }

    public function isComplete(): bool
    {
        return $this->remaining() === 0;
    }

    /** Rough activity numbers for the dashboard tiles. */
    public function counts(): array
    {
        return [
            'pages' => Page::count(),
            'posts' => Post::count(),
            'media' => Media::count(),
            'enquiries' => \App\Models\Enquiry::count(),
        ];
    }

    /**
     * "Touched" means any theme value has been saved, whatever it is — the
     * point of the step is to get someone into the Brand screen once, not to
     * police which colour they chose.
     */
    private function brandTouched(): bool
    {
        return \App\Models\ThemeSetting::query()->exists();
    }

    private function hasPageWithContent(): bool
    {
        return Page::query()->whereHas('sections')->exists();
    }

    private function hasMenuItems(): bool
    {
        return \App\Models\MenuItem::query()->exists();
    }
}
