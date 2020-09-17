<?php

namespace App\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Composer;
use App\Deployments\DeploymentCreator;

class MakeDeployment extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'make:deployment {version : The version to run on}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new deployment file';

    /**
     * The deployment creator instance.
     *
     * @var DeploymentCreator
     */
    protected $creator;

    /**
     * The Composer instance.
     *
     * @var Composer
     */
    protected $composer;

    /**
     * Constructor.
     *
     * @param DeploymentCreator $creator
     * @param Composer          $composer
     *
     * @return void
     */
    public function __construct(DeploymentCreator $creator, Composer $composer)
    {
        parent::__construct();

        $this->creator = $creator;
        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $name = ltrim($this->input->getArgument('name'), 'v');

        $this->writeDeployment($name);

        $this->composer->dumpAutoloads();
    }

    /**
     * Write the deployment file to disk.
     *
     * @param string $name
     *
     * @return string
     */
    protected function writeDeployment($name)
    {
        $file = $this->creator->create(
            $name, $this->getDeploymentPath()
        );

        $this->line("<info>Created Deployment:</info> {$file}");
    }

    /**
     * Get deployment path.
     *
     * @return string
     */
    protected function getDeploymentPath()
    {
        return $this->laravel->basePath().DIRECTORY_SEPARATOR.'deployments';
    }
}
