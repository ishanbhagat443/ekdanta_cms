<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\ThemeSetting;
use App\Repositories\Contracts\ThemeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ThemeRepository implements ThemeRepositoryInterface
{
    public function __construct(protected ThemeSetting $model)
    {
    }

    public function allSettings(): Collection
    {
        return $this->model->newQuery()->get();
    }

    public function byGroup(string $group): Collection
    {
        return $this->model->newQuery()->where('group', $group)->get();
    }

    public function upsertValue(string $group, string $key, ?string $value, string $type = 'text'): void
    {
        $this->model->newQuery()->updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => $value, 'type' => $type]
        );
    }

    public function replaceAll(array $flatMap): void
    {
        DB::transaction(function () use ($flatMap): void {
            $this->model->newQuery()->delete();

            $now = now();
            $rows = [];

            foreach ($flatMap as $dotted => $entry) {
                [$group, $key] = explode('.', $dotted, 2);
                $rows[] = [
                    'group' => $group,
                    'key' => $key,
                    'value' => $entry['value'] ?? null,
                    'type' => $entry['type'] ?? 'text',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            foreach (array_chunk($rows, 200) as $chunk) {
                $this->model->newQuery()->insert($chunk);
            }
        });
    }
}
