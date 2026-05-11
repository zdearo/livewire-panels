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

        $this->app->make(PanelRegistry::class)->register(
            $this->panel(Panel::make())
        );
    }

    abstract public function panel(Panel $panel): Panel;
}
