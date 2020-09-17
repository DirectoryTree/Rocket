<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Configuration;

class RegisterTest extends TestCase
{
    use EnsuresConfigExists;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureConfigFileExists();
    }

    public function test_invalid_directories_cannot_be_registered()
    {
        $this->artisan('register', ['directory' => 'invalid'])
            ->expectsOutput('Directory does not exist.');
    }

    public function test_current_working_directory_is_registered_by_default()
    {
        $this->artisan('register')
            ->expectsOutput(sprintf('Successfully registered [%s]', $currentDir = getcwd()));

        $config = app(Configuration::class);

        $this->assertEquals($currentDir, $config->read()['applications'][0]['path']);
    }

    public function test_directories_can_be_registered()
    {
        $directory = __DIR__;

        $this->artisan('register', ['directory' => $directory])
            ->expectsOutput(sprintf('Successfully registered [%s]', $directory));

        $config = app(Configuration::class);

        $this->assertEquals($directory, $config->read()['applications'][0]['path']);
    }
}
