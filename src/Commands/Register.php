<?php

namespace DirectoryTree\Rocket\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use TitasGailius\Terminal\Terminal;
use DirectoryTree\Rocket\Windows\DeploymentTask;

class Register extends Command
{
    /**
     * The name and signature of the console command
     *
     * @var string
     */
    protected $signature = 'rocket:register {--as-system}';

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
        if (! $this->option('as-system')) {
            $user = Terminal::run('whoami')->output();
        } else {
            $user = DeploymentTask::USER_SYSTEM;
        }

        $task = new DeploymentTask([
            'user_id' => $user,
            'author' => env('APP_NAME'),
            'name' => Str::studly(env('APP_NAME').'Deployment'),
            'description' => 'Automates application deployment.',
        ]);

        $taskPath = implode(DIRECTORY_SEPARATOR, [
            base_path(), Str::snake($task->name).'.xml',
        ]);

        File::put($taskPath, $task->toXml());

        $response = Terminal::run(
            sprintf('schtasks /Create /TN "%s" /XML "%s" /F', $task->name, $taskPath)
        );

        File::delete($taskPath);

        return $response->successful()
            ? $this->info('Successfully registered scheduled task.')
            : $this->error('Unable to register scheduled task.');
    }
}
