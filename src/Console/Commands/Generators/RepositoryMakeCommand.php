<?php

namespace SaineshMamgain\LaravelRepositories\Console\Commands\Generators;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputOption;

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
    protected $signature = 'make:repository {name}';

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
     * The name of repository being generated.
     *
     * @var string
     */
    private $repositoryClass;

    /**
     * The name of model for which repository is being generated.
     *
     * @var string
     */
    private $model;

    /**
     * The full namespace of model for which repository is being generated.
     *
     * @var string
     */
    private $modelNameSpace;

    public function handle()
    {
        $this->setRepositoryClass();

        $path = $this->getPath($this->repositoryClass);

        if ($this->alreadyExists($this->repositoryClass)) {
            $this->error($this->type.' already exists!');

            return false;
        }

        $this->makeDirectory($path);

        $this->files->put($path, $this->buildClass($this->repositoryClass));

        $this->info($this->type.' created successfully.');

        $this->line("<info>Created Repository :</info> $this->repositoryClass");
    }

    protected function getStub()
    {
        return $this->resolveStubPath('/stubs/laravel-repository.stub');
    }

    protected function resolveStubPath($stub)
    {
        return is_file($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__ . '/../../../../' . $stub;
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Repositories';
    }

    protected function getOptions()
    {
        return [
            ['model', InputOption::VALUE_REQUIRED, 'The name of Model Class'],
        ];
    }

    private function setRepositoryClass()
    {
        $name = $this->argument('name');

        $name = str_ireplace(['repository', 'Repository'], '', $name);

        $fullyQualifiedModelNameSpace = '';

        if (file_exists(base_path('app/Models/'.$name.'.php'))) {
            $fullyQualifiedModelNameSpace = 'App\\Models\\'.$name;
        }
        if (file_exists(base_path('app/'.$name.'.php'))) {
            $fullyQualifiedModelNameSpace = 'App\\'.$name;
        }

        if (!(new $fullyQualifiedModelNameSpace() instanceof Model)) {
            throw new \Exception($name.' is not a valid model');
        }

        if (empty($fullyQualifiedModelNameSpace)) {
            throw new \Exception('Model '.$name.' doesn\'t exist');
        }

        $this->model = $name;
        $this->modelNameSpace = $fullyQualifiedModelNameSpace;

        $modelClass = $this->qualifyClass($name);

        $this->repositoryClass = $modelClass.'Repository';

        return $this;
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param string $stub
     * @param string $name
     *
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        if (!$this->argument('name')) {
            throw new InvalidArgumentException('Missing required argument model name');
        }

        $stub = parent::replaceClass($stub, $name);

        $stub = str_replace('{{DummyModel}}', $this->model, $stub);

        return str_replace('{{DummyModelNamespace}}', $this->modelNameSpace, $stub);
    }
}
