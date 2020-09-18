<?php

namespace App;

use Exception;
use TitasGailius\Terminal\Terminal;

class WindowsSystem extends System
{
    /**
     * Constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        if (strpos(strtolower(PHP_OS), 'win') === false) {
            throw new Exception('System is not Windows.');
        }
    }

    /**
     * Import the scheduled task.
     *
     * @param string $name
     * @param string $path
     *
     * @return bool
     */
    public function importScheduledTask($name, $path)
    {
        return Terminal::with(['name' => $name, 'path' => $path])
            ->run('schtasks /Create /TN "{{ $name }}" /XML "{{ $path }}" /F')
            ->successful();
    }
}
