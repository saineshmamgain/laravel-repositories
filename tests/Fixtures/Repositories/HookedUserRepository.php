<?php

namespace Tests\Fixtures\Repositories;

use Illuminate\Database\Eloquent\Model;
use Tests\Fixtures\Models\User;

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

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    #[\Override]
    protected function beforeCreate(array $attributes): array
    {
        $this->calls[] = 'beforeCreate';
        $attributes['name'] = strtoupper($attributes['name']);

        return $attributes;
    }

    /**
     * @param  User  $model
     * @param  array<string, mixed>  $originalAttributes
     * @param  array<string, mixed>  $savedAttributes
     * @return User
     */
    #[\Override]
    protected function afterCreate(Model $model, array $originalAttributes, array $savedAttributes): Model
    {
        $this->calls[] = 'afterCreate';
        $this->rememberAttributes($originalAttributes, $savedAttributes);

        return $model;
    }

    /**
     * @param  User  $model
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    #[\Override]
    protected function beforeUpdate(Model $model, array $attributes): array
    {
        $this->calls[] = 'beforeUpdate';
        $attributes['email'] = strtoupper($attributes['email']);

        return $attributes;
    }

    /**
     * @param  User  $model
     * @param  array<string, mixed>  $originalAttributes
     * @param  array<string, mixed>  $savedAttributes
     * @return User
     */
    #[\Override]
    protected function afterUpdate(Model $model, array $originalAttributes, array $savedAttributes): Model
    {
        $this->calls[] = 'afterUpdate';
        $this->rememberAttributes($originalAttributes, $savedAttributes);

        return $model;
    }

    /**
     * @param  User  $model
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    #[\Override]
    protected function beforeSave(Model $model, array $attributes): array
    {
        $this->calls[] = 'beforeSave';
        $attributes['password'] = 'hashed-'.$attributes['password'];

        return $attributes;
    }

    /**
     * @param  User  $model
     * @param  array<string, mixed>  $originalAttributes
     * @param  array<string, mixed>  $savedAttributes
     * @return User
     */
    #[\Override]
    protected function afterSave(Model $model, array $originalAttributes, array $savedAttributes): Model
    {
        $this->calls[] = 'afterSave';
        $this->rememberAttributes($originalAttributes, $savedAttributes);

        return $model;
    }

    /**
     * @param  User  $model
     * @return User
     */
    #[\Override]
    protected function beforeDelete(Model $model): Model
    {
        $this->calls[] = 'beforeDelete';
        $model->name = 'before-delete';

        return $model;
    }

    /**
     * @param  User  $model
     */
    #[\Override]
    protected function afterDelete(Model $model): void
    {
        $this->calls[] = 'afterDelete';
    }

    /**
     * @param  User  $model
     * @return User
     */
    #[\Override]
    protected function beforeForceDelete(Model $model): Model
    {
        $this->calls[] = 'beforeForceDelete';
        $model->name = 'before-force-delete';

        return $model;
    }

    /**
     * @param  User  $model
     */
    #[\Override]
    protected function afterForceDelete(Model $model): void
    {
        $this->calls[] = 'afterForceDelete';
    }

    /**
     * @param  User  $model
     * @return User
     */
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
