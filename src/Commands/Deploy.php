<?php

namespace DirectoryTree\Rocket\Commands;

use DirectoryTree\Rocket\Git;
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

        $current = $git->getCurrentTag();
        $latest = $git->getLatestTag();

        if (! $current) {
            return $this->error('Unable to retrieve current git tag');
        }

        if (! $latest) {
            return $this->error('Unable to retrieve latest git tag.');
        }

        if (! $this->tagIsOld($current, $latest)) {
            return $this->info('No new tags found to deploy.');
        }

        $this->call('down');

        $rocket->runBeforeCallbacks();

        logger()->info(sprintf('Updating tag from [%s] to [%s]', $current, $latest));

        if (! $git->pull($latest)) {
            logger()->info(sprintf('Unable to deploy latest tag [%s]', $latest));

            $this->call('up');

            return $this->error("Unable to deploy latest tag [$latest]");
        }

        $this->runComposerInstall();

        $rocket->runAfterCallbacks();

        logger()->info(sprintf('Completed deployment of tag [%s]', $latest));

        $this->call('up');

        return $this->info("Successfully deployed tag [$latest]");
    }

    /**
     * Execute composer package installation.
     *
     * @return void
     */
    protected function runComposerInstall()
    {
        $args = app()->isLocal()
            ? ['--optimize-autoloader']
            : ['--optimize-autoloader', '--no-dev'];

        $this->composer->install($args);
    }

    /**
     * Determine if the current tag is less than the latest.
     *
     * @param string $current
     * @param string $latest
     *
     * @return bool
     */
    protected function tagIsOld($current, $latest)
    {
        return version_compare(
            $this->makeComparableVersion($current),
            $this->makeComparableVersion($latest),
            '<'
        );
    }

    /**
     * Make a comparable version string.
     *
     * @param string $version
     *
     * @return string
     */
    protected function makeComparableVersion($version)
    {
        return substr(ltrim($version, 'v'), 0, 5);
    }
}
