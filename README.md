# Laravel Repositories

A package to create repositories in your laravel applications.

## Install

``composer require saineshmamgain/laravel-repositories``

## Create a repository

You can create a repository by `php artisan make:repository {ModelName}`

This package will automatically search for model mentioned in the command in both `\App` and `\App\Models` namespaces.

This package ships with a simple repository.

After running command you will get a Repository created in the `\App\Repositories` namespace.

Repository ships with some common functions for example:

### Create

```php
use App\Repositories\UserRepository;

UserRepository::init()
    ->create([
        "name" => "John Doe", 
        "email" => "johndoe@example.com
    ]);
```

Since you will get a repository class you can define `beforeCreate`, `afterCreate`, `beforeUpdate`, `afterUpdate`, `beforeDestroy`, `afterDestroy`, `beforeRestore`, `afterRestore`,`beforeSave` and `afterSave` methods in the class itself.

Example:

```php
// in \App\Repositories\UserRepository

use Illuminate\Support\Facades\Hash;

protected function beforeSave($fields)
{
    if (array_key_exists('password', $fields)){
        $fields['password'] = Hash::make($fields['password']);
    }
    if (array_key_exists('role', $fields)){
        unset($fields['role']);
    }
}
```

`afterSave()` method receives 2 values:

`$original_fields`: These are the fields that were actually inserted.

`$fields`: These are the fields changed by `beforeSave()` method.

If `beforeSave` method is not defined then both `$original_fields` and `$fields` will be same.

```php
use App\Repositories\RoleRepository;

protected function afterSave($original_fields, $fields)
{
    $role = RoleRepository::init()
            ->persist(false)
            ->create([
                'role' => $original_fields['role']
            ]);
            
    $this->model->role()->save($role);        
}

```
