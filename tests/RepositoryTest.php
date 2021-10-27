<?php

namespace Tests;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use SaineshMamgain\LaravelRepositories\Exceptions\RepositoryException;

/**
 * File: RepositoryTest.php
 * Author: Sainesh Mamgain
 * Email: saineshmamgain@gmail.com
 * Date: 02/07/21
 * Time: 2:02 AM.
 */
class RepositoryTest extends TestCase
{
    public function testItCreatesARecord()
    {
        $this->createRepository();

        UserRepository::init()
            ->create([
                'name'     => 'John Doe',
                'email'    => 'john@example.com',
                'password' => '123456',
            ]);

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    public function testItUpdatesARecord()
    {
        $this->createRepository();

        $user = UserRepository::init()
            ->create([
                'name'     => 'John Doe',
                'email'    => 'john@example.com',
                'password' => '123456',
            ]);

        UserRepository::init($user)
            ->update([
                'email' => 'doe@example.com',
            ]);

        $this->assertDatabaseHas('users', ['email' => 'doe@example.com']);
    }

    public function testItThrowsAnExceptionWhileCreatingAnInstanceThatAlreadyExists()
    {
        $this->createRepository();

        $user = UserRepository::init()
            ->create([
                'name'     => 'John Doe',
                'email'    => 'john@example.com',
                'password' => '123456',
            ]);

        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('Fresh instance required for creation');

        UserRepository::init($user)
            ->create([
                'email' => 'doe@example.com',
            ]);
    }

    public function testItThrowsAnExceptionWhileUpdatingAnInstanceThatDoesNotExists()
    {
        $this->createRepository();

        $user = new User();

        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('Instance should not be fresh for update');

        UserRepository::init($user)
            ->update([
                'email' => 'doe@example.com',
            ]);
    }

    public function testItDestroysARecord()
    {
        $this->createRepository();

        $user = UserRepository::init()
            ->create([
                'name'     => 'John Doe',
                'email'    => 'john@example.com',
                'password' => '123456',
            ]);

        UserRepository::init($user)
            ->destroy();

        $this->assertDatabaseMissing('users', ['email' => 'john@example.com']);
    }

    public function testItThrowsAnExceptionWhileDestroyingAFreshInstance()
    {
        $this->createRepository();

        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('Model doesn\'t exist');

        $user = new User();

        UserRepository::init($user)
            ->destroy();
    }

    public function testItDoesNotPersistsAModel()
    {
        $this->createRepository();

        UserRepository::init()
            ->persist(false)
            ->create([
                'name'     => 'John Doe',
                'email'    => 'john@example.com',
                'password' => '123456',
            ]);

        $this->assertDatabaseMissing('users', ['email' => 'john@example.com']);
    }

    public function testItRefreshesAModel()
    {
        $this->createRepository();

        UserRepository::init()
            ->create([
                'name'     => 'John Doe',
                'email'    => 'john@example.com',
                'password' => '123456',
            ]);

        $user = User::select('id')->where('id', '=', 1)->first();

        $user = UserRepository::init($user)
            ->update([
                'email' => 'doe@example.com',
            ]);

        $this->assertEmpty($user->name);

        $user = UserRepository::init($user)
            ->refresh()
            ->update([
                'email' => 'doe@example.com',
            ]);

        $this->assertNotEmpty($user->name);
        $this->assertEquals($user->name, 'John Doe');
        $this->assertDatabaseHas('users', ['email' => 'doe@example.com']);
    }

    public function testItCreatesManyRecords()
    {
        $this->createRepository();

        UserRepository::init()
            ->createMany([
                [
                    'name'     => 'John Doe',
                    'email'    => 'john@example.com',
                    'password' => '123456',
                ],
                [
                    'name'     => 'Doe John',
                    'email'    => 'doe@example.com',
                    'password' => '123456',
                ],
            ]);

        $this->assertDatabaseHas('users', ['email' => 'doe@example.com']);
        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    public function testItSoftDeletesARecord()
    {
        $this->createRepository();

        $user = UserRepository::init(new UserModel())
            ->create([
                'name'     => 'John Doe',
                'email'    => 'john@example.com',
                'password' => '123456',
            ]);

        UserRepository::init($user)
            ->destroy();

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
        $this->assertNotEmpty($user->deleted_at);
    }

    public function testItPermanentlyDeletesASoftDeletableRecord()
    {
        $this->createRepository();

        $user = UserRepository::init(new UserModel())
            ->create([
                'name'     => 'John Doe',
                'email'    => 'john@example.com',
                'password' => '123456',
            ]);

        UserRepository::init($user)
            ->destroy(true);

        $this->assertDatabaseMissing('users', ['email' => 'john@example.com']);
    }

    public function testItTouchesAnExistingRecordWithoutPersisting()
    {
        $this->createRepository();

        $user = UserRepository::init()
            ->create([
                'name'     => 'John Doe',
                'email'    => 'john@example.com',
                'password' => '123456',
            ]);

        $user = UserRepository::init($user)
            ->persist(false)
            ->touch([
                'email' => 'jason@example.com'
            ]);

        $this->assertDatabaseMissing('users', ['email' => 'jason@example.com']);
        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
        $this->assertEquals($user->email, 'jason@example.com');
        $this->assertEquals($user->exists, true);
        $this->assertEquals($user->getDirty(), ['email' => 'jason@example.com']);
    }

    public function testItTouchesAnExistingRecordWithPersisting()
    {
        $this->createRepository();

        $user = UserRepository::init()
            ->create([
                'name'     => 'John Doe',
                'email'    => 'john@example.com',
                'password' => '123456',
            ]);

        $user = UserRepository::init($user)
            ->persist(true)
            ->touch([
                'email' => 'jason@example.com'
            ]);

        $this->assertDatabaseMissing('users', ['email' => 'john@example.com']);
        $this->assertDatabaseHas('users', ['email' => 'jason@example.com']);
        $this->assertEquals($user->email, 'jason@example.com');
        $this->assertEquals($user->exists, true);
        $this->assertEquals($user->getDirty(), []);
    }

    public function testItTouchesANonExistingRecordWithoutPersisting()
    {
        $this->createRepository();

        $user = UserRepository::init()
            ->persist(false)
            ->touch([
                'email' => 'jason@example.com'
            ]);

        $this->assertDatabaseMissing('users', ['email' => 'jason@example.com']);
        $this->assertEquals($user->email, 'jason@example.com');
        $this->assertEquals($user->exists, false);
        $this->assertEquals($user->getDirty(), ['email' => 'jason@example.com']);
    }

    public function testItTouchesANonExistingRecordWithPersisting()
    {
        $this->createRepository();

        $user = UserRepository::init()
            ->persist(true)
            ->touch([
                'name'     => 'John Doe',
                'password' => '123456',
                'email' => 'jason@example.com'
            ]);

        $this->assertDatabaseHas('users', ['email' => 'jason@example.com']);
        $this->assertEquals($user->email, 'jason@example.com');
        $this->assertEquals($user->exists, true);
        $this->assertEquals($user->getDirty(), []);
    }
}

class UserModel extends User
{
    use SoftDeletes;

    protected $table = 'users';
}
