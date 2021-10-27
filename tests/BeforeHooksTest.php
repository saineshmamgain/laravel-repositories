<?php

namespace Tests;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * File: BeforeHooksTest.php
 * Author: Sainesh Mamgain
 * Email: saineshmamgain@gmail.com
 * Date: 02/07/21
 * Time: 4:20 PM.
 */
class BeforeHooksTest extends TestCase
{
    public function testItExecutesBeforeSaveMethodBeforeCreating()
    {
        $this->createRepository();

        UserRepositoryTest::init()
            ->create([
                'name'     => 'John Doe',
                'email'    => 'john@example.com',
                'password' => '123456',
            ]);

        $this->assertDatabaseHas('users', ['password' => md5('123456')]);
    }

    public function testItExecutesBeforeSaveMethodBeforeUpdating()
    {
        $this->createRepository();

        $user = UserRepositoryTest::init()
            ->create([
                'name'     => 'John Doe',
                'email'    => 'john@example.com',
                'password' => '123456',
            ]);

        UserRepositoryTest::init($user)
            ->update([
                'password' => '987654',
            ]);

        $this->assertDatabaseHas('users', ['password' => md5('987654')]);
    }

    public function testItExecutesBeforeCreateMethodBeforeCreating()
    {
        $this->createRepository();

        $user = UserRepositoryTest::init()
            ->create([
                'name'     => 'john doe',
                'email'    => 'john@example.com',
                'password' => '123456',
            ]);

        $this->assertDatabaseHas('users', ['name' => 'JOHN DOE']);
        $this->assertEquals('JOHN DOE', $user->name);
    }

    public function testItDoesNotExecutesBeforeCreateMethodBeforeUpdating()
    {
        $this->createRepository();

        $user = UserRepositoryTest::init()
            ->create([
                'name'     => 'john doe',
                'email'    => 'john@example.com',
                'password' => '123456',
            ]);

        $user = UserRepositoryTest::init($user)
            ->update([
                'name' => 'john dow',
            ]);

        $this->assertDatabaseHas('users', ['name' => 'john dow']);
        $this->assertEquals('john dow', $user->name);
    }

    public function testItExecutesBeforeUpdateMethodBeforeUpdating()
    {
        $this->createRepository();

        $user = UserRepositoryTest::init()
            ->create([
                'name'     => 'john doe',
                'email'    => 'john@example.com',
                'password' => '123456',
            ]);

        $user = UserRepositoryTest::init($user)
            ->update([
                'email' => 'doe@example.com',
            ]);

        $this->assertDatabaseHas('users', ['email' => 'DOE@EXAMPLE.COM']);
        $this->assertEquals('DOE@EXAMPLE.COM', $user->email);
    }

    public function testItDoesNotExecutesBeforeUpdateMethodBeforeCreating()
    {
        $this->createRepository();

        $user = UserRepositoryTest::init()
            ->create([
                'name'     => 'john doe',
                'email'    => 'john@example.com',
                'password' => '123456',
            ]);

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
        $this->assertEquals('john@example.com', $user->email);
    }

    public function testItExecutesBeforeDestroyMethodBeforeDeleting()
    {
        $this->createRepository();

        $user = UserRepository::init(new UserModelTest())
            ->create([
                'name'     => 'john doe',
                'email'    => 'john@example.com',
                'password' => '123456',
            ]);

        UserRepositoryTest::init($user)
            ->destroy();

        $this->assertDatabaseHas('users', ['name' => 'DELETED_john doe']);
    }
    public function testItExecutesBeforeSaveMethodBeforeNonPersistingTouch()
    {
        $this->createRepository();

        $user = UserRepositoryTest::init()
            ->persist(false)
            ->touch([
                'name'     => 'John Doe',
                'email'    => 'john@example.com',
                'password' => '123456',
            ]);

        $this->assertDatabaseMissing('users', ['password' => md5('123456')]);
        $this->assertEquals($user->password, md5('123456'));
        $this->assertEquals($user->exists, false);
    }

    public function testItExecutesBeforeSaveMethodBeforePersistingTouch()
    {
        $this->createRepository();

        $user = UserRepositoryTest::init()
            ->persist(true)
            ->touch([
                'name'     => 'John Doe',
                'email'    => 'john@example.com',
                'password' => '123456',
            ]);

        $this->assertDatabaseHas('users', ['password' => md5('123456')]);
        $this->assertEquals($user->password, md5('123456'));
        $this->assertEquals($user->exists, true);
    }

}

class UserRepositoryTest extends UserRepository
{
    protected function beforeSave($fields)
    {
        if (array_key_exists('password', $fields)) {
            $fields['password'] = md5($fields['password']);
        }

        return $fields;
    }

    protected function beforeCreate($fields)
    {
        if (array_key_exists('name', $fields)) {
            $fields['name'] = strtoupper($fields['name']);
        }

        return $fields;
    }

    protected function beforeUpdate($fields)
    {
        if (array_key_exists('email', $fields)) {
            $fields['email'] = strtoupper($fields['email']);
        }

        return $fields;
    }

    protected function beforeDestroy($isSoftDeletable, $permanent)
    {
        if ($isSoftDeletable) {
            self::init($this->model)
                ->update([
                    'name' => 'DELETED_'.$this->model->name,
                ]);
        }

        return $this->model;
    }
}

class UserModelTest extends User
{
    use SoftDeletes;

    protected $table = 'users';
}
