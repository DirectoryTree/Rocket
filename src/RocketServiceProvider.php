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
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('rocket', function () {
            return new Rocket;
        });
    }
}
