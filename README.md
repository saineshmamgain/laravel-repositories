# Laravel Repositories

Laravel Repositories is a small Laravel package for creating simple repository classes around Eloquent models. It stays close to Laravel: repositories return normal Eloquent models, collections, and builders, and generated repositories only declare which model they manage.

![GitHub release (latest by date)](https://img.shields.io/github/v/release/saineshmamgain/laravel-repositories?style=flat-square)
[![Latest Stable Version](http://poser.pugx.org/saineshmamgain/laravel-repositories/v)](https://packagist.org/packages/saineshmamgain/laravel-repositories) [![Total Downloads](http://poser.pugx.org/saineshmamgain/laravel-repositories/downloads)](https://packagist.org/packages/saineshmamgain/laravel-repositories) [![Latest Unstable Version](http://poser.pugx.org/saineshmamgain/laravel-repositories/v/unstable)](https://packagist.org/packages/saineshmamgain/laravel-repositories) [![License](http://poser.pugx.org/saineshmamgain/laravel-repositories/license)](https://packagist.org/packages/saineshmamgain/laravel-repositories)

## Requirements

- PHP `^8.3`
- Laravel `^11.0|^12.0|^13.12`

## Installation

```bash
composer require saineshmamgain/laravel-repositories
```

## Generate a Repository

```bash
php artisan make:repository User
php artisan make:repository Admin/User
php artisan make:repository User --force
```

By default, `php artisan make:repository User` creates `App\Repositories\UserRepository`.

Generated repositories are intentionally small:

```php
<?php

namespace App\Repositories;

use App\Models\User;
use SaineshMamgain\LaravelRepositories\Repositories\Repository;

class UserRepository extends Repository
{
    protected function model(): string
    {
        return User::class;
    }
}
```

## Usage

Inject repositories through Laravel's container:

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;

class UserController
{
    public function __construct(
        private readonly UserRepository $users,
    ) {
    }

    public function index()
    {
        return view('users.index', [
            'users' => $this->users->query()
                ->where('active', true)
                ->paginate(),
        ]);
    }

    public function store(Request $request)
    {
        $user = $this->users->create($request->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]));

        return redirect()->route('users.show', $user);
    }

    public function update(Request $request, User $user)
    {
        $this->users->update($user, $request->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
        ]));

        return redirect()->route('users.show', $user);
    }
}
```

## Public API

```php
$users->query();                         // Builder
$users->all(['*']);                      // Eloquent Collection
$users->find($id, ['*']);                // Model|null
$users->findOrFail($id, ['*']);          // Model
$users->create($attributes);             // Model
$users->createMany($records);            // Eloquent Collection
$users->update($userOrId, $attributes);  // Model
$users->delete($userOrId);               // bool
$users->forceDelete($userOrId);          // bool
$users->restore($userOrId);              // Model
```

`create()` and `update()` use Eloquent `fill()` and `save()`, so normal Laravel mass-assignment rules apply.

`update()`, `delete()`, `forceDelete()`, and `restore()` accept either an Eloquent model instance or a model id. Passing a model instance from the wrong class throws `RepositoryException`.

## Lifecycle Hooks

Repositories may override protected hooks for small model-specific behavior:

```php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

protected function beforeSave(Model $model, array $attributes): array
{
    if (array_key_exists('password', $attributes)) {
        $attributes['password'] = Hash::make($attributes['password']);
    }

    return $attributes;
}

protected function afterCreate(Model $model, array $originalAttributes, array $savedAttributes): Model
{
    if (array_key_exists('roles', $originalAttributes)) {
        $model->roles()->sync($originalAttributes['roles']);
    }

    return $model;
}
```

Hook order:

- `create`: `beforeCreate -> beforeSave -> save -> afterSave -> afterCreate`
- `update`: `beforeUpdate -> beforeSave -> save -> afterSave -> afterUpdate`
- `delete`: `beforeDelete -> delete -> afterDelete`
- `forceDelete`: `beforeForceDelete -> forceDelete/delete -> afterForceDelete`
- `restore`: `restore -> afterRestore`

Available hooks:

```php
protected function beforeCreate(array $attributes): array;
protected function afterCreate(Model $model, array $originalAttributes, array $savedAttributes): Model;
protected function beforeUpdate(Model $model, array $attributes): array;
protected function afterUpdate(Model $model, array $originalAttributes, array $savedAttributes): Model;
protected function beforeSave(Model $model, array $attributes): array;
protected function afterSave(Model $model, array $originalAttributes, array $savedAttributes): Model;
protected function beforeDelete(Model $model): Model;
protected function afterDelete(Model $model): void;
protected function beforeForceDelete(Model $model): Model;
protected function afterForceDelete(Model $model): void;
protected function afterRestore(Model $model): Model;
```

`beforeDelete()` and `beforeForceDelete()` can mutate the model object, but those changes are not automatically saved unless the hook explicitly saves them.

## When Not to Use Repositories

Do not add a repository just to wrap every Eloquent call. For simple reads, direct model queries are often clearer. This package is most useful when a model has repeated persistence behavior, a small lifecycle hook, or a team convention that keeps write operations behind one class.

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to test before submitting a PR.

## License

[MIT](https://choosealicense.com/licenses/mit/)
