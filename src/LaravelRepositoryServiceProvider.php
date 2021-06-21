<?php

namespace SaineshMamgain\LaravelRepositories;

use Illuminate\Support\ServiceProvider;
use SaineshMamgain\LaravelRepositories\Console\Commands\Generators\RepositoryMakeCommand;

/**
 * File: LaravelRepositoryServiceProvider.php
 * Author: Sainesh Mamgain
 * Email: saineshmamgain@gmail.com
 * Date: 21/06/21
 * Time: 6:02 PM
 */

class LaravelRepositoryServiceProvider extends ServiceProvider {

    public function boot()
    {
        $this->app->bind('command.laravel-repositories.make.repository', RepositoryMakeCommand::class);
        $this->commands(['command.laravel-repositories.make.repository']);
    }

}
