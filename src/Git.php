<?php

namespace DirectoryTree\Rocket;

class Git
{
    /**
     * The git API key.
     *
     * @var string
     */
    protected $key;

    /**
     * The git remote.
     *
     * @var string
     */
    protected $remote;

    /**
     * Constructor.
     *
     * @param string $key
     * @param string $remote
     */
    public function __construct($key, $remote = 'origin')
    {
        $this->key = $key;
        $this->remote = $remote;
    }

    /**
     * Add a remote URL to the current git repo.
     *
     * @param string $remote
     * @param string $url
     *
     * @return bool
     */
    public function addRemote($remote, $url)
    {
        $command = sprintf('git remote add %s %s --tags 2>nul', $remote, $url);

        exec($command, $output, $status);

        return $status === 0;
    }

    /**
     * Get the available tracked repositories
     *
     * @return array
     */
    public function getRemotes()
    {
        exec('git remote -v', $output, $status);

        if ($status !== 0) {
            return [];
        }

        $remotes = [];

        foreach ($output as $line) {
            //
        }

        return $remotes;
    }

    /**
     * Update to the given repository tag.
     *
     * @param string $tag
     *
     * @return bool
     */
    public function pull($tag)
    {
        $command = sprintf('git pull %s %s --ff-only 2>nul', $this->remote, $tag);

        exec($command, $output, $status);

        return $status === 0;
    }

    /**
     * Reset the repository to the HEAD, or to the given tag.
     *
     * @param string|null $tag
     *
     * @return bool
     */
    public function reset($tag = null)
    {
        $command = implode(' ', array_filter(['git reset --hard', $tag, '2>nul']));

        exec($command, $output, $status);

        return $status === 0;
    }

    /**
     * Get all available tags.
     *
     * @return array
     */
    public function getAllTags()
    {
        exec('git tag', $output, $status);

        return $status === 0 ? $output : [];
    }

    /**
     * Get the latest repository tag.
     *
     * @return string|false
     */
    public function getLatestTag()
    {
        exec('git tag 2>nul', $output, $status);

        return $status === 0 ? end($output) : false;
    }

    /**
     * Get the current repository tag.
     *
     * @return string|false
     */
    public function getCurrentTag()
    {
        exec('git describe --tags --exact-match 2>nul', $output, $status);

        return $status === 0 ? reset($output) : false;
    }
}
