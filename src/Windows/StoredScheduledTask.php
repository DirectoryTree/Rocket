<?php

namespace DirectoryTree\Rocket\Windows;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

abstract class StoredScheduledTask extends ScheduledTask
{
    /**
     * Create the scheduled task XML file.
     *
     * @return bool
     */
    public function create()
    {
        $path = $this->path();

        return File::put($path, $this->toXml());
    }

    /**
     * Determine if the scheduled task file exists.
     *
     * @return bool
     */
    public function exists()
    {
        return File::exists($this->path());
    }

    /**
     * Get the full file path of the XML document.
     *
     * @return string
     */
    public function path()
    {
        return implode(DIRECTORY_SEPARATOR, [
            base_path(),
            'deployments',
            Str::snake($this->name).'.xml',
        ]);
    }
}
