<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Panel;

use Illuminate\Support\ServiceProvider;
use Zdearo\LivewirePanels\Icons\PanelsIconManager;
use Zdearo\LivewirePanels\Routing\PanelRouter;
use Zdearo\LivewirePanels\Routing\PanelUrlGenerator;
use Zdearo\LivewirePanels\Tenant\TenantManager;

abstract class PanelProvider extends ServiceProvider
{
    private Panel $registeredPanel;

    #[\Override]
    final public function register(): void
    {
        $this->app->singletonIf(PanelRegistry::class);
        $this->app->singletonIf(PanelManager::class);
        $this->app->singletonIf(PanelRouter::class);
        $this->app->singletonIf(PanelUrlGenerator::class);
        $this->app->singletonIf(TenantManager::class);
        $this->app->singletonIf(PanelsIconManager::class);

        $panel = $this->panel(Panel::make());
        $this->registeredPanel = $panel;

        $this->app->make(PanelRegistry::class)->register($panel);

        $manager = $this->app->make(PanelManager::class);

        if ($manager->currentPanel() === null || $panel->isDefault) {
            $manager->setCurrentPanel($panel);
        }
    }

    final public function boot(): void
    {
        $this->app->make(PanelRouter::class)->register($this->registeredPanel);
    }

    abstract public function panel(Panel $panel): Panel;
}
