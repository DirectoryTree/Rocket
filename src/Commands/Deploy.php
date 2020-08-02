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
    protected $description = 'Deploy the latest tagged application version.';

    /**
     * The Git instance.
     *
     * @var Git
     */
    protected $git;

    /**
     * The Composer instance.
     *
     * @var Composer
     */
    protected $composer;

    /**
     * Constructor.
     *
     * @param Git      $git
     * @param Composer $composer
     */
    public function __construct(Git $git, Composer $composer)
    {
        parent::__construct();

        $this->git = $git;
        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     *
     * @param Rocket $rocket
     *
     * @return int
     */
    public function handle(Rocket $rocket)
    {
        $current = $this->git->getCurrentTag();
        $latest = $this->git->getLatestTag();

        if (! $this->tagIsOld($current, $latest)) {
            return $this->info('No updates found.');
        }

        $this->call('down');

        $rocket->runBeforeCallbacks();

        logger()->info(sprintf('Updating tag from [%s] to [%s]', $current, $latest));

        chdir(base_path());

        if ($this->deploy($latest)) {
            $rocket->runAfterCallbacks();

            logger()->info(sprintf('Completed deployment of version [%s]', $latest));
        } else {
            logger()->info(sprintf('Unable to deploy version [%s]', $latest));
        }

        $this->call('up');

        return 0;
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
        if (! $this->git->pull($tag)) {
            return false;
        }

        $args = app()->isLocal()
            ? ['--optimize-autoloader']
            : ['--optimize-autoloader', '--no-dev'];

        $this->composer->install($args);

        return true;
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
