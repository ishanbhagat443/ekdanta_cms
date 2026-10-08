<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Gradient;
use Illuminate\Support\Collection;

interface GradientRepositoryInterface
{
    /** @return Collection<int, Gradient> */
    public function all(): Collection;

    /** @return Collection<int, Gradient> */
    public function allActive(): Collection;

    public function create(array $data): Gradient;

    public function update(Gradient $gradient, array $data): Gradient;

    public function delete(Gradient $gradient): void;
}
