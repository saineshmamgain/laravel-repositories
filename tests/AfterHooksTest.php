<?php

namespace Tests;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Repositories\UserDetailRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * File: AfterHooksTest.php
 * Author: Sainesh Mamgain
 * Email: saineshmamgain@gmail.com
 * Date: 09/07/21
 * Time: 4:38 PM
 */

class AfterHooksTest extends TestCase {

    public function testItExecutesAfterSaveMethodAfterCreating()
    {
        $this->createRepository();
        $this->createDetailsRepository();

        UserRepositoryAfterHooksTest::init(new UserModelAfterHookTest())
            ->create([
                'name'     => 'John Doe',
                'email'    => 'john@example.com',
                'password' => '123456',
                'address' => '221B Baker Street 222'
            ]);

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
        $this->assertDatabaseHas('user_details', ['address' => '221B Baker Street 222']);
    }

    public function testItExecutesAfterCreateMethodAfterCreating()
    {
        $this->createRepository();
        $this->createDetailsRepository();

        UserRepositoryAfterHooksTest::init(new UserModelAfterHookTest())
            ->create([
                'name'     => 'John Doe',
                'email'    => 'john@example.com',
                'password' => '123456',
                'nickname' => 'Sherlock'
            ]);

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
        $this->assertDatabaseHas('user_details', ['nickname' => 'Sherlock']);
    }

    public function testItExecutesAfterSaveMethodAfterUpdating()
    {
        $this->createRepository();
        $this->createDetailsRepository();

        $user = UserRepositoryAfterHooksTest::init(new UserModelAfterHookTest())
            ->create([
                'name'     => 'Jane Doe',
                'email'    => 'jane@example.com',
                'password' => '123456'
            ]);

        UserRepositoryAfterHooksTest::init($user)
            ->update([
                'address' => '221B Baker Street 2'
            ]);

        $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
        $this->assertDatabaseHas('user_details', ['address' => '221B Baker Street 2']);
    }

    public function testItExecutesAfterUpdateMethodAfterUpdating()
    {
        $this->createRepository();
        $this->createDetailsRepository();

        $user = UserRepositoryAfterHooksTest::init(new UserModelAfterHookTest())
            ->create([
                'name'     => 'Jane Doe',
                'email'    => 'jane@example.com',
                'password' => '123456'
            ]);

        UserRepositoryAfterHooksTest::init($user)
            ->update([
                'nickname' => 'jenny'
            ]);

        $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
        $this->assertDatabaseHas('user_details', ['nickname' => 'JENNY']);
    }
}

class UserRepositoryAfterHooksTest extends UserRepository {

    protected function beforeSave($fields)
    {
        if (array_key_exists('address', $fields)){
            unset($fields['address']);
        }
        return $fields;
    }

    protected function afterSave($original_fields, $fields)
    {
        if (array_key_exists('address', $original_fields)){
            UserDetailRepository::init()
                ->create([
                    'user_id' => $this->model->id,
                    'address' => $original_fields['address']
                ]);
        }
        return $this->model;
    }

    protected function beforeCreate($fields)
    {
        if (array_key_exists('nickname', $fields)){
            unset($fields['nickname']);
        }
        return $fields;
    }

    protected function afterCreate($original_fields, $fields)
    {
        if (array_key_exists('nickname', $original_fields)){
            UserDetailRepository::init()
                ->create([
                    'user_id' => $this->model->id,
                    'nickname' => $original_fields['nickname']
                ]);
        }
        return $this->model;
    }

    protected function beforeUpdate($fields)
    {
        if (array_key_exists('nickname', $fields)){
            unset($fields['nickname']);
        }
        return $fields;
    }

    protected function afterUpdate($original_fields, $fields)
    {
        if (array_key_exists('nickname', $original_fields)){
            UserDetailRepository::init()
                ->create([
                    'user_id' => $this->model->id,
                    'nickname' => strtoupper($original_fields['nickname'])
                ]);
        }
        return $this->model;
    }
}

class UserModelAfterHookTest extends User {

    protected $table = 'users';

    public function details()
    {
        return $this->hasOne('UserDetail');
    }

}
