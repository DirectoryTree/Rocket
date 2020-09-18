<?php

namespace App;

trait BuildsAppConsoleMessage
{
    /**
     * Make a console message.
     *
     * @param string $application
     * @param string $message
     *
     * @return string
     */
    protected function makeConsoleMessage($application, $message)
    {
        return "[{$application}] $message";
    }
}
