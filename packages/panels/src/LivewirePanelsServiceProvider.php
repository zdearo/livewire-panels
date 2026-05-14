<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\LivewireManager;
use Zdearo\LivewirePanels\Commands\MakePanelCommand;
use Zdearo\LivewirePanels\Panel\PanelManager;
use Zdearo\LivewirePanels\Panel\PanelRegistry;
use Zdearo\LivewirePanels\Routing\PanelRouter;
use Zdearo\LivewirePanels\Routing\PanelUrlGenerator;
use Zdearo\LivewirePanels\Support\Http\OriginalRequestResolver;
use Zdearo\LivewirePanels\Tenant\TenantManager;

final class LivewirePanelsServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register(): void
    {
        $this->app->singleton(PanelRegistry::class);
        $this->app->singleton(PanelManager::class);
        $this->app->singleton(PanelRouter::class);
        $this->app->singleton(PanelUrlGenerator::class);
        $this->app->singleton(TenantManager::class);
        $this->app->scoped('originalRequest', fn (): Request => $this->app
            ->make(OriginalRequestResolver::class)
            ->resolve());
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'livewire-panels');
        Blade::anonymousComponentPath(__DIR__.'/../resources/views', 'livewire-panels');
        $this->app->make(LivewireManager::class)
            ->addNamespace('livewire-panels', viewPath: __DIR__.'/../resources/views/components');

        if ($this->app->runningInConsole()) {
            $this->commands([
                MakePanelCommand::class,
            ]);
        }
    }
}
