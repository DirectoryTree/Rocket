<?php

namespace App;

use Illuminate\Filesystem\Filesystem;

class Configuration
{
    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * Constructor.
     *
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    /**
     * Install the configuration file.
     *
     * @return void
     */
    public function install()
    {
        $this->createConfigurationDirectory();
        $this->writeBaseConfiguration();
    }

    /**
     * Forcefully delete the Valet home configuration directory and contents.
     *
     * @return void
     */
    public function uninstall()
    {
        $this->files->deleteDirectory(ROCKET_HOME_PATH);
    }

    /**
     * Create the working configuration directory.
     *
     * @return void
     */
    public function createConfigurationDirectory()
    {
        $this->files->ensureDirectoryExists(ROCKET_HOME_PATH);
    }

    /**
     * Write the base configuration values.
     *
     * @return void
     */
    public function writeBaseConfiguration()
    {
        if (! $this->files->exists($this->path())) {
            $this->write(['applications' => []]);
        }
    }

    /**
     * Add the given path to the configuration.
     *
     * @param string $path
     * @param array  $git
     * @param bool   $prepend
     *
     * @return void
     */
    public function addApplication($path, $git = [], $prepend = false)
    {
        $this->write(tap($this->read(), function (&$config) use ($path, $git, $prepend) {
            $method = $prepend ? 'prepend' : 'push';

            $data = ['name' => basename($path), 'path' => $path, 'git' => $git];

            $config['applications'] = collect($config['applications'])->{$method}($data)->unique()->all();
        }));
    }

    /**
     * Get the registered applications.
     *
     * @return array
     */
    public function getApplications()
    {
        return $this->read()['applications'];
    }

    /**
     * Prepend the given path to the configuration.
     *
     * @param string $path
     *
     * @return void
     */
    public function prependPath($path)
    {
        $this->addApplication($path, $prepend = true);
    }

    /**
     * Remove the given path from the configuration.
     *
     * @param string $path
     *
     * @return void
     */
    public function removePath($path)
    {
        $this->write(tap($this->read(), function (&$config) use ($path) {
            $config['applications'] = collect($config['applications'])->reject(function ($value) use ($path) {
                return $value === $path;
            })->values()->all();
        }));
    }

    /**
     * Read the current configuration.
     *
     * @return array
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function read()
    {
        return json_decode($this->files->get($this->path()), $assoc = true);
    }

    /**
     * Update a specific key in the configuration file.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return array
     */
    public function updateKey($key, $value)
    {
        return tap($this->read(), function (&$config) use ($key, $value) {
            $config[$key] = $value;

            $this->write($config);
        });
    }

    /**
     * Write the given configuration to disk.
     *
     * @param array $config
     *
     * @return void
     */
    public function write($config)
    {
        $this->files->put($this->path(), json_encode(
            $config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        ).PHP_EOL);
    }

    /**
     * Get the config path.
     *
     * @return string
     */
    public function path()
    {
        return ROCKET_HOME_PATH.'/config.json';
    }
}
