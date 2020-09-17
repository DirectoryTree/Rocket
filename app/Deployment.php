<?php

namespace App;

use App\Commands\Deploy;
use TitasGailius\Terminal\Terminal;

class Deployment
{
    /**
     * The Composer instance.
     *
     * @var Git
     */
    protected $composer;

    /**
     * The application being deployed.
     *
     * @var array
     */
    protected $application;

    /**
     * The applications name.
     *
     * @var string
     */
    protected $applicationName;

    /**
     * The Git instance.
     *
     * @var Git
     */
    protected $git;

    /**
     * Constructor.
     *
     * @param Composer $composer
     * @param array    $application
     */
    public function __construct(Composer $composer, array $application)
    {
        $this->composer = $composer;
        $this->application = $application;
        $this->applicationName = basename(getcwd());
        $this->git = new Git($application['git']['remote'] ?? 'origin');
    }

    /**
     * Execute the deployment.
     *
     * @param Deploy $command
     *
     * @return void
     */
    public function run(Deploy $command)
    {
        if (! $this->git->fetch()) {
            return $command->error($this->makeMessage('Unable to fetch git tags.'));
        }

        switch (true) {
            case empty($currentTag = $this->git->getCurrentTag()):
                return $command->error($this->makeMessage('Unable to retrieve current git tag'));
            case empty($latestTag = $this->git->getLatestTag()):
                return $command->error($this->makeMessage('Unable to retrieve latest git tag.'));
        }

        if (! (new Tag($currentTag))->isOlderThan($latestTag)) {
            return $command->info($this->makeMessage("No new tags found to deploy. Current tag is [$currentTag]"));
        }

        $this->takeApplicationDown();

        $command->info(
            $this->makeMessage(sprintf('Updating tag from [%s] to [%s]', $currentTag, $latestTag))
        );

        if (! $this->git->pull($latestTag)) {
            $this->bringApplicationUp();

            return $command->error($this->makeMessage("Unable to deploy latest tag [$latestTag]"));
        }

        $this->composer->install();

        $this->bringApplicationUp();

        return $command->info($this->makeMessage("Successfully deployed tag [$latestTag]"));
    }

    /**
     * Make a console message.
     *
     * @param string $message
     *
     * @return string
     */
    protected function makeMessage($message)
    {
        return "[{$this->applicationName}] $message";
    }

    /**
     * Take the application down.
     *
     * @return bool
     */
    protected function takeApplicationDown()
    {
        return Terminal::run('php artisan down')->successful();
    }

    /**
     * Bring the application up.
     *
     * @return bool
     */
    protected function bringApplicationUp()
    {
        return Terminal::run('php artisan up')->successful();
    }
}
