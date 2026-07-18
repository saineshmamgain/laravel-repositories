<?php

declare(strict_types=1);

namespace SaineshMamgain\LaravelRepositories\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
interface RepositoryInterface
{
    /**
     * @return Builder<TModel>
     */
    public function query(): Builder;

    /**
     * @return Collection<int, TModel>
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * @return TModel|null
     */
    public function find(int|string $id, array $columns = ['*']): ?Model;

    /**
     * @return TModel
     */
    public function findOrFail(int|string $id, array $columns = ['*']): Model;

    /**
     * @return TModel
     */
    public function create(array $attributes): Model;

    /**
     * @param  iterable<array<string, mixed>>  $records
     * @return Collection<int, TModel>
     */
    public function createMany(iterable $records): Collection;

    /**
     * @return TModel
     */
    public function update(Model|int|string $model, array $attributes): Model;

    public function delete(Model|int|string $model): bool;

    public function forceDelete(Model|int|string $model): bool;

    /**
     * @return TModel
     */
    public function restore(Model|int|string $model): Model;
}
