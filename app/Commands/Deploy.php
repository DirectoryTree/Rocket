<?php

namespace App\Commands;

use App\Composer;
use App\Deployment;
use App\Configuration;
use Illuminate\Console\Command;

class Deploy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deploy {path?} {--tag=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deploy the latest tagged application versions.';

    /**
     * The configuration instance.
     *
     * @var Configuration
     */
    protected $config;

    /**
     * The Composer instance.
     *
     * @var Composer
     */
    protected $composer;

    /**
     * Constructor.
     *
     * @param Configuration $config
     * @param Composer      $composer
     */
    public function __construct(Configuration $config, Composer $composer)
    {
        parent::__construct();

        $this->config = $config;
        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (empty($apps = $this->config->getApplications())) {
            return $this->error('There are no registered applications to deploy.');
        }

        if ($path = $this->argument('path')) {
            $key = array_search($path, array_column($apps, 'path'));

            if ($key === false) {
                return $this->error("There is no application registered with path [$path].");
            }

            return $this->deploy($apps[$key], $this->option('tag'));
        }

        foreach ($apps as $app) {
            $this->deploy($app);
        }
    }

    /**
     * Deploy the application.
     *
     * @param array       $app
     * @param string|null $tag
     *
     * @return void
     */
    protected function deploy($app, $tag = null)
    {
        if (! chdir($app['path'])) {
            return $this->error("Unable to change current directory to [{$app['path']}");
        }

        (new Deployment($this->composer, $app))->upgrade($this, $tag);
    }
}
