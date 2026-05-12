<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels;

use Illuminate\Support\ServiceProvider;

abstract class PanelProvider extends ServiceProvider
{
    #[\Override]
    final public function register(): void
    {
        $this->app->singletonIf(PanelRegistry::class);
        $this->app->singletonIf(PanelManager::class);

        $panel = $this->panel(Panel::make());

        $this->app->make(PanelRegistry::class)->register($panel);

        $manager = $this->app->make(PanelManager::class);

        if ($manager->getCurrentPanel() === null || $panel->isDefault) {
            $manager->setCurrentPanel($panel);
        }
    }

    abstract public function panel(Panel $panel): Panel;
}
