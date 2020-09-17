<?php

namespace App;

use TitasGailius\Terminal\Terminal;

class System
{
    use FormatsConsoleOutput;

    /**
     * Get the current system user.
     *
     * @return string|false
     */
    public function getCurrentUser()
    {
        $response = Terminal::run('whoami');

        return $response->successful()
            ? $this->trimOutput($response->output())
            : false;
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
