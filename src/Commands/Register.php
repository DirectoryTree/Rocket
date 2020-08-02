<?php

namespace DirectoryTree\Rocket\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use DirectoryTree\Rocket\Windows\DeploymentTask;

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

        if (! File::isDirectory($path = base_path('deployments'))) {
            File::makeDirectory($path);
        }

        $taskPath = implode(DIRECTORY_SEPARATOR, [
            $path, Str::snake($task->name).'.xml',
        ]);

        File::put($taskPath, $task->toXml());

        $command = sprintf('schtasks /Create /TN "%s" /XML "%s" /F', $task->name, $taskPath);

        exec($command, $output, $status);

        return $status;
    }
}
