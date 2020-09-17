<?php

namespace App;

use TitasGailius\Terminal\Terminal;

class WindowsSystem extends System
{
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
