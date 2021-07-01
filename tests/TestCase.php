<?php

namespace Tests;

/**
 * File: TestCase.php
 * Author: Sainesh Mamgain
 * Email: saineshmamgain@gmail.com
 * Date: 22/06/21
 * Time: 12:46 AM.
 */
class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (file_exists(app_path('Models/User.php'))) {
            unlink(app_path('Models/User.php'));
        }

        if (file_exists(app_path('Repositories/UserRepository.php'))) {
            unlink(app_path('Repositories/UserRepository.php'));
            rmdir(app_path('Repositories'));
        }
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
}
