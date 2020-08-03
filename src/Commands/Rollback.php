<?php

namespace DirectoryTree\Rocket\Commands;

use DirectoryTree\Rocket\Git;
use DirectoryTree\Rocket\Tag;
use DirectoryTree\Rocket\Rocket;
use DirectoryTree\Rocket\Composer;
use Illuminate\Console\Command;

class Rollback extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rocket:rollback';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rollback to the previously tagged application version';

    /**
     * The composer instance.
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
            return $this->error('Unable to fetch all repository tags.');
        }

        $currentTag = $git->getCurrentTag();

        if (! $currentTag) {
            return $this->error('Unable to get current repository tag.');
        }

        $tags = $git->getAllTags();

        array_pop($tags);

        $previousTag = end($tags);

        if (! $previousTag) {
            return $this->error('Unable to retrieve last repository tag.');
        }

        if (! (new Tag($currentTag))->isNewerThan($previousTag)) {
            return $this->error('The current repository tag is not newer than the last.');
        }

        logger()->info("Rolling back from tag [$currentTag] to [$previousTag]");

        $this->call('down');

        $rocket->runBeforeCallbacks();

        if (! $git->reset($previousTag)) {
            return $this->error("Unable to rollback repository to tag [$previousTag]");
        }

        $this->composer->install();

        $this->call('up');

        $rocket->runAfterCallbacks();

        logger()->info("Rolled back to tag [$previousTag].");

        return $this->info("Successfully rolled back to tag [$previousTag]");
    }
}
