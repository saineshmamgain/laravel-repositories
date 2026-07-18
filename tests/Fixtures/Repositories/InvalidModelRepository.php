<?php

declare(strict_types=1);

namespace Tests\Fixtures\Repositories;

use SaineshMamgain\LaravelRepositories\Repositories\Repository;
use Tests\Fixtures\Models\NonEloquentModel;

class InvalidModelRepository extends Repository
{
    protected function model(): string
    {
        return NonEloquentModel::class;
    }
}
