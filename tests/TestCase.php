<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Tests;

use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Zdearo\LivewirePanels\LivewirePanelsServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            LivewirePanelsServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
    }
}
