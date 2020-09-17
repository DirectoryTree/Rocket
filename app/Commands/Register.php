<?php

namespace App\Commands;

use App\Configuration;
use Illuminate\Console\Command;

class Register extends Command
{
    /**
     * The name and signature of the console command
     *
     * @var string
     */
    protected $signature = 'register {directory?} {--git}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register a directory for automated deployment.';

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
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $directory = $this->argument('directory') ?? getcwd();

        $this->config->addApplication($directory);

        $this->info("Successfully registered [$directory]");
    }
}
