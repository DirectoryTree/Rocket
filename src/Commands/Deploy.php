<?php

namespace DirectoryTree\Rocket\Commands;

use DirectoryTree\Rocket\Git;
use DirectoryTree\Rocket\Tag;
use DirectoryTree\Rocket\Rocket;
use DirectoryTree\Rocket\Composer;
use Illuminate\Console\Command;

class Deploy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rocket:deploy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deploy the latest tagged application version';

    /**
     * The Composer instance.
     *
     * @var Composer
     */
    protected $composer;

    /**
     * Constructor.
     *
     * @param Composer $composer
     */
    public function __construct(Composer $composer)
    {
        parent::__construct();

        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     *
     * @param Git    $git
     * @param Rocket $rocket
     *
     * @return int
     */
    public function handle(Git $git, Rocket $rocket)
    {
        if (! $git->fetch()) {
            return $this->error('Unable to fetch git tags.');
        }

        switch (true) {
            case empty($currentTag = $git->getCurrentTag()): {
                return $this->error('Unable to retrieve current git tag');
            }
            case empty($latestTag = $git->getLatestTag()): {
                return $this->error('Unable to retrieve latest git tag.');
            }
        }

        if (! (new Tag($currentTag))->isOlderThan($latestTag)) {
            return $this->info('No new tags found to deploy.');
        }

        $this->call('down');

        $rocket->runBeforeCallbacks();

        logger()->info(sprintf('Updating tag from [%s] to [%s]', $currentTag, $latestTag));

        if (! $git->pull($latestTag)) {
            logger()->info(sprintf('Unable to deploy latest tag [%s]', $latestTag));

            $this->call('up');

            return $this->error("Unable to deploy latest tag [$latestTag]");
        }

        $this->composer->install();

        $rocket->runAfterCallbacks();

        logger()->info(sprintf('Completed deployment of tag [%s]', $latestTag));

        $this->call('up');

        return $this->info("Successfully deployed tag [$latestTag]");
    }
}
