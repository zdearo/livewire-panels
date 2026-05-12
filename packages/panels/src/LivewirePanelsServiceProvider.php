<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Zdearo\LivewirePanels\Commands\MakePanelCommand;

final class LivewirePanelsServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register(): void
    {
        $this->app->singleton(PanelRegistry::class);
        $this->app->singleton(PanelManager::class);
        $this->app->singleton(PanelRouter::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'livewire-panels');
        Blade::anonymousComponentPath(__DIR__.'/../resources/views', 'livewire-panels');

        if ($this->app->runningInConsole()) {
            $this->commands([
                MakePanelCommand::class,
            ]);
        }
    }
}
