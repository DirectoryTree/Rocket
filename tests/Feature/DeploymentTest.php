<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Configuration;
use App\BuildsAppConsoleMessage;
use TitasGailius\Terminal\Terminal;

class DeploymentTest extends TestCase
{
    use EnsuresConfigExists, BuildsAppConsoleMessage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureConfigFileExists();
    }

    public function test_deployment_fails_when_there_are_no_registered_applications()
    {
        $this->artisan('deploy')
            ->expectsOutput('There are no registered applications to deploy.');
    }

    public function test_deployment_fails_when_tags_cannot_be_fetched()
    {
        $this->artisan('register');

        Terminal::fake(['git fetch --tags -f' => Terminal::response()->shouldFail()]);

        $application = app(Configuration::class)->getApplications()[0]['name'];

        $this->artisan('deploy')
            ->expectsOutput($this->makeConsoleMessage($application, 'Unable to fetch git tags.'));
    }

    public function test_deployment_fails_when_current_tag_cannot_be_fetched()
    {
        $this->markTestSkipped('Incomplete test.');

        $this->artisan('register');

        Terminal::fake([
            'git fetch --tags -f' => Terminal::response()->successful(),
        ]);
    }
}
