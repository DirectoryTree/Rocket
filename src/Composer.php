<?php

namespace DirectoryTree\Rocket;

use Illuminate\Support\Composer as BaseComposer;

class Composer extends BaseComposer
{
    /**
     * Install the composer dependencies.
     *
     * @return void
     */
    public function install()
    {
        $args = app()->isLocal()
            ? ['--optimize-autoloader']
            : ['--optimize-autoloader', '--no-dev'];

        $command = array_merge($this->findComposer(), ['install'], $args);

        $this->getProcess($command)->run();
    }
}
