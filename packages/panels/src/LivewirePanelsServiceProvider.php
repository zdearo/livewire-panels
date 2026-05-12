<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\LivewireManager;
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
        app(LivewireManager::class)->addNamespace('livewire-panels', viewPath: __DIR__.'/../resources/views/components');

        if ($this->app->runningInConsole()) {
            $this->commands([
                MakePanelCommand::class,
            ]);
        }
    }
}
