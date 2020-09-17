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
}
