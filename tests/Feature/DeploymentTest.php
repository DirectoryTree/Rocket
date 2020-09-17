<?php

namespace Tests\Feature;

use App\Configuration;
use Tests\TestCase;
use TitasGailius\Terminal\Terminal;

class DeploymentTest extends TestCase
{
    use EnsuresConfigExists;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureConfigFileExists();
    }

    public function test_fails_when_there_are_no_registered_applications()
    {
        $this->artisan('deploy')
            ->expectsOutput('There are no registered applications to deploy.');
    }

    public function test_fails_when_tags_cannot_be_fetched()
    {
        $this->artisan('register');

        Terminal::fake(['git fetch --tags -f' => Terminal::response()->shouldFail()]);

        $path = app(Configuration::class)->read()['applications'][0]['path'];

        $this->artisan('deploy')
            ->expectsOutput(sprintf('[%s] Unable to fetch git tags.', basename($path)));
    }
}
