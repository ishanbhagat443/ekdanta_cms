<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements BaseRepositoryInterface
{
    public function __construct(protected Model $model)
    {
    }

    public function all(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->query()->with($relations)->get($columns);
    }

    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): LengthAwarePaginator
    {
        return $this->query()->with($relations)->latest()->paginate($perPage, $columns);
    }

    public function find(int $id, array $relations = []): ?Model
    {
        return $this->query()->with($relations)->find($id);
    }

    public function findOrFail(int $id, array $relations = []): Model
    {
        return $this->query()->with($relations)->findOrFail($id);
    }

    public function findBySlug(string $slug, array $relations = []): ?Model
    {
        return $this->query()->with($relations)->where('slug', $slug)->first();
    }

    public function create(array $attributes): Model
    {
        return $this->model->newQuery()->create($attributes);
    }

    public function update(Model $model, array $attributes): Model
    {
        $model->fill($attributes)->save();

        return $model->refresh();
    }

    public function delete(Model $model): bool
    {
        return (bool) $model->delete();
    }

    protected function query(): Builder
    {
        return $this->model->newQuery();
    }
}
