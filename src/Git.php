<?php

namespace DirectoryTree\Rocket;

use InvalidArgumentException;
use TitasGailius\Terminal\Terminal;

class Git
{
    use FormatsConsoleOutput;

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
        return Terminal::with(['remote' => $remote, 'url' => $url])
            ->run('git remote add {{ $remote }} {{ $url }}')
            ->successful();
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
        return Terminal::with(['remote' => $remote, 'newUrl' => $newUrl])
            ->run('git remote set-url {{ $remote }} {{ $newUrl }}')
            ->successful();
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
        return Terminal::with(['remote' => $remote])
            ->run('git remote rm {{ $remote }}')
            ->successful();
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
            $this->username.':'.$this->token.'@'.$parts['host'].$parts['path'],
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
        $response = Terminal::run('git remote -v');

        if (! $response->successful()) {
            return [];
        }

        $lines = $this->getLinesFromResponse($response->output());

        $remotes = [];

        foreach ($lines as $line) {
            [$remote, $url, $type] = $this->splitLineOutput($line);

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
        return Terminal::with(['remote' => $this->remote, 'tag' => $tag])
            ->run('git pull {{ $remote }} {{ $tag }} --ff-only')
            ->successful();
    }

    /**
     * Fetch the repository's tags.
     *
     * @return bool
     */
    public function fetch()
    {
        return Terminal::run('git fetch --tags -f')
            ->successful();
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
        return Terminal::with(['tag' => $tag])
            ->run('git reset --hard {{ $tag }}')
            ->successful();
    }

    /**
     * Get all available tags.
     *
     * @return array
     */
    public function getAllTags()
    {
        $response = Terminal::run('git tag');

        return $response->successful()
            ? $this->getLinesFromResponse($response)
            : [];
    }

    /**
     * Get the latest repository tag.
     *
     * @return string|false
     */
    public function getLatestTag()
    {
        $tags = $this->getAllTags();

        return end($tags);
    }

    /**
     * Get the current repository tag.
     *
     * @return string|false
     */
    public function getCurrentTag()
    {
        $response = Terminal::run('git describe --tags');

        $response->successful()
            ? $this->trimOutput($response->output())
            : false;
    }
}
