<?php

namespace DirectoryTree\Rocket\Commands;

use Illuminate\Console\Command;
use DirectoryTree\Rocket\Windows\DeploymentTask;
use Illuminate\Support\Str;

class Register extends Command
{
    /**
     * The name and signature of the console command
     *
     * @var string
     */
    protected $signature = 'rocket:register';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register the deployment scheduler';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $task = new DeploymentTask([
            'name' => Str::studly(env('APP_NAME').'Deployment'),
            'author' => env('APP_NAME'),
            'description' => 'Automates application deployment.',
        ]);

        if (! $task->exists()) {
            $task->create();
        }

        $command = sprintf('schtasks /Create /TN "%s" /XML "%s" /F', $this->name, $task->path());

        exec($command, $output, $status);

        return $status;
    }
}
