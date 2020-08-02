<?php

namespace DirectoryTree\Rocket\Windows;

class DeploymentTask extends ScheduledTask
{
    /**
     * The task attributes.
     *
     * @var array
     */
    protected $attributes = [
        'user_id' => ScheduledTask::USER_SYSTEM,
        'interval' => 'PT1M',
        'time_limit' => 'PT30M',
        'command' => 'rocket:deploy',
    ];
}
