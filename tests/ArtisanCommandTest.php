<?php

namespace Tests;

/**
 * File: ArtisanCommandTest.php
 * Author: Sainesh Mamgain
 * Email: saineshmamgain@gmail.com
 * Date: 02/07/21
 * Time: 12:45 AM
 */

class ArtisanCommandTest extends TestCase
{
    public function testItCreatesRepository()
    {
        $this->artisan('make:model', [
            'name' => 'User'
        ]);

        $this->artisan('make:repository', [
            'model' => 'User'
        ]);

        $this->assertTrue(file_exists(app_path('Repositories/UserRepository.php')));
    }

    public function testItThrowsExceptionIfModelDoesNotExists()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Model Test doesn\'t exist');

        $this->artisan('make:repository', [
            'model' => 'Test'
        ]);
    }

    public function testItThrowsExceptionIfModelIsNotAValidModel()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Model Post is not a valid model');

        file_put_contents(app_path('Models/Post.php'), '<?php namespace App\Models; class Post {}');

        $this->artisan('make:repository', [
            'model' => 'Post'
        ]);
        unlink(app_path('Models/Post.php'));
    }

    public function testItThrowsErrorIfRepositoryAlreadyExists()
    {
        $this->artisan('make:model', [
            'name' => 'User'
        ]);

        $this->artisan('make:repository', [
            'model' => 'User'
        ]);

        $command = $this->artisan('make:repository', [
            'model' => 'User'
        ]);

        $command->expectsOutput('Repository already exists!');
    }
}
