<?php

namespace SaineshMamgain\LaravelRepositories\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use SaineshMamgain\LaravelRepositories\LaravelRepositoryServiceProvider;

/**
 * File: TestCase.php
 * Author: Sainesh Mamgain
 * Email: saineshmamgain@gmail.com
 * Date: 22/06/21
 * Time: 12:46 AM
 */

class TestCase extends \Orchestra\Testbench\TestCase {

    public function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelRepositoryServiceProvider::class
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $app->setBasePath(__DIR__ . '/..');

        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }
}
