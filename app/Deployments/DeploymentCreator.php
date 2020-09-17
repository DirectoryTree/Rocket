<?php

namespace App\Deployments;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Illuminate\Filesystem\Filesystem;

class DeploymentCreator
{
    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Constructor.
     *
     * @param Filesystem $files
     *
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    /**
     * Create a new deployment file at the given path.
     *
     * @param string $name
     * @param string $path
     *
     * @return string
     *
     * @throws \Exception
     */
    public function create($name, $path)
    {
        $this->ensureDeploymentDoesntAlreadyExist($name, $path);

        $stub = $this->getStub();

        $this->files->put(
            $path = $this->getPath($name, $path),
            $this->populateStub($name, $stub)
        );

        return $path;
    }

    /**
     * Ensure that a deployment with the given name doesn't already exist.
     *
     * @param string $name
     * @param string $deploymentPath
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    protected function ensureDeploymentDoesntAlreadyExist($name, $deploymentPath = null)
    {
        if (! empty($deploymentPath)) {
            $deploymentFiles = $this->files->glob($deploymentPath.'/*.php');

            foreach ($deploymentFiles as $deploymentFile) {
                $this->files->requireOnce($deploymentFile);
            }
        }

        if (class_exists($className = $this->getClassName($name))) {
            throw new InvalidArgumentException("A {$className} class already exists.");
        }
    }

    /**
     * Get the deployment stub file.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->files->get($this->stubPath().'/deployment.stub');
    }

    /**
     * Populate the place-holders in the deployment stub.
     *
     * @param string $name
     * @param string $stub
     *
     * @return string
     */
    protected function populateStub($name, $stub)
    {
        return str_replace(
            '{{ class }}', $this->getClassName($name), $stub
        );
    }

    /**
     * Get the class name of a deployment name.
     *
     * @param  string  $name
     * @return string
     */
    protected function getClassName($name)
    {
        return Str::studly(
            NumberConverter::convert(str_replace('.', '', $name))
        );
    }

    /**
     * Get the full path to the deployment.
     *
     * @param string $name
     * @param string $path
     *
     * @return string
     */
    protected function getPath($name, $path)
    {
        return $path.'/'.str_replace('.', '_', $name).'.php';
    }

    /**
     * Get the path to the stubs.
     *
     * @return string
     */
    public function stubPath()
    {
        return __DIR__.'/stubs';
    }

    /**
     * Get the filesystem instance.
     *
     * @return \Illuminate\Filesystem\Filesystem
     */
    public function getFilesystem()
    {
        return $this->files;
    }
}
