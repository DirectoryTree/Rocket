<?php

namespace Tests;

use App\Configuration;
use TitasGailius\Terminal\Terminal;
use LaravelZero\Framework\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        if (! defined('ROCKET_HOME_PATH')) {
            define('ROCKET_HOME_PATH', __DIR__.'/stubs');
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        app(Configuration::class)->uninstall();

        Terminal::reset();
    }
}
