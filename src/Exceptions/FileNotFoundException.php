<?php

namespace SaineshMamgain\SetupHelper\Exceptions;

use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;

/**
 * File: FileNotFoundException.php
 * Author: Sainesh Mamgain
 * Email: saineshmamgain@gmail.com
 * Date: 01/03/21
 * Time: 9:22 PM.
 */
class FileNotFoundException extends \Exception implements ProvidesSolution
{
    public function getSolution(): Solution
    {
        return BaseSolution::create('It looks like Stubs are not published yet')
            ->setSolutionDescription('run `php artisan setup-helper:install` to publish the stubs');
    }
}
