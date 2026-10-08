<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Gradient;
use App\Repositories\Contracts\GradientRepositoryInterface;
use Illuminate\Support\Collection;

class GradientRepository implements GradientRepositoryInterface
{
    public function all(): Collection
    {
        return Gradient::query()->orderBy('name')->get();
    }

    public function allActive(): Collection
    {
        return Gradient::query()->where('is_active', true)->orderBy('name')->get();
    }

    public function create(array $data): Gradient
    {
        return Gradient::create($data);
    }

    public function update(Gradient $gradient, array $data): Gradient
    {
        $gradient->update($data);

        return $gradient->refresh();
    }

    public function delete(Gradient $gradient): void
    {
        $gradient->delete();
    }
}
