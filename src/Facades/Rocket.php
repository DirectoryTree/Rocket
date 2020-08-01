<?php

namespace DirectoryTree\Rocket\Facades;

use Illuminate\Support\Facades\Facade;

class Rocket extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'rocket';
    }
}
