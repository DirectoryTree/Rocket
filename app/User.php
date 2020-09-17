<?php

namespace App;

class User
{
    /**
     * Get the current user.
     *
     * @return string
     */
    public static function get()
    {
        if (! isset($_SERVER['SUDO_USER'])) {
            return $_SERVER['USER'];
        }

        return $_SERVER['SUDO_USER'];
    }
}
