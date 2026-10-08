<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\ButtonStyle;
use App\Repositories\Contracts\ButtonStyleRepositoryInterface;
use Illuminate\Support\Collection;

class ButtonStyleRepository implements ButtonStyleRepositoryInterface
{
    public function all(): Collection
    {
        return ButtonStyle::query()->orderBy('name')->get();
    }

    public function allActive(): Collection
    {
        return ButtonStyle::query()->where('is_active', true)->orderBy('name')->get();
    }

    public function create(array $data): ButtonStyle
    {
        return ButtonStyle::create($data);
    }

    public function update(ButtonStyle $style, array $data): ButtonStyle
    {
        $style->update($data);

        return $style->refresh();
    }

    public function delete(ButtonStyle $style): void
    {
        $style->delete();
    }
}
