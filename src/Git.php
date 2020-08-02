<?php

namespace DirectoryTree\Rocket;

use InvalidArgumentException;

class Git
{
    /**
     * The git username.
     *
     * @var string
     */
    protected $username;

    /**
     * The git API token.
     *
     * @var string
     */
    protected $token;

    /**
     * The git remote.
     *
     * @var string
     */
    protected $remote;

    /**
     * Constructor.
     *
     * @param string $username
     * @param string $token
     * @param string $remote
     */
    public function __construct($username, $token, $remote = 'origin')
    {
        $this->username = $username;
        $this->token = $token;
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
        $command = sprintf('git remote add %s %s --tags 2>&1', $remote, $url);

        exec($command, $output, $status);

        return $status === 0;
    }

    /**
     * Change the URLs for the remote.
     *
     * @param string $remote
     * @param string $newUrl
     * @param
     *
     * @return bool
     */
    public function setRemoteUrl($remote, $newUrl)
    {
        exec("git remote set-url $remote $newUrl 2>&1", $output, $status);

        return $status === 0;
    }

    /**
     * Remove the specified remote.
     *
     * @param string $remote
     *
     * @return bool
     */
    public function removeRemote($remote)
    {
        exec("git remote rm $remote", $output, $status);

        return $status === 0;
    }

    /**
     * Convert the given git remote URL to token.
     *
     * @param string $remote
     *
     * @return bool
     */
    public function convertRemoteToToken($remote)
    {
        if (empty($this->token)) {
            throw new InvalidArgumentException('No token has been defined');
        }

        if (! $urls = $this->getRemote($remote)) {
            return false;
        }

        return $this->setRemoteUrl(
            $remote, $this->makeTokenBasedUrl($urls['push'])
        );
    }

    /**
     * Make a token based URL from the given.
     *
     * @param string $url
     *
     * @return string
     */
    protected function makeTokenBasedUrl($url)
    {
        $parts = parse_url($url);

        return implode('/', [
            $parts['scheme'].':/',
            $this->username.':'.$this->token.'@',
            $parts['host'].$parts['path'],
        ]);
    }

    /**
     * Get the URLs for the remote.
     *
     * @param string $remote
     *
     * @return array|null
     */
    public function getRemote($remote)
    {
        foreach ($this->getRemotes() as $name => $urls) {
            if ($name == $remote) {
                return $urls;
            }
        }
    }

    /**
     * Get the available tracked repositories
     *
     * @return array
     */
    public function getRemotes()
    {
        exec('git remote -v 2>&1', $output, $status);

        if ($status !== 0) {
            return [];
        }

        $remotes = [];

        foreach ($output as $line) {
            [$remote, $url, $type] = preg_split('/\s+/', $line);

            $type = str_replace(['(', ')'], '', $type);

            $remotes[$remote][$type] = $url;
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
        $command = sprintf('git pull %s %s --ff-only 2>&1', $this->remote, $tag);

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
        $command = implode(' ', array_filter(['git reset --hard', $tag, '2>&1']));

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
        exec('git tag 2>&1', $output, $status);

        return $status === 0 ? end($output) : false;
    }

    /**
     * Get the current repository tag.
     *
     * @return string|false
     */
    public function getCurrentTag()
    {
        exec('git describe --tags --exact-match 2>&1', $output, $status);

        return $status === 0 ? reset($output) : false;
    }
}
