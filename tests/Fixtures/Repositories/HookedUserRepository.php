<?php

namespace Tests\Fixtures\Repositories;

use Illuminate\Database\Eloquent\Model;

class HookedUserRepository extends UserRepository
{
    /**
     * @var list<string>
     */
    public array $calls = [];

    /**
     * @var array<string, mixed>
     */
    public array $lastOriginalAttributes = [];

    /**
     * @var array<string, mixed>
     */
    public array $lastSavedAttributes = [];

    #[\Override]
    protected function beforeCreate(array $attributes): array
    {
        $this->calls[] = 'beforeCreate';
        $attributes['name'] = strtoupper($attributes['name']);

        return $attributes;
    }

    #[\Override]
    protected function afterCreate(Model $model, array $originalAttributes, array $savedAttributes): Model
    {
        $this->calls[] = 'afterCreate';
        $this->rememberAttributes($originalAttributes, $savedAttributes);

        return $model;
    }

    #[\Override]
    protected function beforeUpdate(Model $model, array $attributes): array
    {
        $this->calls[] = 'beforeUpdate';
        $attributes['email'] = strtoupper($attributes['email']);

        return $attributes;
    }

    #[\Override]
    protected function afterUpdate(Model $model, array $originalAttributes, array $savedAttributes): Model
    {
        $this->calls[] = 'afterUpdate';
        $this->rememberAttributes($originalAttributes, $savedAttributes);

        return $model;
    }

    #[\Override]
    protected function beforeSave(Model $model, array $attributes): array
    {
        $this->calls[] = 'beforeSave';
        $attributes['password'] = 'hashed-'.$attributes['password'];

        return $attributes;
    }

    #[\Override]
    protected function afterSave(Model $model, array $originalAttributes, array $savedAttributes): Model
    {
        $this->calls[] = 'afterSave';
        $this->rememberAttributes($originalAttributes, $savedAttributes);

        return $model;
    }

    #[\Override]
    protected function beforeDelete(Model $model): Model
    {
        $this->calls[] = 'beforeDelete';
        $model->name = 'before-delete';

        return $model;
    }

    #[\Override]
    protected function afterDelete(Model $model): void
    {
        $this->calls[] = 'afterDelete';
    }

    #[\Override]
    protected function beforeForceDelete(Model $model): Model
    {
        $this->calls[] = 'beforeForceDelete';
        $model->name = 'before-force-delete';

        return $model;
    }

    #[\Override]
    protected function afterForceDelete(Model $model): void
    {
        $this->calls[] = 'afterForceDelete';
    }

    #[\Override]
    protected function afterRestore(Model $model): Model
    {
        $this->calls[] = 'afterRestore';

        return $model;
    }

    /**
     * @param  array<string, mixed>  $originalAttributes
     * @param  array<string, mixed>  $savedAttributes
     */
    private function rememberAttributes(array $originalAttributes, array $savedAttributes): void
    {
        $this->lastOriginalAttributes = $originalAttributes;
        $this->lastSavedAttributes = $savedAttributes;
    }
}
