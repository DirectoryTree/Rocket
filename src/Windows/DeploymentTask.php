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
        'interval' => 'PT1M',
        'time_limit' => 'PT30M',
        'command' => 'rocket:deploy',
        'user_id' => DeploymentTask::USER_SYSTEM,
    ];
}
