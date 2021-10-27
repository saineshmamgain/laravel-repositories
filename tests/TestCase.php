<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * File: TestCase.php
 * Author: Sainesh Mamgain
 * Email: saineshmamgain@gmail.com
 * Date: 22/06/21
 * Time: 12:46 AM.
 */
class TestCase extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRepository();
        $this->createDetailsRepository();
    }

    protected function getPackageProviders($app)
    {
        return [
            'SaineshMamgain\LaravelRepositories\LaravelRepositoryServiceProvider',
        ];
    }

    protected function defineEnvironment($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }

    protected function createRepository()
    {
        $this->artisan('make:model', [
            'name' => 'User',
        ]);

        $this->artisan('make:repository', [
            'model' => 'User',
        ]);
    }

    protected function createDetailsRepository()
    {
        $this->artisan('make:model', [
            'name' => 'UserDetail',
        ]);

        $this->artisan('make:repository', [
            'model' => 'UserDetail',
        ]);
    }
}
