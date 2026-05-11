<?php

declare(strict_types=1);

namespace Dearo\LivewirePanels;

use Illuminate\Support\ServiceProvider;

final class LivewirePanelsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/livewire-panels.php', 'livewire-panels');
    }

    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/livewire-panels.php' => config_path('livewire-panels.php'),
        ], 'livewire-panels-config');
    }
}
