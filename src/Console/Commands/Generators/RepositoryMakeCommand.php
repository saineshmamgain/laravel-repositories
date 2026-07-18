<?php

namespace SaineshMamgain\LaravelRepositories\Console\Commands\Generators;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * File: RepositoryMakeCommand.php
 * Author: Sainesh Mamgain
 * Email: saineshmamgain@gmail.com
 * Date: 03/03/21
 * Time: 10:35 AM.
 */
class RepositoryMakeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:repository {model : The Eloquent model class} {--f|force : Create the class even if the repository already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to create repository';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Repository';

    /**
     * The model class for which the repository is being generated.
     *
     * @var class-string<Model>
     */
    private string $modelClass;

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    #[\Override]
    public function handle()
    {
        $this->setModelClass();

        return parent::handle();
    }

    protected function getStub()
    {
        return $this->resolveStubPath('/stubs/laravel-repository.stub');
    }

    protected function resolveStubPath($stub)
    {
        return is_file($customPath = $this->laravel->basePath(trim((string) $stub, '/')))
            ? $customPath
            : __DIR__.'/../../../../'.$stub;
    }

    #[\Override]
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Repositories';
    }

    #[\Override]
    protected function getNameInput()
    {
        return $this->repositoryNameFromModel();
    }

    private function setModelClass(): void
    {
        $model = $this->modelNameInput();
        $this->guardAgainstInvalidModelName($model);

        $modelClass = $this->qualifyModel($model);

        if (! class_exists($modelClass)) {
            throw new InvalidArgumentException(sprintf('Model [%s] does not exist.', $modelClass));
        }

        if (! is_a($modelClass, Model::class, true)) {
            throw new InvalidArgumentException(sprintf('Model [%s] is not an Eloquent model.', $modelClass));
        }

        $this->modelClass = $modelClass;
    }

    private function modelNameInput(): string
    {
        $model = trim($this->argument('model'));

        if (str_ends_with($model, '.php')) {
            $model = substr($model, 0, -4);
        }

        return preg_replace('/Repository$/i', '', $model) ?: $model;
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    #[\Override]
    protected function replaceClass($stub, $name)
    {
        $stub = parent::replaceClass($stub, $name);

        $replace = [
            '{{ namespacedModel }}' => $this->modelClass,
            '{{namespacedModel}}' => $this->modelClass,
            '{{ model }}' => class_basename($this->modelClass),
            '{{model}}' => class_basename($this->modelClass),
        ];

        return str_replace(array_keys($replace), array_values($replace), $stub);
    }

    private function repositoryNameFromModel(): string
    {
        $model = $this->modelNameInput();
        $this->guardAgainstInvalidModelName($model);

        $model = trim(str_replace('\\', '/', $model), '/');
        $rootNamespace = trim(str_replace('\\', '/', $this->rootNamespace()), '/');
        $modelsNamespace = $rootNamespace.'/Models/';

        if (Str::startsWith($model, $modelsNamespace)) {
            $model = Str::after($model, $modelsNamespace);
        } elseif (Str::startsWith($model, $rootNamespace.'/')) {
            $model = Str::after($model, $rootNamespace.'/');
        }

        return $model.'Repository';
    }

    private function guardAgainstInvalidModelName(string $model): void
    {
        if ($model === '' || preg_match('/[^A-Za-z0-9_\/\\\\]/', $model)) {
            throw new InvalidArgumentException('Model name contains invalid characters.');
        }
    }
}
