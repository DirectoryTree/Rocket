<?php

namespace App;

use Illuminate\Console\Command;
use TitasGailius\Terminal\Terminal;

class Deployment
{
    use BuildsAppConsoleMessage;

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
     * @param Git|null $git
     */
    public function __construct(Composer $composer, array $application, Git $git = null)
    {
        $this->composer = $composer;
        $this->application = $application;
        $this->applicationName = $application['name'];
        $this->git = $git ?? new Git($application['git']['remote'] ?? 'origin');
    }

    /**
     * Execute the deployment.
     *
     * @param Command      $command
     * @param string|null  $tag
     *
     * @return void
     */
    public function upgrade(Command $command, $tag = null)
    {
        if (! $this->git->fetch()) {
            return $command->error($this->message('Unable to fetch git tags.'));
        }

        if (empty($currentTag = $this->git->getCurrentTag())) {
            return $command->error($this->message('Unable to retrieve current git tag.'));
        }

        if (empty($nextTag = $tag ?? $this->git->getNextTag($currentTag))) {
            return $command->error($this->message('Unable to retrieve next git tag.'));
        }

        if (! (new Tag($currentTag))->isOlderThan($nextTag)) {
            return $command->info($this->message("No new tags found to deploy. Current tag is [$currentTag]."));
        }

        $command->info($this->message('Taking application down...'));

        if (! $this->takeApplicationDown()) {
            return $command->error(
                $this->message('There was an error attempting to bring the application down.')
            );
        }

        $command->info($this->message("Updating from [$currentTag] to [$nextTag]"));

        if (! $this->git->pull($nextTag)) {
            $command->error($this->message('Unable to pull next tag. Bringing application back up...'));

            $this->bringApplicationUp();

            return $command->error($this->message("Unable to deploy latest tag [$nextTag]."));
        }

        $command->info($this->message("Successfully updated to tag [$nextTag]. Running composer install..."));

        $this->composer->install();

        $this->bringApplicationUp();

        return $command->info($this->message("Successfully deployed tag [$nextTag]."));
    }

    /**
     * Rollback to the previous tag.
     *
     * @param Command     $command
     * @param string|null $tag
     *
     * @return void
     */
    public function rollback(Command $command, $tag = null)
    {
        if (! $this->git->fetch()) {
            return $command->error($this->message('Unable to fetch git tags.'));
        }

        if (empty($currentTag = $this->git->getCurrentTag())) {
            return $command->error($this->message('Unable to retrieve current git tag'));
        }

        if (empty($previousTag = $tag ?? $this->git->getPreviousTag($currentTag))) {
            return $command->error($this->message('Unable to retrieve previous git tag.'));
        }

        if (! (new Tag($currentTag))->isNewerThan($previousTag)) {
            return $command->info($this->message("No previous tags found to deploy. Current tag is [$currentTag]."));
        }

        $command->info($this->message('Taking application down...'));

        if (! $this->takeApplicationDown()) {
            return $command->error(
                $this->message('There was an error attempting to bring the application down.')
            );
        }

        $command->info($this->message("Rolling back from [$currentTag] to [$previousTag]"));

        if (! $this->git->pull($previousTag)) {
            $command->error($this->message('Unable to pull next tag. Bringing application back up...'));

            $this->bringApplicationUp();

            return $command->error($this->message("Unable to deploy latest tag [$previousTag]."));
        }

        $command->info($this->message("Successfully rolled back to tag [$previousTag]. Running composer install..."));

        $this->composer->install();

        $this->bringApplicationUp();

        return $command->info($this->message("Successfully rolled back to tag [$previousTag]."));
    }

    /**
     * Make a console message.
     *
     * @param string $message
     *
     * @return string
     */
    protected function message($message)
    {
        return $this->makeConsoleMessage($this->applicationName, $message);
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
