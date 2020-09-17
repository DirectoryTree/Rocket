<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\File;

class InstallTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->deleteConfigStub();
    }

    protected function deleteConfigStub()
    {
        File::delete(ROCKET_HOME_PATH.'/config.json');
    }

    public function test_install_creates_json_config()
    {
        $this->artisan('install')->assertExitCode(0);

        $this->assertTrue(File::exists(ROCKET_HOME_PATH.'/config.json'));
    }
}
