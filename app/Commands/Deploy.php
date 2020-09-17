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
    protected $signature = 'deploy';

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
        foreach ($this->config->getApplications() as $application) {
            if (! chdir($application['path'])) {
                return $this->error("Unable to change current directory to [{$application['path']}");
            }

            (new Deployment($this->composer, $application))->run($this);
        }
    }
}
