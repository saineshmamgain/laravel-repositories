<?php

namespace Tests;

use Illuminate\Filesystem\Filesystem;

class RepositoryMakeCommandTest extends TestCase
{
    private Filesystem $files;

    protected function setUp(): void
    {
        parent::setUp();

        $this->files = new Filesystem;
    }

    protected function tearDown(): void
    {
        $this->deletePath(app_path('Models/User.php'));
        $this->deletePath(app_path('Models/Admin/User.php'));
        $this->deletePath(app_path('Models/Post.php'));
        $this->deletePath(app_path('Repositories/UserRepository.php'));
        $this->deletePath(app_path('Repositories/Admin/UserRepository.php'));

        $this->deleteDirectoryIfEmpty(app_path('Models/Admin'));
        $this->deleteDirectoryIfEmpty(app_path('Repositories/Admin'));
        $this->deleteDirectoryIfEmpty(app_path('Models'));
        $this->deleteDirectoryIfEmpty(app_path('Repositories'));

        parent::tearDown();
    }

    public function test_it_creates_repository_for_model(): void
    {
        $this->writeModel('User');

        $this->artisan('make:repository', [
            'model' => 'User',
        ])->assertSuccessful();

        $path = app_path('Repositories/UserRepository.php');

        $this->assertFileExists($path);
        $this->assertStringContainsString('namespace App\Repositories;', $this->files->get($path));
        $this->assertStringContainsString('use App\Models\User;', $this->files->get($path));
        $this->assertStringContainsString('extends Repository', $this->files->get($path));
        $this->assertStringContainsString('return User::class;', $this->files->get($path));
    }

    public function test_force_overwrites_existing_repository(): void
    {
        $this->writeModel('User');

        $path = app_path('Repositories/UserRepository.php');
        $this->files->ensureDirectoryExists(dirname($path));
        $this->files->put($path, '<?php // old repository');

        $this->artisan('make:repository', [
            'model' => 'User',
            '--force' => true,
        ])->assertSuccessful();

        $this->assertStringContainsString('return User::class;', $this->files->get($path));
    }

    public function test_nested_model_generation(): void
    {
        $this->writeModel('Admin/User', 'App\\Models\\Admin');

        $this->artisan('make:repository', [
            'model' => 'Admin/User',
        ])->assertSuccessful();

        $path = app_path('Repositories/Admin/UserRepository.php');

        $this->assertFileExists($path);
        $this->assertStringContainsString('namespace App\Repositories\Admin;', $this->files->get($path));
        $this->assertStringContainsString('use App\Models\Admin\User;', $this->files->get($path));
        $this->assertStringContainsString('return User::class;', $this->files->get($path));
    }

    public function test_missing_model_fails_clearly(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing');
        $this->expectExceptionMessage('does not exist');

        $this->artisan('make:repository', [
            'model' => 'Missing',
        ])->run();
    }

    public function test_non_eloquent_model_fails_clearly(): void
    {
        $this->writePlainClass('Post');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Model [App\Models\Post] is not an Eloquent model.');

        $this->artisan('make:repository', [
            'model' => 'Post',
        ])->run();
    }

    private function writeModel(string $model, string $namespace = 'App\\Models'): void
    {
        $path = app_path('Models/'.str_replace('\\', '/', $model).'.php');
        $class = class_basename(str_replace('/', '\\', $model));

        $this->files->ensureDirectoryExists(dirname($path));
        $this->files->put($path, <<<PHP
<?php

namespace {$namespace};

use Illuminate\Database\Eloquent\Model;

class {$class} extends Model
{
}
PHP);

        require_once $path;
    }

    private function writePlainClass(string $class): void
    {
        $path = app_path("Models/{$class}.php");

        $this->files->ensureDirectoryExists(dirname($path));
        $this->files->put($path, <<<PHP
<?php

namespace App\Models;

class {$class}
{
}
PHP);

        require_once $path;
    }

    private function deletePath(string $path): void
    {
        if ($this->files->exists($path)) {
            $this->files->delete($path);
        }
    }

    private function deleteDirectoryIfEmpty(string $path): void
    {
        if ($this->files->isDirectory($path) && count($this->files->files($path)) === 0 && count($this->files->directories($path)) === 0) {
            $this->files->deleteDirectory($path);
        }
    }
}
