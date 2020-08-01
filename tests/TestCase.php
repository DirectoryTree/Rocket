<?php

namespace DirectoryTree\Rocket\Tests;

use DirectoryTree\Rocket\RocketServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [RocketServiceProvider::class];
    }
}
