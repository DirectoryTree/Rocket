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
        $current = $git->getCurrentTag();
        $latest = $git->getLatestTag();

        if (! $current) {
            return $this->info('Unable to retrieve current git tag');
        }

        if (! $latest) {
            return $this->info('Unable to retrieve latest git tag.');
        }

        if (! $this->tagIsOld($current, $latest)) {
            return $this->info('No updates found.');
        }

        $this->call('down');

        $rocket->runBeforeCallbacks();

        logger()->info(sprintf('Updating tag from [%s] to [%s]', $current, $latest));

        chdir(base_path());

        if (! $git->pull($latest)) {
            logger()->info(sprintf('Unable to deploy latest tag [%s]', $latest));

            $this->call('up');

            return -1;
        }

        $this->runComposerInstall();

        $rocket->runAfterCallbacks();

        logger()->info(sprintf('Completed deployment of tag [%s]', $latest));

        $this->call('up');

        return 0;
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
     * Deploy the given tag.
     *
     * @param string $tag
     *
     * @return bool
     */
    protected function deploy($tag)
    {

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
        return version_compare($current, $latest, '<');
    }
}
