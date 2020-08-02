<?php

namespace DirectoryTree\Rocket;

use Illuminate\Support\ServiceProvider;
use DirectoryTree\Rocket\Commands\Deploy;
use DirectoryTree\Rocket\Commands\Register;
use DirectoryTree\Rocket\Commands\MakeDeployment;

class RocketServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Deploy::class,
                Register::class,
                MakeDeployment::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/rocket.php' => config_path('rocket.php'),
            ]);
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Git::class, function ($app) {
            $config = $app['config']['rocket'];

            return new Git(
                $config['git_key'], $config['git_username'], $config['git_remote'],
            );
        });

        $this->app->singleton('rocket', function () {
            return new Rocket;
        });
    }
}
