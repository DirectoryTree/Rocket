<?php

namespace DirectoryTree\Rocket;

class Git
{
    /**
     * Update to the given repository tag.
     *
     * @param string $tag
     *
     * @return bool
     */
    public function pull($tag)
    {
        $command = sprintf('git pull origin %s --ff-only 2>nul', $tag);

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
        $command = implode(' ', array_filter(['git reset --hard', $tag]));

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
        exec('git tag --sort=committerdate 2>nul | tail -1', $output, $status);

        return $status === 0 ? reset($output) : false;
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
