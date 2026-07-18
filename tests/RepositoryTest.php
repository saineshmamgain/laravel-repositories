<?php

namespace Tests;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use SaineshMamgain\LaravelRepositories\Exceptions\RepositoryException;
use Tests\Fixtures\Models\NonEloquentModel;
use Tests\Fixtures\Models\User;
use Tests\Fixtures\Models\UserDetail;
use Tests\Fixtures\Repositories\InvalidModelRepository;
use Tests\Fixtures\Repositories\UserDetailRepository;
use Tests\Fixtures\Repositories\UserRepository;

class RepositoryTest extends TestCase
{
    public function test_query_returns_a_new_eloquent_query(): void
    {
        $query = (new UserRepository)->query();

        $this->assertSame(User::class, $query->getModel()::class);
    }

    public function test_all_returns_eloquent_collection(): void
    {
        User::query()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'secret',
        ]);

        $users = (new UserRepository)->all();

        $this->assertInstanceOf(Collection::class, $users);
        $this->assertCount(1, $users);
        $this->assertSame('jane@example.com', $users->first()->email);
    }

    public function test_find_returns_model_or_null(): void
    {
        $user = User::query()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'secret',
        ]);

        $repository = new UserRepository;

        $this->assertSame($user->id, $repository->find($user->id)->id);
        $this->assertNull($repository->find(999));
    }

    public function test_find_or_fail_returns_model_or_throws(): void
    {
        $user = User::query()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'secret',
        ]);

        $repository = new UserRepository;

        $this->assertSame($user->id, $repository->findOrFail($user->id)->id);

        $this->expectException(ModelNotFoundException::class);

        $repository->findOrFail(999);
    }

    public function test_create_persists_model_using_fill_and_save(): void
    {
        $user = (new UserRepository)->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'secret',
        ]);

        $this->assertTrue($user->exists);
        $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
    }

    public function test_create_many_persists_iterable_records_and_returns_eloquent_collection(): void
    {
        $users = (new UserRepository)->createMany([
            [
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'password' => 'secret',
            ],
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'secret',
            ],
        ]);

        $this->assertInstanceOf(Collection::class, $users);
        $this->assertCount(2, $users);
        $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    public function test_update_accepts_model_instance(): void
    {
        $user = User::query()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'secret',
        ]);

        $updated = (new UserRepository)->update($user, [
            'email' => 'jane.updated@example.com',
        ]);

        $this->assertSame($user->id, $updated->id);
        $this->assertSame('jane.updated@example.com', $updated->email);
        $this->assertDatabaseHas('users', ['email' => 'jane.updated@example.com']);
    }

    public function test_update_accepts_model_id(): void
    {
        $user = User::query()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'secret',
        ]);

        $updated = (new UserRepository)->update($user->id, [
            'email' => 'jane.updated@example.com',
        ]);

        $this->assertSame($user->id, $updated->id);
        $this->assertSame('jane.updated@example.com', $updated->email);
    }

    public function test_delete_soft_deletes_model(): void
    {
        $user = User::query()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'secret',
        ]);

        $this->assertTrue((new UserRepository)->delete($user));
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_force_delete_permanently_deletes_soft_deleted_model_by_id(): void
    {
        $user = User::query()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'secret',
        ]);

        $repository = new UserRepository;

        $repository->delete($user);

        $this->assertTrue($repository->forceDelete($user->id));
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_restore_restores_soft_deleted_model_by_id(): void
    {
        $user = User::query()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'secret',
        ]);

        $repository = new UserRepository;

        $repository->delete($user);
        $restored = $repository->restore($user->id);

        $this->assertSame($user->id, $restored->id);
        $this->assertNull(User::query()->findOrFail($user->id)->deleted_at);
    }

    public function test_restore_throws_for_non_soft_deletable_model(): void
    {
        $detail = UserDetail::query()->create([
            'user_id' => 1,
            'address' => '221B Baker Street',
        ]);

        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('does not support soft deletes');

        (new UserDetailRepository)->restore($detail);
    }

    public function test_wrong_model_class_throws_repository_exception(): void
    {
        $detail = UserDetail::query()->create([
            'user_id' => 1,
            'address' => '221B Baker Street',
        ]);

        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage(UserDetail::class);

        (new UserRepository)->update($detail, ['name' => 'Jane Doe']);
    }

    public function test_non_eloquent_model_class_throws_repository_exception(): void
    {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage(NonEloquentModel::class);

        (new InvalidModelRepository)->query();
    }
}
