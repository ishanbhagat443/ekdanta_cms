<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Setting;
use App\Repositories\Contracts\SettingRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class SettingRepository implements SettingRepositoryInterface
{
    public function __construct(protected Setting $model)
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
}
