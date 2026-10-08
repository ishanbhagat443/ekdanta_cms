<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Gradient;
use App\Repositories\Contracts\GradientRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Global Gradient Library. Publishes every active gradient as
 * --gsm-gradient-{slug} plus a .gsm-bg-gradient-{slug} utility so any
 * surface (buttons, cards, sections, hero, header, footer) can reuse it.
 */
class GradientService
{
    public const CACHE_KEY = 'gradients.active';

    public function __construct(protected GradientRepositoryInterface $gradients)
    {
    }

    /** @return Collection<int, Gradient> */
    public function active(): Collection
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => $this->gradients->allActive());
    }

    public function findBySlug(?string $slug): ?Gradient
    {
        return $slug ? $this->active()->firstWhere('slug', $slug) : null;
    }

    /** CSS value for a slug — safe fallback when the gradient was deleted. */
    public function cssValue(?string $slug, string $fallback = 'none'): string
    {
        return $this->findBySlug($slug)?->toCss() ?? $fallback;
    }

    /** :root custom properties for every active gradient. */
    public function cssVariables(): string
    {
        return $this->active()
            ->map(fn (Gradient $g) => $g->cssVar().':'.$g->toCss().';')
            ->implode('');
    }

    /** Reusable background utility classes. */
    public function utilityCss(): string
    {
        return $this->active()
            ->map(fn (Gradient $g) => '.gsm-bg-gradient-'.$g->slug.'{background-image:var('.$g->cssVar().');}')
            ->implode('');
    }

    public function create(array $data): Gradient
    {
        $data['slug'] = $data['slug'] ?? $this->uniqueSlug($data['name']);
        $gradient = $this->gradients->create($data);
        $this->flushCache();

        return $gradient;
    }

    public function update(Gradient $gradient, array $data): Gradient
    {
        $gradient = $this->gradients->update($gradient, $data);
        $this->flushCache();

        return $gradient;
    }

    public function delete(Gradient $gradient): void
    {
        $this->gradients->delete($gradient);
        $this->flushCache();
    }

    /** Gradient CSS is embedded in the theme stylesheet — flush both. */
    public function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget(ThemeService::CSS_CACHE_KEY);
        Cache::forget(ButtonStyleService::CACHE_KEY);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'gradient';
        $slug = $base;
        $i = 2;

        while (Gradient::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
