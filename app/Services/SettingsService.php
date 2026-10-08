<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\SettingRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SettingsService
{
    public const CACHE_KEY = 'settings.all';

    private const UPLOAD_DIR = 'settings';

    public function __construct(protected SettingRepositoryInterface $settings)
    {
    }

    /**
     * Get one value by "group.key" notation.
     */
    public function get(string $dottedKey, mixed $default = null): mixed
    {
        return $this->cached()[$dottedKey] ?? $default;
    }

    /**
     * All values of a group as [key => value].
     */
    public function group(string $group): array
    {
        $prefix = $group.'.';
        $result = [];

        foreach ($this->cached() as $dotted => $value) {
            if (str_starts_with($dotted, $prefix)) {
                $result[substr($dotted, strlen($prefix))] = $value;
            }
        }

        return $result;
    }

    /**
     * Persist a whole settings group from validated input.
     *
     * Text-like fields come as strings/null. Image fields come as
     * UploadedFile (replace), "1" in $removals (delete), or absent (keep).
     */
    public function updateGroup(string $group, array $data, array $removals = []): void
    {
        $schema = (array) config("settings.{$group}.fields", []);
        $current = $this->group($group);

        foreach ($schema as $key => $field) {
            $type = $field['type'] ?? 'text';

            if ($type === 'image') {
                $value = $this->resolveImageValue(
                    $data[$key] ?? null,
                    $current[$key] ?? null,
                    in_array($key, $removals, true)
                );
                $this->settings->upsertValue($group, $key, $value, 'image');

                continue;
            }

            if (! array_key_exists($key, $data)) {
                continue;
            }

            $value = $data[$key];
            $this->settings->upsertValue($group, $key, $value === null ? null : (string) $value, $type);
        }

        $this->flushCache();
    }

    public function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Public URL for an image setting, or null.
     */
    public function imageUrl(string $dottedKey): ?string
    {
        $path = $this->get($dottedKey);

        return is_string($path) && $path !== '' ? Storage::disk('public')->url($path) : null;
    }

    private function resolveImageValue(mixed $incoming, ?string $currentPath, bool $remove): ?string
    {
        if ($incoming instanceof UploadedFile) {
            $this->deleteFile($currentPath);

            return mm_store_upload($incoming, self::UPLOAD_DIR);
        }

        if ($remove) {
            $this->deleteFile($currentPath);

            return null;
        }

        return $currentPath;
    }

    private function deleteFile(?string $path): void
    {
        if ($path !== null && $path !== '' && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /** @return array<string, string|null> */
    private function cached(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            return $this->settings->allSettings()
                ->mapWithKeys(fn ($row) => [$row->group.'.'.$row->key => $row->value])
                ->all();
        });
    }
}
