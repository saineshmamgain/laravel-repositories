<?php

namespace SaineshMamgain\LaravelRepositories\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use SaineshMamgain\LaravelRepositories\Contracts\RepositoryInterface;
use SaineshMamgain\LaravelRepositories\Exceptions\RepositoryException;

/**
 * @template TModel of Model
 *
 * @implements RepositoryInterface<TModel>
 */
abstract class Repository implements RepositoryInterface
{
    /**
     * @var class-string<TModel>|null
     */
    private ?string $modelClass = null;

    /**
     * @return class-string<TModel>
     */
    abstract protected function model(): string;

    /**
     * @return Builder<TModel>
     */
    public function query(): Builder
    {
        return $this->newModel()->newQuery();
    }

    /**
     * @return Collection<int, TModel>
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->query()->get($columns);
    }

    /**
     * @return TModel|null
     */
    public function find(int|string $id, array $columns = ['*']): ?Model
    {
        return $this->query()->find($id, $columns);
    }

    /**
     * @return TModel
     */
    public function findOrFail(int|string $id, array $columns = ['*']): Model
    {
        return $this->query()->findOrFail($id, $columns);
    }

    /**
     * @return TModel
     */
    public function create(array $attributes): Model
    {
        $model = $this->newModel();

        $originalAttributes = $attributes;
        $savedAttributes = $this->beforeCreate($attributes);
        $savedAttributes = $this->beforeSave($model, $savedAttributes);

        $model->fill($savedAttributes);
        $model->save();

        $model = $this->afterSave($model, $originalAttributes, $savedAttributes);

        return $this->afterCreate($model, $originalAttributes, $savedAttributes);
    }

    /**
     * @param  iterable<array<string, mixed>>  $records
     * @return Collection<int, TModel>
     */
    public function createMany(iterable $records): Collection
    {
        $models = new Collection;

        foreach ($records as $attributes) {
            $models->push($this->create($attributes));
        }

        return $models;
    }

    /**
     * @return TModel
     */
    public function update(Model|int|string $model, array $attributes): Model
    {
        $model = $this->resolveModel($model);

        $originalAttributes = $attributes;
        $savedAttributes = $this->beforeUpdate($model, $attributes);
        $savedAttributes = $this->beforeSave($model, $savedAttributes);

        $model->fill($savedAttributes);
        $model->save();

        $model = $this->afterSave($model, $originalAttributes, $savedAttributes);

        return $this->afterUpdate($model, $originalAttributes, $savedAttributes);
    }

    public function delete(Model|int|string $model): bool
    {
        $model = $this->beforeDelete($this->resolveModel($model));
        $deleted = (bool) $model->delete();

        if ($deleted) {
            $this->afterDelete($model);
        }

        return $deleted;
    }

    public function forceDelete(Model|int|string $model): bool
    {
        $model = $this->resolveModel($model, withTrashed: true);
        $model = $this->beforeForceDelete($model);

        $deleted = $this->supportsSoftDeletes() ? (bool) $model->forceDelete() : (bool) $model->delete();

        if ($deleted) {
            $this->afterForceDelete($model);
        }

        return $deleted;
    }

    /**
     * @return TModel
     */
    public function restore(Model|int|string $model): Model
    {
        if (! $this->supportsSoftDeletes()) {
            throw new RepositoryException(sprintf(
                'Model [%s] does not support soft deletes.',
                $this->modelClass(),
            ));
        }

        $model = $this->resolveModel($model, withTrashed: true);
        $model->restore();

        return $this->afterRestore($model);
    }

    protected function beforeCreate(array $attributes): array
    {
        return $attributes;
    }

    /**
     * @return TModel
     */
    protected function afterCreate(Model $model, array $originalAttributes, array $savedAttributes): Model
    {
        return $model;
    }

    protected function beforeUpdate(Model $model, array $attributes): array
    {
        return $attributes;
    }

    /**
     * @return TModel
     */
    protected function afterUpdate(Model $model, array $originalAttributes, array $savedAttributes): Model
    {
        return $model;
    }

    protected function beforeSave(Model $model, array $attributes): array
    {
        return $attributes;
    }

    /**
     * @return TModel
     */
    protected function afterSave(Model $model, array $originalAttributes, array $savedAttributes): Model
    {
        return $model;
    }

    /**
     * @return TModel
     */
    protected function beforeDelete(Model $model): Model
    {
        return $model;
    }

    protected function afterDelete(Model $model): void
    {
        //
    }

    /**
     * @return TModel
     */
    protected function beforeForceDelete(Model $model): Model
    {
        return $model;
    }

    protected function afterForceDelete(Model $model): void
    {
        //
    }

    /**
     * @return TModel
     */
    protected function afterRestore(Model $model): Model
    {
        return $model;
    }

    /**
     * @return TModel
     */
    protected function newModel(): Model
    {
        $modelClass = $this->modelClass();

        return new $modelClass;
    }

    /**
     * @return TModel
     */
    protected function resolveModel(Model|int|string $model, bool $withTrashed = false): Model
    {
        if (! $model instanceof Model) {
            return $this->queryForResolution($withTrashed)->findOrFail($model);
        }

        $modelClass = $this->modelClass();

        if (! $model instanceof $modelClass) {
            throw new RepositoryException(sprintf(
                'Model [%s] is not an instance of [%s].',
                $model::class,
                $modelClass,
            ));
        }

        return $model;
    }

    /**
     * @return Builder<TModel>
     */
    private function queryForResolution(bool $withTrashed): Builder
    {
        $query = $this->query();

        if ($withTrashed && $this->supportsSoftDeletes()) {
            $query->withTrashed();
        }

        return $query;
    }

    /**
     * @return class-string<TModel>
     */
    private function modelClass(): string
    {
        if ($this->modelClass !== null) {
            return $this->modelClass;
        }

        $modelClass = $this->model();

        if (! is_a($modelClass, Model::class, true)) {
            throw new RepositoryException(sprintf(
                'Repository model [%s] must be an instance of [%s].',
                $modelClass,
                Model::class,
            ));
        }

        return $this->modelClass = $modelClass;
    }

    private function supportsSoftDeletes(): bool
    {
        return method_exists($this->newModel(), 'getDeletedAtColumn');
    }
}
