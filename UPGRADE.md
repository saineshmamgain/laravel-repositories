# Upgrade Guide

## v1 to v2

v2 is a breaking release. It replaces the mutable v1 repository instance API with a Laravel-native repository class that is resolved through dependency injection and manages one Eloquent model class.

## Main API Changes

Old v1 create:

```php
UserRepository::init()->create([
    'name' => 'Jane Doe',
]);
```

New v2 create:

```php
$users->create([
    'name' => 'Jane Doe',
]);
```

Old v1 update:

```php
UserRepository::init($user)->update([
    'name' => 'Jane Doe',
]);
```

New v2 update:

```php
$users->update($user, [
    'name' => 'Jane Doe',
]);

$users->update($user->id, [
    'name' => 'Jane Doe',
]);
```

Old v1 delete:

```php
UserRepository::init($user)->destroy();
```

New v2 delete:

```php
$users->delete($user);
$users->forceDelete($user);
```

## Removed APIs

These v1 APIs were removed:

- `BaseRepository`
- `static init()`
- `persist(false)`
- `refresh()`
- `touch()`
- `destroy($permanent)`

Use constructor injection or Laravel container resolution for repositories. Use Eloquent model methods directly when you need unsaved in-memory mutation, refreshing, or timestamp touching.

## Generated Repositories

Generated repositories no longer include constructors or pass-through CRUD methods. A generated v2 repository only declares its model:

```php
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

## Hooks

Hooks remain supported, but signatures changed.

v2 hooks receive the model where useful and distinguish original input attributes from the attributes actually saved after before-hooks run:

```php
protected function beforeSave(Model $model, array $attributes): array;
protected function afterSave(Model $model, array $originalAttributes, array $savedAttributes): Model;
```

Delete hooks are now split:

```php
protected function beforeDelete(Model $model): Model;
protected function afterDelete(Model $model): void;
protected function beforeForceDelete(Model $model): Model;
protected function afterForceDelete(Model $model): void;
```

## Mass Assignment

v2 uses Eloquent `fill()` and `save()` for `create()` and `update()`. Normal Laravel mass-assignment rules apply, so review `$fillable` and `$guarded` on your models before upgrading.

## Migration Checklist

1. Replace generated repositories with the v2 generated shape.
2. Inject repositories through constructors instead of calling the old static constructor.
3. Replace old update calls with `$repository->update($modelOrId, $attributes)`.
4. Replace old delete calls with `$repository->delete($modelOrId)` or `$repository->forceDelete($modelOrId)`.
5. Move any custom hook logic to the new hook signatures.
6. Review model `$fillable` and `$guarded` settings.
7. Run your test suite and check every repository that previously depended on removed v1 APIs.
