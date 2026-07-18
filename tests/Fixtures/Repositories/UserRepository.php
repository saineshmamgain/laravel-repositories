<?php

declare(strict_types=1);

namespace Tests\Fixtures\Repositories;

use SaineshMamgain\LaravelRepositories\Repositories\Repository;
use Tests\Fixtures\Models\User;

/**
 * @extends Repository<User>
 */
class UserRepository extends Repository
{
    /**
     * @return class-string<User>
     */
    protected function model(): string
    {
        return User::class;
    }
}
