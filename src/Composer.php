<?php

namespace DirectoryTree\Rocket;

use Illuminate\Support\Composer as BaseComposer;

class Composer extends BaseComposer
{
    /**
     * Install the composer dependencies.
     *
     * @param string $extra
     *
     * @return void
     */
    public function install($extra = '')
    {
        $extra = $extra ? (array) $extra : [];

        $command = array_merge($this->findComposer(), ['install'], $extra);

        $this->getProcess($command)->run();
    }
}
