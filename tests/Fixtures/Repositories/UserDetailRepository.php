<?php

declare(strict_types=1);

namespace Tests\Fixtures\Repositories;

use SaineshMamgain\LaravelRepositories\Repositories\Repository;
use Tests\Fixtures\Models\UserDetail;

/**
 * @extends Repository<UserDetail>
 */
class UserDetailRepository extends Repository
{
    protected function model(): string
    {
        return UserDetail::class;
    }
}
