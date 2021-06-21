# Laravel Repositories

Laravel Repositories is a Laravel package that makes creating and managing repositories a breeze.

## Installation

Use the package manager [composer](https://getcomposer.org/download/) to install the package.

```bash
composer require saineshmamgain/laravel-repositories
```

## Usage

### Create a repository

```bash
#php artisan make:repository {ModelName}

php artisan make:repository User
```

This command will create a `UserRepository` in `App\Repositories` namespace.

### Inserting a record

```php
// In UsersController
use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller{

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required'
            'password' => 'required'
        ]);
        
        $validated = $validator->validated();

        UserRepository::init()
            ->create($validated);

        return redirect()->back();
    }
}
```

### Updating a record

```php
// In UsersController
use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class UsersController extends Controller{

    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required'
            'password' => 'required'
        ]);
        
        $validated = $validator->validated();

        UserRepository::init($user)
            ->update($validated);

        return redirect()->back();
    }
}
```
### Deleting a record

This package also supports `softDelete`. By default the package will check if the model uses `softDelete` if yes it will `softDelete` the model. To delete a model permanently just pass `true` while calling `destroy(true)` method.

```php
// In UsersController
use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class UsersController extends Controller{

    public function destroy(Request $request, User $user)
    {
        UserRepository::init($user)
            ->destroy();

        return redirect()->back();
    }
}
```
### Querying from model

Laravel already provides a nice abstraction for writing queries so querying with using repository can be optional. You can just use models to write queries. But if you still want to use the repository for querying then the repository provides a `query()` method that proxies model. After calling `query()` method you can chain all the methods provided by `Eloquent`.

```php
// In UsersController
use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class UsersController extends Controller{

    public function index(Request $request)
    {
        $users = UserRepository::init()
            ->query()
            ->where('status', '=', 1)
            ->paginate();

        return view('users.index')->with(compact('users'));
    }
}
```

### Repository Hooks

This package provides some hooks for the `create`, `update`, `destroy` and `restore` methods.

__List of hooks are:__

1. For `create` method `beforeCreate` and `afterCreate`.
2. For `update` method `beforeUpdate` and `afterUpdate`.
3. For `destroy` method `beforeDestroy` and `afterDestroy`.
4. For `restore` method `afterRestore`.
5. Additional `beforeSave` and `afterSave` hooks which work on both `create` and `update` methods.

__Usage of Hooks__

Let's take a scenario for where you want to hash a users password while creating or updating.

```php
// In App\Repositories\UserRepository create a method beforSave

protected function beforeSave($fields)
{
    if(array_key_exists('password', $fields)){
        $fields['password'] = Hash::make($fields['password']);
    }
    return $fields;
}
```

That's it! Now for every create and update action the password field will be hashed. And yes don't forget to check if `password` key exists in the `$fields` array.

`$fields` is the array of fields that were passed while calling `create` or `update` method.

Now let's take another slightly complicated example:

Suppose you are submitting a form while creating a user that also has a field called `roles`. Since `users` table doesn't have any roles column then while calling `create` method Repository will throw an exception.

You can tackle this problem using hooks.

```php
// In App\Repositories\UserRepository create a method beforSave

protected function beforeSave($fields)
{
    if(array_key_exists('roles', $fields)){
        unset($fields['roles']);
    }
    return $fields;
}

protected function afterSave($orignal_fields, $fields)
{
    if(array_key_exists('roles', $orignal_fields)){
        $this->model->roles()->sync($orignal_fields['roles']);
    }
    return $this->model;
}
```

All three methods `afterSave`, `afterCreate` and `afterUpdate` will receive two parameters `$original_fields`, those were submitted originally and `$fields`, those were returned using `before` hooks. So you can safely unset all the fields that are not needed while creating/updating a record and use them after the creating/updating the record.

__List of Hooks and their Return values__

```php
protected function beforeCreate(array $fields)
{
    return $fields;
}

protected function afterCreate(array $original_fields, array $fields)
{
    return $this->model;
}

protected function beforeUpdate(array $fields)
{
    return $fields;
}

protected function afterUpdate(array $original_fields, array $fields)
{
    return $this->model;
}

protected function beforeSave(array $fields)
{
    return $fields;
}

protected function afterSave(array $original_fields, array $fields)
{
    return $this->model;
}

protected function beforeDestroy(bool $isSoftDeletable, bool $permanent)
{
    return $this->model;
}

protected function afterRestore()
{
    return $this->model;
}
```

## What's Next

1. Writing tests.
2. Optimizing code where necessary.

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to test before submitting a PR.

## License
[MIT](https://choosealicense.com/licenses/mit/)
