<?php

declare(strict_types=1);

namespace Dearo\LivewirePanels\Tests;

use Dearo\LivewirePanels\LivewirePanelsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            LivewirePanelsServiceProvider::class,
        ];
    }
}
