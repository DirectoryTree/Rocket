<?php

namespace Tests\Feature;

trait EnsuresConfigExists
{
    protected function ensureConfigFileExists()
    {
        $this->artisan('install', ['--force' => true]);
    }
}