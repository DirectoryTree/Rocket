<?php

namespace App\Commands;

use App\Configuration;
use Illuminate\Console\Command;

class Install extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize rocket.';

    /**
     * The configuration instance.
     *
     * @var Configuration
     */
    protected $config;

    /**
     * Constructor.
     *
     * @param Configuration $config
     */
    public function __construct(Configuration $config)
    {
        parent::__construct();

        $this->config = $config;
    }

    /**
     * Install rocket.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->option('force')) {
            $this->config->uninstall();
        }

        $this->config->install();

        $this->info("Successfully created configuration file in [{$this->config->path()}].");
    }
}
