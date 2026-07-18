<?php

declare(strict_types=1);

namespace Tests\Fixtures\Repositories;

use Illuminate\Database\Eloquent\Model;
use SaineshMamgain\LaravelRepositories\Repositories\Repository;
use Tests\Fixtures\Models\NonEloquentModel;

/**
 * @extends Repository<Model>
 */
class InvalidModelRepository extends Repository
{
    /**
     * @return class-string<Model>
     */
    protected function model(): string
    {
        /** @phpstan-ignore return.type (Negative test fixture intentionally returns a non-Eloquent class.) */
        return NonEloquentModel::class;
    }
}
