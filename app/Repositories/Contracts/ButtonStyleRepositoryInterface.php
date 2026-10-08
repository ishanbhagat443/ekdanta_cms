<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\ButtonStyle;
use Illuminate\Support\Collection;

interface ButtonStyleRepositoryInterface
{
    /** @return Collection<int, ButtonStyle> */
    public function all(): Collection;

    /** @return Collection<int, ButtonStyle> */
    public function allActive(): Collection;

    public function create(array $data): ButtonStyle;

    public function update(ButtonStyle $style, array $data): ButtonStyle;

    public function delete(ButtonStyle $style): void;
}
