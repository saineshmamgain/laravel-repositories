<?php

namespace SaineshMamgain\LaravelRepositories\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use SaineshMamgain\LaravelRepositories\Exceptions\RepositoryException;

/**
 * File: Repository.php
 * Date: 06/07/20
 * Author: Sainesh Mamgain <saineshmamgain@gmail.com>.
 */
abstract class BaseRepository
{
    /**
     * @var Model $model
     */
    protected $model;

    /**
     * Persist model in database.
     * @var bool $persist
     */
    protected $persist = true;

    /**
     * Refresh model after insert or update.
     * @var bool $refresh
     */
    protected $refresh = false;

    /**
     * @param Model|null $model
     */
    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * @param Model|null $model
     * @return static
     */
    public static function init($model = null)
    {
        return new static($model);
    }

    /**
     * @param array $fields
     * @throws RepositoryException
     * @return Model
     */
    public function update($fields)
    {
        if (!$this->model->exists) {
            throw new RepositoryException('Instance should not be fresh for update');
        }
        $original_fields = $fields;
        $fields = $this->beforeUpdate($fields);
        $this->save($fields);
        return $this->afterUpdate($original_fields, $fields);
    }

    /**
     * @param array $fields
     * @return array
     */
    protected function beforeUpdate($fields)
    {
        return $fields;
    }

    /**
     * @param array $original_fields
     * @param array $fields
     * @return Model
     */
    protected function afterUpdate($original_fields, $fields)
    {
        return $this->model;
    }

    /**
     * @param array $fields
     * @return array
     */
    protected function beforeCreate($fields)
    {
        return $fields;
    }

    /**
     * @param array $original_fields
     * @param array $fields
     * @return Model
     */
    protected function afterCreate($original_fields, $fields)
    {
        return $this->model;
    }

    /**
     * @param array $fields
     *
     * @return Model
     */
    public function save($fields)
    {
        $original_fields = $fields;
        $fields = $this->beforeSave($fields);

        foreach ($fields as $field => $value) {
            $this->model->{$field} = $value;
        }
        if ($this->persist) {
            $this->model->save();
            if ($this->refresh) {
                $this->model->refresh();
            }
        }

        return $this->afterSave($original_fields, $fields);
    }

    /**
     * @param bool $permanent
     *
     * @return bool|null
     * @throws RepositoryException
     *
     */
    public function destroy($permanent = false)
    {
        if (!$this->model->exists) {
            throw new RepositoryException('Model doesn\'t exist');
        }

        $isSoftDeletable = method_exists($this->model, 'getDeletedAtColumn');

        $this->model = $this->beforeDestroy($isSoftDeletable, $permanent);

        // check if model is soft deletable
        if ($isSoftDeletable) {
            if ($permanent) {
                return $this->model->forceDelete();
            }
        }

        return $this->model->delete();
    }

    /**
     * @param bool $persist
     *
     * @return $this
     *
     */
    public function persist($persist = true)
    {
        $this->persist = $persist;

        return $this;
    }

    /**
     * @param bool $refresh
     *
     * @return $this
     *
     */
    public function refresh($refresh = true)
    {
        $this->refresh = $refresh;

        return $this;
    }

    /**
     * @param array|callable $rows
     *
     * @throws RepositoryException
     *
     * @return Model[]
     */
    public function createMany($rows)
    {
        if (is_callable($rows)) {
            $rows = $rows();
        }
        $saved = [];
        foreach ($rows as $fields) {
            $saved[] = static::init()
                ->create($fields);
        }

        return $saved;
    }

    /**
     * @param array $fields
     *
     * @return Model
     * @throws RepositoryException
     *
     */
    public function create($fields)
    {
        if ($this->model->exists) {
            throw new RepositoryException('Fresh instance required for creation');
        }
        $original_fields = $fields;
        $fields = $this->beforeCreate($fields);
        $this->save($fields);
        return $this->afterCreate($original_fields, $fields);
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    protected function beforeSave($fields)
    {
        return $fields;
    }

    /**
     * @param array $original_fields
     * @param array $fields
     *
     * @return Model
     */
    protected function afterSave($original_fields, $fields)
    {
        return $this->model;
    }

    /**
     * @param bool $isSoftDeletable
     * @param bool $permanent
     *
     * @return Model
     */
    protected function beforeDestroy($isSoftDeletable, $permanent)
    {
        return $this->model;
    }

    /**
     * @throws RepositoryException
     *
     * @return Model
     */
    public function restore()
    {
        if (!$this->model->exists) {
            throw new RepositoryException('Instance should not be fresh for restoring');
        }
        if (!method_exists($this->model, 'getDeletedAtColumn')) {
            throw new RepositoryException('Model is not soft deletable');
        }
        if (!array_key_exists($this->model->getDeletedAtColumn(), $this->model->attributesToArray())) {
            throw new RepositoryException('Deleted at column doesn\'t exists on this instance');
        }
        $this->model->restore();

        return $this->afterRestore();
    }

    /**
     * @return Model
     */
    protected function afterRestore()
    {
        return $this->model;
    }

    /**
     * @return Builder
     */
    public function query(): Builder
    {
        return $this->model->newQuery();
    }

    /**
     * @param array $fields
     * @return Model
     *
     * Function that is not aware if the model is a new instance or already existing instance.
     *
     */
    public function touch($fields)
    {
        $original_fields = $fields;
        $fields = $this->beforeSave($fields);
        $this->save($fields);
        return $this->afterSave($original_fields, $fields);
    }
}
