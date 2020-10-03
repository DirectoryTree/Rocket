<?php

namespace App\Commands;

use App\WindowsSystem;
use App\Windows\DeploymentTask;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallTask extends Command
{
    /**
     * The name and signature of the console command
     *
     * @var string
     */
    protected $signature = 'install-task {--as-system}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Windows automated deployment scheduled task.';

    /**
     * Execute the console command.
     *
     * @param WindowsSystem $system
     *
     * @return int
     */
    public function handle(WindowsSystem $system)
    {
        $user = ! empty($this->option('as-system'))
            ? DeploymentTask::USER_SYSTEM
            : $system->getCurrentUser();

        if (! $user) {
            return $this->error('Unable to retrieve user to register scheduled task.');
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

        $imported = $system->importScheduledTask($task->name, $taskPath);

        File::delete($taskPath);

        return $imported
            ? $this->info('Successfully registered scheduled task.')
            : $this->error('Unable to register scheduled task.');
    }
}
