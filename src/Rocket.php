<?php

namespace DirectoryTree\Rocket;

class Rocket
{
    /**
     * The global callbacks to execute during a deployment.
     *
     * @var array
     */
    protected $callbacks = [
        'before' => [],
        'after' => [],
    ];

    /**
     * Register a "before update" callback.
     *
     * @param callable $callback
     *
     * @return $this
     */
    public function beforeUpdate(callable $callback)
    {
        $this->callbacks['before'][] = $callback;

        return $this;
    }

    /**
     * Register a "after update" callback.
     *
     * @param callable $callback
     *
     * @return $this
     */
    public function afterUpdate(callable $callback)
    {
        $this->callbacks['after'][] = $callback;

        return $this;
    }

    /**
     * Run the global "after update" callbacks.
     *
     * @return void
     */
    public function runAfterCallbacks()
    {
        $this->runCallbacks('after');
    }

    /**
     * Run the global "before update" callbacks.
     *
     * @return void
     */
    public function runBeforeCallbacks()
    {
        $this->runCallbacks('before');
    }

    /**
     * Run the callbacks for the given type.
     *
     * @param string $type
     */
    protected function runCallbacks($type)
    {
        foreach ($this->callbacks[$type] as $callback) {
            $callback();
        }
    }
}
