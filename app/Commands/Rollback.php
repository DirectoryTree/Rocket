<?php

namespace App\Commands;

use App\Composer;
use App\Deployment;
use App\Configuration;
use Illuminate\Console\Command;

class Rollback extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rollback {path} {--tag=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rollback to the previously tagged application version.';

    /**
     * The configuration instance.
     *
     * @var Configuration
     */
    protected $config;

    /**
     * The composer instance.
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
            return $this->error('There are no registered applications to rollback.');
        }

        $path = $this->argument('path');

        $key = array_search($path, array_column($apps, 'path'));

        if ($key === false) {
            return $this->error("There is no application registered with path [$path].");
        }

        return $this->rollback($apps[$key], $this->option('tag'));
    }

    /**
     * Rollback the application to the specified tag.
     *
     * @param array       $app
     * @param string|null $tag
     *
     * @return void
     */
    protected function rollback($app, $tag = null)
    {
        if (! chdir($app['path'])) {
            return $this->error("Unable to change current directory to [{$app['path']}");
        }

        (new Deployment($this->composer, $app))->rollback($this, $tag);
    }
}
