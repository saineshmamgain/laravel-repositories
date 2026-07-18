<?php

namespace Tests;

use Tests\Fixtures\Models\User;
use Tests\Fixtures\Repositories\HookedUserRepository;

class RepositoryHooksTest extends TestCase
{
    public function test_create_runs_hooks_in_order(): void
    {
        $repository = new HookedUserRepository;

        $user = $repository->create([
            'name' => 'jane doe',
            'email' => 'jane@example.com',
            'password' => 'secret',
        ]);

        $this->assertSame([
            'beforeCreate',
            'beforeSave',
            'afterSave',
            'afterCreate',
        ], $repository->calls);
        $this->assertSame('JANE DOE', $user->name);
        $this->assertSame('hashed-secret', $user->password);
        $this->assertDatabaseHas('users', [
            'name' => 'JANE DOE',
            'password' => 'hashed-secret',
        ]);
    }

    public function test_update_runs_hooks_in_order(): void
    {
        $user = User::query()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'secret',
        ]);

        $repository = new HookedUserRepository;

        $updated = $repository->update($user, [
            'email' => 'updated@example.com',
            'password' => 'changed',
        ]);

        $this->assertSame([
            'beforeUpdate',
            'beforeSave',
            'afterSave',
            'afterUpdate',
        ], $repository->calls);
        $this->assertSame('UPDATED@EXAMPLE.COM', $updated->email);
        $this->assertSame('hashed-changed', $updated->password);
        $this->assertDatabaseHas('users', [
            'email' => 'UPDATED@EXAMPLE.COM',
            'password' => 'hashed-changed',
        ]);
    }

    public function test_before_save_runs_for_create_and_update(): void
    {
        $repository = new HookedUserRepository;

        $user = $repository->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'created',
        ]);

        $repository->update($user, [
            'email' => 'updated@example.com',
            'password' => 'updated',
        ]);

        $this->assertSame(2, count(array_filter(
            $repository->calls,
            fn (string $call): bool => $call === 'beforeSave',
        )));
    }

    public function test_after_save_receives_original_and_saved_attributes(): void
    {
        $repository = new HookedUserRepository;

        $repository->create([
            'name' => 'jane doe',
            'email' => 'jane@example.com',
            'password' => 'secret',
        ]);

        $this->assertSame([
            'name' => 'jane doe',
            'email' => 'jane@example.com',
            'password' => 'secret',
        ], $repository->lastOriginalAttributes);
        $this->assertSame([
            'name' => 'JANE DOE',
            'email' => 'jane@example.com',
            'password' => 'hashed-secret',
        ], $repository->lastSavedAttributes);
    }

    public function test_delete_runs_hooks_in_order(): void
    {
        $user = User::query()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'secret',
        ]);

        $repository = new HookedUserRepository;

        $this->assertTrue($repository->delete($user));
        $this->assertSame(['beforeDelete', 'afterDelete'], $repository->calls);
        $this->assertSame('before-delete', $user->name);
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_force_delete_runs_hooks_in_order(): void
    {
        $user = User::query()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'secret',
        ]);

        $repository = new HookedUserRepository;

        $this->assertTrue($repository->forceDelete($user));
        $this->assertSame(['beforeForceDelete', 'afterForceDelete'], $repository->calls);
        $this->assertSame('before-force-delete', $user->name);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_restore_runs_after_restore_hook(): void
    {
        $user = User::query()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'secret',
        ]);

        $repository = new HookedUserRepository;

        $repository->delete($user);
        $repository->calls = [];

        $restored = $repository->restore($user->id);

        $this->assertSame(['afterRestore'], $repository->calls);
        $this->assertSame($user->id, $restored->id);
        $this->assertNull(User::query()->findOrFail($user->id)->deleted_at);
    }
}
