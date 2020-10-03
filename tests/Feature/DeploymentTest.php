<?php

namespace Tests\Feature;

use App\Git;
use Illuminate\Console\Command;
use Mockery as m;
use App\Composer;
use App\Deployment;
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

        $app = app(Configuration::class)->getApplications()[0]['name'];

        $this->artisan('deploy')
            ->expectsOutput($this->makeConsoleMessage($app, 'Unable to fetch git tags.'));
    }

    public function test_deployment_fails_when_current_tag_cannot_be_fetched()
    {
        $this->artisan('register');

        Terminal::fake(['git fetch --tags -f' => Terminal::response()->successful()]);
        Terminal::fake(['git describe --tags' => Terminal::response()->shouldFail()]);

        $app = app(Configuration::class)->getApplications()[0]['name'];

        $this->artisan('deploy')
            ->expectsOutput($this->makeConsoleMessage($app, 'Unable to retrieve current git tag.'));
    }

    public function test_deployment()
    {
        $git = m::mock(Git::class);

        $composer = m::mock(Composer::class);

        $deployment = new Deployment($composer, ['name' => 'app'], $git);

        $command = m::mock(Command::class);

        $git->shouldReceive('fetch')->once()->andReturnTrue();
        $git->shouldReceive('getCurrentTag')->once()->andReturn('v1.0.0');
        $git->shouldReceive('getNextTag')->once()->with('v1.0.0')->andReturn('v1.0.1');
        $git->shouldReceive('pull')->once()->with('v1.0.1')->andReturnTrue();

        $command->shouldReceive('info')->once()->with('[app] Taking application down...');
        $command->shouldReceive('info')->once()->with('[app] Updating from [v1.0.0] to [v1.0.1]');
        $command->shouldReceive('info')->once()->with('[app] Successfully updated to tag [v1.0.1]. Running composer install...');
        $command->shouldReceive('info')->once()->with('[app] Successfully deployed tag [v1.0.1].');

        Terminal::fake(['php artisan down' => Terminal::response()->successful()]);
        Terminal::fake(['php artisan up' => Terminal::response()->successful()]);

        $composer->shouldReceive('install')->once()->andReturnTrue();

        $deployment->upgrade($command);
    }
}
