<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ThemeFont;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ThemeFontService
{
    private const DISK = 'public';

    private const DIR = 'theme/fonts';

    public function __construct(protected ThemeService $theme)
    {
    }

    public function upload(array $data): ThemeFont
    {
        $slug = $this->uniqueSlug($data['name']);

        $font = ThemeFont::create([
            'name' => $data['name'],
            'slug' => $slug,
            'font_weight' => $data['font_weight'] ?? '400',
            'font_style' => $data['font_style'] ?? 'normal',
            'woff2_path' => $this->storeIfPresent($data['woff2'] ?? null, $slug, 'woff2'),
            'woff_path' => $this->storeIfPresent($data['woff'] ?? null, $slug, 'woff'),
            'ttf_path' => $this->storeIfPresent($data['ttf'] ?? null, $slug, 'ttf'),
        ]);

        $this->theme->flushCache();

        return $font;
    }

    public function delete(ThemeFont $font): void
    {
        foreach ([$font->woff2_path, $font->woff_path, $font->ttf_path] as $path) {
            if ($path && Storage::disk(self::DISK)->exists($path)) {
                Storage::disk(self::DISK)->delete($path);
            }
        }

        $font->delete();
        $this->theme->flushCache();
    }

    private function storeIfPresent(?UploadedFile $file, string $slug, string $ext): ?string
    {
        return $file?->storeAs(self::DIR, "{$slug}.{$ext}", self::DISK);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'font';
        $slug = $base;
        $suffix = 2;

        while (ThemeFont::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
