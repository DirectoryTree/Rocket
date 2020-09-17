<?php

namespace App\Commands;

use App\Configuration;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

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
     * @param Filesystem $files
     *
     * @return void
     */
    public function handle(Filesystem $files)
    {
        $directory = $this->argument('directory') ?? getcwd();

        if (! $files->exists($directory)) {
            return $this->error('Directory does not exist.');
        }

        $this->config->addApplication($directory);

        $this->info("Successfully registered [$directory]");
    }
}
