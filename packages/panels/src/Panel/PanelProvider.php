<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Panel;

use Illuminate\Support\ServiceProvider;
use Zdearo\LivewirePanels\Routing\PanelRouter;

abstract class PanelProvider extends ServiceProvider
{
    private Panel $registeredPanel;

    #[\Override]
    final public function register(): void
    {
        $this->app->singletonIf(PanelRegistry::class);
        $this->app->singletonIf(PanelManager::class);
        $this->app->singletonIf(PanelRouter::class);

        $panel = $this->panel(Panel::make());
        $this->registeredPanel = $panel;

        $this->app->make(PanelRegistry::class)->register($panel);

        $manager = $this->app->make(PanelManager::class);

        if ($manager->getCurrentPanel() === null || $panel->isDefault) {
            $manager->setCurrentPanel($panel);
        }
    }

    final public function boot(): void
    {
        $this->app->make(PanelRouter::class)->register($this->registeredPanel);
    }

    abstract public function panel(Panel $panel): Panel;
}
